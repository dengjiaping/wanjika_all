<?php

define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
require(ROOT_PATH . 'includes/lib_payment.php');
require(ROOT_PATH . 'includes/lib_order.php');
require(ROOT_PATH . 'includes/modules/payment/suixingpay.php');

$umpay = new suixingpay();

//获取配置信息中的商户号和商户密钥
$pay_config = $GLOBALS['db']->getOne("SELECT pay_config FROM " . $GLOBALS['ecs']->table('payment') . " WHERE pay_code = 'suixingpay' AND enabled = 1");
$pay_config = unserialize($pay_config);
$mercKey = "";
foreach($pay_config as $v)
{
    if ($v['name'] == 'suixingpay_key')
    {
        $mercKey = $v['value'];
    }
}

//字符集
$charset = $_REQUEST['charset'];
//商户编号
$mercId = $_REQUEST['mercId'];
//加密类型
$signType = $_REQUEST['signType'];
//接口类型
$interfaceName = $_REQUEST['interface'];
//版本号
$version = $_REQUEST['version'];
//商户订单号
$mercOrderNo = $_REQUEST['mercOrderNo'];
//订单金额
$amount = $_REQUEST['ordAmt'];
//支付状态
$paySts = $_REQUEST['paySts'];
//支付金额
$payAmt = $_REQUEST['payAmt'];
//支付日期
$payDt = $_REQUEST['payDt'];
//页面通知地址
$payTime = $_REQUEST['payTime'];
//签名数据
$hmac = $_REQUEST['hmac'];

/* 生成加密签名串，将密钥加到后面，md5之~*/
$hmacStr = $charset . $mercId . $signType . $interfaceName . $version . $mercOrderNo . $amount . $paySts . $payAmt;
$hmacStr .= $mercKey;
$hmacShould = md5($hmacStr);

$result = "";
if ($paySts != 'S')
{
    $result = 'PAYFAIL';
    $result = 'payfail';
}
else if ($hmac != $hmacShould)
{
    echo "VALIDATEFAIL";
    $result="vaildatefail";
}
else
{
    $sql = 'SELECT order_id FROM ' . $ecs->table('order_info') . " WHERE order_sn='$mercOrderNo' AND pay_status='0'";
    $orderId = $db->getOne($sql);
    if (empty($orderId))
    {
        echo "ORDERNOERROR";
        $result="ordernoerror";
    }
    else
    {
        $sql = 'SELECT log_id FROM ' . $ecs->table('pay_log') . " WHERE order_id='$orderId' AND order_amount='$payAmt' AND is_paid='0'";
        $logId = $db->getOne($sql);
        if (empty($logId))
        {
            echo "LOGIDERROR";
            $result="logiderror";
        }
        else
        {
            order_paid($logId);
            echo "SUCCESS";
            $result="success";
        }
    }
}

$file_name = "/web/pay_log/suixing_notify_".date("Ymd") . ".log";
$logStr = "charset=" . $charset . '|merid=' . $mercId . '|signType=' . $signType . '|interface=' . $interfaceName . '|version=' .  $version . '|mercOrdNo=' .
            $mercOrderNo . '|amount=' . $amount . '|paySts=' . $paySts . '|payAmt=' . $payAmt . '|payDt=' . $payDt . '|payTime=' .
            $payTime . '|hmac=' . $hmac . '|hmacShould=' . $hmacShould . '|result=' . $result . "\n";
file_put_contents($file_name, $logStr, FILE_APPEND);
