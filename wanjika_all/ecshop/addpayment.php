<?php
define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');

$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('goods') . " WHERE payment_ids != ''";
$goods_list = $GLOBALS['db']->getAll($sql);
$total = 0;
$success = 0;
foreach ($goods_list as $key => $info)
{
	$payment_arr = explode(',', $info['payment_ids']);
	$payment_arr[] = 8;
	$payment_ids = implode(',', $payment_arr);
	
	$sql = "UPDATE " . $GLOBALS['ecs']->table('goods') . " SET payment_ids = '" . $payment_ids . "' WHERE goods_id = '{$info['goods_id']}'";
	$res = $GLOBALS['db']->query($sql);
	if ($res)
	{
		$success++;
	}
	
	$total++;
}

echo "total = $total;	success = $success";