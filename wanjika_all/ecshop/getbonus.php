<?php

/**
 * 领取红包页面
 * 
 * @author qihua 
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');

if ((DEBUG_MODE & 2) != 2)
{
    $smarty->caching = true;
}

$get_bonus_res = '';
if ($_REQUEST['act'] == 'getbonus')
{
	$user_id = $_SESSION['user_id'];
	if ($user_id == 0)
	{
		$_SESSION['back_act'] = 'getbonus.php';
		ecs_header("Location: user.php");
		exit;
	}
	
	$bonus_type = 32;
	
	$sql = "SELECT count(*) FROM " . $ecs->table('user_bonus') . 
			   " WHERE user_id = '{$user_id}' AND bonus_type_id = '{$bonus_type}'";
	if ($db->getOne($sql) > 0)
	{
		$get_bonus_res = 'fail';
	}
	else
	{
		 $sql = "INSERT INTO " . $ecs->table('user_bonus') .
	                "(bonus_type_id, bonus_sn, user_id, used_time, order_id, emailed) " .
	                "VALUES ('$bonus_type', 0, '$user_id', 0, 0, " .BONUS_MAIL_FAIL. ")";
		 
		$db->query($sql);
		
		$get_bonus_res = 'succ';
	}
}

if (!$smarty->is_cached('getbonus.dwt'))
{
 	assign_template();
	$position = assign_ur_here(0, '领取优惠券');

	$smarty->assign('page_title', $position['title']);    // 页面标题
	$smarty->assign('ur_here',    $position['ur_here']);  // 当前位置
	$smarty->assign('categories', get_categories_tree()); // 分类树
	$smarty->assign('helps',      get_shop_help());       // 网店帮助
	$smarty->assign('get_bonus_res', $get_bonus_res);
    $smarty->assign('action_url', 'getbonus.php');
}

$smarty->display('getbonus.dwt');