<?php
//==============test=====================
define('IN_ECS', true);
/*
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
*/
//==============test=================
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
require(ROOT_PATH . 'includes/lib_payment.php');
require(ROOT_PATH . 'includes/lib_order.php');
require(ROOT_PATH . 'includes/modules/payment/umpay.php');

define(BAIZHI, 611853);
define(WJIKA, 762464);

$CALL_BACK_ARR = array(
    BAIZHI => 'http://123.1.150.143:8088/AndroidFree/AndroidNetGameSync.aspx',    //百志
    WJIKA => 'http://www.wjike.com/test.php',
);

//$logStr = base64_encode(serialize($_REQUEST));
$file_name = "/web/pay_log/notify_".date("Ymd").".log";
$logStr = "\n[".date("Y-m-d H:i:s")."][".$_REQUEST['merId']."][".$_REQUEST['goodsId']."][".$_REQUEST['orderId']."][".$_REQUEST['merDate']."][".$_REQUEST['payDate']."][".$_REQUEST['amount']."][".$_REQUEST['amtType']."][".$_REQUEST['bankType']."][".$_REQUEST['mobileId']."][".$_REQUEST['transType']."][".$_REQUEST['settleDate']."][".$_REQUEST['merPriv']."][".$_REQUEST['merPriv']."][".$_REQUEST['retCode']."][".$_REQUEST['version']."][".$_REQUEST['sign']."][".$merArr['log_id']."]";
file_put_contents($file_name, $logStr, FILE_APPEND);
$umpay = new umpay();

/* 支付方式代码 */
$pay_code = !empty($_REQUEST['code']) ? trim($_REQUEST['code']) : '';
if (empty($pay_code) && ($_REQUEST['merId'] == '7145' || $_REQUEST['merId'] == '6844')) {
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
$merArr = unserialize(base64_decode($_REQUEST['merPriv']));
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
	$paramNew = $paramNew . "&merPriv=" . trim($merPriv,"\x00..\x1F");
}
$paramNew = $paramNew . "&retCode=". trim($retCode,"\x00..\x1F");
echo "<br>".$paramNew = $paramNew . "&version=". trim($version,"\x00..\x1F");
echo "<br>";
//验签
$certfile = $_SERVER['DOCUMENT_ROOT']."/includes/cert_2d59.cert.pem";
$result=$umpay->ssl_verify($paramNew, $sign, $certfile);
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

    $sql = "SELECT user_id, order_id FROM " . $GLOBALS['ecs']->table('pay_code_info') . " WHERE wjk_order_id='$orderId'";
    $pinfo = $GLOBALS['db']->getRow($sql);
    if (!empty($pinfo))
    {
        //回调
        $user_id = $pinfo['user_id'];
        $order_id = $pinfo['order_id'];
        $remote_server = $CALL_BACK_ARR[$user_id];
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $remote_server);
        if ($user_id == BAIZHI)
        {
            curl_setopt($ch, CURLOPT_POSTFIELDS, "order_id=$order_id");
        }
        else if ($user_id == WJIKA)
        {
            $md5_sign_key = 'wDU6KEtBRJ8qhGQOLi9wyNIqpDfD7v';
            $sign_str = "order_id=$order_id&md5_key=$md5_sign_key";
            $sign = md5($sign_str);
            $params_arr = array('order_id' => $order_id, 'sign' => $sign);
            $params = json_encode($params_arr);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "params=$params");
        }
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        $data = curl_exec($ch);
        curl_close($ch);

        $file_name = "/web/pay_log/get_pay_code_respond_" . date("Ymd") . ".log";
        $logStr = "\nuser_id=$user_id&order_id=$order_id\tcallback_res=" . var_export($data, true);
        file_put_contents($file_name, $logStr, FILE_APPEND);
    }
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
$merInfo = get_merid_and_primary_key($mobileId);
$priv_key_file = $_SERVER['DOCUMENT_ROOT']."/includes/" . $merInfo['primary_key'];
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
