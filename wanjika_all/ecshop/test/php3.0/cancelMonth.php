<!--'===============================================================-->
<!--'			联动优势 商户web 下订单 的示例与开发模板-->
<!--'			作者:  郝静华-->
<!--'			日期:2006 09 14-->
<!--'	-->
<!--'	该模板提供了用php页的WEB网站来取消用户包月的服务.-->
<!--'-->
<!--'===============================================================-->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=GB18030">
<title>联动优势包月服务状态取消参考例子</title>
</head>
<body>
<form name="form1" action="monthCancelResult.php" method="post">
<h2>取消包月服务状态</h2>	
<TABLE width="100%" >
<tr><td width="139">SP平台代码：</td><td><input type=text name="merId" value="">手机钱包定义的商户号</td></tr>
<tr><td width="139">包月商品代码</td><td><input type=text name="goodsId" value="" >手机钱包平台和商户确定的包月服务的商品代码</td></tr>
<tr><td width="139">手机号码</td><td><input type=text name="mobileId" value="" >要查询的用户的手机号</td></tr>
<tr><td width="139">版本号</td><td><input type=text name="version" value="" ></td></tr>

<tr><td width="139"><input type=submit name="submitbutton" value="提交" ></td><td></td></tr>
</TABLE>
</form>
</body>
</html>
