<?php
define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');
require(ROOT_PATH . 'includes/lib_payment.php');
require(ROOT_PATH . 'includes/lib_order.php');
require(ROOT_PATH . 'includes/modules/payment/umpay.php');

$CALL_BACK_ARR = array(
    'wjika' => 'http://www.wjike.com/test.php',    //百志
);

$file_name = "/web/pay_log/pay_fee_notify_".date("Ymd").".log";
$logStr = "\n[".date("Y-m-d H:i:s")."][".$_REQUEST['merId']."][".$_REQUEST['goodsId']."][".$_REQUEST['orderId']."][".$_REQUEST['merDate']."][".$_REQUEST['payDate']."][".$_REQUEST['amount']."][".$_REQUEST['amtType']."][".$_REQUEST['bankType']."][".$_REQUEST['mobileId']."][".$_REQUEST['transType']."][".$_REQUEST['settleDate']."][".$_REQUEST['merPriv']."][".$_REQUEST['merPriv']."][".$_REQUEST['retCode']."][".$_REQUEST['version']."][".$_REQUEST['sign']."][".$merArr['log_id']."]";
file_put_contents($file_name, $logStr, FILE_APPEND);
$umpay = new umpay();

/* 支付方式代码 */
$pay_code = !empty($_REQUEST['code']) ? trim($_REQUEST['code']) : '';
if (empty($pay_code) && ($_REQUEST['merId'] == '7145' || $_REQUEST['merId'] == '6844'))
{
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
$ret_retcode = "";
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
if(!empty($mobileId))
{
    $paramNew =$paramNew . "&mobileId=" . trim($mobileId,"\x00..\x1F");
}
$paramNew = $paramNew . "&transType=" . trim($transType,"\x00..\x1F");
$paramNew = $paramNew . "&settleDate=" . trim($settleDate,"\x00..\x1F");
if(!empty($merPriv))
{
    $paramNew = $paramNew . "&merPriv=" . trim($merPriv,"\x00..\x1F");
}
$paramNew = $paramNew . "&retCode=". trim($retCode,"\x00..\x1F");
echo "<br>".$paramNew = $paramNew . "&version=". trim($version,"\x00..\x1F");
echo "<br>";
//验签
$certfile = $_SERVER['DOCUMENT_ROOT']."/includes/cert_2d59.cert.pem";
$result=$umpay->ssl_verify($paramNew,$sign,$certfile);
$ret = 'fail';
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
    $ret = 'succ';
}

$params = $_POST['params'];
$params_arr = json_decode($params);
$params_arr['result'] = $ret;
$params = json_encode($params_arr);
//回调
$user_name = $_REQUEST['user_name'];
$remote_server = $CALL_BACK_ARR[$user_name];
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $remote_server);
curl_setopt($ch, CURLOPT_POSTFIELDS, "params=$params");
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
$data = curl_exec($ch);
curl_close($ch);
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