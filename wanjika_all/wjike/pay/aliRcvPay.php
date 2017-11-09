<?php
//==============test=====================
define('IN_ECS', true);
require('/web/wjike/includes/init.php');
require('/web/wjike/includes/lib_payment.php');
require('/web/wjike/includes/lib_order.php');

$logStr1 = serialize($_REQUEST);
$file_name1 = "/web/pay_logs/aliRcv_notify_oristr_".date("Ymd").".log";
file_put_contents($file_name1,"\n".$logStr1,FILE_APPEND);

$file_name = "/web/pay_logs/alipay_paras_".date("Ymd").".log";
$logStr = "\n========================================";
if (!empty($_POST))
{
    foreach($_POST as $key => $data)
    {

        $logStr .= "\n$key:$data";
        $_GET[$key] = $data;
    }
}
file_put_contents($file_name,$logStr,FILE_APPEND);
$payment  = get_payment('alipay');
$seller_email = rawurldecode($_GET['seller_email']);
$order_sn = str_replace($_GET['subject'], '', $_GET['out_trade_no']);
$order_sn = trim($order_sn);

/* 检查支付的金额是否相符 */
if (!check_money($order_sn, $_GET['total_fee']))
{
    $logStr = "\n[MOENY ERROR]";
    file_put_contents($file_name,$logStr,FILE_APPEND);
    return false;
}

/* 检查数字签名是否正确 */
ksort($_GET);
reset($_GET);

$sign = '';
foreach ($_GET AS $key=>$val)
{
    if ($key != 'sign' && $key != 'sign_type' && $key != 'code')
    {
        $sign .= "$key=$val&";
    }
}

$sign = substr($sign, 0, -1) . $payment['alipay_key'];
//$sign = substr($sign, 0, -1) . ALIPAY_AUTH;
if (md5($sign) != $_GET['sign'])
{
    $logStr = "\n[SIGN ERROR]";
    file_put_contents($file_name,$logStr,FILE_APPEND);
    return false;
}

if ($_GET['trade_status'] == 'WAIT_SELLER_SEND_GOODS')
{
    /* 更新第三方支付接口交易流水号 */
    update_trade_no($_GET['subject'], $_GET['trade_no']);
    /* 改变订单状态 */
    order_paid($order_sn, 2);

    $logStr = "\n[SUCCESS]";
    file_put_contents($file_name,$logStr,FILE_APPEND);
    echo "SUCCESS";
}
elseif ($_GET['trade_status'] == 'TRADE_FINISHED')
{
    /* 更新第三方支付接口交易流水号 */
    update_trade_no($_GET['subject'], $_GET['trade_no']);
    /* 改变订单状态 */
    order_paid($order_sn);

    $logStr = "\n[SUCCESS]";
    file_put_contents($file_name,$logStr,FILE_APPEND);
    echo "SUCCESS";
}
elseif ($_GET['trade_status'] == 'TRADE_SUCCESS')
{
    /* 更新第三方支付接口交易流水号 */
    update_trade_no($_GET['subject'], $_GET['trade_no']);
    /* 改变订单状态 */
    order_paid($order_sn, 2);

    $logStr = "\n[SUCCESS]";
    file_put_contents($file_name,$logStr,FILE_APPEND);
    echo "SUCCESS";
}
else
{
    return false;
}
?>

