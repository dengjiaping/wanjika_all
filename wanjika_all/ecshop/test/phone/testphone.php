<html>
	<head>
		<title>testPhone</title>
		<link href="css/sdk.css" rel="stylesheet" type="text/css" />
		<meta http-equiv="Content-Type" content="text/html; charset=utf8">
	</head>
<?php
$cpid = 8392;
$trade_type = 'CZ';
$operator = 'YD';
$province = base64_encode('北京');
$create_time = '201307151515';
$mobile_num = '13581753069';
$cp_order_no = date("YmdHis");
$amount = 1;
$ret_para = '';

?>
	<body>
      <form name="form1" method="post" action="test.php">
         <br>
         <center>
            <font size=2 color=black face=Verdana><b>DirectPayoffline</b>
            </font>
            <br>
            <br>
            <table class="api">
            <tr>
               <td class="field">cpid       </td>
               <td>
               	<input type="text" name="cpid" value="<?php echo $cpid       ?>">
               </td>
            </tr>
<tr>
   <td class="field">trade_type </td>
   <td>      <input type="text" name="trade_type" value="<?php echo $trade_type ?>">   </td>
</tr>
<tr>
   <td class="field">operator   </td>   
   <td>      <input type="text" name="operator" value="<?php echo $operator   ?>">   </td>
</tr>
<tr>
   <td class="field">province   </td>   
   <td>      <input type="text" name="province" value="<?php echo $province   ?>">   </td>
</tr>
<tr>
   <td class="field">create_time</td>   
   <td>      <input type="text" name="create_time" value="<?php echo $create_time?>">   </td>
</tr>
<tr>
   <td class="field">mobile_num </td>   
   <td>      <input type="text" name="mobile_num" value="<?php echo $mobile_num ?>">   </td>
</tr>
<tr>
   <td class="field">cp_order_no</td>   
   <td>      <input type="text" name="cp_order_no" value="<?php echo $cp_order_no?>">   </td>
</tr>
<tr>
   <td class="field">amount     </td>   
   <td>      <input type="text" name="amount" value="<?php echo $amount     ?>">   </td>
</tr>
<tr>
   <td class="field">ret_para   </td>   
   <td>      <input type="text" name="ret_para" value="<?php echo $ret_para   ?>">   </td>
</tr>
<tr>
   <td class="field">sign       </td>   
   <td>      <input type="text" name="sign" value="<?php echo $sign       ?>">   </td>
</tr>

               <tr>
                  <td class="field">
                  </td>
                  <td>
                     <input type="Submit" value="提交" id="Submit" name="submit" />
                  </td>
               </tr>
            </table>
         </center>
      </form>
	</body>
</html>
