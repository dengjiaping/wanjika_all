<?php

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require(ROOT_PATH . 'includes/lib_order.php');


$sql = "SELECT * FROM " . $ecs->table("order_info") .  " WHERE order_sn = '2014031057341'";
$order = $db->getOne($sql);
$region_id_list = array($order['country'], $order['province'], $order['city'], $order['district']);
$shipping = shipping_area_info($order['shipping_id'], $region_id_list);
$pay_fee = pay_fee($order["pay_id"], $order["order_amount"], $shipping['pay_fee']);
$sql2 = "UPDATE " . $ecs->table("order_info") . " SET pay_fee = $pay_fee WHERE order_sn = '2014031057341'";
$ret = $db->query($sql2);
echo $ret ? "succ" : "false";
exit;


$sql = "SELECT o.order_id, o.order_sn, o.goods_amount, o.order_amount, o.shipping_fee, g.goods_number * g.goods_price AS real_total " . 
			" FROM " . $ecs->table("order_goods") . " AS g LEFT JOIN " . $ecs->table("order_info") . "AS o ON " . 
			" o.order_id = g.order_id WHERE o.add_time >= " . local_strtotime("2014-03-10 16:40") . " AND " . 
			" o.add_time <= " . local_strtotime("2014-03-10 21:36:00");

$res = $db->getAll($sql);


$shipping = shipping_area_info($order['shipping_id'], $region_id_list);
			$pay_fee = pay_fee($pay_id, $order_amount, $shipping['pay_fee']);

$arr = array();
foreach ($res as $info)
{
	if (!isset($arr[$info['order_id']]))
	{
		$arr[$info['order_id']] = $info;
	}
	else
	{
		$arr[$info['order_id']]['real_total'] += $info['real_total'];
	}
}

$total = 0;
foreach ($arr as $info)
{
	$total ++;
	
	//$sql = "UPDATE " . $ecs->table("order_info") . " SET goods_amount = {$info['real_total']}, order_amount = {$info['real_total']} + {$info["shipping_fee"]} WHERE order_id = {$info["order_id"]}";
	//$ret = $db->query($sql);
	echo "{$info['order_id']}\t{$info['order_sn']}\t{$info['goods_amount']}\t{$info['shipping_fee']}\t{$info['order_amount']}\t{$info['real_total']}\t<br />";
}

//echo "total = $total";