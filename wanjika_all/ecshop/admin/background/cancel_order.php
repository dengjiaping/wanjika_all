<?php

/**
 *
 *取消超过24小时未付款的订单
 *
 * @author  qihua
 */

define('IN_ECS', true);

require('./init.php');
require_once(ROOT_PATH . 'includes/lib_order.php');

//获取超过24小时未付款的订单
$sql = "SELECT * FROM " . $ecs->table("order_info") . " WHERE pay_status = " . PS_UNPAYED .
        " AND order_status = " . OS_UNCONFIRMED . " AND add_time <= " . gmtime() - 86400 . " LIMIT 10000";

$order_list = $db->getAll($sql);

if (empty($order_list))
{
    exit;
}

foreach($order_list as $order)
{
    if (empty($order))
    {
        continue;
    }

    $order_id = $order['order_id'];

    /* 标记订单为“取消”，记录取消原因 */
    $cancel_note = '订单过期';
    update_order($order_id, array('order_status' => OS_CANCELED, 'to_buyer' => $cancel_note));

    /* 记录log */
    order_action($order['order_sn'], OS_CANCELED, $order['shipping_status'], PS_UNPAYED, $action_note);

    /* 如果使用库存，且下订单时减库存，则增加库存 */
    if ($_CFG['use_storage'] == '1' && $_CFG['stock_dec_time'] == SDT_PLACE)
    {
        change_order_goods_storage($order_id, false, SDT_PLACE);
    }

    /* 退还用户余额、积分、红包 */
    return_user_surplus_integral_bonus($order);
}

exit;

?>