<?php
/*
//==============test=====================
define('IN_ECS', true);
$_REQUEST['code'] = "umpay";
//商户号码
$_REQUEST['merId'] = "7145";
//商品产品
$_REQUEST['goodsId'] = "001";
//订单号
$_REQUEST['orderId'] = "21";
//商户下单日期
$_REQUEST['merDate'] = "20130413";
//支付日期
$_REQUEST['payDate'] = "20130413";
//金额
$_REQUEST['amount'] = 1010;
//金额类型
$_REQUEST['amtType'] = "02";
//银行类型
$_REQUEST['bankType'] = 3;
//手机号
$_REQUEST['mobileId'] = "15810629113";
//交易类型
$_REQUEST['transType'] = "0";
//结算日期
$_REQUEST['settleDate'] = "20130413";
//商户私有信息
$_REQUEST['merPriv'] = "";
//返回码
$_REQUEST['retCode'] = "0000";
//版本
$_REQUEST['version'] = "3.0";
//签名
$_REQUEST['sign'] = "shZXtlchGP3uEkYcVT+wSvZt83wyhcxfQjN6Z2j5mxdA8bW7VlH3Dvu8X3hJw9hA/AhleX2Ec8y1PqMMgOmS5svDCKz993zQx6RLA1DFZfU19If4slDYB2bVQGBa/rEv0phypMeoPaBWnNdFQKrBw+IbXt+ti50EvbGfdORGG6I=";
//订单支付记录
$_REQUEST['log_id'] = 24;
//==============test=================
*/
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
require(ROOT_PATH . 'includes/lib_payment.php');
require(ROOT_PATH . 'includes/lib_order.php');
require(ROOT_PATH . 'includes/modules/payment/umpay.php');

