<?php

/**
 *在线支付插件
 *
 *满足中移和建行联合活动需求 2013-12-12
 */

require("mobpay/globalParam.php");
require_once("mobpay/callcmpay.php");
/*
if (!defined('IN_ECS'))
{
die('Hacking attempt');
}*/

$payment_lang = ROOT_PATH . 'languages/' . $GLOBALS['_CFG']['lang'] . '/payment/olpay.php';

$bank_code_arr = array(
	'jsyh' => 'CCB',			//建设银行
	'gsyh' => 'ICBC',			//中国工商银行
	'yzcx' => 'PSBC',			//邮政储蓄
	'nyyh' => 'ABC',			//农业银行
	'pfyh' => 'SPDB',			//浦发银行
	'jtyh' => 'BCOM',			//交通银行
	'zsyh' => 'CMB',			//招商银行
	'msyh' => 'CMBC',			//民生银行
	'fzyh' => 'SDB',			//深圳发展银行
	'gfyh' => 'GDB',			//广发银行
	'zxyh' => 'ECITIC',			//中信银行
	'hxyh' => 'HXB',			//华夏银行
	'xyyh' => 'CIB',			//兴业银行
	'zgyh' => 'BOC',			//中国银行
);

if (file_exists($payment_lang))
{
	global $_LANG;

	include_once($payment_lang);
}
if ($_POST['submit']=='在线支付') {

	//设置请求参数start
	$type         = "GWDirectPay";
	$reqUrl       = $GLOBALS['reqUrl'];
	$ipAddress    = getClientIP();
	$characterSet = $GLOBALS['characterSet'];
	$callbackUrl  = $GLOBALS['callbackUrl'];
	$notifyUrl    = $GLOBALS['notifyUrl'];
	$merchantId   = $GLOBALS['merchantId'];
	$requestId    = $GLOBALS['requestId'];
	$signType     = $GLOBALS['signType'];
	$version      = $GLOBALS['version'];
	$amount   = intval($_POST['amount']*100);
	$bankAbbr     = $_POST['bankAbbr'];
	$currency     = $_POST['currency'];
	$orderDate    = $_POST['orderDate'];
	$orderId 	  = $_POST['orderId'];
	$merAcDate    = $_POST['merAcDate'];
	$period 	  = $_POST['period'];
	$periodUnit   = $_POST['periodUnit'];
	$merchantAbbr = $_POST['merchantAbbr'];
	$productDesc  = $_POST['productDesc'];
	$productId    = $_POST['productId'];
	$productName  = mb_convert_encoding($_POST['productName'], "gb2312", "UTF-8");
	$productNum   = $_POST['productNum'];
	$reserved1    = $_POST['reserved1'];
	$reserved2    = $_POST['reserved2'];
	$userToken    = $_POST['userToken'];
	$showUrl 	  = $_POST['showUrl'];
	$couponsFlag  = $_POST['couponsFlag'];
	//设置请求参数 end

	//组织签名数据
	$signData = $characterSet.$callbackUrl.$notifyUrl.$ipAddress
	.$merchantId  .$requestId  .$signType .$type
	.$version     .$amount     .$bankAbbr .$currency
	.$orderDate   .$orderId    .$merAcDate .$period   .$periodUnit
	.$merchantAbbr.$productDesc.$productId.$productName
	.$productNum  .$reserved1  .$reserved2.$userToken
	.$showUrl     .$couponsFlag;

	$signKey=$GLOBALS['signKey'];

	//MD5方式签名
	$hmac=MD5sign($signKey,$signData);
	$requestData = array();
	$requestData["characterSet"] = $characterSet;
	$requestData["callbackUrl"]  = $callbackUrl;
	$requestData["notifyUrl"]    = $notifyUrl;
	$requestData["ipAddress"]    = $ipAddress;
	$requestData["merchantId"]   = $merchantId;
	$requestData["requestId"]    = $requestId;
	$requestData["signType"]     = $signType;
	$requestData["type"]         = $type;
	$requestData["version"]      = $version;
	$requestData["hmac"]         = $hmac;
	$requestData["amount"]       = $amount;
	$requestData["bankAbbr"]     = $bankAbbr;
	$requestData["currency"]     = $currency;
	$requestData["orderDate"]    = $orderDate;
	$requestData["orderId"]      = $orderId;
	$requestData["merAcDate"]    = $merAcDate;
	$requestData["period"]       = $period;
	$requestData["periodUnit"]   = $periodUnit;
	$requestData["merchantAbbr"] = $merchantAbbr;
	$requestData["productDesc"]  = $productDesc;
	$requestData["productId"]    = $productId;
	$requestData["productName"]  = $productName;
	$requestData["productNum"]   = $productNum;
	$requestData["reserved1"]    = $reserved1;
	$requestData["reserved2"]    = $reserved2;
	$requestData["userToken"]    = $userToken;
	$requestData["showUrl"] 	   = $showUrl;
	$requestData["couponsFlag"]  = $couponsFlag;


	//	$requestData = array();
	//	$requestData["characterSet"] = $_POST['characterSet'];
	//	$requestData["callbackUrl"]  = $_POST['callbackUrl'];
	//	$requestData["notifyUrl"]    = $_POST['notifyUrl'];
	//	$requestData["ipAddress"]    = $_POST['ipAddress'];
	//	$requestData["merchantId"]   = $_POST['merchantId'];
	//	$requestData["requestId"]    = $_POST['requestId'];
	//	$requestData["signType"]     = $_POST['signType'];
	//	$requestData["type"]         = $_POST['type'];
	//	$requestData["version"]      = $_POST['version'];
	//	$requestData["hmac"]         = $_POST['hmac'];
	//	$requestData["merCert"]         = '';
	//	$amount = intval($_POST['amount']) * 100;
	//	$requestData["amount"]       = $amount;
	//	$requestData["bankAbbr"]     = 'ICBC';
	////	$requestData["bankAbbr"]     = $_POST['bankAbbr'];
	//	$requestData["currency"]     = $_POST['currency'];
	//	$requestData["orderDate"]    = $_POST['orderDate'];
	//	$requestData["orderId"]      = $_POST['orderId'];
	//	$requestData["merAcDate"]    = $_POST['merAcDate'];
	//	$requestData["period"]       = $_POST['period'];
	//	$requestData["periodUnit"]   = $_POST['periodUnit'];
	//	$requestData["merchantAbbr"] = $_POST['merchantAbbr'];
	//	$requestData["productDesc"]  = $_POST['productDesc'];
	//	$requestData["productId"]    = $_POST['productId'];
	//	$requestData["productName"]  = $_POST['productName'];
	//	$requestData["productNum"]   = $_POST['productNum'];
	//	$requestData["reserved1"]    = $_POST['reserved1'];
	//	$requestData["reserved2"]    = $_POST['reserved2'];
	//	$requestData["userToken"]    = $_POST['userToken'];
	//	$requestData["showUrl"] 	   = $_POST['showUrl'];
	//	$requestData["couponsFlag"]  = $_POST['couponsFlag'];


	//http请求到手机支付平台
	$post_data = '';
	if (!empty($requestData))
	{
		while (list($k,$v) = each($requestData))
		{
			$post_data .= ($post_data ? "&" : "");
			$post_data .= rawurlencode($k)."=".rawurlencode($v);
		}
	}
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $reqUrl);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
	curl_setopt($ch, CURLOPT_POST, true); 
	curl_exec($ch); 
	curl_close($ch); 
	exit;
	//print_r($requestData);exit();
