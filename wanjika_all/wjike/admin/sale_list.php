<?php

/**
 * ECSHOP 销售明细列表程序
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liubo $
 * $Id: sale_list.php 17217 2011-01-19 06:29:08Z liubo $
*/

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . 'includes/lib_order.php');
require_once(ROOT_PATH . 'includes/lib_main.php');
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/admin/statistic.php');
$smarty->assign('lang', $_LANG);

if (isset($_REQUEST['act']) && ($_REQUEST['act'] == 'query' ||  $_REQUEST['act'] == 'download'))
{
    /* 检查权限 */
    check_authz_json('sale_order_stats');
    if (strstr($_REQUEST['start_date'], '-') === false)
    {
        $_REQUEST['start_date'] = local_date('Y-m-d', $_REQUEST['start_date']);
        $_REQUEST['end_date'] = local_date('Y-m-d', $_REQUEST['end_date']);
    }
    /*------------------------------------------------------ */
    //--Excel文件下载
    /*------------------------------------------------------ */
    if ($_REQUEST['act'] == 'download')
    {
        $file_name = $_REQUEST['start_date'].'_'.$_REQUEST['end_date'] . '_sale';
        $goods_sales_list = get_sale_list(false);
        header("Content-type: application/vnd.ms-excel; charset=utf-8");
        header("Content-Disposition: attachment; filename=$file_name.xls");

        /* 文件标题 */
        echo ecs_iconv(EC_CHARSET, 'GB2312', $_REQUEST['start_date']. $_LANG['to'] .$_REQUEST['end_date']. $_LANG['sales_list']) . "\t\n";

        /* 商品名称,订单号,商品数量,销售价格,销售日期 */
        echo ecs_iconv(EC_CHARSET, 'GB2312', $_LANG['goods_name']) . "\t";
        echo ecs_iconv(EC_CHARSET, 'GB2312', $_LANG['order_sn']) . "\t";
        echo ecs_iconv(EC_CHARSET, 'GB2312', $_LANG['amount']) . "\t";
        echo ecs_iconv(EC_CHARSET, 'GB2312', $_LANG['sell_price']) . "\t";
        echo ecs_iconv(EC_CHARSET, 'GB2312', $_LANG['sell_date']) . "\t";
        echo ecs_iconv(EC_CHARSET, 'GB2312', $_LANG['warehouse_type']) . "\t";
        echo ecs_iconv(EC_CHARSET, 'GB2312', $_LANG['is_overseas']) . "\t";
        echo ecs_iconv(EC_CHARSET, 'GB2312', "一级分类") . "\t";
        echo ecs_iconv(EC_CHARSET, 'GB2312', "二级分类") . "\t";
        echo ecs_iconv(EC_CHARSET, 'GB2312', "三级分类") . "\t";
        echo ecs_iconv(EC_CHARSET, 'GB2312', "四级分类") . "\t";
        echo ecs_iconv(EC_CHARSET, 'GB2312', "五级分类") . "\t";
        echo ecs_iconv(EC_CHARSET, 'GB2312', "负责人") . "\t\n";

        foreach ($goods_sales_list['sale_list_data'] AS $key => $value)
        {
            $cats = get_parent_cats($value['cat_id']);
            $i = count($cats)-1;
            foreach($cats as $k=>$v)
            {
                $catlist[$i] = $v['cat_name'];
                $i--;
            }
            if($catlist ==null)
            {
                $catlist =array();
            }
            ksort($catlist);
            $value['is_overseas'] = $value['is_overseas'] == 1 ? "是" : "否";
            echo ecs_iconv(EC_CHARSET, 'GB2312', $value['goods_name']) . "\t";
            echo ecs_iconv(EC_CHARSET, 'GB2312', '[ ' . $value['order_sn'] . ' ]') . "\t";
            echo ecs_iconv(EC_CHARSET, 'GB2312', $value['goods_num']) . "\t";
            echo ecs_iconv(EC_CHARSET, 'GB2312', $value['sales_price']) . "\t";
            echo ecs_iconv(EC_CHARSET, 'GB2312', $value['sales_time']) . "\t";
            echo ecs_iconv(EC_CHARSET, 'GB2312', $value['warehouse_type']) . "\t";
            echo ecs_iconv(EC_CHARSET, 'GB2312', $value['is_overseas']) . "\t";
            echo ecs_iconv(EC_CHARSET, 'GB2312', $catlist['0']) . "\t";
            echo ecs_iconv(EC_CHARSET, 'GB2312', $catlist['1']) . "\t";
            echo ecs_iconv(EC_CHARSET, 'GB2312', $catlist['2']) . "\t";
            echo ecs_iconv(EC_CHARSET, 'GB2312', $catlist['3']) . "\t";
            echo ecs_iconv(EC_CHARSET, 'GB2312', $catlist['4']) . "\t";
            echo ecs_iconv(EC_CHARSET, 'GB2312', $value['person_charge']) . "\t";
            echo "\n";
            unset($catlist);
        }
        exit;
    }
    $sale_list_data = get_sale_list();
    $smarty->assign('goods_sales_list', $sale_list_data['sale_list_data']);
    $smarty->assign('filter',       $sale_list_data['filter']);
    $smarty->assign('record_count', $sale_list_data['record_count']);
    $smarty->assign('page_count',   $sale_list_data['page_count']);

    make_json_result($smarty->fetch('sale_list.htm'), '', array('filter' => $sale_list_data['filter'], 'page_count' => $sale_list_data['page_count']));
}
/*------------------------------------------------------ */
//--商品明细列表
/*------------------------------------------------------ */
else
{
    /* 权限判断 */
    admin_priv('sale_order_stats');
    /* 时间参数 */
    if (!isset($_REQUEST['start_date']))
    {
        $start_date = local_strtotime('-7 days');
    }
    if (!isset($_REQUEST['end_date']))
    {
        $end_date = local_strtotime('today');
    }
    
    $sale_list_data = get_sale_list();
    /* 赋值到模板 */
    $smarty->assign('filter',       $sale_list_data['filter']);
    $smarty->assign('record_count', $sale_list_data['record_count']);
    $smarty->assign('page_count',   $sale_list_data['page_count']);
    $smarty->assign('goods_sales_list', $sale_list_data['sale_list_data']);
    $smarty->assign('ur_here',          $_LANG['sell_stats']);
    $smarty->assign('full_page',        1);
    $smarty->assign('start_date',       local_date('Y-m-d', $start_date));
    $smarty->assign('end_date',         local_date('Y-m-d', $end_date));
    $smarty->assign('ur_here',      $_LANG['sale_list']);
    $smarty->assign('cfg_lang',     $_CFG['lang']);
    $smarty->assign('action_link',  array('text' => $_LANG['down_sales'],'href'=>'#download'));

    /* 显示页面 */
    assign_query_info();
    $smarty->display('sale_list.htm');
}
/*------------------------------------------------------ */
//--获取销售明细需要的函数
/*------------------------------------------------------ */
/**
 * 取得销售明细数据信息
 * @param   bool  $is_pagination  是否分页
 * @return  array   销售明细数据
 */
