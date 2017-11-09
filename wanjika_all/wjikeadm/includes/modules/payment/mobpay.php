<?php

/**
 * ECSHOP 联动优势支付插件
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liubo $
 * $Id: mobpay.php 17217 2011-01-19 06:29:08Z liubo $
 */

require("mobpay/globalParam.php");
require_once("mobpay/callcmpay.php");
if (!defined('IN_ECS'))
{
    define('IN_ECS', true);
}
//require_once('/web/ecshop/includes/init.php');

/*
if (!defined('IN_ECS'))
{
die('Hacking attempt');
}*/

$payment_lang = ROOT_PATH . 'languages/' . $GLOBALS['_CFG']['lang'] . '/payment/mobpay.php';

if (file_exists($payment_lang))
{
	global $_LANG;

	include_once($payment_lang);
}
if (isset($_POST['submit']) && $_POST['submit']=='手机支付') {

	//设置请求参数start
	$type         = "DirectPayConfirm";
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
	$bankAbbr     = 'ICBC';
	$currency     = $_POST['currency'];
	$orderDate    = $_POST['orderDate'];
	$orderId 	  = $_POST['orderId'];
	$merAcDate    = $_POST['merAcDate'];
	$period 	  = $_POST['period'];
	$periodUnit   = $_POST['periodUnit'];
	$merchantAbbr = $_POST['merchantAbbr'];
	$productDesc  = $_POST['productDesc'];
	$productId    = $_POST['productId'];
	//$productName  = mb_convert_encoding($_POST['productName'], "gb2312", "UTF-8");
    $productName  = mb_convert_encoding("万集客商品", "gb2312", "UTF-8");
    $productNum   = $_POST['productNum'];
	$reserved1    = $_POST['reserved1'];
	$reserved2    = $_POST['reserved2'];
	$userToken    = $_POST['userToken'];
	$showUrl 	  = $_POST['showUrl'];
	$couponsFlag  = $_POST['couponsFlag'];
    $is_real      = intval($_POST['is_real']);
	//设置请求参数 end

    $signKey=$GLOBALS['signKey'];

    if (!isset($_POST['is_real']))
    {
        require_once('/web/ecshop/includes/init.php');

        $sql = "SELECT order_id  FROM " . $GLOBALS['ecs']->table('order_info') .
            " WHERE order_sn = '$orderId'";
        $order =  $db->getRow($sql);

        $goods_sql = "SELECT  is_real FROM ecs_order_goods  WHERE order_id = '{$order['order_id']}'";
        $goods_row = $db->getRow($goods_sql);
        $is_real = intval($goods_row['is_real']);
    }

    //根据虚拟和实物来判断使用哪一个merchantId
    //if ($_SESSION['user_id'] == 169617)
    {
        $merchantId = ($is_real == 1 ? $GLOBALS['merchantId'] : $GLOBALS['merchantId_virtual']);
        $signKey= ($is_real == 1 ? $GLOBALS['signKey'] : $GLOBALS['signKey_virtual']);
    }

	//组织签名数据
	$signData = $characterSet.$callbackUrl.$notifyUrl.$ipAddress
        .$merchantId  .$requestId  .$signType .$type
        .$version     .$amount     .$bankAbbr .$currency
        .$orderDate   .$orderId    .$merAcDate .$period   .$periodUnit
        .$merchantAbbr.$productDesc.$productId.$productName
        .$productNum  .$reserved1  .$reserved2.$userToken
        .$showUrl     .$couponsFlag;

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

	//http请求到手机支付平台
	//print_r($requestData);exit();
	$sTotalString = POSTDATA($reqUrl,$requestData);
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
		//$signKey=$GLOBALS['signKey'];
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
	<html>
	<head>
		<title>即时到账(双向确认)DirectPayConfirm</title>
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
	$modules[$i]['desc'] = 'mobpay_desc';

	/* 是否支持货到付款 */
	$modules[$i]['is_cod'] = '0';

	/* 是否支持在线支付 */
	$modules[$i]['is_online'] = '1';

	/* 作者 */
	$modules[$i]['author']  = 'XUAN';

	/* 网址 */
	$modules[$i]['website'] = 'https://ipos.10086.cn/ips/cmpayService';

	/* 版本号 */
	$modules[$i]['version'] = '2.0.0';

	/* 配置信息 */
	$modules[$i]['config'] = array(
	array('name' => 'mobpay_account', 'type' => 'text', 'value' => ''),
	array('name' => 'mobpay_key',     'type' => 'text', 'value' => ''),
	);

	return;

}