//	$sTotalString = POSTDATA($reqUrl,$requestData);
//	$recv = $sTotalString["MSG"];
//	$recvArray = parseRecv($recv);
//	echo $sTotalString['MSG'];
//	$code=$recvArray["returnCode"];
//	$payUrl;
//	if ($code!="000000") {
//		echo "code:".$code."</br>msg:".decodeUtf8($recvArray["message"]);
//		exit();
//	}
//	else
//	{
//		$signKey=$GLOBALS['signKey'];
//		$vfsign=$recvArray["merchantId"].$recvArray["requestId"]
//		.$recvArray["signType"]  .$recvArray["type"]
//		.$recvArray["version"]   .$recvArray["returnCode"]
//		.$recvArray["message"]   .$recvArray["payUrl"];
//		$hmac=MD5sign($signKey,$vfsign);
//		$vhmac=$recvArray["hmac"];
//		if($hmac!=$vhmac)
//		{
//			echo "验证签名失败!";
//			exit();
//		}
//		else
//		{
//			$payUrl = $recvArray["payUrl"];
//			//返回url处理
//			$rpayUrl= parseUrl($payUrl);
//		}
//	}
	?>
	<html>
	<head>
		<title>即时到帐(网银网关)GWDirectPay</title>
		<link href="sdk.css" rel="stylesheet" type="text/css" />
		<meta http-equiv="Content-Type" content="text/html; charset=gb2312">
	</head>

	<body>
	<?php
	$recv = $sTotalString["MSG"];
	$recvArray = parseRecv($recv);

	$code=$recvArray["returnCode"];
	$payUrl;
	if ($code!="000000") {
		echo "code:".$code."</br>msg:".decodeUtf8($recvArray["message"]);
		exit();
	}
	else
	{
		$vfsign=$recvArray["merchantId"].$recvArray["requestId"]
		.$recvArray["signType"]  .$recvArray["type"]
		.$recvArray["version"]   .$recvArray["returnCode"]
		.$recvArray["message"]   .$recvArray["payUrl"];
		$hmac=MD5sign($signKey,$vfsign);
		$vhmac=$recvArray["hmac"];
		if($hmac!=$vhmac)
		{
			echo "验证签名失败!";
			exit();
		}
		else
		{
			$payUrl = $recvArray["payUrl"];
			//返回url处理
			$rpayUrl= parseUrl($payUrl);
		}
	}
		?>
		<form name="frm0" action="<?php echo $rpayUrl["url"]?>" method="<?php echo $rpayUrl["method"];?>">
			<input type="submit" value="提交"/>
		</form>
		<script type="text/javascript">
		document.forms['frm0'].submit();
		</script>

	</body>
