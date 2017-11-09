<!--     '===============================================================-->
<!--	'			联动优势 商户web 下订单 的示例与开发模板-->
<!--	'			作者:郝静华-->
<!--	'			日期:2006 09 14-->
<!--	'	-->
<!--	'	该模板提供了用PHP页的WEB网站来查看话费支付对帐的结果.-->
<!--	'-->
<!--	'	具体参数格式请参见 手机钱包商户接入文档2.2 及其更高-->
<!--	'===============================================================    -->
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=GB18030">
<title>联动优势交易数据对帐信息参考例子</title>
</head>
<body>
<form name="form1" action="transBillResult.php" method="post">
<h2>提交信息</h2>	
<TABLE width="100%" >
<tr>
    <td>商户号：</td><td><input type=text NAME="merId" value=""></td>
  </tr>
  <tr>
    <td>交易日期：</td><td><input type=text NAME="payDate" value=""></td>
  </tr>
  <tr>
    <td>银行编号：</td><td><input type=text NAME="gateId" value=""></td>
  </tr>
  <tr>
    <td>版本号：</td><td><input type=text NAME="version" value=""></td>
  </tr>
  <tr>
    <td>签名：</td><td><input type=text NAME="sign" value=""></td>
  </tr>

<tr><td width="139">
<input type=submit name="submitbutton" value="提交" ></td>
<td></td>
</tr>
</TABLE>
</form>
</body>
</html> 
