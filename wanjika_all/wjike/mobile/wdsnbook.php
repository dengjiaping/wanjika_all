<?php

/**
 * 领取红包页面
 *
 * @author qihua
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
if ((DEBUG_MODE & 2) != 2)
{
    $smarty->caching = true;
}
if ($_SESSION['user_id'] > 0)
{
    $smarty->assign('user_name', $_SESSION['user_name']);
}
$get_bonus_res = '';
if ($_REQUEST['act'] == 'getbonus')
{
    include_once(dirname(__FILE__) . '/../includes/cls_json.php');

    $json = new JSON;
    $r['errorcode']   = 0;
    $r['msg'] = '领取成功！';
    $user_id = $_SESSION['user_id'];
    if ($user_id == 0)
    {
        $_SESSION['back_act'] = 'wdsnbook.php';
        $r['errorcode']   = 2;
        $r['msg'] = '请注册！';
        die($json->encode($r));
    }

//    $bonus_type = json_str_iconv($_REQUEST['bonus_type']);
    $bonus_type=array(52);
    if ($bonus_type[0] == 0)
    {
        //提示错误参数
        $r['errorcode']   = 1;
        $r['msg'] = '参数错误！';
        die($json->encode($r));
    }
    $sql = "SELECT reg_time ".
        " FROM " . $GLOBALS['ecs'] ->table('users').
        " WHERE user_id = '$user_id' LIMIT 1";
    $reg_time = $GLOBALS['db']->getOne($sql);

    $sql = "SELECT send_start_date ".
        " FROM " . $GLOBALS['ecs'] ->table('bonus_type').
        " WHERE type_id = '$bonus_type[0]' LIMIT 1";
    $send_start_date = $GLOBALS['db']->getOne($sql);
//    if ($reg_time < $send_start_date)
//    {
//        //提示只允许新用户参加
//        $r['errorcode']   = 1;
//        $r['msg'] = '该活动只允许新用户参加！';
//        die($json->encode($r));
//    }
    $sql = "SELECT send_end_date ".
        " FROM " . $GLOBALS['ecs'] ->table('bonus_type').
        " WHERE type_id = '$bonus_type[0]' LIMIT 1";
    $send_end_date = $GLOBALS['db']->getOne($sql);
    if ($reg_time > $send_end_date)
    {
        //优惠券过期
        $r['errorcode']   = 1;
        $r['msg'] = '活动已过期！';
        die($json->encode($r));
    }
    $sql = "SELECT count(*) FROM " . $ecs->table('user_bonus') .
        " WHERE user_id = '{$user_id}' AND bonus_type_id = '{$bonus_type[0]}'";
    if ($db->getOne($sql) > 0)
    {

        $r['errorcode']   = 1;
        $r['msg'] = '不要太贪心哦，您已经领取了优惠券！';
        die($json->encode($r));
    }
    else
    {
        $now = gmtime();
        foreach($bonus_type AS $value)
        {
            $sql1 = "SELECT * FROM " . $GLOBALS['ecs']->table('bonus_type') .
                " WHERE type_id = '$value'";
            $bonus =  $GLOBALS['db']->getRow($sql1);
            $sql = "INSERT INTO " . $ecs->table('user_bonus') .
                "(bonus_type_id, bonus_sn, user_id, used_time, order_id, emailed,binding_time,use_start_datetime,use_end_datetime) " .
                "VALUES ('$value', 0, '$user_id', 0, 0, " .BONUS_MAIL_FAIL. ",'$now','$bonus[use_start_date]','$bonus[use_end_date]')";
            $db->query($sql);
            $new_bonus_id = $db->insert_id();
            if($bonus['use_datetime'] == 1)
            {
                //按起止日期计算
                $use_effective_date = $now+$bonus['use_effective_date']*24 * 3600;
                $m_sql = "UPDATE " .$GLOBALS['ecs']->table('user_bonus'). " SET ".
                    "use_start_datetime  = '$now', ".
                    "use_end_datetime    = '$use_effective_date' ".
                    "WHERE bonus_id   = '$new_bonus_id'";
                $GLOBALS['db']->query($m_sql);
            }
        }


        $r['errorcode']   = 0;
        $r['msg'] = '优惠券领取成功！';
        die($json->encode($r));
    }
}

//if (!$smarty->is_cached('new_user.html'))
//{
// 	assign_template();
//	$position = assign_ur_here(0, '领取优惠券');
//
//	$smarty->assign('page_title', $position['title']);    // 页面标题
//	$smarty->assign('ur_here',    $position['ur_here']);  // 当前位置
//	$smarty->assign('categories', get_categories_tree()); // 分类树
//	$smarty->assign('helps',      get_shop_help());       // 网店帮助
//	$smarty->assign('get_bonus_res', $get_bonus_res);
//    $smarty->assign('action_url', 'wdsnbook.php');
//}

$usermembers_99 = get_all_user_members(99);
$members_flag_99 = false;
if(!empty($usermembers_99)){
    $members_flag_99 = true;
    $smarty->assign('goods_id_one',$usermembers_99['goods_id']);
}
$smarty->display('cloundquan.html');

?>