</html>
<?php


}


/* 模块的基本信息 */
if (isset($set_modules) && $set_modules == true)
{
	$i = isset($modules) ? count($modules) : 0;

	/* 代码 */
	$modules[$i]['code'] = basename(__FILE__, '.php');

	/* 描述对应的语言项 */
	$modules[$i]['desc'] = 'olpay_desc';

	/* 是否支持货到付款 */
	$modules[$i]['is_cod'] = '0';

	/* 是否支持在线支付 */
	$modules[$i]['is_online'] = '1';

	/* 作者 */
	$modules[$i]['author']  = 'QIHUA';

	/* 网址 */
	$modules[$i]['website'] = 'https://ipos.10086.cn/ips/cmpayService';

	/* 版本号 */
	$modules[$i]['version'] = '1.0.0';

	/* 配置信息 */
	$modules[$i]['config'] = array(
	array('name' => 'olpay_account', 'type' => 'text', 'value' => ''),
	array('name' => 'olpay_key',     'type' => 'text', 'value' => ''),
	);

	return;

}

class olpay
{
	/**
     * 构造函数
     *
     * @access  public
     * @param
     *
     * @return void
     */

    function __construct()
    {
        $this->olpay();
    }

	function olpay()
	{
	}

	/**
     * 生成支付代码
     * @param   array   $order  订单信息
     * @param   array   $payment    支付方式信息
     */
	function get_code($order, $payment, $bank_val, $bank_code_arr)
	{
		$bank_code = !empty($bank_code_arr[$bank_val]) ? $bank_code_arr[$bank_val] : 'CCB';
		
		global $db;
		//设置请求参数start
		$type         = "GWDirectPay";
		$reqUrl       =  $GLOBALS['reqUrl'];
		$ipAddress    = getClientIP();
		$characterSet = $GLOBALS['characterSet'];
		$callbackUrl  = $GLOBALS['callbackUrl'];
		$notifyUrl    = $GLOBALS['notifyUrl'];
		$merchantId   = $GLOBALS['merchantId'];
		$requestId    = $GLOBALS['requestId'];
		$signType     = $GLOBALS['signType'];
		$version      = $GLOBALS['version'];
		$amount 	    = intval($order['order_amount']*100);;
		$bankAbbr     = $bank_code;
		$currency     = '00';
		$orderDate    = date("Ymd",$order['add_time']);
		$orderId 	    = $order['order_sn'];
		$merAcDate    = $orderDate;
		$period 	    = '07';
		$periodUnit   = '02';
		$merchantAbbr = '';
		$productDesc  = '';
		$productId    = '';
		$rank_sql = "SELECT mobile_phone FROM ecs_users WHERE user_id = '" . $order['user_id'] . "'";
		$rank_row = $db->getRow($rank_sql);
		
		$goods_sql = "SELECT goods_name FROM ecs_order_goods WHERE order_id = '" . $order['order_id'] . "'";
		$goods_row = $db->getRow($goods_sql);
		//$productName  = sub_str($goods_row['goods_name'], 10, true);
		$productName  = 'goods';
		$productNum   = 1;
		$merArr = array("code"=>"olpay", "bank_abbr" => $bankAbbr, "log_id"=>$order['log_id']);
		$reserved1    =  base64_encode(serialize($merArr));
		$reserved2    = '';
		$userToken    = $rank_row['mobile_phone'];
		$showUrl = '';
		$couponsFlag  = '';
		//设置请求参数 end

		//组织签名数据
		$signData = $characterSet.$callbackUrl.$notifyUrl.$ipAddress
		.$merchantId  .$requestId  .$signType .$type
		.$version     .$amount     .$bankAbbr .$currency
		.$orderDate   .$orderId    .$merAcDate .$period   .$periodUnit
		.$merchantAbbr.$productDesc.$productId.$productName
		.$productNum  .$reserved1  .$reserved2.$userToken
		.$showUrl     .$couponsFlag;

		$signKey=$GLOBALS['signKey'];
		//MD5方式签名
		$hmac=MD5sign($signKey,$signData);



		$def_url  = '<div style="text-align:center"><form name="olpay" style="text-align:center;" method="post"'.
		'action="https://ipos.10086.cn/ips/cmpayService">';
		$def_url .= "<input type='hidden' name='characterSet' value='" . $characterSet . "' />";
		$def_url .= "<input type='hidden' name='callbackUrl' value='" . $callbackUrl  . "' />";
		$def_url .= "<input type='hidden' name='notifyUrl' value='" . $notifyUrl    . "' />";
		$def_url .= "<input type='hidden' name='ipAddress' value='" . $ipAddress    . "' />";
		$def_url .= "<input type='hidden' name='merchantId' value='" . $merchantId   . "' />";
		$def_url .= "<input type='hidden' name='requestId' value='" . $requestId    . "' />";
		$def_url .= "<input type='hidden' name='signType' value='" . $signType     . "' />";
		$def_url .= "<input type='hidden' name='type' value='" . $type         . "' />";
		$def_url .= "<input type='hidden' name='version' value='" . $version      . "' />";
		$def_url .= "<input type='hidden' name='hmac' value='" . $hmac         . "' />";
		$def_url .= "<input type='hidden' name='amount' value='" . $amount       . "' />";
		$def_url .= "<input type='hidden' name='bankAbbr' value='" . $bankAbbr     . "' />";
		$def_url .= "<input type='hidden' name='currency' value='" . $currency     . "' />";
		$def_url .= "<input type='hidden' name='orderDate' value='" . $orderDate    . "' />";
		$def_url .= "<input type='hidden' name='orderId' value='" . $orderId      . "' />";
		$def_url .= "<input type='hidden' name='merAcDate' value='" . $merAcDate    . "' />";
		$def_url .= "<input type='hidden' name='period' value='" . $period       . "' />";
		$def_url .= "<input type='hidden' name='periodUnit' value='" . $periodUnit   . "' />";
		$def_url .= "<input type='hidden' name='merchantAbbr' value='" . $merchantAbbr . "' />";
		$def_url .= "<input type='hidden' name='productDesc' value='" . $productDesc  . "' />";
		$def_url .= "<input type='hidden' name='productId' value='" . $productId    . "' />";
		$def_url .= "<input type='hidden' name='productName' value='" . $productName  . "' />";
		$def_url .= "<input type='hidden' name='productNum' value='" . $productNum   . "' />";
		$def_url .= "<input type='hidden' name='reserved1' value='" . $reserved1    . "' />";
		$def_url .= "<input type='hidden' name='reserved2' value='" . $reserved2    . "' />";
		$def_url .= "<input type='hidden' name='userToken' value='" . $userToken    . "' />";
		$def_url .= "<input type='hidden' name='showUrl' value='" . $showUrl      . "' />";
		$def_url .= "<input type='hidden' name='couponsFlag' value='" . $couponsFlag  . "' />";


		$def_url .= "<input type='submit' name='submit' value='".$GLOBALS['_LANG']['pay_button']."' />";
		$def_url .= "</form></div><br />";

		return $def_url;
	}

