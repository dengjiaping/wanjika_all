<?php

define('IN_ECS', true);
set_time_limit(0);

require(dirname(__FILE__) . '/../includes/init.php');
require(dirname(__FILE__) . '/../includes/modules/kjt/create_order.php');
require(dirname(__FILE__) . '/../includes/modules/jjt/create_order.php');

$sql = "SELECT t1.order_no FROM " . $GLOBALS['ecs']->table('kjt_task'). " AS t1 inner JOIN " . $GLOBALS['ecs']->table('order_info').
    " AS t2 ON t1.order_no=t2.order_sn WHERE (t1.task_status = 12 or t1.task_status = 10 or t1.task_status = 14) and t2.pay_status=2 ORDER BY t1.task_status,t1.addtime desc";
$tasks = $db->getAll($sql);

if(count($tasks) == 0){
    exit;
}

$obj = new create_order();
$obj_jjt = new create_jorder();
$file_name = "/web/wjike/cliscript/log/kjt_task_".date("Ymd").".log";
$order_str = '';
$suc = 0;
$fal = 0;
foreach($tasks as $task){
    sleep(1);
    $order_sn = $task['order_no'];
    $order_str .= ("\n".$order_sn);

    $sql = "SELECT * FROM " . $GLOBALS['ecs']->table('order_info') . " WHERE order_sn = '$order_sn'";
    $order = $db->getRow($sql);

    $order_id = $order['order_id'];
    $sql="SELECT t2.kjt_goods_id,t1.goods_number,t2.kjt_price,t2.kjt_tariffrate FROM " . $GLOBALS['ecs']->table('order_goods') . " AS t1 LEFT JOIN "
        . $GLOBALS['ecs']->table('goods') . " AS t2 ON t1.goods_id=t2.goods_id WHERE t1.order_id=$order_id and t1.extension_code<>'package_buy' ";
    $order_goods = $db->getAll($sql);

    $list = array();
    foreach($order_goods as $v){
        $goods_item['ProductID'] = $v['kjt_goods_id'];
        $goods_item['Quantity'] = $v['goods_number'];
        $goods_item['SalePrice'] = $v['kjt_price'];
        $goods_item['TaxPrice'] = $v['kjt_price'] * $v['kjt_tariffrate'];
        array_push($list,$goods_item);
    }
    //优惠组合购买推送
    $package_sql="SELECT goods_id FROM " . $GLOBALS['ecs']->table('order_goods') . " WHERE order_id=$order_id and extension_code='package_buy' ";
    $package_id = $db->getOne($package_sql);
    if(intval($package_id) > 0){
        $sql="SELECT t2.kjt_goods_id,t1.goods_number,t2.kjt_price,t2.kjt_tariffrate FROM " . $GLOBALS['ecs']->table('package_goods') . " AS t1 LEFT JOIN "
            . $GLOBALS['ecs']->table('goods') . " AS t2 ON t1.goods_id=t2.goods_id WHERE t1.package_id=$package_id";
        $order_goods = $db->getAll($sql);
        foreach($order_goods as $v){
            $goods_item['ProductID'] = $v['kjt_goods_id'];
            $goods_item['Quantity'] = $v['goods_number'];
            $goods_item['SalePrice'] = $v['kjt_price'];
            $goods_item['TaxPrice'] = $v['kjt_price'] * $v['kjt_tariffrate'];
            array_push($list,$goods_item);
        }
    }

    $pay_id = $order['pay_id'];
    $supplier_id = get_supplierid_byorderid($order_id);
    $order['shipping_id'] = get_shippingid_supplierid($supplier_id);
    if($supplier_id == 38){
        set_jjt_order($order_sn);
        $sql = "SELECT pay_code FROM " . $GLOBALS['ecs']->table('payment') . " WHERE pay_id = '$pay_id'";
        $pay_code = $db->getOne($sql);
        $r = $obj_jjt->createJOrder($order,$list,$pay_code);
    }
    else{
        $r = $obj->createOrder($order,$list);
    }
    if($r){
        $suc++;
    }
    else{
        $fal++;
    }
}
echo 'SUCCESS:'.$suc.',FAILED:'.$fal;
file_put_contents($file_name,$order_str,FILE_APPEND);