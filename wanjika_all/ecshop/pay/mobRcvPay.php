<?php
//==============test=====================
define('IN_ECS', true);
require('/web/ecshop/includes/init.php');
require(ROOT_PATH . 'includes/lib_payment.php');
require(ROOT_PATH . 'includes/lib_order.php');
//require(ROOT_PATH . 'includes/modules/payment/mobpay.php');

$logStr = base64_encode(serialize($_REQUEST));
file_put_contents("/tmp/request_mob.txt",$logStr);
$tmp = file("/tmp/request.txt");
$file_name = "/web/pay_log/mobRcv_notify_".date("Ymd").".log";
$logStr = "\n[".date("Y-m-d H:i:s")."][".$_REQUEST['merchantId']."][".$_REQUEST['payNo']."][".$_REQUEST['returnCode']."][".$_REQUEST['message']."][".$_REQUEST['signType']."][".$_REQUEST['type']."][".$_REQUEST['version']."][".$_REQUEST['amount']."][".$_REQUEST['amtItem']."][".$_REQUEST['bankAbbr']."][".$_REQUEST['mobile']."][".$_REQUEST['orderId']."][".$_REQUEST['payDate']."][".$_REQUEST['accountDate']."][".$_REQUEST['reserved1']."][".$_REQUEST['reserved2']."][".$_REQUEST['status']."][".$_REQUEST['orderDate']."][".$_REQUEST['fee']."][".$_REQUEST['serverCert']."][".$_REQUEST['hmac']."]";
file_put_contents($file_name,$logStr,FILE_APPEND);
//$mobpay = new mobpay();

/* 支付方式代码 */
//$pay_code="mobpay";

require("/web/ecshop/includes/modules/payment/mobpay/globalParam.php");
require("/web/ecshop/includes/modules/payment/mobpay/callcmpay.php");
$merchantId  = $_REQUEST['merchantId'];//商户编号
$payNo       = $_REQUEST['payNo'];//流水号
$returnCode  = $_REQUEST['returnCode'];//返回码
$message     = $_REQUEST['message'];//返回码描述信息
$signType    = $_REQUEST['signType'];//签名方式
$type        = $_REQUEST['type'];//接口类型
$version     = $_REQUEST['version'];//版本号
$amount      = $_REQUEST['amount'];//支付金额
$amtItem     = $_REQUEST['amtItem'];//金额明细
$bankAbbr    = $_REQUEST['bankAbbr'];//支付银行
$mobile      = $_REQUEST['mobile'];//支付手机号
$orderId     = $_REQUEST['orderId'];//商户订单号
$payDate     = $_REQUEST['payDate'];//支付时间
$accountDate = $_REQUEST['accountDate'];//会计日期
$reserved1   = $_REQUEST['reserved1'];//保留字段1
$reserved2   = $_REQUEST['reserved2'];//保留字段2
$status      = $_REQUEST['status'];//支付结果
$orderDate   = $_REQUEST['orderDate'];//订单提交日期
$fee         = $_REQUEST['fee'];//费用
$serverCert  = $_REQUEST['serverCert'];//服务器证书公钥
$hmac        = $_REQUEST['hmac'];//签名数据


//重新组装签名字符串
//组装签字符串
$signData = $merchantId .$payNo.$returnCode .$message
.$signType   .$type        .$version    .$amount
.$amtItem    .$bankAbbr    .$mobile     .$orderId
.$payDate    .$accountDate .$reserved1  .$reserved2
.$status     .$orderDate   .$fee;

//MD5方式签名
$signKey=$GLOBALS['signKey'];
//根据虚拟和实物来判断使用哪一个signKey
if ($merchantId == $GLOBALS['merchantId_virtual'])
{
    $signKey = $GLOBALS['signKey_virtual'];
}
$vhmac=MD5sign($signKey,$signData);
$merArr = unserialize(base64_decode($reserved1));
$log_id = $merArr['log_id'];
$bank_abbr = $merArr['bank_abbr'];
//此处000000仅代表程序无错误。订单是否支付成功是以支付结果（status）为准
if($returnCode!=000000)
{
	echo $returnCode.decodeUtf8($message);
}

if($hmac != $vhmac)
{
	echo "验签失败";
}
else {
	if ($status == 'SUCCESS')  //有成功支付的结果返回0000
	{
		order_paid($log_id, PS_PAYED, '', $bank_abbr);
		echo "SUCCESS";
	}
	else
	{
		//'实际支付金额不能小于0';
		echo $returnCode.decodeUtf8($message);
	}
}
?>

