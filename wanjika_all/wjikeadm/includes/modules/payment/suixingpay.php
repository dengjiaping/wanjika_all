<?php

/**
 * 随行支付插件
 */

if (!defined('IN_ECS'))
{
	die('Hacking attempt');
}

//引入语言包
$payment_lang = ROOT_PATH . 'languages/' . $GLOBALS['_CFG']['lang'] . '/payment/suixingpay.php';
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
	$modules[$i]['desc'] = 'suixingpay_desc';

	/* 是否支持货到付款 */
	$modules[$i]['is_cod'] = '0';

	/* 是否支持在线支付 */
	$modules[$i]['is_online'] = '1';

	/* 作者 */
	$modules[$i]['author']  = 'qihua';

	/* 网址 */
	$modules[$i]['website'] = '';

	/* 版本号 */
	$modules[$i]['version'] = '1.0.1';

	/* 配置信息 */
	$modules[$i]['config'] = array(
	array('name' => 'suixingpay_account', 'type' => 'text', 'value' => ''),
	array('name' => 'suixingpay_key',     'type' => 'text', 'value' => ''),
	);

	return;

}

class suixingpay
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
		$this->suixingpay();
	}

    function suixingpay()
    {
    }

	/**
     * 生成支付代码
     * @param   array   $order  订单信息
     * @param   array   $payment    支付方式信息
     */
	function get_code($order)
	{
        $mercId = '';
        $mercKey = '';

        //获取配置信息中的商户号和商户密钥
        $pay_config = $GLOBALS['db']->getOne("SELECT pay_config FROM " . $GLOBALS['ecs']->table('payment') . " WHERE pay_code = 'suixingpay' AND enabled = 1");
        $pay_config = unserialize($pay_config);
        foreach($pay_config as $v)
        {
            if ($v['name'] == 'suixingpay_account')
            {
                $mercId = $v['value'];
            }
            if ($v['name'] == 'suixingpay_key')
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

        //字符集
        $charset = 'UTF-8';
        //商户编号
        //$mercId = $pay_config['suixingpay_account'];
        //接口类型
        $interfaceName = 'mercCrtCsmOrd';
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
        //订单描述
        $description = $productName;
        //用户IP地址
        $ip = self::getClientIP();
        //页面通知地址
        $pageUrl = 'http://www.wjike.com/suixingrespond.php';
        //后台通知地址
        $notifyUrl = 'http://www.wjike.com/suixingnotify.php';
        //签名数据
        $hmac = '';
        //收件人姓名
        $userName = $order['consignee'];
        //收件人地址
        $address = $order['address'];
        //收件人手机号
        $mobile = $order['tel'] ? $order['tel'] : $order['mobile'];

        /* 生成加密签名串，将密钥加到后面，md5之~*/
        $hmacStr = $charset . $mercId . $interfaceName . $version . $signType . $mercOrderNo . $amount . $validTime . $description . $ip . $pageUrl . $notifyUrl;
        $hmacStr .= $mercKey;
        $hmac = md5($hmacStr);

        $file_name = "/web/pay_log/request_suixing_".date("Ymd").".log";
        $logStr = "hmacStr=$hmacStr|hmac=$hmac\n";
        file_put_contents($file_name, $logStr, FILE_APPEND);

		$def_url  = '<div style="text-align:center">';
        $def_url .= '<form name="suixingpay" style="text-align:center;" method="post"action="https://baitiao.shanqb.com/ips/mercCsm/charges/mercCrtCsmOrd" target="_blank">';
        $def_url .= "<input type='hidden' name='charset' value='" . $charset . "' />";
        $def_url .= "<input type='hidden' name='mercId' value='" . $mercId . "' />";
        $def_url .= "<input type='hidden' name='interfaceName' value='" . $interfaceName . "' />";
        $def_url .= "<input type='hidden' name='version' value='" . $version . "' />";
        $def_url .= "<input type='hidden' name='signType' value='" . $signType . "' />";
        $def_url .= "<input type='hidden' name='mercOrderNo' value='" . $mercOrderNo . "' />";
        $def_url .= "<input type='hidden' name='amount' value='" . $amount . "' />";
        $def_url .= "<input type='hidden' name='validTime' value='" . $validTime . "' />";
        $def_url .= "<input type='hidden' name='description' value='" . $description . "' />";
        $def_url .= "<input type='hidden' name='userName' value='" . $userName . "' />";
        $def_url .= "<input type='hidden' name='userAddr' value='" . $address . "' />";
        $def_url .= "<input type='hidden' name='userMbl' value='" . $mobile . "' />";
        $def_url .= "<input type='hidden' name='clientIp' value='" . $ip . "' />";
		$def_url .= "<input type='hidden' name='pageUrl' value='" . $pageUrl . "' />";
        $def_url .= "<input type='hidden' name='notifyUrl' value='" . $notifyUrl . "' />";
        $def_url .= "<input type='hidden' name='hmac' value='" . $hmac . "' />";
		$def_url .= "<input style=\"width:200px;height:50px;font-size:200%;\" type='submit' name='submit' value='".$GLOBALS['_LANG']['pay_button']."' />";
		$def_url .= "</form></div><br />";

		return $def_url;
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
