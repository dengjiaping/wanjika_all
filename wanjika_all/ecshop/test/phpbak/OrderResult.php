<!-- 


 -->
<?php include("umpayHead.php");?>

<!--------------------------------------------->
<!--1、接收参数                                     -->
<!--------------------------------------------->	
<?php
	//商户号码
	$merId = $_REQUEST['merId'];
        //商品号码
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
        $gateId = $_REQUEST['gateId'];
        //交易类型
        $transType = $_REQUEST['transType'];
   	//前台通知地址
	$retUrl= $_REQUEST['retUrl'];
	//后台通知地址
	$notifyUrl= $_REQUEST['notifyUrl'];
	//商户私有信息
        $merPriv = $_REQUEST['merPriv'];
        //版本号
        $version = $_REQUEST['version'];
?>
<!--------------------------------------------->
<!--2、生成签名                                     -->
<!--------------------------------------------->
<?php	
	//按照手机钱包商户接入规范2.2V的5.1.2.1的格式组装字符串
        $paramNew="";  
	$paramNew =$paramNew . "merId=" . trim($merId,"\x00..\x1F");
	$paramNew =$paramNew . "&goodsId=" . trim($goodsId,"\x00..\x1F");
        $paramNew =$paramNew . "&goodsInf=" .trim($goodsInf,"\x00..\x1F");
	$paramNew =$paramNew . "&mobileId=" . trim($mobileId,"\x00..\x1F");
	$paramNew =$paramNew . "&orderId=" . trim($orderId,"\x00..\x1F");
	$paramNew =$paramNew . "&merDate=" . trim($merDate,"\x00..\x1F");
	$paramNew =$paramNew . "&amount=" . trim($amount,"\x00..\x1F");
        $paramNew =$paramNew . "&amtType=" . trim($amtType,"\x00..\x1F");
	$paramNew =$paramNew . "&gateId=" . trim($gateId,"\x00..\x1F");
        $paramNew =$paramNew . "&transType=" .trim($transType,"\x00..\x1F");
	$paramNew =$paramNew . "&retUrl=" . trim($retUrl,"\x00..\x1F");
	$paramNew =$paramNew . "&notifyUrl=" . trim($notifyUrl,"\x00..\x1F");
	$paramNew =$paramNew . "&merPriv=" . trim($merPriv,"\x00..\x1F");
	$paramNew =$paramNew . "&version=" . trim($version,"\x00..\x1F");
	$pemSignNew = ssl_sign($paramNew,$priv_key_file);
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=GB18030">
<title>联动优势商户web向手机钱包平台提交订单参考例子</title>
</head>
<body>
<form action="<?php echo $url;?>" method="post">
<tr><td><input type="text" name="merId" value="<?php echo $merId;?>"></td></tr>
<tr><td><input type="text" name="goodsId" value="<?php echo $goodsId;?>"> </td></tr>
<tr><td><input type="text" name="goodsInf" value="<?php echo $goodsInf;?>"> </td></tr>
<tr><td><input type="text" name="mobileId" value="<?php echo $mobileId;?>"> </td></tr>
<tr><td><input type="text" name="orderId" value="<?php echo $orderId;?>"> </td></tr>
<tr><td><input type="text" name="merDate" value="<?php echo $merDate;?>"> </td></tr>
<tr><td><input type="text" name="amount" value="<?php echo $amount;?>"></td></tr>
<tr><td><input type="text" name="amtType" value="<?php echo $amtType;?>"></td></tr>
<tr><td><input type="text" name="gateId" value="<?php echo $gateId;?>"> </td></tr>
<tr><td><input type="text" name="transType" value="<?php echo $transType;?>"> </td></tr>
<tr><td><input type="text" name="retUrl" value="<?php echo $retUrl;?>"> </td></tr>
<tr><td><input type="text" name="notifyUrl" value="<?php echo $notifyUrl;?>"> </td></tr>
<tr><td><input type="text" name="merPriv" value="<?php echo $merPriv;?>"> </td></tr>
<tr><td><input type="text" name="version" value="<?php echo $version;?>"> </td></tr>
<tr><td><input type="text" name="sign" value="<?php echo $pemSignNew;?>"> </td></tr>
<tr><td><input	type=submit name=sbmt value="包月 or 按次"></td></tr>
</form>
</body>
</html> 
