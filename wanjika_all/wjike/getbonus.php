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
	
	$bonus_type = empty($_REQUEST['bonus_type']) ? 0 : intval($_REQUEST['bonus_type']);
    if ($bonus_type == 0)
    {
        //提示参数错误
    }

	$sql = "SELECT count(*) FROM " . $ecs->table('user_bonus') . 
			   " WHERE user_id = '{$user_id}' AND bonus_type_id = '{$bonus_type}'";
	if ($db->getOne($sql) > 0)
	{
		$get_bonus_res = 'fail';
	}
	else
	{
        $sql1 = "SELECT * FROM " . $GLOBALS['ecs']->table('bonus_type') .
            " WHERE type_id = '$bonus_type'";
        $bonus = $db->getRow($sql1);
        $now = gmtime();
	    $sql = "INSERT INTO " . $ecs->table('user_bonus') .
	                "(bonus_type_id, bonus_sn, user_id, used_time, order_id, emailed,binding_time,use_start_datetime,use_end_datetime) " .
	                "VALUES ('$bonus_type', 0, '$user_id', 0, 0, " .BONUS_MAIL_FAIL. ",'$now','$bonus[use_start_date]','$bonus[use_end_date]')";

        $new_bonus_id = $db->insert_id();
		$db->query($sql);
        if($bonus['use_datetime'] == 1)
        {
            //按起止日期计算
            $use_effective_date = $now+$bonus['use_effective_date']*24 * 3600;
            $m_sql = "UPDATE " .$ecs->table('user_bonus'). " SET ".
                "use_start_datetime  = '$now', ".
                "use_end_datetime    = '$use_effective_date' ".
                "WHERE bonus_id   = '$new_bonus_id'";
            $db->query($m_sql);
        }
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
    $smarty->assign('action_url', 'getbonus2.php');
}

$smarty->display('getbonus.dwt');