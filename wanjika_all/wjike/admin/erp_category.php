<?php

/**
 * ECSHOP erp分类管理程序
**/

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
$exc = new exchange($ecs->table("erp_category"), $db, 'erpcat_id', 'erpcat_name');

/* act操作项的初始化 */
if (empty($_REQUEST['act']))
{
    $_REQUEST['act'] = 'list';
}
else
{
    $_REQUEST['act'] = trim($_REQUEST['act']);
}

/*------------------------------------------------------ */
//-- erp分类列表
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{
    /* 获取ERP分类列表 */
    $cat_list = erpcat_list();

    /* 模板赋值 */
    $smarty->assign('ur_here',      $_LANG['59_erp_category_list']);
    $smarty->assign('action_link',  array('href' => 'erp_category.php?act=add', 'text' => $_LANG['erp_category_list']));
    $smarty->assign('full_page',    1);

    $smarty->assign('cat_info',     $cat_list);

    /* 列表页面 */
    assign_query_info();
    $smarty->display('erp_category_list.htm');
}

/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    $cat_list = erpcat_list();
    $smarty->assign('cat_info',     $cat_list);

    make_json_result($smarty->fetch('erp_category_list.htm'));
}
/*------------------------------------------------------ */
//-- 添加erp分类
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'add')
{

    /* 模板赋值 */
    $smarty->assign('ur_here',      $_LANG['erp_category_list']);
    $smarty->assign('action_link',  array('href' => 'erp_category.php?act=list', 'text' => $_LANG['59_erp_category_list']));
    $smarty->assign('form_act',     'insert');
    $smarty->assign('cat_info',     array('is_show' => 1));

    /* 显示页面 */
    assign_query_info();
    $smarty->display('erp_category_info.htm');
}

/*------------------------------------------------------ */
//-- erp分类添加时的处理
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'insert')
{

    /* 初始化变量 */
    $cat['erpcat_name']     = !empty($_POST['erpcat_name'])     ? trim($_POST['erpcat_name'])     : '';
    $cat['erpcat_code']       = !empty($_POST['erpcat_code'])     ? trim($_POST['erpcat_code'])     : '';

    if (erpcat_exists($cat['erpcat_name'], $cat['erpcat_code']))
    {
        /* 同级别下不能有重复的分类名称 */
       $link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
       sys_msg($_LANG['catname_exist'], 0, $link);
    }

    /* 入库的操作 */
    if ($db->autoExecute($ecs->table('erp_category'), $cat) !== false)
    {
        $cat_id = $db->insert_id();

        admin_log($_POST['erpcat_name'], 'add', 'erp_category');   // 记录管理员操作
        clear_cache_files();    // 清除缓存

        /*添加链接*/
        $link[0]['text'] = '继续添加新ERP分类';
        $link[0]['href'] = 'erp_category.php?act=add';

        $link[1]['text'] = '返回ERP分类列表';
        $link[1]['href'] = 'erp_category.php?act=list';

        sys_msg('新ERP分类添加成功!', 0, $link);
    }
 }

/*------------------------------------------------------ */
//-- 编辑erp分类信息
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'edit')
{
    $erpcat_id = intval($_REQUEST['erpcat_id']);
    $cat_info = get_erpcat_info($erpcat_id);  // 查询分类信息数据

    /* 模板赋值 */
    $smarty->assign('ur_here',     '编辑ERP分类');
    $smarty->assign('action_link', array('text' => $_LANG['59_erp_category_list'], 'href' => 'erp_category.php?act=list'));

    $smarty->assign('cat_info',    $cat_info);
    $smarty->assign('form_act',    'update');

    /* 显示页面 */
    assign_query_info();
    $smarty->display('erp_category_info.htm');
}
/*------------------------------------------------------ */
//-- 编辑erp分类信息
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'update')
{

    /* 初始化变量 */
    $cat_id              = !empty($_POST['cat_id'])       ? intval($_POST['cat_id'])     : 0;
    $old_cat_name        = $_POST['old_cat_name'];
    $old_erpcat_code        = $_POST['old_erpcat_code'];
    $cat['erpcat_name']     = !empty($_POST['erpcat_name'])     ? trim($_POST['erpcat_name'])     : '';
    $cat['erpcat_code']       = !empty($_POST['erpcat_code'])     ? trim($_POST['erpcat_code'])     : '';

    if($cat['erpcat_name']!=$old_cat_name)
    {
        if (erpcat_exists($cat['erpcat_name'], ''))
        {
            /* 同级别下不能有重复的分类名称 */
            $link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
            sys_msg($_LANG['catname_exist'], 0, $link);
        }
    }
    if($cat['erpcat_code']!=$old_erpcat_code)
    {
        if (erpcat_exists('', $cat['erpcat_code']))
        {
            /* 同级别下不能有重复的分类名称 */
            $link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
            sys_msg('已存在相同的编码', 0, $link);
        }
    }

    if ($db->autoExecute($ecs->table('erp_category'), $cat, 'UPDATE', "erpcat_id='$cat_id'"))
    {
        /* 更新分类信息成功 */
        clear_cache_files(); // 清除缓存
        admin_log($_POST['erpcat_name'], 'edit', 'erp_category'); // 记录管理员操作

        /* 提示信息 */
        $link[] = array('text' => $_LANG['back_list'], 'href' => 'erp_category.php?act=list');
        sys_msg('修改ERP分类成功', 0, $link);
    }
}

/*------------------------------------------------------ */
//-- 删除erp分类
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'remove')
{
    check_authz_json('erpcat_drop');
    $erpcat_id   = intval($_GET['id']);
    $erpcat_name = $db->getOne('SELECT erpcat_name FROM ' .$ecs->table('erp_category'). " WHERE erpcat_id='$erpcat_id'");
    $count = $db->getOne('SELECT COUNT(*) FROM ' .$ecs->table('goods'). " WHERE erpcat_id='$erpcat_id'");
    if($count>0)
    {
        make_json_error($erpcat_name .' '. '分类下还存在商品.您不能删除!');
    }
    else
    {
        /* 删除分类 */
        $sql = 'DELETE FROM ' .$ecs->table('erp_category'). " WHERE erpcat_id = '$erpcat_id'";
        if ($db->query($sql))
        {
            clear_cache_files();
            admin_log($cat_name, 'remove', 'category');
        }
        else
        {
            make_json_error($erpcat_name .' '. '删除失败!');
        }
    }
    $url = 'erp_category.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);

    ecs_header("Location: $url\n");
    exit;
}
/**
 * 获得ERP分类的所有信息
 *
 * @param   integer     $erpcat_id     指定的分类ID
 *
 * @return  mix
 */
function get_erpcat_info($erpcat_id)
{
    $sql = "SELECT * FROM " .$GLOBALS['ecs']->table('erp_category'). " WHERE erpcat_id='$erpcat_id' LIMIT 1";
    return $GLOBALS['db']->getRow($sql);
}
?>