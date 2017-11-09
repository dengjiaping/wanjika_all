<?php
define('IN_ECS', true);
require(dirname(__FILE__) . '/../includes/init.php');
require(dirname(__FILE__) . '/../includes/lib_order.php');
$mytime = time() - date('Z') - 7200;
//$sql = "select * from ecs_order_info where (order_status=0 or order_status=1) and pay_status=0 and add_time<$mytime limit 10";
$sql = "SELECT * FROM " . $GLOBALS['ecs']->table('order_info'). " WHERE (order_status=0 or order_status=1) and pay_status=0 and add_time<$mytime limit 500";
$results = $GLOBALS['db']->getAll($sql);
if(count($results) == 0){
    exit;
}

$cancel_note = "您的订单在下单后2小时内未付款，订单已被取消";
$action_note = "订单在下单后2小时内未付款，订单已被取消";

foreach($results as $order){
    $order['order_status'] = OS_CANCELED;
    $order['to_buyer'] = $cancel_note;
    update_order($order['order_id'], $order);

    //如果是海淘订单，取消订单推送任务
    if($order['is_overseas']){
        $order_no = $order['order_sn'];
        $sql = "UPDATE ".$GLOBALS['ecs']->table('kjt_task') ." SET task_status = 13 WHERE order_no = '$order_no'";
        $GLOBALS['db']->query($sql);
    }

    /* 记录log */
//    order_action($order['order_sn'], OS_CANCELED, $order['shipping_status'], PS_UNPAYED, $action_note);

    /* 如果使用库存，且下订单时减库存，则增加库存 */
    if ($_CFG['use_storage'] == '1' && $_CFG['stock_dec_time'] == SDT_PLACE)
    {
        change_order_goods_storage($order['order_id'], false, SDT_PLACE);
    }

    /* 退还用户余额、积分、红包 */
    return_user_surplus_integral_bonus($order);

    //记录日志
    record_log($order['order_sn']);
}

function return_user_surplus_integral_bonus($order)
{
    /* 处理余额、积分、红包 */
    if ($order['user_id'] > 0 && $order['surplus'] > 0)
    {
        $surplus = $order['money_paid'] < 0 ? $order['surplus'] + $order['money_paid'] : $order['surplus'];
        log_account_change($order['user_id'], $surplus, 0, 0, 0, sprintf($GLOBALS['_LANG']['return_order_surplus'], $order['order_sn']));
        $GLOBALS['db']->query("UPDATE ". $GLOBALS['ecs']->table('order_info') . " SET `order_amount` = '0' WHERE `order_id` =". $order['order_id']);
    }

    if ($order['user_id'] > 0 && $order['integral'] > 0)
    {
        log_account_change($order['user_id'], 0, 0, 0, $order['integral'], sprintf($GLOBALS['_LANG']['return_order_integral'], $order['order_sn']));
    }
    //是否为已拆单订单（如已拆单，不退还优惠券）
    if($order['supplier_status']!=SS_ALREADY)
    {
        if ($order['bonus_id'] > 0)
        {
            unuse_bonus($order['bonus_id']);
        }
    }
    //退还礼品卡
    if ($order['gift_card_id'] > 0)
    {
        change_user_gift($order['gift_card_id'],$order['order_sn'],$order['gift_money'],true);
    }

    /* 修改订单 */
    $arr = array(
        'bonus_id'  => 0,
        'bonus'     => 0,
        'integral'  => 0,
        'integral_money'    => 0,
        'surplus'   => 0
    );
    update_order($order['order_id'], $arr);
}

function record_log($orderid) {
    $file_name = "/web/logs/order_cancel_".date("Ymd").".log";
    $logStr = "\n[".$orderid."]";
    file_put_contents($file_name,$logStr,FILE_APPEND);
}

