<?php

define('IN_ECS', true);
require(dirname(__FILE__) . '/../includes/init.php');
require(dirname(__FILE__) . '/../includes/lib_payment.php');
require(dirname(__FILE__) . '/../includes/lib_order.php');
require(dirname(__FILE__) . '/../includes/modules/payment/guomeipay.php');

$umpay = new guomeipay();

//获取配置信息中的商户密钥
$pay_config = $GLOBALS['db']->getOne("SELECT pay_config FROM " . $GLOBALS['ecs']->table('payment') . " WHERE pay_code = 'guomeipay' AND enabled = 1");
$pay_config = unserialize($pay_config);
$mercKey = "";
foreach($pay_config as $v)
{
    if ($v['name'] == 'guomeipay_key')
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
    echo "PAYFAIL";
}
else if ($hmac != $hmacShould)
{
    $result="vaildatefail";
    echo "VALIDATEFAIL";
}
else
{
    $sql = 'SELECT order_id FROM ' . $ecs->table('order_info') . " WHERE order_sn='$mercOrderNo' AND pay_status='0'";
    $orderId = $db->getOne($sql);
    if (empty($orderId))
    {
        $result="ordernoerror";
        echo "ORDERNOERROR";
    }
    else
    {
        $sql = 'SELECT log_id FROM ' . $ecs->table('pay_log') . " WHERE order_id='$orderId' AND order_amount='$payAmt' AND is_paid='0'";
        $logId = $db->getOne($sql);
        if (empty($logId))
        {
            $result="logiderror";
            echo "LOGIDERROR";
        }
        else
        {
            order_paid($logId);
            $result="success";
            echo "SUCCESS";
        }
    }
}

$file_name = "/web/pay_logs/guomei_notify_".date("Ymd") . ".log";
$logStr = "charset=" . $charset . '|merid=' . $mercId . '|signType=' . $signType . '|interface=' . $interfaceName . '|version=' .  $version . '|mercOrdNo=' .
    $mercOrderNo . '|amount=' . $amount . '|paySts=' . $paySts . '|payAmt=' . $payAmt . '|payDt=' . $payDt . '|payTime=' .
    $payTime . '|hmac=' . $hmac . '|hmacShould=' . $hmacShould . '|result=' . $result . "\n";
file_put_contents($file_name, $logStr, FILE_APPEND);
