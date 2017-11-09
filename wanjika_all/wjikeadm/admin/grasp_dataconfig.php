<?php

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
if ($_REQUEST['act'] == 'list')
{
    $smarty->assign('ur_here',      $_LANG['01_data_config']);
    $smarty->assign('action_link',  array('text' => '添加抓取配置', 'href' => 'grasp_dataconfig.php?act=add'));
    $smarty->assign('full_page',    1);

    $config_list = get_configlist();

    $smarty->assign('config_list',   $config_list['lists']);
    $smarty->assign('filter',       $config_list['filter']);
    $smarty->assign('record_count', $config_list['record_count']);
    $smarty->assign('page_count',   $config_list['page_count']);

    assign_query_info();
    $smarty->display('data_config.htm');
}

else if ($_REQUEST['act'] == 'add')
{
    /* 模板赋值 */
    $smarty->assign('ur_here',      '抓取配置添加');
    $smarty->assign('action_link',  array('href' => 'grasp_dataconfig.php?act=list', 'text' => $_LANG['01_data_config']));
    $smarty->assign('form_act',     'insert');
    /* 显示页面 */
    assign_query_info();
    $smarty->display('data_config_info.htm');
}

else if ($_REQUEST['act'] == 'edit')
{
    $config_id = intval($_REQUEST['config_id']);
    $config_info = get_config_info($config_id);  // 查询分类信息数据


    /* 模板赋值 */
    $smarty->assign('ur_here',      '抓取配置添加');
    $smarty->assign('action_link',  array('href' => 'grasp_dataconfig.php?act=list', 'text' => $_LANG['01_data_config']));
    $smarty->assign('form_act',     'update');
    $smarty->assign('config_id',     $config_id);
    $smarty->assign('config_info',    $config_info);

    /* 显示页面 */
    assign_query_info();
    $smarty->display('data_config_info.htm');
}

else if ($_REQUEST['act'] == 'insert')
{

    /* 初始化变量 */
    $config['keywords']     = !empty($_POST['keywords'])     ? trim($_POST['keywords'])     : '';
    $config['sorttype']       = !empty($_POST['sorttype'])       ? intval($_POST['sorttype'])     : 0;
    $config['page']    = !empty($_POST['page'])    ? intval($_POST['page'])  : 0;

    /* 入库的操作 */
    $sql = "INSERT INTO " . $ecs->table('data_config') .
        " (keywords,page,sorttype)".
        " VALUES('" . $config['keywords'] . "',  '".$config['page']."',  '".$config['sorttype']."')";
    $db->query($sql);

    /*添加链接*/
    $link[0]['text'] = '继续添加';
    $link[0]['href'] = 'grasp_dataconfig.php?act=add';

    $link[1]['text'] = $_LANG['01_data_config'];
    $link[1]['href'] = 'grasp_dataconfig.php?act=list';

    sys_msg('抓取配置添加成功', 0, $link);
}

else if ($_REQUEST['act'] == 'update')
{

    /* 初始化变量 */
    $config['config_id']       = !empty($_POST['config_id'])       ? intval($_POST['config_id'])     : 0;
    $config['keywords']     = !empty($_POST['keywords'])     ? trim($_POST['keywords'])     : '';
    $config['sorttype']       = !empty($_POST['sorttype'])       ? intval($_POST['sorttype'])     : 0;
    $config['page']    = !empty($_POST['page'])    ? intval($_POST['page'])  : 0;

    $sql = "UPDATE " . $ecs->table('data_config') . " SET keywords = '" . $config['keywords'] . "' , sorttype = '" . $config['sorttype'].
        "' , page = '" . $config['page']. "' WHERE id = '" . $config['config_id'] ."'";
    $db->query($sql);

    /*添加链接*/
    $link[0]['text'] = $_LANG['01_data_config'];
    $link[0]['href'] = 'grasp_dataconfig.php?act=list';

    sys_msg('抓取配置修改成功', 0, $link);
}

else if ($_REQUEST['act'] == 'remove')
{
    $id = intval($_GET['id']);

    $sql = 'DELETE FROM ' . $ecs->table('data_config') . " WHERE id = '" . $id ."'";
    $db->query($sql);

    $url = 'grasp_dataconfig.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);

    ecs_header("Location: $url\n");
    exit;
}

else if ($_REQUEST['act'] == 'query')
{
    $config_list = get_configlist();

    $smarty->assign('config_list',   $config_list['lists']);
    $smarty->assign('filter',       $config_list['filter']);
    $smarty->assign('record_count', $config_list['record_count']);
    $smarty->assign('page_count',   $config_list['page_count']);

    make_json_result($smarty->fetch('data_config.htm'), '',
        array('filter' => $config_list['filter'], 'page_count' => $config_list['page_count']));
}

function get_configlist()
{
    $result = get_filter();
    if ($result === false)
    {
        /* 过滤信息 */

        $where = 'WHERE 1 ';

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
        $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('data_config'). $where;

        $filter['record_count']   = $GLOBALS['db']->getOne($sql);
        $filter['page_count']     = $filter['record_count'] > 0 ? ceil($filter['record_count'] / $filter['page_size']) : 1;

        /* 查询 */
        $sql = "SELECT * FROM " . $GLOBALS['ecs']->table('data_config') .  $where .
            " LIMIT " . ($filter['page'] - 1) * $filter['page_size'] . ",$filter[page_size]";

        set_filter($filter, $sql);
    }
    else
    {
        $sql    = $result['sql'];
        $filter = $result['filter'];
    }

    $row = $GLOBALS['db']->getAll($sql);

    $arr = array('lists' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}

function get_config_info($config_id)
{
    $sql = "SELECT * FROM " .$GLOBALS['ecs']->table('data_config'). " WHERE id='$config_id' LIMIT 1";
    return $GLOBALS['db']->getRow($sql);
}
?>
