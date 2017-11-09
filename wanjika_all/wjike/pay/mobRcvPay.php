<?php
//==============test=====================
define('IN_ECS', true);

require('/web/wjike/includes/init.php');
require('/web/wjike/includes/lib_payment.php');
require('/web/wjike/includes/lib_order.php');

$logStr1 = serialize($_REQUEST);
$file_name1 = "/web/pay_logs/mobRcv_notify_oristr_".date("Ymd").".log";
file_put_contents($file_name1,"\n".$logStr1,FILE_APPEND);
$file_name2 = "/web/pay_logs/mobRcv_notify_".date("Ymd").".log";
$logStr2 = "\n[".date("Y-m-d H:i:s")."][".$_REQUEST['merchantId']."][".$_REQUEST['payNo']."][".$_REQUEST['returnCode']."][".$_REQUEST['message']."][".$_REQUEST['signType']."][".$_REQUEST['type']."][".$_REQUEST['version']."][".$_REQUEST['amount']."][".$_REQUEST['amtItem']."][".$_REQUEST['bankAbbr']."][".$_REQUEST['mobile']."][".$_REQUEST['orderId']."][".$_REQUEST['payDate']."][".$_REQUEST['accountDate']."][".$_REQUEST['reserved1']."][".$_REQUEST['reserved2']."][".$_REQUEST['status']."][".$_REQUEST['orderDate']."][".$_REQUEST['fee']."][".$_REQUEST['serverCert']."][".$_REQUEST['hmac']."]";
file_put_contents($file_name2,$logStr2,FILE_APPEND);

/* 支付方式代码 */

require("/web/wjike/includes/modules/payment/mobpay/globalParam.php");
require("/web/wjike/includes/modules/payment/mobpay/callcmpay.php");
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

/* 检查支付的金额是否相符 */
if (!check_bank_money($log_id, $amount))
{
    $file_name = "/web/pay_logs/mobRcv_notify_".date("Ymd").".log";
    $logStr = "\n[price_false][".$_REQUEST['payNo']."][".$log_id."][".$amount."]";
    file_put_contents($file_name,$logStr,FILE_APPEND);
    echo "金额错误";
    return false;
}

if($hmac != $vhmac)
{
    $file_name = "/web/pay_logs/mobRcv_notify_".date("Ymd").".log";
    $logStr = "\n[sign_false][".$_REQUEST['payNo']."]";
    file_put_contents($file_name,$logStr,FILE_APPEND);
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
        $file_name = "/web/pay_logs/mobRcv_notify_".date("Ymd").".log";
        $logStr = "\n[amount_false][".$_REQUEST['payNo']."]";
        file_put_contents($file_name,$logStr,FILE_APPEND);
		//'实际支付金额不能小于0';
		echo $returnCode.decodeUtf8($message);
	}
}
?>