class mobpay
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
        $this->mobpay();
    }

	function mobpay()
	{
	}

	/**
     * 生成支付代码
     * @param   array   $order  订单信息
     * @param   array   $payment    支付方式信息
     */
	function get_code($order, $payment)
	{
		global $db;
		//设置请求参数start
		$type         = "DirectPayConfirm";
		$reqUrl       =  $GLOBALS['reqUrl'];
		$ipAddress    = getClientIP();
		$characterSet = $GLOBALS['characterSet'];
		$callbackUrl  = $GLOBALS['callbackUrl'];
		$notifyUrl    = $GLOBALS['notifyUrl'];
		$merchantId   = $GLOBALS['merchantId'];
		$requestId    = $GLOBALS['requestId'];
		$signType     = $GLOBALS['signType'];
		$version      = $GLOBALS['version'];
		$amount 	    = $order['order_amount'];
		$bankAbbr     = '';
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
		
		$goods_sql = "SELECT goods_name, is_real FROM ecs_order_goods WHERE order_id = '" . $order['order_id'] . "'";
		$goods_row = $db->getRow($goods_sql);
		$productName  = sub_str($goods_row['goods_name'], 10, true);
		$productNum   = 1;
		$merArr = array("code"=>"umpay","log_id"=>$order['log_id']);
		$reserved1    =  base64_encode(serialize($merArr));
		$reserved2    = '';
		$userToken    = $rank_row['mobile_phone'];
		$showUrl = '';
		$couponsFlag  = '';
		//设置请求参数 end

        $signKey=$GLOBALS['signKey'];

        //根据虚拟和实物来判断使用哪一个merchantId
        //if ($_SESSION['user_id'] == 169617)
        {
            $merchantId = ($goods_row['is_real'] == 1 ? $GLOBALS['merchantId'] : $GLOBALS['merchantId_virtual']);
            $signKey= ($goods_row['is_real'] == 1 ? $GLOBALS['signKey'] : $GLOBALS['signKey_virtual']);
        }

		//组织签名数据
		$signData = $characterSet.$callbackUrl.$notifyUrl.$ipAddress
		.$merchantId  .$requestId  .$signType .$type
		.$version     .$amount     .$bankAbbr .$currency
		.$orderDate   .$orderId    .$merAcDate .$period   .$periodUnit
		.$merchantAbbr.$productDesc.$productId.$productName
		.$productNum  .$reserved1  .$reserved2.$userToken
		.$showUrl     .$couponsFlag;

		//MD5方式签名
		$hmac=MD5sign($signKey,$signData);



		$def_url  = '<div style="text-align:center"><form name="mobPay" style="text-align:center;" method="post"'.
		'action="http://www.wjike.com/includes/modules/payment/mobpay.php" enctype="multipart/form-data" target="_blank">';
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
        $def_url .= "<input type='hidden' name='is_real' value='" . $goods_row['is_real'] . "' />";

		$def_url .= "<input type='submit' name='submit' value='".$GLOBALS['_LANG']['pay_button']."' />";
		$def_url .= "</form></div><br />";

		return $def_url;
	}

    //刷单->跳转中移支付页面
    function get_post_data($order)
    {
        global $db;
        //设置请求参数start
        $type         = "DirectPayConfirm";
        $reqUrl       =  $GLOBALS['reqUrl'];
        $ipAddress    = getClientIP();
        $characterSet = $GLOBALS['characterSet'];
        $callbackUrl  = $GLOBALS['callbackUrl'];
        $notifyUrl    = $GLOBALS['notifyUrl'];
        $merchantId   = $GLOBALS['merchantId'];
        $requestId    = $GLOBALS['requestId'];
        $signType     = $GLOBALS['signType'];
        $version      = $GLOBALS['version'];
        $amount   = intval($order['order_amount']*100);
        $bankAbbr     = 'ICBC';
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

        $goods_sql = "SELECT goods_name, is_real FROM ecs_order_goods WHERE order_id = '" . $order['order_id'] . "'";
        $goods_row = $db->getRow($goods_sql);
        $productName  = mb_convert_encoding("万集客商品", "gb2312", "UTF-8");;
        $productNum   = 1;
        $merArr = array("code"=>"umpay","log_id"=>$order['log_id']);
        $reserved1    =  base64_encode(serialize($merArr));
        $reserved2    = '';
        $userToken    = $rank_row['mobile_phone'];
        $showUrl = '';
        $couponsFlag  = '';
        //设置请求参数 end

        $signKey=$GLOBALS['signKey'];

        //根据虚拟和实物来判断使用哪一个merchantId
        //if ($_SESSION['user_id'] == 169617)
        {
            $merchantId = ($goods_row['is_real'] == 1 ? $GLOBALS['merchantId'] : $GLOBALS['merchantId_virtual']);
            $signKey= ($goods_row['is_real'] == 1 ? $GLOBALS['signKey'] : $GLOBALS['signKey_virtual']);
        }

        //组织签名数据
        $signData = $characterSet.$callbackUrl.$notifyUrl.$ipAddress
            .$merchantId  .$requestId  .$signType .$type
            .$version     .$amount     .$bankAbbr .$currency
            .$orderDate   .$orderId    .$merAcDate .$period   .$periodUnit
            .$merchantAbbr.$productDesc.$productId.$productName
            .$productNum  .$reserved1  .$reserved2.$userToken
            .$showUrl     .$couponsFlag;

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

        //http请求到手机支付平台
        //print_r($requestData);exit();
        $sTotalString = POSTDATA($reqUrl,$requestData);
        $recv = $sTotalString["MSG"];
        $recvArray = parseRecv($recv);

        $code=$recvArray["returnCode"];
        $payUrl;
        if ($code!="000000")
        {
            echo "code:".$code."</br>msg:".decodeUtf8($recvArray["message"]);
            exit();
        }
        else
        {
            //$signKey=$GLOBALS['signKey'];
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
        <html>
        <head>
        <title>即时到账(双向确认)DirectPayConfirm</title>
            <link href="sdk.css" rel="stylesheet" type="text/css" />
            <meta http-equiv="Content-Type" content="text/html; charset=gb2312">
        </head>

        <body>
        <?php
        $recv = $sTotalString["MSG"];
        $recvArray = parseRecv($recv);

        $code=$recvArray["returnCode"];
        $payUrl;
        if ($code!="000000")
        {
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
	/**
     * 响应操作
     */
	function respond()
	{
		$payment             = get_payment(basename(__FILE__, '.php'));

		$merchant_acctid     = $payment['mobpay_account'];                 //收款帐号 不可空
		$key                 = $payment['mobpay_key'];


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

        //根据虚拟和实物来判断使用哪一个signKey
        $signKey = $GLOBALS['signKey'];
        if ($merchantId == $GLOBALS['merchantId_virtual'])
        {
            $signKey = $GLOBALS['signKey_virtual'];
        }

		//MD5方式签名

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
