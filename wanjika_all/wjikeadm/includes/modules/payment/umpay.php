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
 * $Id: umpay.php 17217 2011-01-19 06:29:08Z liubo $
 */

if (!defined('IN_ECS'))
{
	die('Hacking attempt');
}

$payment_lang = ROOT_PATH . 'languages/' . $GLOBALS['_CFG']['lang'] . '/payment/umpay.php';

if (file_exists($payment_lang))
{
	global $_LANG;

	include_once($payment_lang);
}

/* 模块的基本信息 */
if (isset($set_modules) && $set_modules == true)
{
	$i = isset($modules) ? count($modules) : 0;

	/* 代码 */
	$modules[$i]['code'] = basename(__FILE__, '.php');

	/* 描述对应的语言项 */
	$modules[$i]['desc'] = 'umpay_desc';

	/* 是否支持货到付款 */
	$modules[$i]['is_cod'] = '0';

	/* 是否支持在线支付 */
	$modules[$i]['is_online'] = '1';

	/* 作者 */
	$modules[$i]['author']  = 'XUAN';

	/* 网址 */
	$modules[$i]['website'] = 'http://www.99bill.com';

	/* 版本号 */
	$modules[$i]['version'] = '1.0.1';

	/* 配置信息 */
	$modules[$i]['config'] = array(
	array('name' => 'umpay_account', 'type' => 'text', 'value' => ''),
	array('name' => 'umpay_key',     'type' => 'text', 'value' => ''),
	);

	return;

}

