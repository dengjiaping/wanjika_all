<?php

/**
 * 2015实惠当道
 * 
 * @author qihua 
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');

if ((DEBUG_MODE & 2) != 2)
{
    $smarty->caching = true;
}

if (!$smarty->is_cached('kitchen.dwt'))
{
 	assign_template();
	$position = assign_ur_here(0, '万集客实惠当道活动');

	$smarty->assign('page_title', $position['title']);    // 页面标题
	$smarty->assign('ur_here',    $position['ur_here']);  // 当前位置
	$smarty->assign('categories', get_categories_tree()); // 分类树
	$smarty->assign('helps',      get_shop_help());       // 网店帮助
	$smarty->assign('get_bonus_res', $get_bonus_res);
}

$smarty->display('kitchen.dwt');