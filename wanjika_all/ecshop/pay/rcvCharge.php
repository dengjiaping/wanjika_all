<?php

define('IN_ECS', true);
require('/web/ecshop/includes/init.php');
require(ROOT_PATH . 'includes/lib_order.php');

$file_in = file_get_contents("php://input");
$request=simplexml_load_string($file_in);
$r = (array)$request;
//file_put_contents("/tmp/request_mob.txt",$logStr);
$file_name = "/web/logs/charge_result_".date("Ymd").".log";
$logStr = "\n[".date("Y-m-d H:i:s")."][".$r['retcode']."][".$r['jno_cli']."][".$r['succ_amount']."]";
file_put_contents($file_name,$logStr,FILE_APPEND);

if($r["retcode"] === "000000"){
    $sql = "SELECT order_id FROM ".$GLOBALS['ecs']->table('order_info') ." WHERE order_sn = ".$r['jno_cli'];
    $order_id = $GLOBALS['db']->getOne($sql);
    if($order_id > 0){
        update_order($order_id, array('shipping_status' => SS_SHIPPED, 'shipping_time' => gmtime()));
    }
}

$xml_data = "<?xml version='1.0' encoding='UTF-8'?><root><retcode>000000</retcode></root>";
echo $xml_data;
?>