class umpay
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
		$this->umpay();
	}

    function umpay()
    {
    }

	/**
     * 生成支付代码
     * @param   array   $order  订单信息
     * @param   array   $payment    支付方式信息
     */
	function get_code($order, $payment)
	{
		/*print_r($order);
		print_r($payment);
		exit;*/
        $merInfo = get_merid_and_primary_key($order['pay_mobile']);
		$priv_key_file = $_SERVER['DOCUMENT_ROOT']."/includes/" . $merInfo['primary_key'];

		//商户号
		$merId = $merInfo['merId'];;
		//商品号
		$goodsId = '001';//需确认是联动商品id还是万集客商品id
		//商品信息
		$goodsInf = "wjike";
		//手机号
		if (preg_match("/1\d{10}/",$order['pay_mobile'])) {
			$mobileId = $order['pay_mobile'];
		}
		else {
			$mobileId = $order['tel'];
		}

		//订单号
		$orderId = $order['order_id'];
		//商户下单日期
		$merDate = date("Ymd");//格式：yyyyMMdd
		//金额
		$amount = $order['order_amount']*100;//订单金额，单位分
		//金额类型
		$amtType = '02';//定值02 代表话费
		//银行类型
		$bankType = 3;//定值3，话费
		//网银Id
		$gateId = '';//默认空
		//返回商户URL
		$retUrl = 'http://www.wjike.com/respond.php';//?act=order_list';//返回地址设空
		//支付通知URL
//		$notifyUrl = $GLOBALS['ecs']->url() . 'respond.php?code=umpay&log_id='.$order['log_id'];
		$notifyUrl = 'http://www.wjike.com/notify.php';//?code=umpay&log_id='.$order['log_id'];
		//商户私有信息
		$merArr = array("code"=>"umpay","log_id"=>$order['log_id']);
		$merPriv = base64_encode(serialize($merArr));
		//扩展信息
		$expand = 'mer';
		//版本号
		$version = '3.0';


		/* 生成加密签名串 请务必按照如下顺序和规则组成加密串！*/
		$paramNew="";
		$paramNew =$paramNew . "merId=" . trim($merId,"\x00..\x1F");//商户号
		$paramNew =$paramNew . "&goodsId=" . trim($goodsId,"\x00..\x1F");//商品号
		if(!empty($goodsInf)){
			$paramNew =$paramNew . "&goodsInf=" .trim($goodsInf,"\x00..\x1F");//商品信息
		}
		if(!empty($mobileId)){
			$paramNew =$paramNew . "&mobileId=" . trim($mobileId,"\x00..\x1F");
		}
		$paramNew =$paramNew . "&orderId=" . trim($orderId,"\x00..\x1F");//订单号
		$paramNew =$paramNew . "&merDate=" . trim($merDate,"\x00..\x1F");//商户日期
		$paramNew =$paramNew . "&amount=" . trim($amount,"\x00..\x1F");//商品金额
		$paramNew =$paramNew . "&amtType=" . trim($amtType,"\x00..\x1F");//货币类型 定值02
		if(!empty($bankType)){
			$paramNew =$paramNew . "&bankType=" . trim($bankType,"\x00..\x1F");//银行类型 定值3
		}
//		if(!empty($gateId)){
			$paramNew =$paramNew . "&gateId=" . trim($gateId,"\x00..\x1F");//通道号
//		}
		$paramNew =$paramNew . "&retUrl=" . trim($retUrl,"\x00..\x1F");//返回页面地址
		if(!empty($notifyUrl)){
			$paramNew =$paramNew . "&notifyUrl=" . trim($notifyUrl,"\x00..\x1F");//后台通知地址
		}
		if(!empty($merPriv)){
			$paramNew =$paramNew . "&merPriv=" . trim($merPriv,"\x00..\x1F");//商户私有信息
		}
		if(!empty($expand)){
			$paramNew =$paramNew . "&expand=" . trim($expand,"\x00..\x1F");//商户扩展信息
		}
		$paramNew =$paramNew . "&version=" . trim($version,"\x00..\x1F");//版本信息 定值3.0
		$priv_key_file = $_SERVER['DOCUMENT_ROOT']."/includes/" . $merInfo['primary_key'];
		$pemSignNew = $this->ssl_sign($paramNew,$priv_key_file);
		$file_name = "/web/pay_log/request_".date("Ymd").".log";
		$logStr = "[".$goodsId."][".$goodsInf."][".$mobileId."][".$orderId."][".$merDate."][".$amount."][".$amtType."][".$bankType."][".$gateId."][".$retUrl."][".$notifyUrl."][".$merPriv."][".$expand."][".$version."][".$pemSignNew."][".$paramNew."]";
                file_put_contents($file_name,$logStr,FILE_APPEND);
		$def_url  = '<div style="text-align:center"><form name="kqPay" style="text-align:center;" method="post"'.
		'action="http://payment.umpay.com/hfwebbusi/pay/page.do" target="_blank">';
		$def_url .= "<input type='hidden' name='merId' value='" . $merId . "' />";
		$def_url .= "<input type='hidden' name='goodsId' value='" . $goodsId . "' />";
		$def_url .= "<input type='hidden' name='goodsInf' value='" . $goodsInf . "' />";
		$def_url .= "<input type='hidden' name='mobileId' value='" . $mobileId . "' />";
		$def_url .= "<input type='hidden' name='orderId' value='" . $orderId . "' />";
		$def_url .= "<input type='hidden' name='merDate' value='" . $merDate . "' />";
		$def_url .= "<input type='hidden' name='amount' value='" . $amount . "' />";
		$def_url .= "<input type='hidden' name='amtType' value='" . $amtType . "' />";
		$def_url .= "<input type='hidden' name='bankType' value='" . $bankType . "' />";
		$def_url .= "<input type='hidden' name='gateId' value='" . $gateId . "' />";
		$def_url .= "<input type='hidden' name='retUrl' value='" . $retUrl . "' />";
		$def_url .= "<input type='hidden' name='notifyUrl' value='" . $notifyUrl . "' />";
		$def_url .= "<input type='hidden' name='merPriv' value='" . $merPriv . "' />";
		$def_url .= "<input type='hidden' name='expand' value='" . $expand . "' />";
		$def_url .= "<input type='hidden' name='version' value='" . $version . "' />";
		$def_url .= "<input type='hidden' name='sign' value='" .$pemSignNew ."' />";
		$def_url .= "<input type='hidden' name='sign' value='" . $paramNew . "' />";


		$def_url .= "<input type='submit' name='submit' value='".$GLOBALS['_LANG']['pay_button']."' />";
		$def_url .= "</form></div><br />";
		return $def_url;
	}

    //刷单数据->跳转中移支付页面
    function get_post_data($order)
    {

        $merInfo = get_merid_and_primary_key($order['pay_mobile']);
        $priv_key_file = $_SERVER['DOCUMENT_ROOT']."/includes/" . $merInfo['primary_key'];

        //商户号
        $merId = $merInfo['merId'];;
        //商品号
        $goodsId = '001';//需确认是联动商品id还是万集客商品id
        //商品信息
        $goodsInf = "wjike";
        //手机号
        if (preg_match("/1\d{10}/",$order['pay_mobile'])) {
            $mobileId = $order['pay_mobile'];
        }
        else {
            $mobileId = $order['tel'];
        }

        //订单号
        $orderId = $order['order_id'];
        //商户下单日期
        $merDate = date("Ymd");//格式：yyyyMMdd
        //金额
        $amount = $order['order_amount']*100;//订单金额，单位分
        //金额类型
        $amtType = '02';//定值02 代表话费
        //银行类型
        $bankType = 3;//定值3，话费
        //网银Id
        $gateId = '';//默认空
        //返回商户URL
        $retUrl = 'http://www.wjike.com/respond.php';//?act=order_list';//返回地址设空
        //支付通知URL
//		$notifyUrl = $GLOBALS['ecs']->url() . 'respond.php?code=umpay&log_id='.$order['log_id'];
        $notifyUrl = 'http://www.wjike.com/notify.php';//?code=umpay&log_id='.$order['log_id'];
        //商户私有信息
        $merArr = array("code"=>"umpay","log_id"=>$order['log_id']);
        $merPriv = base64_encode(serialize($merArr));
        //扩展信息
        $expand = 'mer';
        //版本号
        $version = '3.0';


        /* 生成加密签名串 请务必按照如下顺序和规则组成加密串！*/
        $paramNew="";
        $paramNew =$paramNew . "merId=" . trim($merId,"\x00..\x1F");//商户号
        $paramNew =$paramNew . "&goodsId=" . trim($goodsId,"\x00..\x1F");//商品号
        if(!empty($goodsInf)){
            $paramNew =$paramNew . "&goodsInf=" .trim($goodsInf,"\x00..\x1F");//商品信息
        }
        if(!empty($mobileId)){
            $paramNew =$paramNew . "&mobileId=" . trim($mobileId,"\x00..\x1F");
        }
        $paramNew =$paramNew . "&orderId=" . trim($orderId,"\x00..\x1F");//订单号
        $paramNew =$paramNew . "&merDate=" . trim($merDate,"\x00..\x1F");//商户日期
        $paramNew =$paramNew . "&amount=" . trim($amount,"\x00..\x1F");//商品金额
        $paramNew =$paramNew . "&amtType=" . trim($amtType,"\x00..\x1F");//货币类型 定值02
        if(!empty($bankType)){
            $paramNew =$paramNew . "&bankType=" . trim($bankType,"\x00..\x1F");//银行类型 定值3
        }
//		if(!empty($gateId)){
        $paramNew =$paramNew . "&gateId=" . trim($gateId,"\x00..\x1F");//通道号
//		}
        $paramNew =$paramNew . "&retUrl=" . trim($retUrl,"\x00..\x1F");//返回页面地址
        if(!empty($notifyUrl)){
            $paramNew =$paramNew . "&notifyUrl=" . trim($notifyUrl,"\x00..\x1F");//后台通知地址
        }
        if(!empty($merPriv)){
            $paramNew =$paramNew . "&merPriv=" . trim($merPriv,"\x00..\x1F");//商户私有信息
        }
        if(!empty($expand)){
            $paramNew =$paramNew . "&expand=" . trim($expand,"\x00..\x1F");//商户扩展信息
        }
        $paramNew =$paramNew . "&version=" . trim($version,"\x00..\x1F");//版本信息 定值3.0
        $priv_key_file = $_SERVER['DOCUMENT_ROOT']."/includes/" . $merInfo['primary_key'];
        $pemSignNew = $this->ssl_sign($paramNew,$priv_key_file);
        //$post = "merId=$merId&goodsId=$goodsId&goodsInf=$goodsInf&mobileId=$mobileId&orderId=$orderId&merDate=$merDate&amount=$amount&amtType=$amtType&bankType=$bankType&gateId=$gateId&retUrl=$retUrl&notifyUrl=$notifyUrl&merPriv=$merPriv&expand=$expand&version=$version&sign=$pemSignNew";
        //$post = array('merId' => $merId, 'goodsId' =>$goodsId,'goodsInf' => $goodsInf,'mobileId' => $mobileId,'orderId' => $orderId,'merDate' => $merDate, 'amount' => $amount,'amtType' => $amtType,'bankType' => $bankType, 'gateId' => $gateId,'retUrl' =>$retUrl,'notifyUrl' =>$notifyUrl,'merPriv' => $merPriv,'expand' =>$expand,'version' =>$version,'sign' =>$pemSignNew );
//        $ch = curl_init();
//        curl_setopt($ch,CURLOPT_URL,'http://payment.umpay.com/hfwebbusi/pay/page.do');
//        curl_setopt($ch,CURLOPT_POST,1);
//        curl_setopt($ch,CURLOPT_POSTFIELDS,$post);
//        curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
//        $res = curl_exec($ch);
//        curl_close($ch);
//        return $res;
        $ch=curl_init();
        $curlPost = "merId=$merId&goodsId=$goodsId&goodsInf=$goodsInf&mobileId=$mobileId&orderId=$orderId&merDate=$merDate&amount=$amount&amtType=$amtType&bankType=$bankType&gateId=$gateId&retUrl=$retUrl&notifyUrl=$notifyUrl&merPriv=$merPriv&expand=$expand&version=$version&sign=$pemSignNew";
        curl_setopt($ch,CURLOPT_URL,'http://payment.umpay.com/hfwebbusi/pay/page.do');
        curl_setopt($ch,CURLOPT_POST,1);
        curl_setopt($ch,CURLOPT_POSTFIELDS,$curlPost);
        curl_setopt($ch,CURLOPT_RETURNTRANSFER,0);
        $res = curl_exec($ch);
        curl_close($ch);

        return $res;
        //return array('post' =>'http://payment.umpay.com/hfwebbusi/pay/page.do' ,'data' => $post );
    }

	/**
     * 响应操作
     */
	function respond()
	{
		$payment             = get_payment(basename(__FILE__, '.php'));

		$merchant_acctid     = $payment['umpay_account'];                 //收款帐号 不可空
		$key                 = $payment['umpay_key'];
		$file_name = "/web/pay_log/response_".date("Ymd").".log";
		$logStr = "[".date("Y-m-d H:i:s")."][".$_REQUEST['merId']."][".$_REQUEST['goodsId']."][".$_REQUEST['orderId']."][".$_REQUEST['merDate']."][".$_REQUEST['payDate']."][".$_REQUEST['amount']."][".$_REQUEST['amtType']."][".$_REQUEST['bankType']."][".$_REQUEST['mobileId']."][".$_REQUEST['transType']."][".$_REQUEST['settleDate']."][".$_REQUEST['merPriv']."][".$_REQUEST['merPriv']."][".$_REQUEST['retCode']."][".$_REQUEST['version']."][".$_REQUEST['sign']."][".$merArr['log_id']."][".$merchant_acctid."][".$key."]";
		file_put_contents($file_name,$logStr,FILE_APPEND);

		//商户号码
		$merId = $_REQUEST['merId'];
		//商品产品
		$goodsId = $_REQUEST['goodsId'];
		//订单号
		$orderId = $_REQUEST['orderId'];
		//商户下单日期
		$merDate = $_REQUEST['merDate'];
		//支付日期
		$payDate = $_REQUEST['payDate'];
		//金额
		$amount = $_REQUEST['amount'];
		//金额类型
		$amtType = $_REQUEST['amtType'];
		//银行类型
		$bankType = $_REQUEST['bankType'];
		//手机号
		$mobileId = $_REQUEST['mobileId'];
		//交易类型
		$transType = $_REQUEST['transType'];
		//结算日期
		$settleDate = $_REQUEST['settleDate'];
		//商户私有信息
		$merPriv = $_REQUEST['merPriv'];
		$merArr = unserialize(base64_decode($_REQUEST['merPriv']));
		//订单支付记录
       		$log_id = $merArr['log_id'];
		//返回码
		$retCode = $_REQUEST['retCode'];
		//版本
		$version = $_REQUEST['version'];
		//签名
		$sign = $_REQUEST['sign'];

		//重新组装签名字符串
		//根据手机钱包商户接入规范2.2V的5.1.2.1组装字符串，进行验签
		$paramNew="";
		$paramNew =$paramNew . "merId=" . trim($merId,"\x00..\x1F");
		$paramNew =$paramNew . "&goodsId=" . trim($goodsId,"\x00..\x1F");
		$paramNew =$paramNew . "&orderId=" . trim($orderId,"\x00..\x1F");
		$paramNew =$paramNew . "&merDate=" . trim($merDate,"\x00..\x1F");
		$paramNew =$paramNew . "&payDate=" . trim($payDate,"\x00..\x1F");
		$paramNew =$paramNew . "&amount=" . trim($amount,"\x00..\x1F");
		$paramNew = $paramNew . "&amtType=" . trim($amtType,"\x00..\x1F");
		$paramNew = $paramNew . "&bankType=" . trim($bankType,"\x00..\x1F");
		if(!empty($mobileId)){
			$paramNew =$paramNew . "&mobileId=" . trim($mobileId,"\x00..\x1F");
		}
		$paramNew = $paramNew . "&transType=" . trim($transType,"\x00..\x1F");
		$paramNew = $paramNew . "&settleDate=" . trim($settleDate,"\x00..\x1F");
		if(!empty($merPriv)){
			$paramNew = $paramNew . "&merPriv=" . trim($merPriv,"\x00..\x1F");
		}
		$paramNew = $paramNew . "&retCode=". trim($retCode,"\x00..\x1F");
		$paramNew = $paramNew . "&version=". trim($version,"\x00..\x1F");
		//首先对获得的商户号进行比对
/*
		if ($merId != $merchant_acctid)
		{
			//'商户号错误';
			return false;
		}
*/
		//验签
		$certfile = $_SERVER['DOCUMENT_ROOT']."/includes/cert_2d59.cert.pem";
		$result=self::ssl_verify($paramNew,$sign,$certfile);
		if(!$result)
		{
			//商户在此处做相关的验签失败的处理，如果失败说明有不正常的客户端在访问支付结果通知
			//验签失败后，返回码必然是不成功的
			return false;
		}
		else {
			if ($retCode == '0000')  //有成功支付的结果返回0000
			{
				order_paid($log_id);
				return true;
			}/*
			elseif ($pay_result == 11  && $pay_amount > 0)
			{
			$sql = "SELECT order_amount FROM " . $GLOBALS['ecs']->table('order_info') ."WHERE order_id = '$order_id'";
			$get_order_amount = $GLOBALS['db']->getOne($sql);
			if ($get_order_amount == $pay_amount && $get_order_amount == $order_amount) //检查订单金额、实际支付金额和订单是否相等
			{
			order_paid($ext1);

			return true;
			}
			elseif ($get_order_amount == $order_amount && $pay_amount > 0) //订单金额相等 实际支付金额 > 0的情况
			{
			$surplus_amount = $get_order_amount - $pay_amount;        //计算订单剩余金额
			$sql = 'UPDATE' . $GLOBALS['ecs']->table('order_info') . "SET `money_paid` = (money_paid  + '$pay_amount')," .
			" order_amount = (order_amount - '$pay_amount') WHERE order_id = '$order_id'";
			$result = $GLOBALS['db']->query($sql);
			$sql = 'UPDATE' . $GLOBALS['ecs']->table('order_info') . "SET `order_status` ='" . OS_CONFIRMED . "' WHERE order_id = '$orderId'";
			$result = $GLOBALS['db']->query($sql);
			//order_paid($orderId, PS_UNPAYED);
			//'订单金额小于0';
			return false;
			}
			else
			{
			//'订单金额不相等';
			return false;
			}
			}*/
			else
			{
				//'实际支付金额不能小于0';
				return false;
			}
		}
	}
	/*	function respond()
	{
	$payment             = get_payment(basename(__FILE__, '.php'));
	$merchant_acctid     = $payment['umpay_account'];                 //收款帐号 不可空
	$key                 = $payment['umpay_key'];
	$get_merchant_acctid = trim($_REQUEST['merchantAcctId']);     //接收的收款帐号
	$pay_result          = trim($_REQUEST['payResult']);
	$version             = trim($_REQUEST['version']);
	$language            = trim($_REQUEST['language']);
	$sign_type           = trim($_REQUEST['signType']);
	$pay_type            = trim($_REQUEST['payType']);            //20代表神州行卡密直接支付；22代表快钱账户神州行余额支付
	$card_umber          = trim($_REQUEST['cardNumber']);
	$card_pwd            = trim($_REQUEST['cardPwd']);
	$order_id            = trim($_REQUEST['orderId']);            //订单号
	$order_time          = trim($_REQUEST['orderTime']);
	$order_amount        = trim($_REQUEST['orderAmount']);
	$deal_id             = trim($_REQUEST['dealId']);             //获取该交易在快钱的交易号
	$ext1                = trim($_REQUEST['ext1']);
	$ext2                = trim($_REQUEST['ext2']);
	$pay_amount          = trim($_REQUEST['payAmount']);          //获取实际支付金额
	$bill_order_time     = trim($_REQUEST['billOrderTime']);
	$pay_result          = trim($_REQUEST['payResult']);         //10代表支付成功； 11代表支付失败
	$sign_type           = trim($_REQUEST['signType']);
	$sign_msg            = trim($_REQUEST['signMsg']);

	//生成加密串。必须保持如下顺序。
	$merchant_signmsgval = $this->append_param($merchant_signmsgval, "merchantAcctId", $merchant_acctid);
	$merchant_signmsgval = $this->append_param($merchant_signmsgval, "version", $version);
	$merchant_signmsgval = $this->append_param($merchant_signmsgval, "language", $language);
	$merchant_signmsgval = $this->append_param($merchant_signmsgval, "payType", $pay_type);
	$merchant_signmsgval = $this->append_param($merchant_signmsgval, "cardNumber", $card_number);
	$merchant_signmsgval = $this->append_param($merchant_signmsgval, "cardPwd", $card_pwd);
	$merchant_signmsgval = $this->append_param($merchant_signmsgval, "orderId", $order_id);
	$merchant_signmsgval = $this->append_param($merchant_signmsgval, "orderAmount", $order_amount);
	$merchant_signmsgval = $this->append_param($merchant_signmsgval, "dealId", $deal_id);
	$merchant_signmsgval = $this->append_param($merchant_signmsgval, "orderTime", $order_time);
	$merchant_signmsgval = $this->append_param($merchant_signmsgval, "ext1", $ext1);
	$merchant_signmsgval = $this->append_param($merchant_signmsgval, "ext2", $ext2);
	$merchant_signmsgval = $this->append_param($merchant_signmsgval, "payAmount", $pay_amount);
	$merchant_signmsgval = $this->append_param($merchant_signmsgval, "billOrderTime", $bill_order_time);
	$merchant_signmsgval = $this->append_param($merchant_signmsgval, "payResult", $pay_result);
	$merchant_signmsgval = $this->append_param($merchant_signmsgval, "signType", $sign_type);
	$merchant_signmsgval = $this->append_param($merchant_signmsgval, "key", $key);
	$merchant_signmsg    = md5($merchant_signmsgval);

	//首先对获得的商户号进行比对
	if ($get_merchant_acctid != $merchant_acctid)
	{
	//'商户号错误';
	return false;
	}

	if (strtoupper($sign_msg) == strtoupper($merchant_signmsg))
	{
	if ($pay_result == 10)  //有成功支付的结果返回10
	{
	order_paid($ext1);

	return true;
	}
	elseif ($pay_result == 11  && $pay_amount > 0)
	{
	$sql = "SELECT order_amount FROM " . $GLOBALS['ecs']->table('order_info') ."WHERE order_id = '$order_id'";
	$get_order_amount = $GLOBALS['db']->getOne($sql);
	if ($get_order_amount == $pay_amount && $get_order_amount == $order_amount) //检查订单金额、实际支付金额和订单是否相等
	{
	order_paid($ext1);

	return true;
	}
	elseif ($get_order_amount == $order_amount && $pay_amount > 0) //订单金额相等 实际支付金额 > 0的情况
	{
	$surplus_amount = $get_order_amount - $pay_amount;        //计算订单剩余金额
	$sql = 'UPDATE' . $GLOBALS['ecs']->table('order_info') . "SET `money_paid` = (money_paid  + '$pay_amount')," .
	" order_amount = (order_amount - '$pay_amount') WHERE order_id = '$order_id'";
	$result = $GLOBALS['db']->query($sql);
	$sql = 'UPDATE' . $GLOBALS['ecs']->table('order_info') . "SET `order_status` ='" . OS_CONFIRMED . "' WHERE order_id = '$orderId'";
	$result = $GLOBALS['db']->query($sql);
	//order_paid($orderId, PS_UNPAYED);
	//'订单金额小于0';
	return false;
	}
	else
	{
	//'订单金额不相等';
	return false;
	}
	}
	else
	{
	//'实际支付金额不能小于0';
	return false;
	}
	}
	else
	{
	//'签名校对错误';
	return false;
	}
	}
	*/
	/**
     * 将变量值不为空的参数组成字符串
     * @param   string   $strs  参数字符串
     * @param   string   $key   参数键名
     * @param   string   $val   参数键对应值
    */
	function append_param($strs,$key,$val)
	{
		if($strs != "")
		{
			if($val != "")
			{
				$strs .= '&' . $key . '=' . $val;
			}
		}
		else
		{
			if($val != "")
			{
				$strs = $key . '=' . $val;
			}
		}

		return $strs;
	}
	/*function ssl_sign($data,$priv_key_file){
	if(!$priv_key_file){$priv_key_file ="./7145_WanJiKe.key.pem";}
	if(!File_exists($priv_key_file)){
	echo "key_file is not exists!\n";
	return FALSE;
	}
	//error_log($data,3,'/tmp/datastring');
	$fp = fopen($priv_key_file, "rb");

	$priv_key = fread($fp, 8192);

	@fclose($fp);
	$pkeyid = openssl_get_privatekey($priv_key);

	if(!is_resource($pkeyid)){echo "not a resource \n " ; return FALSE;}
	// compute signature

	@openssl_sign($data, $signature, $pkeyid);

	// free the key from memory
	@openssl_free_key($pkeyid);
	return base64_encode($signature);
	}*/
	function ssl_sign($data,$priv_key_file){
    if(!$priv_key_file){$priv_key_file ="./7145_WanJiKe.key.pem";}
    if(!File_exists($priv_key_file)){
        echo "key_file is not exists!\n";
        return FALSE;
    }
    //error_log($data,3,'/tmp/datastring');
    $fp = fopen($priv_key_file, "rb");

    $priv_key = fread($fp, 8192);
    @fclose($fp);
    $pkeyid = openssl_get_privatekey($priv_key);

    if(!is_resource($pkeyid)){echo "not a resource \n " ; return FALSE;}
    // compute signature

    @openssl_sign($data, $signature, $pkeyid);

    // free the key from memory
    @openssl_free_key($pkeyid);
    return base64_encode($signature);
}
	function ssl_verify($data,$signature,$cert_file){
		if(!$cert_file){$cert_file ="./cert_2d59.cert.pem"; }
		if(!File_exists($cert_file)){
			return FALSE;
			echo "cert_file is not exists!\n";
		}
		$signature = base64_decode($signature);
		$fp = fopen($cert_file, "r");
		$cert = fread($fp, 8192);
		fclose($fp);
		//echo $data."<br>".$signature."<br>".$cert_file."<br>" ;//exit;
		$pubkeyid = openssl_get_publickey($cert);
		if(!is_resource($pubkeyid)){
			return FALSE;
		}
		$ok = openssl_verify($data,$signature,$pubkeyid);
		@openssl_free_key($pubkeyid);
		if ($ok == 1) {
			//echo "sucessful!";
			return TRUE;
		} elseif ($ok == 0) {
			//echo "fail!!!";
			return FALSE;
		} else {
			return FALSE;
		}
		return FALSE;
	}
}

?>
