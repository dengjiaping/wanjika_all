<?php
 $date=date("Ymd");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=GB18030">
<title>联动优势商户web下订单参考例子</title>
</head>
<body>
<form method=POST action="OrderResult.php">
<table>
    <tr>
         <td>商户号：</td>
         <td><input type=text NAME="merId" value=""></td>
    </tr>
    <tr>
        <td>商品号：</td>
    	<td><input type=text NAME="goodsId" value=""></td>
    </tr>
    <tr>
    	<td>商品信息：</td>
	<td><input type=text NAME="goodsInf" value=""></td>
    </tr>
    <tr>
    	<td>手机号：</td>
	<td><input type=text NAME="mobileId" value=""></td>
    </tr>
    <tr>
    	<td>订单号：</td>
	<td><input type=text NAME="orderId" value=""></td>
    </tr>
    <tr>
	<td>商户下单日期</td>
	<td><input type=text name=merDate value="<?php echo $date;?>"></td>
    </tr>
    <tr>
    	<td>金额：</td>
	<td><input type=text NAME="amount" value=""></td>
    </tr>
    <tr>
    	<td>金额类型：</td>	
	<td><input type=text NAME="amtType" value=""></td>
    </tr>
    <tr>
    	<td>交易类型：</td>
	<td><input type=text NAME="transType" value=""></td>
    </tr>
    <tr>
    	<td>银行类型：</td>
	<td><input type=text NAME="gateId" value=""></td>
    </tr>
    <tr>
    	<td>前台交易结果通知地址：</td>
	<td><input type=text NAME="retUrl" value=""></td>
    </tr>
    <tr>
    	<td>后台交易结果通知地址：</td>	
	<td><input type=text NAME="notifyUrl" value=""></td>
    </tr>
    <tr>
    	<td>商户私有信息：</td>
	<td><input type=text NAME="merPriv" value=""></td>
    </tr>
    <tr>
    	<td>版本号：</td>
	<td><input type=text NAME="version" value=""></td>
    </tr>
     <tr>
    	<td>签名：</td>
	<td><input type=text NAME="sign" value=""></td>
    </tr>
    <tr>
	<td colspan=2 align=center><input type=submit value="提交订单"></td>
	<td></td>
   </tr>
</table>
</form>
</body>
</html>
