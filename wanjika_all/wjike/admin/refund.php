<?php

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');

/*------------------------------------------------------ */
//-- 退款列表
/*------------------------------------------------------ */

if ($_REQUEST['act'] == 'refund_list')
{
    $smarty->assign('ur_here',      $_LANG['16_refund_list']);
    if ($_REQUEST['download']=='下载') {
        // 导出到文本;
        ini_set('memory_limit',-1);
        set_time_limit(0);
        $file_name = "refund_list_". date ( "Ymd" ) . ".csv";
        $str_down = "订单号\t商品名称\t货号\t退货数量\t礼品卡退款金额\t现金退款金额\t安抚费金额\t安抚费原因\t退款金额总计\t退款时间";

        $list = refund_list();

        reset($list['refund_list']);

        $refund_list = array();
        while ( list ( $key, $val ) = each ( $list['refund_list'] ) ) {
            $str_down .= "\n" . $val ['order_sn']. "\t". $val['goods_name'] . "\t". $val['goods_sn'] . "\t" . $val['refund_goods_num']. "\t" . $val['refund_gift'] . "\t" . $val['refund_money'] . "\t" . $val['comfort_money'] . "\t" . $val['comfort_reason'] . "\t" . $val ['total']. "\t". $val ['add_time'];
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

    $refund_list = refund_list();

    $smarty->assign('refund_list',    $refund_list['refund_list']);
    $smarty->assign('filter',       $refund_list['filter']);
    $smarty->assign('record_count', $refund_list['record_count']);
    $smarty->assign('page_count',   $refund_list['page_count']);
    $smarty->assign('order_sn', $_REQUEST['order_sn']);
    $smarty->assign('goods_sn', $_REQUEST['goods_sn']);
    $smarty->assign('start_time', $_REQUEST['start_time']);
    $smarty->assign('end_time', $_REQUEST['end_time']);
    $smarty->assign('full_page',    1);

    assign_query_info();
    $smarty->display('refund_list.htm');
}


/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    $list = refund_list();

    $smarty->assign('refund_list',   $list['refund_list']);
    $smarty->assign('filter',       $list['filter']);
    $smarty->assign('record_count', $list['record_count']);
    $smarty->assign('page_count',   $list['page_count']);
    make_json_result($smarty->fetch('refund_list.htm'), '', array('filter' => $list['filter'], 'page_count' => $list['page_count']));
}
/**
 *  返回支付流水号列表
 *
 * @access  public
 * @param
 *
 * @return void
 */
function refund_list()
{
    $result = get_filter();
    if ($result === false)
    {
        /* 过滤信息 */
        $filter['order_sn'] = empty($_REQUEST['order_sn']) ? '' : trim($_REQUEST['order_sn']);
        $filter['goods_sn'] = empty($_REQUEST['goods_sn']) ? '' : trim($_REQUEST['goods_sn']);

        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'add_time' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $filter['start_time'] = empty($_REQUEST['start_time']) ? '' : (strpos($_REQUEST['start_time'], '-') > 0 ?  local_strtotime($_REQUEST['start_time']) : $_REQUEST['start_time']);
        $filter['end_time'] = empty($_REQUEST['end_time']) ? '' : (strpos($_REQUEST['end_time'], '-') > 0 ?  local_strtotime($_REQUEST['end_time']) : $_REQUEST['end_time']);


        $where = ' WHERE 1 ';
        if ($filter['order_sn'])
        {
            $where .= " AND r.order_sn LIKE '%" . mysql_like_quote($filter['order_sn']) . "%'";
        }
        if ($filter['goods_sn'])
        {
            $where .= " AND g.goods_sn LIKE '%" . mysql_like_quote($filter['goods_sn']) . "%'";
        }
        if ($filter['start_time'])
        {
            $where .= " AND r.add_time >= '$filter[start_time]'";
        }
        if ($filter['end_time'])
        {
            $where .= " AND r.add_time <= '$filter[end_time]'";
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
        $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('refund_log'). " AS r " .
            " INNER JOIN " .$GLOBALS['ecs']->table('goods'). " AS g ON r.refund_goods_id=g.goods_id". $where;

        $filter['record_count']   = $GLOBALS['db']->getOne($sql);
        $filter['page_count']     = $filter['record_count'] > 0 ? ceil($filter['record_count'] / $filter['page_size']) : 1;

        /* 查询 */
        $sql = "SELECT r.*,g.goods_name,g.goods_sn FROM " . $GLOBALS['ecs']->table('refund_log')  . " AS r " .
            " INNER JOIN " .$GLOBALS['ecs']->table('goods'). " AS g ON r.refund_goods_id=g.goods_id"
            . $where .
            " ORDER BY $filter[sort_by] $filter[sort_order] ".
            " LIMIT " . ($filter['page'] - 1) * $filter['page_size'] . ",$filter[page_size]";

        set_filter($filter, $sql);
    }
    else
    {
        $sql    = $result['sql'];
        $filter = $result['filter'];
    }

    $refund_list = $GLOBALS['db']->getAll($sql);


    /* 格式话数据 */
    foreach ($refund_list AS $key => $value)
    {
        $refund_list[$key]['add_time'] = local_date('Y-m-d H:i:s', $value['add_time']);
        $refund_list[$key]['total'] = $value['refund_gift']+$value['refund_money']+$value['comfort_money'];
    }
    $arr = array('refund_list' => $refund_list, 'filter' => $filter,
        'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}

?>
