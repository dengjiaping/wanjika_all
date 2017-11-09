<?php
//==============test=====================
define('IN_ECS', true);
require('/web/ecshop/includes/init.php');
require(ROOT_PATH . 'includes/lib_payment.php');
require(ROOT_PATH . 'includes/lib_order.php');

$file_name = "/web/pay_logs/telecomRcv_notify_".date("Ymd").".log";
$logStr = "\n[".date("Y-m-d H:i:s")."][".$_REQUEST['ORDERSEQ']."][".$_REQUEST['ORDERREQTRANSEQ']."][".$_REQUEST['TRANDATE']."][".$_REQUEST['ORDERAMOUNT']."][".$_REQUEST['RETNINFO']."]";
file_put_contents($file_name,$logStr,FILE_APPEND);

$mac_str = "UPTRANSEQ=".$_REQUEST['UPTRANSEQ']."&MERCHANTID=02110108040613000&ORDERID=".$_REQUEST['ORDERSEQ']."&PAYMENT=".$_REQUEST['ORDERAMOUNT']."&RETNCODE=".$_REQUEST['RETNCODE']."&RETNINFO=".$_REQUEST['RETNINFO']."&PAYDATE=".$_REQUEST['TRANDATE']."&KEY=9E7BDBE099C7D1DA2C9F93D1A4E366584072FD586540C2BB";
$mac = strtoupper(md5($mac_str));
if($mac!=$_REQUEST['SIGN'])
{
    $file_name = "/web/pay_logs/sign_failed_".date("Ymd").".log";
    $logStr = "\n[".$mac_str."][".$mac."][".$_REQUEST['SIGN']."]";
    file_put_contents($file_name,$logStr,FILE_APPEND);
    echo "验签失败";
}
else {
    $order_sn = str_replace($_REQUEST['ORDERSEQ'], '', $_REQUEST['ORDERREQTRANSEQ']);
    order_paid($order_sn);
    echo "UPTRANSEQ_".$_REQUEST['UPTRANSEQ'];
}
?>

