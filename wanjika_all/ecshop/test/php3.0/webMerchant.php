<!--'===============================================================-->
<!--'			联动优势 商户web 下订单 的示例与开发模板-->
<!--'			作者: 郝静华-->
<!--'			日期:2006 09 14-->
<!--'	-->
<!--'	该模板提供了用php页的WEB网站来向手机钱包平台下订单的例子(统一支付页面).-->
<!--'-->
<!--'===============================================================-->

<?php
 $rdpwd=mt_rand(100000,999999);
 $date=date("Ymd");
?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=GB18030">
<title>联动优势商户web下订单参考例子(统一支付页面)</title>
</head>
<body>
<form method="post" action="webMerchantResult.php">
<table>
        <tr>
		<td>商户号</td>
		<td><input type=text name="merId" value="9998"></td>	 
	</tr>
	<tr>
		<td>商品号</td>
		<td><input type=text name="goodsId" value="100"></td>	 
	</tr>
	<tr>
		<td>商品信息</td>
		<td><input type=text name="goodsInf" value="测试商品"></td>	 
	</tr>
	<tr>
		<td>手机号</td>
		<td><input type=text name="mobileId" value="13676963061"></td>	 
	</tr>
	<tr>
		<td>定单号</td>
		<td><input type=text name="orderId" value=""></td>	 
	</tr>
	<tr>
		<td>下单日期</td>
		<td><input type=text name="merDate" value="<?php echo $date;?>"></td>	 
	</tr>
	<tr>
		<td>金额</td>
		<td><input type=text name="amount" value="1000"></td>	 
	</tr>
	<tr>
		<td>金额类型</td>
		<td><select name="amtType">
			<option value="01">人民币</option>
			<option value="02" selected>话费</option>
			<option value="03">积分</option>
		</select></td>	 
	</tr>
	<tr>
		<td>银行</td>
		<td><select name="bankType">
		    <option value="">－－默认空－－</option>
			<option value="1">银行卡</option>
			<option value="2">网银</option>
			<option value="3">话费</option>
			<option value="4">积分</option>
			<option value="5">预存帐户</option>			 
		</select></td>	 
	</tr>
	<tr>
		<td>网银网关</td>
		<td><select name="gateId">
		    <option value="">－－默认空－－</option>
			<option value="0400">工商银行</option>
			<option value="0500">农业银行</option>		 
		</select></td>	 
	</tr>
	<tr>
		<td>页面返回地址</td>
		<td><input type=text name="retUrl" value=""></td>	 
	</tr>
	<tr>
		<td>结果通知地址</td>
		<td><input type=text name="notifyUrl" value=""></td>	 
	</tr>
	<tr>
		<td>商户私有信息</td>
		<td><input type=text name="merPriv" value="test"></td>	 
	</tr>
        <tr>
         <td><input type="hidden" name="expand" value="mer"></td>
        </tr>
        <tr>
         <td><input type="hidden" name="gateId" value=""></td>
        </tr>
	<tr>
		<td>版本号</td>
		<td><input type=text name="version" value="3.0"></td>	 
	</tr>
	<tr>
		<td>签名</td>
		<td><input type=text name="sign" value="ahyfdshfshf"></td>	 
	</tr>
	<tr>
		<td colspan=2 align=center><input type=submit value="提交订单"></td>
		<td></td>
	</tr>
</table>
</form>
</body>
</html>
