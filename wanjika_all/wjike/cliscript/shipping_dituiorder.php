<?php
define('IN_ECS', true);
require(dirname(__FILE__) . '/../includes/init.php');
require(dirname(__FILE__) . '/../includes/lib_order.php');

$sql = "SELECT t1.order_id FROM " . $GLOBALS['ecs']->table('order_info'). " t1 inner join".$GLOBALS['ecs']->table('order_goods').
    " t2 on t1.order_id=t2.order_id WHERE  t2.goods_id=11443 and shipping_status<>2 and pay_status=2 and order_status<>2";
$results = $GLOBALS['db']->getAll($sql);
if(count($results) == 0){
    exit;
}
$order_ids = "";
foreach($results as $v){
    $order_ids .= ($v['order_id'].',');
}
$order_ids = substr($order_ids, 0, -1);
$order_ids = '('.$order_ids.')';

$sql_update = "update " . $GLOBALS['ecs']->table('order_info'). " set order_status=5,shipping_status=2,shipping_time=pay_time WHERE order_id in ".$order_ids;
$GLOBALS['db']->query($sql_update);

