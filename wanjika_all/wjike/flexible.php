<?php

/**
 * 2014厨房活动
 * 
 * @author qihua 
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');

if ((DEBUG_MODE & 2) != 2)
{
    $smarty->caching = true;
}
if($_REQUEST['act'] == 'newvip')
{
    $smarty->assign('newvip', true);
}
else
{
    $smarty->assign('newvip', false);
}
if (!$smarty->is_cached('flexible.dwt'))
{
 	assign_template();
	$position = assign_ur_here(0, '新会员免费领取');

//	$smarty->assign('page_title', $position['title']);    // 页面标题
//	$smarty->assign('ur_here',    $position['ur_here']);  // 当前位置
//	$smarty->assign('categories', get_categories_tree()); // 分类树
	$smarty->assign('helps',      get_shop_help());       // 网店帮助
//	$smarty->assign('get_bonus_res', $get_bonus_res);
}

$smarty->display('flexible.dwt');