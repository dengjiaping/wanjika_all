<?php

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
/*------------------------------------------------------ */
//-- 添加支付流水号页面
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'tradeno_add')
{
    $smarty->assign('ur_here',          $_LANG['12_add_tradeno']);
    $smarty->assign('action_link',      array('text' => $_LANG['11_tradeno_list'], 'href'=>'pay_tradeno_list.php?act=tradeno_list'));

    $smarty->display('tradeno_add.htm');
}

/*------------------------------------------------------ */
//-- 支付流水号列表
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'tradeno_list')
{
    $smarty->assign('ur_here',      $_LANG['11_tradeno_list']);
    $smarty->assign('action_link',  array('text' => $_LANG['12_add_tradeno'], 'href'=>'pay_tradeno_list.php?act=tradeno_add'));

    $tradeno_list = tradeno_list();

    $smarty->assign('tradeno_list',    $tradeno_list['tradeno_list']);
    $smarty->assign('filter',       $tradeno_list['filter']);
    $smarty->assign('record_count', $tradeno_list['record_count']);
    $smarty->assign('page_count',   $tradeno_list['page_count']);
    $smarty->assign('order_sn', $_REQUEST['order_sn']);
    $smarty->assign('tradeno', $_REQUEST['tradeno']);
    $smarty->assign('status', $_REQUEST['status']);
    $smarty->assign('start_time', $_REQUEST['start_time']);
    $smarty->assign('end_time', $_REQUEST['end_time']);
    $smarty->assign('full_page',    1);

    assign_query_info();
    $smarty->display('tradeno_list.htm');
}

/*------------------------------------------------------ */
//-- 添加支付流水号
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'insert_tradeno')
{
    $tradenos = empty($_POST['tno']) ? "" : trim($_POST['tno']);
    if (empty($tradenos))
    {
        sys_msg('参数错误', 1);
    }
    $list = explode(',',$tradenos);
    $list = array_unique($list);
    $addtime = gmtime();

    $sql = 'INSERT INTO '. $ecs->table('pay_tradeno') . " (`pay_trade_no`, `addtime`, `status`) VALUES";
    foreach($list as $val){
        $val = trim($val);
        $sql.="('$val', '$addtime', '0'),";
    }
    $sql = substr($sql,0,strlen($sql)-1);

    /* 提示信息 */
    $link[] = array('text' => $_LANG['go_back'], 'href'=>'pay_tradeno_list.php?act=tradeno_list');
    $msg = '操作成功';

    $r = $db->query($sql);
    if(!$r){
        $msg = '操作失败';
    }

    sys_msg($msg, 0, $link);
}


/*------------------------------------------------------ */
//-- 批量生成支付流水号
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'make_tradeno')
{
    $num = empty($_POST['mtno']) ? 0 : intval($_POST['mtno']);
    if ($num <= 0)
    {
        sys_msg('参数错误', 1);
    }
    $tradeno_list = array();
    do{
        $addtime = gmtime();
        $str = "110074011500800001";
        $tradeno = date('Ymd',$addtime).$str.rand(88,90).rand(0,9).rand(0,9).rand(0,9).rand(0,9);
        array_push($tradeno_list,$tradeno);
        array_unique($tradeno_list);
    }while(count($tradeno_list) < $num);

    $sql = 'INSERT INTO '. $ecs->table('pay_tradeno') . " (`pay_trade_no`, `addtime`, `status`) VALUES";
    foreach($tradeno_list as $val){
        $val = trim($val);
        $sql.="('$val', '$addtime', '0'),";
    }
    $sql = substr($sql,0,strlen($sql)-1);

    /* 提示信息 */
    $link[] = array('text' => $_LANG['go_back'], 'href'=>'pay_tradeno_list.php?act=tradeno_list');
    $msg = '操作成功';

    $r = $db->query($sql);
    if(!$r){
        $msg = '操作失败';
    }

    sys_msg($msg, 0, $link);
}

