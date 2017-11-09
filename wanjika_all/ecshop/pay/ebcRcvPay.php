<?php

define('IN_ECS', true);
require(dirname(__FILE__) . '/../includes/init.php');
require(dirname(__FILE__) . '/../includes/lib_payment.php');
require(dirname(__FILE__) . '/../includes/lib_order.php');
require(dirname(__FILE__) . '/../includes/modules/payment/ebcpay.php');

//商户密钥
$mercKey = '586ea205';
$dstbdata = $_REQUEST['dstbdata'];
//商户编号
$dstbdatasign = $_REQUEST['dstbdatasign'];
$pramarray = array();
$array = explode("&",$dstbdata);
foreach($array as $v){
    $ss = explode("=",$v);
    $pramarray[$ss[0]] = $ss[1];
}

$result = "";
$des = new DES($mercKey);
$mydstbdatasign = $des->encrypt($dstbdata);
if ($pramarray['returncode'] != '00')
{
    $result = 'PAYFAIL';
}
else if ($dstbdatasign != $mydstbdatasign)
{
    $result="vaildatefail";
}
else
{
    $ordersn = $pramarray['dsorderid'];
    $sql = 'SELECT order_id FROM ' . $ecs->table('order_info') . " WHERE order_sn='$ordersn' AND pay_status='0'";
    $orderId = $db->getOne($sql);
    if (empty($orderId))
    {
        $result="ordernoerror";
    }
    else
    {
        $sql = 'SELECT log_id FROM ' . $ecs->table('pay_log') . " WHERE order_id='$orderId' AND is_paid='0'";
        $logId = $db->getOne($sql);
        if (empty($logId))
        {
            $result="logiderror";
        }
        else
        {
            order_paid($logId);
            $result="success";
            echo "00";
        }
    }
}
$file_name = "/web/pay_log/ebc_notify_".date("Ymd") . ".log";
$logStr = "dstbdata=" . $dstbdata . '|dstbdatasign=' . $dstbdatasign. '|result=' . $result  . "\n";
file_put_contents($file_name, $logStr, FILE_APPEND);