function get_sale_list($is_pagination = true){

    /* 时间参数 */
    $filter['start_date'] = empty($_REQUEST['start_date']) ? local_strtotime('-7 days') : local_strtotime($_REQUEST['start_date']);
    $filter['end_date'] = empty($_REQUEST['end_date']) ? local_strtotime('today') : local_strtotime($_REQUEST['end_date']);
    $filter['goods_name'] = empty($_REQUEST['goods_name']) ? "" : $_REQUEST['goods_name'];

    /* 查询数据的条件 */
    $where = " WHERE og.order_id = oi.order_id". order_query_sql('finished_pay', 'oi.') .
        " AND oi.add_time >= '".$filter['start_date']."' AND oi.add_time < '" . ($filter['end_date'] + 86400) . "'";
    if($filter['goods_name'])
    {
        $where .= " AND og.goods_name LIKE'%". $filter['goods_name'] ."%' ";
    }
    $sql = "SELECT COUNT(og.goods_id) FROM " .
        $GLOBALS['ecs']->table('order_info') . ' AS oi,'.
        $GLOBALS['ecs']->table('order_goods') . ' AS og LEFT JOIN  '.
        $GLOBALS['ecs']->table('goods_supplier') . ' AS gs ON og.supplier_id=gs.type_id '.
        $where;
    $filter['record_count'] = $GLOBALS['db']->getOne($sql);

    /* 分页大小 */
    $filter = page_and_size($filter);

    $sql = 'SELECT og.goods_id,gs.warehouse_type,gs.is_overseas,gs.person_charge,g.cat_id, og.goods_sn, og.goods_name, og.goods_number AS goods_num, og.goods_price '.
        'AS sales_price, oi.add_time AS sales_time, oi.order_id, oi.order_sn '.
        "FROM ". $GLOBALS['ecs']->table('order_info')." AS oi, ". $GLOBALS['ecs']->table('goods')." AS g, ".
        $GLOBALS['ecs']->table('order_goods')." AS og LEFT JOIN ".
        $GLOBALS['ecs']->table('goods_supplier') . ' AS gs ON og.supplier_id=gs.type_id '.
           $where. " AND og.goods_id=g.goods_id ORDER BY goods_num DESC, goods_num DESC";
    if ($is_pagination)
    {
        $sql .= " LIMIT " . $filter['start'] . ', ' . $filter['page_size'];
    }

    $sale_list_data = $GLOBALS['db']->getAll($sql);

    foreach ($sale_list_data as $key => $item)
    {
        $sale_list_data[$key]['sales_price'] = price_format($sale_list_data[$key]['sales_price']);
        $sale_list_data[$key]['sales_time']  = local_date($GLOBALS['_CFG']['time_format'], $sale_list_data[$key]['sales_time']);
    }
    $arr = array('sale_list_data' => $sale_list_data, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
    return $arr;
}
?>