	/**
     * 响应操作
     */
	function respond()
	{
		$payment             = get_payment(basename(__FILE__, '.php'));

		$merchant_acctid     = $payment['olpay_account'];                 //收款帐号 不可空
		$key                 = $payment['olpay_key'];


		$merchantId  = $_REQUEST['merchantId'];//商户编号
		$payNo       = $_REQUEST['payNo'];//流水号
		$returnCode  = $_REQUEST['returnCode'];//返回码
		$message     = $_REQUEST['message'];//返回码描述信息
		$signType    = $_REQUEST['signType'];//签名方式
		$type        = $_REQUEST['type'];//接口类型
		$version     = $_REQUEST['version'];//版本号
		$amount      = $_REQUEST['amount'];//支付金额
		$amtItem     = $_REQUEST['amtItem'];//金额明细
		$bankAbbr    = $_REQUEST['bankAbbr'];//支付银行
		$mobile      = $_REQUEST['mobile'];//支付手机号
		$orderId     = $_REQUEST['orderId'];//商户订单号
		$payDate     = $_REQUEST['payDate'];//支付时间
		$accountDate = $_REQUEST['accountDate'];//会计日期
		$reserved1   = $_REQUEST['reserved1'];//保留字段1
		$reserved2   = $_REQUEST['reserved2'];//保留字段2
		$status      = $_REQUEST['status'];//支付结果
		$orderDate   = $_REQUEST['orderDate'];//订单提交日期
		$fee         = $_REQUEST['fee'];//费用
		$serverCert  = $_REQUEST['serverCert'];//服务器证书公钥
		$hmac        = $_REQUEST['hmac'];//签名数据


		//重新组装签名字符串
		//组装签字符串
		$signData = $merchantId .$payNo.$returnCode .$message
		.$signType   .$type        .$version    .$amount
		.$amtItem    .$bankAbbr    .$mobile     .$orderId
		.$payDate    .$accountDate .$reserved1  .$reserved2
		.$status     .$orderDate   .$fee;

		//MD5方式签名
		$signKey=$GLOBALS['signKey'];
		$vhmac=MD5sign($signKey,$signData);
		$merArr = unserialize(base64_decode($reserved1));
		$log_id = $merArr['log_id'];
		//此处000000仅代表程序无错误。订单是否支付成功是以支付结果（status）为准
		if($returnCode!=000000)
		{
			echo $returnCode.decodeUtf8($message);
		}

		if($hmac != $vhmac)
		{
			echo "验签失败";
			return false;
		}
		else {
			if ($status == 'SUCCESS')  //有成功支付的结果返回0000
			{
				order_paid($log_id);
				return true;
			}
			else
			{
				//'实际支付金额不能小于0';
				echo $returnCode.decodeUtf8($message);
				return false;
			}
		}
	}
}

?>
