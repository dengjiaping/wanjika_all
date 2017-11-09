<!-- 
    ===============================================================
				联动优势 商户web 下订单 的示例与开发模板
				作者:郝静华
				日期:2006-09-14
		
		该模板提供了用PHP页的WEB网站来取消用户包月的服务.
	       
	     接收到monthCancel.php传递来的参数，组装成字符串，
	     然后生成签名，再将参数传递到
	     http://211.154.41.244/webpay/spCancelUserRegInfo.do
	     处理流程
	     1、接收参数
	     2、生成签名
		具体参数格式请参见 手机钱包商户接入规范文档2.2 的5.5.1
	===============================================================	
-->
<?php include("umpayHead.php");?>
	
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<!--------------------------------------------->
<!--1、接收参数                              -->
<!--------------------------------------------->
<?php
        //商户号码
	$merId = $_REQUEST['merId'];
        //商品号码
        $goodsId = $_REQUEST['goodsId'];
        //版本号
	$version = $_REQUEST['version'];
        //手机号码
	$mobileId = $_REQUEST['mobileId'];
?>
<!--------------------------------------------->
<!--2、生成签名                                     -->
<!--------------------------------------------->
<?
     //手机钱包商户接入规范2.2V的5.5.1组装字符串，生成签名
     $sign="merId=" . $merId . "&goodsId=" . $goodsId . "&mobileId=" . $mobileId . "&version=" . $version;
     echo $sign;
     //生成签名
     $SIGN=ssl_sign($sign,$priv_key_file);
?>



<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=GB18030">
<title>联动优势商户web向手机钱包平台提交查询用户包月服务的参考例子</title>
</head>
<body>
<h2><b>查询用户包月服务：</b></h2>
<form action="<?php echo UMPAY_CANCEL_URL;?>" method="post">
<tr><td><input type="text" name="merId"	value="<?php echo $merId;?>"> </td></tr>
<tr><td><input type="text" name="goodsId" value="<?php echo $goodsId;?>"></td></tr>
<tr><td><input type="text" name="mobileId" value="<?php echo $mobileId;?>"> </td></tr>
<tr><td><input type="text" name="version" value="<?php echo $version;?>"></td></tr>
<tr><td><input type="text" name="sign" value="<?php echo $sign;?>"> </td></tr>
<tr><td><input type=submit name=sbmt value="提交"></td></tr>
</form>
</body>
</html> 