$src = "YToxNzp7czo1OiJtZXJJZCI7czo0OiI3MTQ1IjtzOjc6Imdvb2RzSWQiO3M6MzoiMDAxIjtzOjc6Im9yZGVySWQiO3M6MjoiMjciO3M6NzoibWVyRGF0ZSI7czo4OiIyMDEzMDQxNCI7czo3OiJwYXlEYXRlIjtzOjg6IjIwMTMwNDEzIjtzOjY6ImFtb3VudCI7czozOiIxMDAiO3M6NzoiYW10VHlwZSI7czoyOiIwMiI7czo4OiJiYW5rVHlwZSI7czoxOiIzIjtzOjg6Im1vYmlsZUlkIjtzOjExOiIxNTgxMDYyOTExMyI7czo5OiJ0cmFuc1R5cGUiO3M6MToiMCI7czoxMDoic2V0dGxlRGF0ZSI7czo4OiIyMDEzMDQxMyI7czo3OiJtZXJQcml2IjtzOjU6IndqaWtlIjtzOjc6InJldENvZGUiO3M6NDoiMDAwMCI7czo3OiJ2ZXJzaW9uIjtzOjM6IjMuMCI7czo0OiJzaWduIjtzOjE3MjoiVkZMMVc4TTM2MS9PYUpGUEVpL3R3Ty9RN1Bvb3dKMXlwTlNIQTlqVUZmUFE4NEZ3N3doSnM3SlcrQ3NHNjBuOFpycFhQbUYvWUJ3dEltTDg3cXhuRFZXcHAzS3NyeEJSV3BueTNpeTNXNmJZQkhUc1pFeUtJK28vUGlPYm80RTlEVUQxMUdkZXlHNW83NkhhSWtEcU5wd3lweW1EemsvVnBnWjh3dHBCRVJVPSI7czozOiJFQ1MiO2E6Mzp7czoxMToidmlzaXRfdGltZXMiO3M6MToiOSI7czo3OiJkaXNwbGF5IjtzOjQ6ImdyaWQiO3M6NzoiaGlzdG9yeSI7czozOiI5MTkiO31zOjY6IkVDU19JRCI7czo0MDoiZjA0ZTc4YzQ1YTYzODI1MzgzNTkyNTZkMDEwMjZjZjM5ZTExZGU3MCI7fQ==";
$arr = unserialize(base64_decode($src));
$srcMer = array("code"=>"umpay","log_id" =>30);
$arr['merPriv'] = base64_encode(serialize($srcMer));
print_r($arr);
$_REQUEST = $arr;
//$logStr = base64_encode(serialize($_REQUEST));
//file_put_contents("/tmp/request.txt",$logStr);
$umpay = new umpay();
$merArr = unserialize(base64_decode($_REQUEST['merPriv']));
print_r($merArr);
/* 支付方式代码 */
$pay_code = !empty($merArr['code']) ? trim($merArr['code']) : '';
if (empty($pay_code) && ($_REQUEST['merId'] == '7145')) {
	$pay_code=="umpay";
}
//商户号码
$merId = $_REQUEST['merId'];
//商品产品
$goodsId = $_REQUEST['goodsId'];
//订单号
$orderId = $_REQUEST['orderId'];
//商户下单日期
$merDate = $_REQUEST['merDate'];
//支付日期
$payDate = $_REQUEST['payDate'];
//金额
$amount = $_REQUEST['amount'];
//金额类型
$amtType = $_REQUEST['amtType'];
//银行类型
$bankType = $_REQUEST['bankType'];
//手机号
$mobileId = $_REQUEST['mobileId'];
//交易类型
$transType = $_REQUEST['transType'];
//结算日期
$settleDate = $_REQUEST['settleDate'];
//商户私有信息
$merPriv = $_REQUEST['merPriv'];
//返回码
$retCode = $_REQUEST['retCode'];
//版本
$version = $_REQUEST['version'];
//签名
$sign = $_REQUEST['sign'];
//订单支付记录
$log_id = $merArr['log_id'];
?>
<!--------------------------------------------->
<!--2、定义响应参数                          -->
<!--------------------------------------------->
<?php
//返回码
$ret_retcode="";
//支付日期
$retMsg="";
?>
<!--------------------------------------------->
<!--3、验签                                  -->
<!--------------------------------------------->
<?php
//重新组装签名字符串
//根据手机钱包商户接入规范2.2V的5.1.2.1组装字符串，进行验签
$paramNew="";
$paramNew =$paramNew . "merId=" . trim($merId,"\x00..\x1F");
$paramNew =$paramNew . "&goodsId=" . trim($goodsId,"\x00..\x1F");
$paramNew =$paramNew . "&orderId=" . trim($orderId,"\x00..\x1F");
$paramNew =$paramNew . "&merDate=" . trim($merDate,"\x00..\x1F");
$paramNew =$paramNew . "&payDate=" . trim($payDate,"\x00..\x1F");
$paramNew =$paramNew . "&amount=" . trim($amount,"\x00..\x1F");
$paramNew = $paramNew . "&amtType=" . trim($amtType,"\x00..\x1F");
$paramNew = $paramNew . "&bankType=" . trim($bankType,"\x00..\x1F");
if(!empty($mobileId)){
	$paramNew =$paramNew . "&mobileId=" . trim($mobileId,"\x00..\x1F");
}
$paramNew = $paramNew . "&transType=" . trim($transType,"\x00..\x1F");
$paramNew = $paramNew . "&settleDate=" . trim($settleDate,"\x00..\x1F");
if(!empty($merPriv)){
	$paramNew = $paramNew . "&merPriv=" . trim('wjike',"\x00..\x1F");
}
$paramNew = $paramNew . "&retCode=". trim($retCode,"\x00..\x1F");
$paramNew = $paramNew . "&version=". trim($version,"\x00..\x1F");
//验签
$certfile = "/web/ecshop/includes/cert_2d59.cert.pem";
$result=$umpay->ssl_verify($paramNew,$sign,$certfile);
if(!$result)
{
	//商户在此处做相关的验签失败的处理，如果失败说明有不正常的客户端在访问支付结果通知
	//验签失败后，返回码必然是不成功的
	$sing_result = "验签失败,签名原文:"+url+"签名数据:"+sign+"<br/>";
	$retMsg="验签失败,电话4006125880";
	$retCode="1111";
}else
{
	//更新订单
	order_paid($log_id);
}
?>
<!--------------------------------------------->
<!--4、商户对支付结果处理                    -->
<!--------------------------------------------->
<?php
//商户在此处对支付结果做详细处理。
//商户可以根据处理支付结果的情况来决定返回码是否为0000,成功为0000,失败为1111
//失败需要冲帐
$sing_result="验签成功";

//if (处理支付结果成功) {
$retCode="0000";
$description="支付成功，电话4006125880";
//}
?>
<!--------------------------------------------->
<!--5、生成签名                              -->
<!--------------------------------------------->
<?php
//组装需要签名的返回字符串，主要根据文档2.2的5.2.2来组装
//返回码          //交易日期    //商户号  //交易流水号
$ret_signtext = $merId . "|" . $goodsId . "|" . $orderId . "|" . $merDate . "|" . $retCode . "|" . $retMsg . "|" . $version;
//生成签名
$priv_key_file = "/web/ecshop/includes/7145_WanJiKe.key.pem";
$ret_sign=$umpay->ssl_sign($ret_signtext,$priv_key_file);
?>
<!--------------------------------------------->
<!--6、组装响应串并返回                      -->
<!--------------------------------------------->
<?php
//响应的字符串
$ret_signtext=$ret_signtext . "|" . $ret_sign;
?>
<html><head>
<meta http-equiv="Content-Type" content="text/html; charset=GB18030">
<!-- 这个就是响应手机钱包业务平台的信息 -->
<META NAME="MobilePayPlatform" CONTENT="<?php echo $ret_signtext ?>">
<title></title>
</head>
<body>
</body>
</html>
