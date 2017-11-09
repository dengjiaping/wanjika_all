<?php
define('IN_ECS', 1);
require(dirname(__FILE__) . '/../includes/init.php');
require(dirname(__FILE__) . '/../includes/lib_order.php');
require_once (dirname ( __FILE__ ) .'/config/Config.php');

$mytime = time() - date('Z') - 86400;
$sql = "select order_id,money_paid,order_sn,user_id,surplus,integral,bonus_id from ecs_order_info where (order_status=0 or order_status=1) and pay_status=0 and add_time<$mytime limit 10000";
$results = $GLOBALS['db']->getAll($sql);
if(count($results) == 0){
    exit;
}

$cancel_note = "您的订单在下单后24小时内未付款，订单已被取消";
$action_note = "订单在下单后24小时内未付款，订单已被取消";

foreach($results as $order){
    $arr = array(
        'order_status'  => OS_CANCELED,
        'to_buyer'      => $cancel_note,
        'pay_status'    => PS_UNPAYED,
        'pay_time'      => 0,
        'money_paid'    => 0,
        'order_amount'  => $order['money_paid']
    );
    update_order($order['order_id'], $arr);  

    /* 记录log */
//    order_action($order['order_sn'], OS_CANCELED, $order['shipping_status'], PS_UNPAYED, $action_note);

    /* 如果使用库存，且下订单时减库存，则增加库存 */
    if ($_CFG['use_storage'] == '1' && $_CFG['stock_dec_time'] == SDT_PLACE)
    {
        change_order_goods_storage($order['order_id'], false, SDT_PLACE);
    }

    /* 退还用户余额、积分、红包 */
    return_user_surplus_integral_bonus($order);

    /* 发送邮件 */
    $cfg = $_CFG['send_cancel_email'];
    if ($cfg == '1')
    {
        $tpl = get_mail_template('order_cancel');
        $smarty->assign('order', $order);
        $smarty->assign('shop_name', $_CFG['shop_name']);
        $smarty->assign('send_date', local_date($_CFG['date_format']));
        $smarty->assign('sent_date', local_date($_CFG['date_format']));
        $content = $smarty->fetch('str:' . $tpl['template_content']);
        if (!send_mail($order['consignee'], $order['email'], $tpl['template_subject'], $content, $tpl['is_html']))
        {
            $msg = $_LANG['send_mail_fail'];
        }
    }

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

    if ($order['bonus_id'] > 0)
    {
        unuse_bonus($order['bonus_id']);
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

