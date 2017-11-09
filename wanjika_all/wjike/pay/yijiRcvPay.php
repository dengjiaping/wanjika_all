<?php

define('IN_ECS', true);
require(dirname(__FILE__) . '/../includes/init.php');
require(dirname(__FILE__) . '/../includes/lib_payment.php');
require(dirname(__FILE__) . '/../includes/lib_order.php');
require(dirname(__FILE__) . '/../includes/modules/payment/yijipay.php');

$logStr1 = serialize($_REQUEST);
$file_name1 = "/web/pay_logs/yijiRcv_notify_oristr_".date("Ymd").".log";
file_put_contents($file_name1,"\n".$logStr1,FILE_APPEND);

$file_name = "/web/pay_logs/yijipay_paras_".date("Ymd").".log";
$logStr = "\n========================================";

$params = array();
if (!empty($_POST))
{
    foreach($_POST as $key => $data)
    {
        $logStr .= "\n$key:$data";
        $_GET[$key] = $data;
        if($key != 'sign'){
            $params[$key] = $data;
        }
    }
}

$yijipay = new yijipay();

//获取配置信息中的商户密钥
$pay_config = $GLOBALS['db']->getOne("SELECT pay_config FROM " . $GLOBALS['ecs']->table('payment') . " WHERE pay_code = 'yijipay' AND enabled = 1");
$pay_config = unserialize($pay_config);
$security_key = '';
foreach($pay_config as $v)
{
    if ($v['name'] == 'security_key')
    {
        $security_key = $v['value'];
    }
}


$order_sn = $_GET['outOrderNo'];
$sql = 'SELECT order_id,order_amount FROM ' . $ecs->table('order_info') . " WHERE order_sn='$order_sn' AND pay_status='0'";
$orderinfo = $db->getRow($sql);
$orderId = $orderinfo['order_id'];
$order_amount = $orderinfo['order_amount'];

$signStr = $yijipay->digest($params, $security_key, 'MD5');

$result = "";
if ($_GET['tradeStatus'] != 'trade_finished')
{
    $result = 'PAYFAIL';
    echo "PAYFAIL";
}
else if ($_GET['sign'] != $signStr)
{
    $result="vaildatefail";
    echo "VALIDATEFAIL";
}
else
{
    if (empty($orderId))
    {
        $result="ordernoerror";
        echo "ORDERNOERROR";
    }
    else
    {
        $sql = 'SELECT log_id FROM ' . $ecs->table('pay_log') . " WHERE order_id='$orderId' AND order_amount='$order_amount' AND is_paid='0'";
        $logId = $db->getOne($sql);
        if (empty($logId))
        {
            $result="logiderror";
            echo "LOGIDERROR";
        }
        else
        {
            /* 更新第三方支付接口交易流水号 */
            update_trade_no($_GET['outOrderNo'], $_GET['tradeNo']);
            
            order_paid($logId);
            $result="success";
            echo "SUCCESS";
        }
    }
}

$logStr .= ("\n"."mysign:$signStr");
$logStr .= ("\n"."result:$result");
file_put_contents($file_name,$logStr,FILE_APPEND);
