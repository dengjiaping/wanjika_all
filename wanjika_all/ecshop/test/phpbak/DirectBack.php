<!--'===============================================================-->
<!--'			联动优势 商户web 下订单 的示例与开发模板-->
<!--'			作者: 郝静华-->
<!--'			日期:2006 09 14-->
<!--'	-->
<!--'	该模板提供了用php页的WEB网站来向手机钱包平台下订单的例子.-->
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
<title>联动优势商户web下订单参考例子(后台直连方式)</title>
</head>
<body>
<form method=POST action="DirectBackResult.php">
<table>
	<tr>
		<td>商户号</td>
		<td><input type=text name="merId" value=""></td>

		</select></td>
	</tr>
	<tr>
		<td>商品号</td>
		<td><input type=text name="goodsId" value=""></td>
	</tr>
	<tr>
		<td>商品描述</td>
		<td><input type=text name=goodsInf value=""></td>
	</tr>			
        <tr>
		<td>手机号</td>
		<td><input type=text name=mobileId value=""></td>
	</tr>
        <tr>
               <td>订单号</td>
               <td><input type=text name="orderId" value=""></td>
        </tr>
        <tr>
		<td>商户下单日期</td>
		<td><input type=text name=merDate value="<?php echo $date;?>"></td>
	</tr>
	<tr>
		<td>金额</td>
		<td><input type=text name=amount value=""></td>
	</tr>
	<tr>
		<td>金额类型</td>
		<td><input type=text name=amtType value=""></td>
	</tr>
	<tr>
		<td>手机号</td>
		<td><input type=text name=MOBILEID value=""></td>
	</tr>
	<tr>
		<td>随机数</td>
		<td><input type=text name=RDPWD value="<?php echo $rdpwd;?>"></td>
	</tr>
	<tr>
		<td>商品描述</td>
		<td>
		<textarea name=REMARK rows=3 cols=50 maxlength=256>
		php的测试商品
		</textarea>
		</td>
	</tr>	
	<tr>
		<td colspan=2 align=center><input type=submit value="提交订单"></td>
		<td></td>
	</tr>


</table>
</form>
</body>
</html>
