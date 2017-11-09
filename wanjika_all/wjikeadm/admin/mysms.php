<?php

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . 'includes/cls_mysms.php');

$action = isset($_REQUEST['act']) ? $_REQUEST['act'] : 'display_my_info';
$mysms = new mysms();

switch ($action)
{
    /* 显示短信发送界面，如果尚未注册或启用短信服务则显示注册界面。 */
    case 'display_send_ui' :
        /* 检查权限 */
         admin_priv('sms_send');
        $smarty->assign('ur_here', $_LANG['08_sms_sendsms']);
        $special_ranks = get_rank_list();
        $send_rank['1_0'] = $_LANG['user_list'];
        foreach($special_ranks as $rank_key => $rank_value)
        {
            $send_rank['2_' . $rank_key] = $rank_value;
        }
        assign_query_info();
        $smarty->assign('send_rank',   $send_rank);
        $smarty->display('sms_send_market.htm');

        break;

    /* 发送结果列表 */
    case 'list' :
        if ($_REQUEST['download']=='下载') {
            // 导出到文本;
            $file_name = "sendresult_list_". date ( "Ymd" ) . ".csv";
            $str_down = "ID\t手机号\t短信内容\t发送时间\t发送结果";

            $list = sendresult_list();
            while ( list ( $key, $val ) = each ( $list['results'] ) ) {
                $status = ($val['status'] == '1' ? '成功' : '失败');
                $str_down .= "\n" . $val ['id']  . "\t". $val['tel'] . "\t" . $val['content'] . "\t"  . $val['sendtime'] . "\t" . $status;
            }

            header ( 'Content-Description: File Transfer' );
            header ( 'Content-Type: application/vnd.ms-excel ; charset=UTF-16LE' );
            header ( 'Content-Disposition: attachment; filename=' . $file_name );
            // header ( 'Content-Transfer-Encoding: binary' );
            header ( 'Expires: 0' );
            header ( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
            header ( 'Pragma: public' );
            //header ( 'Content-Length: ' . strlen ( $str_down ) );
            ob_clean ();
            flush ();

            //添加BOM，保证csv能够显示utf8的字符
            echo(chr(255).chr(254));
            echo(mb_convert_encoding($str_down,"UTF-16LE","UTF-8"));

            exit;
            // header("Location: downtest.php");
        }

        $smarty->assign('full_page',        1);
        $list = sendresult_list();
        $smarty->assign('ur_here',      $_LANG['10_sms_sendresult']);
        $smarty->assign('sendresult_list',   $list['results']);
        $smarty->assign('filter',       $list['filter']);
        $smarty->assign('tel', $_REQUEST['tel']);
        $smarty->assign('start_time', $_REQUEST['start_time']);
        $smarty->assign('end_time', $_REQUEST['end_time']);
        $smarty->assign('status', $_REQUEST['status']);
        $smarty->assign('record_count', $list['record_count']);
        $smarty->assign('page_count',   $list['page_count']);

        /* 显示模板 */
        assign_query_info();
        $smarty->display('sms_send_result.htm');

        break;

    case 'query' :
        $list = sendresult_list();

        $smarty->assign('sendresult_list',   $list['results']);
        $smarty->assign('filter',       $list['filter']);
        $smarty->assign('record_count', $list['record_count']);
        $smarty->assign('page_count',   $list['page_count']);
        make_json_result($smarty->fetch('sms_send_result.htm'), '', array('filter' => $list['filter'], 'page_count' => $list['page_count']));

        break;

    /* 发送短信 */
    case 'send_sms' :
        $min_num = isset($_POST['min_num']) && (!empty($_POST['min_num']))  ? $_POST['min_num']    : 0;
        $max_num = isset($_POST['max_num']) && (!empty($_POST['max_num']))  ? $_POST['max_num']    : 1000000;

        $send_rank = isset($_POST['send_rank']) ? $_POST['send_rank'] : 0;

        if ($send_rank != 0)
        {
            $rank_array = explode('_', $send_rank);

            if($rank_array['0'] == 1)
            {
                $sql = 'SELECT mobile_phone FROM ' . $ecs->table('users') . "WHERE mobile_phone <>'' ";
                $row = $db->query($sql);
                while ($rank_rs = $db->fetch_array($row))
                {
                    $value[] = $rank_rs['mobile_phone'];
                }
            }
            else
            {
                $rank_sql = "SELECT * FROM " . $ecs->table('user_rank') . " WHERE rank_id = '" . $rank_array['1'] . "'";
                $rank_row = $db->getRow($rank_sql);
                //$sql = 'SELECT mobile_phone FROM ' . $ecs->table('users') . "WHERE mobile_phone <>'' AND rank_points > " .$rank_row['min_points']." AND rank_points < ".$rank_row['max_points']." ";

                if($rank_row['special_rank']==1) 
                {
                    $sql = 'SELECT mobile_phone FROM ' . $ecs->table('users') . " WHERE mobile_phone <>'' AND user_rank = '" . $rank_array['1'] . "'";
                }
                else
                {
                    $min_val = $min_num > $rank_row['min_points'] ? $min_num : $rank_row['min_points'];
                    $max_val = $max_num < $rank_row['max_points'] ? $max_num : $rank_row['max_points'];
                    $sql = 'SELECT mobile_phone FROM ' . $ecs->table('users') . "WHERE mobile_phone <>'' AND rank_points > " .$min_val." AND rank_points < ".$max_val." ";
                }
                
                $row = $db->query($sql);
                
                while ($rank_rs = $db->fetch_array($row))
                {
                    $value[] = $rank_rs['mobile_phone'];
                }
            }
        }
        else{
            $sql = 'SELECT mobile_phone FROM ' . $ecs->table('users') . "WHERE mobile_phone <>'' AND rank_points > " .$min_num." AND rank_points < ".$max_num." ";

            $row = $db->query($sql);

            while ($rank_rs = $db->fetch_array($row))
            {
                $value[] = $rank_rs['mobile_phone'];
            }
        }
        $sumcount = count($value);
        if(count($value) > 0){
            $phone_arr = array_chunk($value, 5000);

            $sql = "SELECT user_id, user_name, email, nav_list ".
                "FROM " .$ecs->table('admin_user'). " WHERE user_id = '".$_SESSION['admin_id']."'";
            $user_info = $db->getRow($sql);

            $msg = isset($_POST['msg']) ? $_POST['msg'] : '';
            $errorcount = 0;

            foreach($phone_arr as $cell){
                $phone_str = implode(',',$cell);
                $result = $mysms->send($phone_str,$msg,'sdkapt',1);
                if(!$result['status']){
                    $errorcount +=count($cell);
                }
//                $phone_str = implode(',',$cell);
//                $sql = "INSERT INTO " . $ecs->table('sms_sendtask') . "(counts,sms_content,sms_phonelist,report_email)
//            VALUES ('" . count($cell) . "', '" . $msg . "', '" . $phone_str . "', '" . $user_info['email'] . "')";
//                $db->query($sql);
            }
            $r = $mysms->sendEmail($user_info['email'],$sumcount,$errorcount,date('Y-m-d H:i:s',time()));
            $sys_msg = $_LANG['send_ok'];
            $pos= stripos($r, 'success');//判断是否存在   返回bool值
            if ($pos === false) {
                $sys_msg = $_LANG['send_mail_error'].$r;
            }

            $link[] = array('text'  =>  $_LANG['back'] . $_LANG['08_sms_sendsms'],
                'href'  =>  'mysms.php?act=display_send_ui');

            sys_msg($sys_msg, 0, $link);
        }
        else{
            sys_msg($_LANG['send_sms_error'], 0, $link);
        }

        break;
}

function sendresult_list()
{
    $result = get_filter();
    if ($result === false)
    {
        /* 过滤信息 */
        $filter['tel'] = empty($_REQUEST['tel']) ? '' : trim($_REQUEST['tel']);
        $filter['start_time'] = empty($_REQUEST['start_time']) ? '' : (strpos($_REQUEST['start_time'], '-') > 0 ?  local_strtotime($_REQUEST['start_time']) : $_REQUEST['start_time']);
        $filter['end_time'] = empty($_REQUEST['end_time']) ? '' : (strpos($_REQUEST['end_time'], '-') > 0 ?  local_strtotime($_REQUEST['end_time']) : $_REQUEST['end_time']);
        $filter['status'] = $_REQUEST['status'];
        
        $where = 'WHERE 1 ';
        if ($filter['tel'])
        {
            $where .= " AND tel LIKE '%" . mysql_like_quote($filter['tel']) . "%'";
        }
        if ($filter['start_time'])
        {
            $where .= " AND sendtime >= '$filter[start_time]'";
        }
        if ($filter['end_time'])
        {
            $where .= " AND sendtime <= '$filter[end_time]'";
        }
        if ($filter['status'] != -1 && $filter['status'] != null)
        {
            $where .= " AND status = '$filter[status]'";
        }

        /* 分页大小 */

        $filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page']) <= 0) ? 1 : intval($_REQUEST['page']);

        if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0)
        {
            $filter['page_size'] = intval($_REQUEST['page_size']);
        }
        elseif (isset($_COOKIE['ECSCP']['page_size']) && intval($_COOKIE['ECSCP']['page_size']) > 0)
        {
            $filter['page_size'] = intval($_COOKIE['ECSCP']['page_size']);
        }
        else
        {
            $filter['page_size'] = 15;
        }
        /* 记录总数 */
        $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('sms_result'). $where;

        $filter['record_count']   = $GLOBALS['db']->getOne($sql);
        $filter['page_count']     = $filter['record_count'] > 0 ? ceil($filter['record_count'] / $filter['page_size']) : 1;

        /* 查询 */
        $sql = "SELECT * FROM " . $GLOBALS['ecs']->table('sms_result') .  $where .
            " ORDER BY sendtime desc ".
            " LIMIT " . ($filter['page'] - 1) * $filter['page_size'] . ",$filter[page_size]";

        set_filter($filter, $sql);
    }
    else
    {
        $sql    = $result['sql'];
        $filter = $result['filter'];
    }

    $row = $GLOBALS['db']->getAll($sql);

    /* 格式话数据 */
    foreach ($row AS $key => $value)
    {
        //ecshop服务器和本地时间差修正显示
        $row[$key]['sendtime'] = date('Y-m-d H:i', $value['sendtime']);
        if(strlen($row[$key]['tel']) > 11){
            $row[$key]['tel'] = substr($row[$key]['tel'],0,11).'...';
        }
    }
    $arr = array('results' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}

?>