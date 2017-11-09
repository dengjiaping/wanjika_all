<?php

/**
 * 国美闪白条支付插件
 */

if (!defined('IN_ECS'))
{
	die('Hacking attempt');
}

//引入语言包
$payment_lang = ROOT_PATH . 'languages/' . $GLOBALS['_CFG']['lang'] . '/payment/guomeipay.php';
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
	$modules[$i]['desc'] = 'guomeipay_desc';

	/* 是否支持货到付款 */
	$modules[$i]['is_cod'] = '0';

	/* 是否支持在线支付 */
	$modules[$i]['is_online'] = '1';

	/* 作者 */
	$modules[$i]['author']  = 'miaoyu';

	/* 网址 */
	$modules[$i]['website'] = '';

	/* 版本号 */
	$modules[$i]['version'] = '1.0.1';

	/* 配置信息 */
	$modules[$i]['config'] = array(
	array('name' => 'guomeipay_account', 'type' => 'text', 'value' => ''),
	array('name' => 'guomeipay_key',     'type' => 'text', 'value' => ''),
	);

	return;

}

class guomeipay
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
		$this->guomeipay();
	}

    function guomeipay()
    {
    }

    /**
     * 生成支付代码
     * @param   array   $order      订单信息
     * @param   array   $payment    支付方式信息
     */
    function get_code($order, $payment,$type = 'web')
    {
        $button = '<form target="_blank" name="payment" method="post" action="user.php">';
        $button .= "<input type='hidden' name='pay_id' value='" . $order['pay_id'] . "'>";
        $button .= "<input type='hidden' name='order_id' value='" . $order['order_id'] . "'>";
        $button .= "<input type='hidden' name='act' value='act_edit_payment' />";
        $button .= "<input class='btn-pay cp' type='submit' value='去支付' name='Submit' onclick='add();'>";

        return $button;
    }

	/**
     * 支付跳转
     * @param   array   $order  订单信息
     * @param   array   $payment    支付方式信息
     */
	function redirect_pay($order, $payment,$type = 'web')
	{
        $mercId = '';
        $mercKey = '';

        //获取配置信息中的商户号和商户密钥
        $pay_config = $GLOBALS['db']->getOne("SELECT pay_config FROM " . $GLOBALS['ecs']->table('payment') . " WHERE pay_code = 'guomeipay' AND enabled = 1");
        $pay_config = unserialize($pay_config);
        foreach($pay_config as $v)
        {
            if ($v['name'] == 'guomeipay_account')
            {
                $mercId = $v['value'];
            }
            if ($v['name'] == 'guomeipay_key')
            {
                $mercKey = $v['value'];
            }
        }

        $goods_sql = "SELECT goods_name, is_real FROM ecs_order_goods WHERE order_id = '" . $order['order_id'] . "'";
        $goods_list = $GLOBALS['db']->getAll($goods_sql);
        $productName  = '';
        if (!empty($goods_list))
        {
            foreach ($goods_list as $goods)
            {
                $productName .= $goods['goods_name'] . '|';
            }
        }
        //$productName = substr($productName, 0, -1);

        //字符集
        $charset = 'UTF-8';
        //商户编号
        //$mercId = $pay_config['guomeipay_account'];
        //接口类型
        $interfaceName = 'mercCrtH5Ord';
        if($type == 'web'){
            $interfaceName = 'mercCrtCsmOrd';
        }
        //版本号
        $version = '1.0';
        //签名算法
        $signType = 'MD5';
        //商户订单号
        $mercOrderNo = $order['order_sn'];
        //订单金额
        $amount = $order['order_amount'];
        //有效时间
        $validTime = '15d';
        //订单类型（传固定默认值）
        $orderType = '0101000';
        //实物标识
        $realFlg = 'Y';
        //订单描述
        $description = $productName;
        //用户IP地址
        $clientIp = self::getClientIP();
        //页面通知地址
        $pageUrl = $GLOBALS['ecs']->url() . 'user.php?act=order_list';
        //后台通知地址
        $notifyUrl = 'http://www.wjike.com/pay/guomeiRcvPay.php';
        //请求地址
        $requestUrl = 'https://www.meifenfenqi.com/ips';
//        $requestUrl = 'http://119.254.98.243/ips';
        if($type == 'web'){
            $pageUrl = $GLOBALS['ecs']->url() . 'user.php?act=order_list';
            $notifyUrl = $GLOBALS['ecs']->url() . 'pay/guomeiRcvPay.php';
            $requestUrl = 'https://syt.meifenfenqi.com/ips/mercCsm/charges/mercCrtCsmOrd';
//            $requestUrl = 'http://119.254.98.243:18080/ips/mercCsm/charges/mercCrtCsmOrd';
        }
        //自营商户标识
        $selfSupport = $order['is_overseas'];
        //收件人姓名
        $userName = $order['consignee'];
        //收件人地址
        $userAddr = $order['address'];
        //收件人手机号
        $userMbl = $order['tel'] ? $order['tel'] : $order['mobile'];
        //签名数据
        $hmac = '';

        /* 生成加密签名串，将密钥加到后面，md5之~*/
        $hmacStr = $charset . $mercId . $interfaceName . $version . $signType . $mercOrderNo . $amount . $validTime. $orderType. $realFlg. $description . $clientIp . $pageUrl . $notifyUrl;
        $hmacStr .= $mercKey;
        $hmac = md5($hmacStr);

        $file_name = "/web/pay_logs/request_guomei_".date("Ymd").".log";
        $logStr = "hmacStr=$hmacStr|hmac=$hmac\n";
        file_put_contents($file_name, $logStr, FILE_APPEND);

        $def_url = '<form name="guomeipay" style="text-align:center;" method="post"action="'.$requestUrl.'">';
        $def_url .= "<input type='hidden' name='charset' value='" . $charset . "' />";
        $def_url .= "<input type='hidden' name='mercId' value='" . $mercId . "' />";
        $def_url .= "<input type='hidden' name='interfaceName' value='" . $interfaceName . "' />";
        $def_url .= "<input type='hidden' name='version' value='" . $version . "' />";
        $def_url .= "<input type='hidden' name='signType' value='" . $signType . "' />";
        $def_url .= "<input type='hidden' name='mercOrderNo' value='" . $mercOrderNo . "' />";
        $def_url .= "<input type='hidden' name='amount' value='" . $amount . "' />";
        $def_url .= "<input type='hidden' name='validTime' value='" . $validTime . "' />";
        $def_url .= "<input type='hidden' name='orderType' value='" . $orderType . "' />";
        $def_url .= "<input type='hidden' name='realFlg' value='" . $realFlg . "' />";
        $def_url .= "<input type='hidden' name='description' value='" . $description . "' />";
        $def_url .= "<input type='hidden' name='clientIp' value='" . $clientIp . "' />";
        $def_url .= "<input type='hidden' name='pageUrl' value='" . $pageUrl . "' />";
        $def_url .= "<input type='hidden' name='notifyUrl' value='" . $notifyUrl . "' />";
        $def_url .= "<input type='hidden' name='selfSupport' value='" . $selfSupport . "' />";
        $def_url .= "<input type='hidden' name='userName' value='" . $userName . "' />";
        $def_url .= "<input type='hidden' name='userAddr' value='" . $userAddr . "' />";
        $def_url .= "<input type='hidden' name='userMbl' value='" . $userMbl . "' />";
        $def_url .= "<input type='hidden' name='hmac' value='" . $hmac . "' />";
        $def_url .= "</form><script>document.forms['guomeipay'].submit();</script>";

		return $def_url;
	}

    /**
     * 退款
     */
    function refund($ordersn,$refordno,$money_paid,$refundid)
    {
        $mercId = '';
        $mercKey = '';

        //获取配置信息中的商户号和商户密钥
        $pay_config = $GLOBALS['db']->getOne("SELECT pay_config FROM " . $GLOBALS['ecs']->table('payment') . " WHERE pay_code = 'guomeipay' AND enabled = 1");
        $pay_config = unserialize($pay_config);
        foreach($pay_config as $v)
        {
            if ($v['name'] == 'guomeipay_account')
            {
                $mercId = $v['value'];
            }
            if ($v['name'] == 'guomeipay_key')
            {
                $mercKey = $v['value'];
            }
        }

        //字符集
        $charset = 'UTF-8';
        //商户编号
        //$mercId = $pay_config['guomeipay_account'];
        //接口类型
        $interfaceName = 'mercRefundOrd';
        //版本号
        $version = '1.0';
        //签名算法
        $signType = 'MD5';
        //商户订单号
        $oldOrdNo = $ordersn;
        //退款订单号
        $refOrdNo = $refordno;
        //退款金额
        $refAmt = $money_paid;
        //用户IP地址
        $clientIp = self::getClientIP();
        //签名数据
        $hmac = '';

        /* 生成加密签名串，将密钥加到后面，md5之~*/
        $hmacStr = $charset . $mercId . $interfaceName . $version . $signType . $oldOrdNo . $refOrdNo . $refAmt . $clientIp;
        $hmacStr .= $mercKey;
        $hmac = md5($hmacStr);

        $file_name = "/web/pay_logs/refund_guomei_".date("Ymd").".log";
        $logStr = "hmacStr=$hmacStr|hmac=$hmac\n";
        file_put_contents($file_name, $logStr, FILE_APPEND);

        $request_url = "https://syt.meifenfenqi.com/ips/mercCsm/charges/mercRefundOrd";
//        $request_url = "http://119.254.98.243:18080/ips/mercCsm/charges/mercRefundOrd";
        $post_str = "charset=$charset&mercId=$mercId&interfaceName=$interfaceName&version=$version&signType=$signType&oldOrdNo=$oldOrdNo&refOrdNo=$refOrdNo&refAmt=$refAmt&clientIp=$clientIp&hmac=$hmac";
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $request_url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $post_str);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $data = curl_exec($ch);
        curl_close($ch);

        $resArr = json_decode($data, true);
        $action_user = $_SESSION["admin_name"];
        if(isset($resArr["returnCode"]) && $resArr["returnCode"] == "IPS0000"){
            $ordersql = 'UPDATE ' . $GLOBALS['ecs']->table('order_info') . " SET order_status='".OS_RETURNED."' WHERE order_sn = '$ordersn'";
            $GLOBALS['db']->query($ordersql);

            $sql = 'UPDATE ' . $GLOBALS['ecs']->table('refund_info') . " SET status='3', action_user = '$action_user' WHERE id = '$refundid'";
            $GLOBALS['db']->query($sql);

            return true;
        }
        else{
            $sql = 'UPDATE ' . $GLOBALS['ecs']->table('refund_info') . " SET status='4', action_user = '$action_user' WHERE id = '$refundid'";
            $GLOBALS['db']->query($sql);

            $file_name = "/web/pay_logs/refund_guomei_".date("Ymd").".log";
            $logStr = "returnCode=".$resArr["returnCode"]."|message=".$resArr["message"]."\n";
            file_put_contents($file_name, $logStr, FILE_APPEND);

            return false;
        }
    }

    private static function getClientIP()
    {
        if(!empty($_SERVER["HTTP_CLIENT_IP"]))
        {
            $cip = $_SERVER["HTTP_CLIENT_IP"];
        }
        else if(!empty($_SERVER["HTTP_X_FORWARDED_FOR"]))
        {
            $cip = $_SERVER["HTTP_X_FORWARDED_FOR"];
        }
        else if(!empty($_SERVER["REMOTE_ADDR"]))
        {
            $cip = $_SERVER["REMOTE_ADDR"];
        }
        else
        {
            $cip = "unknown";
        }
        return $cip;

    }
}

?>
