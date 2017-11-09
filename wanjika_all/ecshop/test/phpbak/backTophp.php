
<!--    '==========================================================-->
<!--	'			联动优势 商户web 下订单 的示例与开发模板               -->
<!--	'			作者:郝静华                                           -->
<!--	'			日期:2006 09 14                                 -->
<!--	'	                                                       -->
<!--	'	该模板提供了用JSP页的WEB网站来重新返回商户网站进行购物的例子.    -->
<!--	'                                                          -->
<!--	'       接收到传递过来的参数，并组装成字符                          -->
<!--	'       串，验证签名                                               -->
<!--	'                                                          -->
<!--	'	具体参数格式请参见 手机钱包商户接入文档2.2 及其更高               -->
<!--	'==========================================================-->
<?php include("umpayHead.php");?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<?php
//返回码
$RETCODE = $_REQUEST['RETCODE'];
//商户号码
$SPID = $_REQUEST['SPID'];
//订单号
$ORDERID = $_REQUEST['ORDERID'];
//金额
$AMOUNT = $_AMOUNT['AMOUNT'];
//日期
$DATETIME = $_REQUEST['DATETIME'];
//手机号码
$MOBILEID = $_REQUEST['MOBILEID'];
//随机数
$RDPWD = $_REQUEST['RDPWD'];
//签名
$SIGN = $_REQUEST['SIGN'];
$hh_result="";
$url="";
//根据手机钱包商户接入规范2.2V的5.3组装字符串，进行验签
$url="RETCODE=" . $RETCODE . "&SPID=" . $SPID . "&ORDERID=" . $ORDERID . "&AMOUNT=" . $AMOUNT . "&DATETIME=" . $DATETIME . "&MOBILEID=" . $MOBILEID . "&RDPWD=" . $RDPWD . "";

$result=ssl_verify($url,$sign,$certfile);

if(result)
{
	//做相关的成功处理
	//这个主要是商户来补充
	//这个是商户要做的主要的工作
	$hh_result="验签成功";
}else
{
    //做相关的不成功的处理
    //这个主要是商户来补充
    //这个是商户要做的主要的工作
	$hh_result="验签失败";
}

?>
<title>联动优势重新返回商户网站进行购物的例子</title>
</head>
<body>
<table><tr><td><?php echo $hh_result;?></td></tr></table>
</body>
</html>
