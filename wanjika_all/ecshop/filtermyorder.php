<?php

/**
 * 查询我的订单页
 * 
 * @author qihua 
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/user.php');

if ((DEBUG_MODE & 2) != 2)
{
    $smarty->caching = true;
}

if (!empty($_REQUEST['submit']))
{
	$user_id = $_SESSION['user_id'];
	if ($user_id == 0)
	{
		ecs_header("Location: user.php");
		exit;
	}

	$where = "user_id = '$user_id'";
	if (!empty($_REQUEST['stime']))
	{
		$where .= " AND add_time >= '" . mysql_escape_string(local_strtotime($_REQUEST['stime'])) . "'";
	}
	if (!empty($_REQUEST['etime']))
	{
		$where .= " AND add_time <= '" . mysql_escape_string(local_strtotime($_REQUEST['etime'])) . "'";
	}
	if (!empty($_REQUEST['order_status']))
	{
		$where .= " AND order_status = '" . mysql_escape_string($_REQUEST['order_status']) . "'";
	}
	if (!empty($_REQUEST['pay_status']))
	{
		$where .= " AND pay_status = '" . mysql_escape_string($_REQUEST['pay_status']) . "'";
	}

	$sql = "SELECT order_id, order_sn, order_status, shipping_status, pay_status, add_time, " .
	"(goods_amount + shipping_fee + insure_fee + pay_fee + pack_fee + card_fee + tax - discount) AS total_fee ".
	" FROM " .$GLOBALS['ecs']->table('order_info') .
	" WHERE $where ORDER BY add_time DESC";
	
	$list = $GLOBALS['db']->getAll($sql);
	if (!empty($list))
	{
		$total_price = 0;
		foreach ($list as $key => $row)
		{
			if ($row['pay_status'] == PS_PAYED)
			{
				$total_price += $row['total_fee'];
			}
			$list[$key]['order_time'] = local_date('Y-m-d H:i', $row['add_time']);
			$list[$key]['order_status'] = $GLOBALS['_LANG']['os'][$row['order_status']] . ',' . $GLOBALS['_LANG']['ps'][$row['pay_status']] . ',' . $GLOBALS['_LANG']['ss'][$row['shipping_status']]; 
		}
	}
	
	$order_status = array();
	foreach ($_LANG['os'] as $key => $value)
	{
		$order_status[] = array('status_id' => $key, 'status_name' => $value);
	}
	
	$pay_status = array();
	foreach ($_LANG['ps'] as $key => $value)
	{
		$pay_status[] = array('status_id' => $key, 'status_name' => $value);
	}
}

if (!$smarty->is_cached('filtermyorder.dwt'))
{
 	assign_template();
	$position = assign_ur_here(0, '订单查询');

	$smarty->assign('page_title', $position['title']);    // 页面标题
	$smarty->assign('ur_here',    $position['ur_here']);  // 当前位置
	$smarty->assign('categories', get_categories_tree()); // 分类树
	$smarty->assign('helps',      get_shop_help());       // 网店帮助
	$smarty->assign('orders', $list);
	$smarty->assign('order_status_list', $order_status);
	$smarty->assign('pay_status_list', $pay_status);
	$smarty->assign('order_status', $_REQUEST['order_status']);
	$smarty->assign('pay_status', $_REQUEST['pay_status']);
	$smarty->assign('stime', $_REQUEST['stime']);
	$smarty->assign('etime', $_REQUEST['etime']);
	$smarty->assign('total_price', $total_price);
}

$smarty->display('filtermyorder.dwt');