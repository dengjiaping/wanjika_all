<?php

/**
 * ECSHOP 管理中心供货商管理
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: wanglei $
 * $Id: suppliers.php 15013 2009-05-13 09:31:42Z wanglei $
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');

define('SUPPLIERS_ACTION_LIST', 'delivery_view,back_view');
/*------------------------------------------------------ */
//-- 供货商列表
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{
     /* 检查权限 */
     admin_priv('suppliers_manage');

    /* 查询 */
    $result = suppliers_list();

    /* 模板赋值 */
    $smarty->assign('ur_here', $_LANG['suppliers_list']); // 当前导航
    $smarty->assign('action_link', array('href' => 'suppliers.php?act=add', 'text' => $_LANG['add_suppliers']));

    $smarty->assign('full_page',        1); // 翻页参数

    $smarty->assign('suppliers_list',    $result['result']);
    $smarty->assign('filter',       $result['filter']);
    $smarty->assign('record_count', $result['record_count']);
    $smarty->assign('page_count',   $result['page_count']);
    $smarty->assign('sort_suppliers_id', '<img src="images/sort_desc.gif">');

    /* 显示模板 */
    assign_query_info();
    $smarty->display('suppliers_list.htm');
}
//添加仓库
elseif($_REQUEST['act'] == 'add_warehouse')
{
    $warehouse = empty($_REQUEST['warehouse']) ? '' : json_str_iconv(trim($_REQUEST['warehouse']));
    $warehousetype = empty($_REQUEST['warehousetype']) ? '' : json_str_iconv(trim($_REQUEST['warehousetype']));
    $isoverseas = empty($_REQUEST['isoverseas']) ? '' : json_str_iconv(intval($_REQUEST['isoverseas']));

    if(warehouse_exists($warehouse,$warehousetype))
    {
        make_json_error('已存在相同仓库');
    }
    else
    {
        $sql = "INSERT INTO " . $ecs->table('goods_supplier') . "(supplier_type,warehouse_type,is_overseas)" .
            "VALUES ( '$warehousetype','$warehouse','$isoverseas')";

        $db->query($sql);
        $warehouse_id = $db->insert_id();

        $arr = array("id"=>$warehouse_id, "brand"=>$warehouse);

        make_json_result($arr);
    }
}

?>