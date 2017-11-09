<?php

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');

if ($_REQUEST['act'] == 'list')
{

    $smarty->assign('full_page',        1);
    $list = chargeconfig_list();

    $smarty->assign('ur_here',      $_LANG['58_charge_calls_config_list']);
    $smarty->assign('chargeconfig_list',   $list['results']);
    $smarty->assign('filter',       $list['filter']);
    $smarty->assign('facevalue', $_REQUEST['facevalue']);
    $smarty->assign('province', $_REQUEST['province']);
    $smarty->assign('catname', $_REQUEST['catname']);
    $smarty->assign('record_count', $list['record_count']);
    $smarty->assign('page_count',   $list['page_count']);

    /* 显示模板 */
    assign_query_info();
    $smarty->display('charge_list.htm');
}

elseif ($_REQUEST['act'] == 'add' || $_REQUEST['act'] == 'edit')
{
    if($_REQUEST['act'] == 'edit'){
        $id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);
        $sql = "SELECT * FROM " . $ecs->table('goods_charge') . " WHERE id = '$id' LIMIT 1";
        $res = $db->getAll($sql);
        if(count($res) == 1){
            $smarty->assign('facevalue', $res[0]['facevalue']);
            $smarty->assign('province', $res[0]['province']);
            $smarty->assign('catname', $res[0]['catname']);
            $smarty->assign('price', $res[0]['price']);
        }
    }
    $smarty->assign('ur_here', $_LANG['59_charge_calls_config_add']);
    assign_query_info();
    $smarty->display('charge_info.htm');
}

elseif ($_REQUEST['act'] == 'insert')
{
    $facevalue = empty($_REQUEST['facevalue']) ? 0 : floatval($_REQUEST['facevalue']);
    $price = empty($_REQUEST['price']) ? 0 : floatval($_REQUEST['price']);
    $catname = empty($_REQUEST['catname']) ? '' : trim($_REQUEST['catname']);
    $province = empty($_REQUEST['province']) ? '' : trim($_REQUEST['province']);

    $sql = "SELECT COUNT(*) FROM " . $ecs->table('goods_charge') . " WHERE facevalue = '$facevalue' AND province = '$province' AND catname = '$catname'";

    $res = $db->getOne($sql);
    if($res == 0){
        $sql = "INSERT INTO " .$ecs->table('goods_charge'). " (facevalue, province, catname, price)".
            "VALUES ('$facevalue', '$province', '$catname', '$price')";
    }
    else{
        $sql = "UPDATE " .$ecs->table('goods_charge'). " SET price = '$price'  WHERE facevalue = '$facevalue' AND province = '$province' AND catname = '$catname'";
    }
    $r = $db->query($sql);

    $sys_msg = '添加话费配置信息成功！';
    if(!$r){
        $sys_msg = '添加话费配置信息失败！';
    }
    $link[] = array('text'  =>  $_LANG['back'] . $_LANG['59_charge_calls_config_add'],
        'href'  =>  'charge.php?act=add');

    sys_msg($sys_msg, 0, $link);
}

elseif ($_REQUEST['act'] == 'delete')
{
    $id = empty($_REQUEST['id']) ? 0 : intval($_REQUEST['id']);
    $sys_msg = '删除话费配置信息失败！';
    $link[] = array('text'  =>  $_LANG['back'] . $_LANG['58_charge_calls_config_list'],
        'href'  =>  'charge.php?act=list');

    if($id > 0){
        $sql = "SELECT COUNT(*) FROM " . $ecs->table('goods_charge') . " WHERE facevalue = '$facevalue' AND province = '$province' AND catname = '$catname'";

        $sql = "DELETE FROM " . $ecs->table('goods_charge') . " WHERE id = '$id'";
        $r = $db->query($sql);
        if($r){
            $sys_msg = '删除话费配置信息成功！';
        }
    }

    sys_msg($sys_msg, 0, $link);
}

function chargeconfig_list()
{
    $result = get_filter();
    if ($result === false)
    {
        /* 过滤信息 */
        $filter['facevalue'] = empty($_REQUEST['facevalue']) ? '' : floatval($_REQUEST['facevalue']);
        $filter['province'] = empty($_REQUEST['province']) ? '' : trim($_REQUEST['province']);
        $filter['catname'] = empty($_REQUEST['catname']) ? '' : trim($_REQUEST['catname']);
        
        $where = 'WHERE 1 ';
        if ($filter['facevalue'])
        {
            $where .= " AND facevalue = '$filter[facevalue]'";
        }
        if ($filter['province'])
        {
            $where .= " AND province = '$filter[province]'";
        }
        if ($filter['catname'])
        {
            $where .= " AND catname = '$filter[catname]'";
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
        $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('goods_charge'). $where;

        $filter['record_count']   = $GLOBALS['db']->getOne($sql);
        $filter['page_count']     = $filter['record_count'] > 0 ? ceil($filter['record_count'] / $filter['page_size']) : 1;

        /* 查询 */
        $sql = "SELECT * FROM " . $GLOBALS['ecs']->table('goods_charge') .  $where .
            " ORDER BY catname,facevalue ".
            " LIMIT " . ($filter['page'] - 1) * $filter['page_size'] . ",$filter[page_size]";

        set_filter($filter, $sql);
    }
    else
    {
        $sql    = $result['sql'];
        $filter = $result['filter'];
    }

    $row = $GLOBALS['db']->getAll($sql);

    $arr = array('results' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}

?>