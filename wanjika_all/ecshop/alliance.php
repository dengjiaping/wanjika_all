<?php

/**
 * 联盟页面
 * 
 * @author qihua 
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');

if ((DEBUG_MODE & 2) != 2)
{
    $smarty->caching = true;
}

if (!$smarty->is_cached('alliance.dwt'))
{
 	assign_template();
	$position = assign_ur_here(0, '联盟页面');

	$smarty->assign('page_title', $position['title']);    // 页面标题
	$smarty->assign('ur_here',    $position['ur_here']);  // 当前位置
	$smarty->assign('categories', get_categories_tree()); // 分类树
	$smarty->assign('helps',      get_shop_help());       // 网店帮助
}

$smarty->display('alliance.dwt');