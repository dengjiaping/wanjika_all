<?php
//订单自动确认任务
define('IN_ECS', true);
require(dirname(__FILE__) . '/../includes/init.php');
require(dirname(__FILE__) . '/../includes/lib_order.php');
$mytime = time() - date('Z') - 1209600;
//var_dump(local_date('Y-m-d H:i:s',$mytime));exit;

$sql = "SELECT order_id,order_sn,order_status,shipping_status,pay_status,shipping_time,extension_code,extension_id,goods_amount,integrals FROM " . $GLOBALS['ecs']->table('order_info'). " WHERE order_status=5 and shipping_status=1 and pay_status=2 and shipping_time<$mytime order by order_id desc limit 500";
$results = $GLOBALS['db']->getAll($sql);

if(count($results) == 0){
    exit;
}

$action_note = "订单在发货14天后，订单已被自动确认";
$o =array();
$o['order_status'] = OS_SPLITED;
$o['shipping_status'] = SS_RECEIVED;
$o['pay_status'] = PS_PAYED;
foreach($results as $order){
    if($order['order_status'] == 5 && $order['shipping_status'] == 1 && $order['pay_status'] == 2 && $order['shipping_time'] < $mytime)
    {
        update_order($order['order_id'], $o);
        /* 计算并发放积分 */
        $integral = integral_to_give($order);

        log_account_change($order['user_id'], 0, 0, intval($integral['rank_points']), intval($integral['custom_points']), sprintf($_LANG['order_gift_integral'], $order['order_sn']),99,$order['integrals'],$order['order_sn']);

        /* 记录log */
        order_action($order['order_sn'], 5, 2, 2, $action_note,'系统自动确认');
        //记录日志
        record_log($order['order_sn']);
    }
}
function record_log($orderid) {
    $file_name = "/web/logs/order_confirm_".date("Ymd").".log";
    $logStr = "\n[".$orderid."]";
    file_put_contents($file_name,$logStr,FILE_APPEND);
}

