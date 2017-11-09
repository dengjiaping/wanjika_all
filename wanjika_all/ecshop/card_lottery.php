<?php

/**
 * 购买礼品卡转盘抽奖
 * 
 * @author qihua 
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
include_once('includes/cls_json.php');

if ((DEBUG_MODE & 2) != 2)
{
    $smarty->caching = true;
}

//以10w为基数
$REWARD_ARR = array(
	'0' => array('id' => 0, 'min' => 61, 'max' => 119, 'prize' => '未中奖', 'v' => 49800, 'num' => -1),		//-1不限数量
	'1' => array('id' => 1, 'min' => 1, 'max' => 59, 'prize' => '一等奖', 'v' => 100, 'num' => 2), 
    '2' => array('id' => 2, 'min' => 301, 'max' => 359, 'prize' => '二等奖', 'v' => 100, 'num' => 5), 
    '3' => array('id' => 3, 'min'=>  241, 'max' => 299, 'prize' => '三等奖', 'v' => 0, 'num' => 20), 
    '4' => array('id' => 4,'min' => 181, 'max' => 239, 'prize' => '四等奖', 'v' => 0, 'num' => 100), 
    '5' => array('id' => 5, 'min' => 121, 'max' => 179, 'prize' => '五等奖', 'v' => 50000, 'num' => 1000), 
//	'0' => array('id' => 0, 'min' => 61, 'max' => 119, 'prize' => '未中奖', 'v' => 100000, 'num' => -1),		//-1不限数量
//	'1' => array('id' => 1, 'min' => 1, 'max' => 59, 'prize' => '一等奖', 'v' => 0, 'num' => 2), 
//    '2' => array('id' => 2, 'min' => 301, 'max' => 359, 'prize' => '二等奖', 'v' => 0, 'num' => 5), 
//    '3' => array('id' => 3, 'min'=>  241, 'max' => 299, 'prize' => '三等奖', 'v' => 0, 'num' => 20), 
//    '4' => array('id' => 4,'min' => 181, 'max' => 239, 'prize' => '四等奖', 'v' => 0, 'num' => 100), 
//    '5' => array('id' => 5, 'min' => 121, 'max' => 179, 'prize' => '五等奖', 'v' => 0, 'num' => 1000), 
);

$start_time = "2014-01-23 00:00:00";
$end_time = '2014-02-28 00:00:00';

$act = trim($_REQUEST['act']);

if ($act == 'lottery')
{
	$data = array();
	do
	{	
		
		if ($_SESSION['user_id'] == 0)
		{
			$data['angle'] = 0;
			$data['result'] = 'fail';
			$data['msg'] = '请先登录再参加抽奖活动！';
			break;
		}
		
		$now = time();
		if ($now < strtotime($start_time) || $now > strtotime($end_time))
		{
			$data['angle'] = 0;
			$data['result'] = 'fail';
			$data['msg'] = '活动已结束！';
			break;
		}
		
		//获取所有礼品卡的cat_id，12是礼品卡的cat_id，这里就直接写定了
		$sql = "SELECT cat_id FROM " . $ecs->table('category') . ' WHERE parent_id = 12';
		$arr = $db->getAll($sql);
		$cat_ids = array();
		if (!empty($arr))
		{
			foreach ($arr as $info)
			{
				$cat_ids[] = $info['cat_id'];
			}
		}
		
		$sql = "SELECT oo.order_sn, oo.order_status, oo.pay_status, o.order_id, o.goods_id FROM " . $ecs->table('order_info') . " as oo INNER JOIN "  . $ecs->table('order_goods') . " as o ON oo.order_id = o.order_id INNER JOIN " . $ecs->table('goods'). " as g ON o.goods_id = g.goods_id WHERE g.cat_id " . 
					db_create_in($cat_ids) . " AND oo.user_id = '" . $_SESSION['user_id'] . "' AND oo.pay_time >='" . strtotime($start_time) . "' AND oo.pay_time <= '" . strtotime($end_time) . "'";
		$card_orders = $db->getAll($sql);
		
		$sql = "SELECT lid FROM " . $ecs->table('card_lottery') . " WHERE user_id = '" . $_SESSION['user_id'] . "'";
		$lottery_list = $db->getAll($sql);
		if (count($lottery_list) >= count($card_orders))
		{
			$data['angle'] = 0;
			$data['result'] = 'fail';
			$data['msg'] = '您的抽奖次数已用完！成功下单购买礼品卡之后就会获得新的抽奖机会！';
			break;
		}
		
		$all_order_sn = array();
		if (!empty($card_orders))
		{
			foreach ($card_orders as $info)
			{
				$all_order_sn[] = $info['order_sn'];
			}
		}
		
		$lottery_order_sn = array();
		if (!empty($lottery_list))
		{
			foreach ($lottery_list as $info)
			{
				$lottery_order_sn[] = $info['order_sn'];
			}
		}
		
		$diff_sn = array_diff($all_order_sn, $lottery_order_sn);
		
		$reward_id = random_reward($REWARD_ARR);
		
		$now = time();
		$sql = "INSERT INTO " . $ecs->table("card_lottery") . 
					"(user_id, user_name, order_sn, add_time, reward_id, reward_name) VALUES " . 
					"('{$_SESSION['user_id']}', '{$_SESSION['user_name']}', '{$diff_sn[0]}', '$now', '$reward_id', '{$REWARD_ARR[$reward_id]['prize']}')";
		
		$res = $db->query($sql);
		if ($res == false)
		{
			$reward_id = 0;
			$data['angle'] = mt_rand($REWARD_ARR[$reward_id]['min'], $REWARD_ARR[$reward_id]['max']);
			$data['result'] = 'fail';
			$data['msg'] = '很遗憾，您这次没有中奖！';
			break;
		}
		
		if ($reward_id == 0)
		{
			$data['angle'] = mt_rand($REWARD_ARR[$reward_id]['min'], $REWARD_ARR[$reward_id]['max']);
			$data['result'] = 'fail';
			$data['msg'] = '很遗憾，您这次没有中奖！';
		}
		else
		{
			$data['angle'] = mt_rand($REWARD_ARR[$reward_id]['min'], $REWARD_ARR[$reward_id]['max']);;
			$data['result'] = 'succ';
			$data['msg'] = '恭喜您抽中' . $REWARD_ARR[$reward_id]['prize'] . '，请您等待客服人员联系您领取您的奖品。';
		}
	}
	while (false);
	
	$json = new Json();
	die($json->encode($data));
}
else
{
	if (!$smarty->is_cached('card_lottery.dwt'))
	{
	 	assign_template();
		$position = assign_ur_here(0, '转盘抽奖');
	
		$smarty->assign('page_title', $position['title']);    // 页面标题
		$smarty->assign('ur_here',    $position['ur_here']);  // 当前位置
		$smarty->assign('categories', get_categories_tree()); // 分类树
		$smarty->assign('helps',      get_shop_help());       // 网店帮助
		$smarty->assign('get_bonus_res', $get_bonus_res);
	}
	
	$smarty->display('card_lottery.dwt');
}

function random_reward($reward_arr)
{
	global $ecs, $db;
	$sql = "SELECT user_id, reward_id FROM " . $ecs->table('card_lottery') . " WHERE reward_id != 0";
	$user_reward = $db->getAll($sql);
	
	$avalible_reward = array();
	$reward_total = array();
	$has_reward1 = false;
	$has_reward2 = false;
	if (!empty($user_reward))
	{
		foreach ($user_reward as $info)
		{
			$reward_total[$info['reward_id']]++;
			if ($info['reward_id'] == 1 && $info['user_id'] == $_SESSION['user_id'])
			{
				$has_reward1 = true;
			}
			
			if ($info['reward_id'] == 2 && $info['user_id'] == $_SESSION['user_id'])
			{
				$has_reward2 = true;
			}
		}
	}
	
	foreach ($reward_arr as $key => $val)
	{
		//如果数量不够了，跳过
		if ($val['num'] <= $reward_total[$val['id']])
		{
			continue;
		}
		
		//概率为0的跳过
		if ($val['v'] == 0)
		{
			continue;
		}
		
		//一等奖只能中一个
		if ($val['id'] == 1 && $has_reward1)
		{
			continue;
		}
		
		//二等奖也只能中一个
		if ($val['id'] == 2 && $has_reward2)
		{
			continue;
		}
		
		$avalible_reward[] = $val;
	}
	
	$rand = mt_rand(1, 100000);
	$start = 1;
	$end = 1;
	$reward_id = 0;
	foreach ($avalible_reward as $val)
	{
		$start = $end;
		$end += $val['v'];
		
		if ($rand >= $start && $rand <= $end)
		{
			$reward_id = $val['id'];
			break;
		}
	}
	
	return $reward_id;
}