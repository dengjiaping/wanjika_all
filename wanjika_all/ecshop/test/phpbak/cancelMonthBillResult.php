<!-- 
 -->
<?php include("umpayHead.php");?>
<!--------------------------------------------->
<!--1、接收参数                              -->
<!--------------------------------------------->
<?php        
        //商户号
	$merId = $_REQUEST['merId'];
        //注销日期
	$cancelDate = $_REQUEST['cancelDate'];
        //版本
        $version = $_REQUEST['version'];
?>
<!--------------------------------------------->
<!--2、生成签名                              -->
<!--------------------------------------------->
<?php
			//手机钱包商户接入规范3.1V的5.11组装字符串，生成签名
			$url = "merId=" . $merId . "&cancelDate=" . $cancelDate . "&version=" . $version;
			//签名
			$sign=ssl_sign($url,$priv_key_file);

?>
<body bgcolor="#f2f2f2" text="#666666" topmargin="0" marginheight="0">
<hr>
<form action="<?php echo UMPAY_AMOUNT_URL;?>" method="post">
<h2><b>交易数据对帐信息：</b></h2>	
<TABLE width="100%" >
<tr><td width="139">SP平台代码：</td><td><input type=text NAME="merId" value="<?php echo $merId;?>"></td></tr>
<tr><td width="139">注销日期</td><td><input type=text NAME="cancelDate" value="<?php echo $cancelDate;?>"></td></tr>
<tr><td width="139">版本号</td><td><input type=text NAME="gateId" value="<?php echo $gateId;?>" ></td></tr>
<tr><td width="139">签名数据L</td><td><input type=text NAME="sign" value="<?php echo $sign;?>"></td></tr>

<tr><td width="139"><input type=submit name="submitbutton" value="取交易文件" size=></td><td></td></tr>
</TABLE>
</form>
</body>
</html>
