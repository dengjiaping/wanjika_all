<?php

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require(dirname(__FILE__) . '/includes/modules/kjt/create_order.php');

$order_id = $_REQUEST['order_id'];
$sql = "SELECT * FROM " . $GLOBALS['ecs']->table('order_info') . " WHERE order_id = '$order_id'";
$order = $db->getRow($sql);

$user_id = $_SESSION['user_id'];
$sql = "SELECT mobile_phone,real_name,id_card FROM " . $GLOBALS['ecs']->table('users') . " WHERE user_id = '$user_id'";
$user = $db->getRow($sql);

$sql="SELECT t2.kjt_goods_id,t1.goods_number,t2.kjt_price,t2.kjt_tariffrate FROM " . $GLOBALS['ecs']->table('order_goods') . " AS t1 LEFT JOIN "
    . $GLOBALS['ecs']->table('goods') . " AS t2 ON t1.goods_id=t2.goods_id WHERE t1.order_id=$order_id ";
$order_goods = $db->getAll($sql);

$list = array();
foreach($order_goods as $v){
    $goods_item['ProductID'] = $v['kjt_goods_id'];
    $goods_item['Quantity'] = $v['goods_number'];
    $goods_item['SalePrice'] = $v['kjt_price'];
    $goods_item['TaxPrice'] = $v['kjt_price'] * $v['kjt_tariffrate'];
    array_push($list,$goods_item);
}

//$sql="SELECT t1.* FROM " . $GLOBALS['ecs']->table('order_info') . " AS t1 LEFT JOIN " . $GLOBALS['ecs']->table('kjt_task')
//    . " AS t2 ON t1.order_no=t2.order_no WHERE t2.task_status=10 ";
//$res = $GLOBALS['db']->getAll($sql);

$obj = new create_order();
$obj->createOrder($order,$user,$list,'alipay');