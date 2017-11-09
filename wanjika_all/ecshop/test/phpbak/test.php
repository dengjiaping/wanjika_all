<!-- 
	===============================================================
				联动优势 商户web 下订单 的示例与开发模板
				作者: 郝静华
				日期:2005-10-21
		
		该模板提供了用php页的WEB网站来支付订单的支付模式.
	
	
	     接收到DirectBack.php传递过来的参数，组装成字符串，
	     生成签名，然后将数据传递到http://211.154.41.244/webpay/spGW.do
	     地址上去，这样就可以根据提示来完成相关的支付操作
	
	
	     处理流程
	     1、接收参数
	     2、生成签名
		具体参数格式请参见 手机钱包商户接入规范文档2.2 的5.1.1.1
	===============================================================

 -->
<?php include("umpayHead.php");?>

<!--------------------------------------------->
<!--1、接收参数                                     -->
<!--------------------------------------------->	
<?php
       //商户号
	$merId = $_REQUEST['merId'];
	//商品号
	$goodsId = $_REQUEST['goodsId'];
        //商品信息
        $goodsInf = $_REQUEST['goodsInf'];
        //手机号
	$mobileId = $_REQUEST['mobileId'];
	//订单号
	$orderId = $_REQUEST['orderId'];
	//商户下单日期
	$merDate = $_REQUEST['merDate'];
        //金额
        $amount = $_REQUEST['amount'];
        //金额类型
        $amtType = $_REQUEST['amtType'];
        //银行类型
        $bankType = $_REQUEST['bankType'];
	//网银Id
	$gateId = $_REQUEST['gateId'];
	//返回商户URL
	$retUrl = $_REQUEST['retUrl'];
	//支付通知URL
	$notifyUrl = $_REQUEST['notifyUrl'];
        //商户私有信息
        $merPriv = $_REQUEST['merPriv'];
        //扩展信息
        $expand = $_REQUEST['expand'];
        //版本号
        $version = $_REQUEST['version'];
?>
<!--------------------------------------------->
<!--2、生成签名                                     -->
<!--------------------------------------------->
<?php	
	//按照手机钱包商户接入规范2.2V的5.1.1.1的格式组装字符串
        $paramNew="";  
	$paramNew =$paramNew . "merId=" . trim($merId,"\x00..\x1F");
	$paramNew =$paramNew . "&goodsId=" . trim($goodsId,"\x00..\x1F");
        if(!empty($goodsInf)){
        $paramNew =$paramNew . "&goodsInf=" .trim($goodsInf,"\x00..\x1F");
        }
        if(!empty($mobileId)){
	$paramNew =$paramNew . "&mobileId=" . trim($mobileId,"\x00..\x1F");
        }
	$paramNew =$paramNew . "&orderId=" . trim($orderId,"\x00..\x1F");
	$paramNew =$paramNew . "&merDate=" . trim($merDate,"\x00..\x1F");
	$paramNew =$paramNew . "&amount=" . trim($amount,"\x00..\x1F");
        $paramNew =$paramNew . "&amtType=" . trim($amtType,"\x00..\x1F");
        if(!empty($bankType)){
        $paramNew =$paramNew . "&bankType=" . trim($bankType,"\x00..\x1F");
        }
        //if(!empty($gateId)){
        $paramNew =$paramNew . "&gateId=" . trim($gateId,"\x00..\x1F");
        //}
        //$paramNew =$paramNew . "&retUrl=" . urlencode(trim($retUrl,"\x00..\x1F"));
		$paramNew =$paramNew . "&retUrl=" . trim($retUrl,"\x00..\x1F");
        //if(!empty($notifyUrl)){
        $paramNew =$paramNew . "&notifyUrl=" . trim($notifyUrl,"\x00..\x1F");
        //}
        if(!empty($merPriv)){
        $paramNew =$paramNew . "&merPriv=" . trim($merPriv,"\x00..\x1F");
        }
        if(!empty($expand)){
        $paramNew =$paramNew . "&expand=" . trim($expand,"\x00..\x1F");
        }
        $paramNew =$paramNew . "&version=" . trim($version,"\x00..\x1F");
	
	$paramNew = "merId=7145&goodsId=001&orderId=21&merDate=20130413&payDate=20130413&amount=1010&amtType=02&bankType=3&mobileId=15810629113&transType=0&settleDate=20130413&retCode=0000&version=3.0";
echo	$pemSignNew = ssl_sign($paramNew,$priv_key_file);
exit;
	//$pemSignNew = urlencode($pemSignNew);
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=GB18030">
<title>联动优势商户web向手机钱包平台提交订单参考例子</title>
</head>
<body>
<!--
<form action="http://211.136.93.20:8081/uniform/spUniformPay.do" method="post">
-->
<!--
<form action="http://114.113.159.207:8756/hfwebbusi/pay/page.do" method="post">
-->
<form action="http://payment.umpay.com/hfwebbusi/pay/page.do" method="post">
<tr><td><input type="text" name="merId" value="<?php echo $merId;?>"></td></tr>
<tr><td><input type="text" name="goodsId" value="<?php echo $goodsId;?>"> </td></tr>
<tr><td><input type="text" name="goodsInf" value="<?php echo $goodsInf;?>"> </td></tr>
<tr><td><input type="text" name="mobileId" value="<?php echo $mobileId;?>"> </td></tr>
<tr><td><input type="text" name="orderId" value="<?php echo $orderId;?>"> </td></tr>
<tr><td><input type="text" name="merDate" value="<?php echo $merDate;?>"> </td></tr>
<tr><td><input type="text" name="amount" value="<?php echo $amount;?>"></td></tr>
<tr><td><input type="text" name="amtType" value="<?php echo $amtType;?>"> </td></tr>
<tr><td><input type="text" name="bankType" value="<?php echo $bankType;?>"> </td></tr>
<tr><td><input type="text" name="gateId" value="<?php echo $gateId;?>"> </td></tr>
<tr><td><input type="text" name="retUrl" value="<?php echo $retUrl;?>"></td></tr>
<tr><td><input type="text" name="notifyUrl" value="<?php echo $notifyUrl;?>"></td></tr>
<tr><td><input type="text" name="merPriv" value="<?php echo $merPriv;?>"> </td></tr>
<tr><td><input type="text" name="expand" value="<?php echo $expand;?>"> </td></tr>
<tr><td><input type="text" name="version" value="<?php echo $version;?>"> </td></tr>
<tr><td><input type="text" name="sign" value="<?php echo $pemSignNew;?>"> </td></tr>
<tr><td><input type="text" name="sign" value="<?php echo $paramNew;?>"> </td></tr>
<tr><td><input	type=submit name=sbmt value="包月 or 按次"></td></tr>
</form>
</body>
</html> 