/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    $list = tradeno_list();

    $smarty->assign('tradeno_list',   $list['tradeno_list']);
    $smarty->assign('filter',       $list['filter']);
    $smarty->assign('record_count', $list['record_count']);
    $smarty->assign('page_count',   $list['page_count']);
    make_json_result($smarty->fetch('tradeno_list.htm'), '', array('filter' => $list['filter'], 'page_count' => $list['page_count']));
}
/**
 *  返回支付流水号列表
 *
 * @access  public
 * @param
 *
 * @return void
 */
function tradeno_list()
{
    $result = get_filter();
    if ($result === false)
    {
        /* 过滤信息 */
        $filter['order_sn'] = empty($_REQUEST['order_sn']) ? '' : trim($_REQUEST['order_sn']);
        $filter['tradeno'] = empty($_REQUEST['tradeno']) ? '' : trim($_REQUEST['tradeno']);
        $filter['status'] = (empty($_REQUEST['status']) && $_REQUEST['status'] !=0) ? -1 : intval($_REQUEST['status']);

        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'usedtime' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $filter['start_time'] = empty($_REQUEST['start_time']) ? '' : (strpos($_REQUEST['start_time'], '-') > 0 ?  local_strtotime($_REQUEST['start_time']) : $_REQUEST['start_time']);
        $filter['end_time'] = empty($_REQUEST['end_time']) ? '' : (strpos($_REQUEST['end_time'], '-') > 0 ?  local_strtotime($_REQUEST['end_time']) : $_REQUEST['end_time']);


        $where = 'WHERE 1 ';
        if ($filter['order_sn'])
        {
            $where .= " AND order_sn LIKE '%" . mysql_like_quote($filter['order_sn']) . "%'";
        }
        if ($filter['tradeno'])
        {
            $where .= " AND pay_trade_no LIKE '%" . mysql_like_quote($filter['tradeno']) . "%'";
        }
        if ($filter['status'] != -1 && $filter['status'] != null)
        {
            $where .= " AND status = '$filter[status]'";
        }
        if ($filter['status'] == 0)
        {
            $where .= " AND status = '$filter[status]'";
        }
        if ($filter['start_time'])
        {
            $where .= " AND usedtime >= '$filter[start_time]'";
        }
        if ($filter['end_time'])
        {
            $where .= " AND usedtime <= '$filter[end_time]'";
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
        $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('pay_tradeno'). $where;

        $filter['record_count']   = $GLOBALS['db']->getOne($sql);
        $filter['page_count']     = $filter['record_count'] > 0 ? ceil($filter['record_count'] / $filter['page_size']) : 1;

        /* 查询 */
        $sql = "SELECT * FROM " . $GLOBALS['ecs']->table('pay_tradeno') .  $where .
            " ORDER BY $filter[sort_by] $filter[sort_order] ".
            " LIMIT " . ($filter['page'] - 1) * $filter['page_size'] . ",$filter[page_size]";

        set_filter($filter, $sql);
    }
    else
    {
        $sql    = $result['sql'];
        $filter = $result['filter'];
    }

    $tradeno_list = $GLOBALS['db']->getAll($sql);


    /* 格式话数据 */
    foreach ($tradeno_list AS $key => $value)
    {
        $tradeno_list[$key]['addtime'] = local_date('Y-m-d H:i:s', $value['addtime']);
        if(!empty($value['usedtime'])){
            $tradeno_list[$key]['usedtime'] = local_date('Y-m-d H:i:s', $value['usedtime']);
        }
        if ($value['status'] == 1)
        {
            $tradeno_list[$key]['status'] = "使用成功";
        }
        else if ($value['status'] == 2)
        {
            $tradeno_list[$key]['status'] = "使用失败";
        }
        else
        {
            $tradeno_list[$key]['status'] = "未使用";
        }
    }
    $arr = array('tradeno_list' => $tradeno_list, 'filter' => $filter,
        'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}

?>
