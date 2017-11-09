<?php

/**
 * 2013岁末领取红包页面
 * 
 * @author qihua 
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');

if ((DEBUG_MODE & 2) != 2)
{
    $smarty->caching = true;
}

$bonus_index_arr = array(1 => 16, 2 => 17, 3 => 18);

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
	
	$bonus_index = intval($_REQUEST['index']);
	$bonus_type = $bonus_index_arr[$bonus_index];
	
	$now = time();
	$sql = "select * from " . $ecs->table('bonus_type') . " where type_id = '$bonus_type'";
	$bonusInfo = $GLOBALS['db']->getRow($sql);
	if ($now <= $bonusInfo['send_start_date'] || $now >= $bonusInfo['send_end_date'])
	{
		$get_bonus_res = 'outofdate';
	}
	else
	{
		$sql = "SELECT count(*) FROM " . $ecs->table('user_bonus') . 
				   " WHERE user_id = '{$user_id}' AND bonus_type_id = '{$bonus_type}'";
		if ($db->getOne($sql) > 0)
		{
			$get_bonus_res = 'fail';
		}
		else
		{
			$num = $db->getOne("SELECT MAX(bonus_sn) FROM ". $ecs->table('user_bonus'));
			$num = $num ? floor($num / 10000) : 100000;
			$bonus_sn = ($num + 0) . str_pad(mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);
    
			 $sql = "INSERT INTO " . $ecs->table('user_bonus') .
		                "(bonus_type_id, bonus_sn, user_id, used_time, order_id, emailed) " .
		                "VALUES ('$bonus_type', '$bonus_sn', '$user_id', 0, 0, " .BONUS_MAIL_FAIL. ")";
			 
			$db->query($sql);
			
			$get_bonus_res = 'succ';
		}
	}
}

if (!$smarty->is_cached('bonusendsof2013.dwt'))
{
 	assign_template();
	$position = assign_ur_here(0, '领取双2013岁末优惠券');

	$smarty->assign('page_title', $position['title']);    // 页面标题
	$smarty->assign('ur_here',    $position['ur_here']);  // 当前位置
	$smarty->assign('categories', get_categories_tree()); // 分类树
	$smarty->assign('helps',      get_shop_help());       // 网店帮助
	$smarty->assign('get_bonus_res', $get_bonus_res);
}

$smarty->display('bonusendsof2013.dwt');