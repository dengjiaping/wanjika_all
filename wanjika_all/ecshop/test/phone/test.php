<html>
	<head>
		<title>test</title>
		<meta http-equiv="Content-Type" content="text/html; charset=utf8">
	</head>

	<body>
		<?php 
		require("common/callcmpay.php");
		$key = 'BJWJKkiaosidfuOIUSDFOIUSdflkasdjfasdfhLKHFSDkerie';
		$cpid =  $_POST["cpid"];
		$trade_type =  $_POST["trade_type"];
		$operator =  $_POST["operator"];
		$province =  $_POST["province"];
		$create_time =  $_POST["create_time"];
		$mobile_num =  $_POST["mobile_num"];
		$cp_order_no =  $_POST["cp_order_no"];
		$amount =  $_POST["amount"];
		$ret_para =  $_POST["ret_para"];
		$sign = md5($cpid."&".$trade_type."&".$operator."&".$province."&".$create_time."&".$mobile_num."&".$cp_order_no."&".$amount."&".$ret_para."&".$key);

		$requestData = array();
		$requestData["cpid"] = $cpid       ;
		$requestData["trade_type"] = $trade_type ;
		$requestData["operator"] = $operator   ;
		$requestData["province"] = $province   ;
		$requestData["create_time"] = $create_time;
		$requestData["mobile_num"] = $mobile_num ;
		$requestData["cp_order_no"] = $cp_order_no;
		$requestData["amount"] = $amount     ;
		$requestData["ret_para"] = $ret_para   ;
		$requestData["sign"] = $sign       ;

print_r($requestData);


		$reqUrl ="http://111.13.47.84:8003/mobile/do";

		$sTotalString = POSTDATA($reqUrl,$requestData);
		$recv = $sTotalString;
print_r($recv);
		?>
	</body>
</html>
