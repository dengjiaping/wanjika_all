<?php

/**
 * ebc支付插件
 */

if (!defined('IN_ECS'))
{
	die('Hacking attempt');
}

//引入语言包
$payment_lang = ROOT_PATH . 'languages/' . $GLOBALS['_CFG']['lang'] . '/payment/ebcpay.php';
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
	$modules[$i]['desc'] = 'ebcpay_desc';

	/* 是否支持货到付款 */
	$modules[$i]['is_cod'] = '0';

	/* 是否支持在线支付 */
	$modules[$i]['is_online'] = '1';

	/* 作者 */
	$modules[$i]['author']  = 'MIAO';

	/* 网址 */
	$modules[$i]['website'] = '';

	/* 版本号 */
	$modules[$i]['version'] = '1.0.1';

	/* 配置信息 */
	$modules[$i]['config'] = array(
	);

	return;

}

class ebcpay
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
		$this->ebcpay();
	}

    function ebcpay()
    {
    }

	/**
     * 生成支付代码
     * @param   array   $order  订单信息
     * @param   array   $payment    支付方式信息
     */
	function get_code($order, $payment)
	{
        //商户编号
        $merchno = '611100000308132';
        //商户密钥
        $mercKey = '586ea205';
        //商户订单号
        $dsorderid = $order['order_sn'];
        //订单金额
        $amount = $order['order_amount'];
        //商品名称
        $product = 'wjike';
        //页面通知地址
        $dsyburl = 'http://www.wjike.com/pay/ebcRcvPay.php';
        //后台通知地址
        $dstburl = 'http://www.wjike.com/user.php?act=order_list';

        $dstbdatasignStr = "merchno=$merchno&dsorderid=$dsorderid&amount=$amount&product=$product&dsyburl=$dsyburl&dstburl=$dstburl";
        //签名数据
        $des = new DES($mercKey);
        $dstbdatasign = $des->encrypt($dstbdatasignStr);

        $file_name = "/web/pay_log/request_ebc_".date("Ymd").".log";
        $logStr = "dstbdatasignStr=$dstbdatasignStr|dstbdatasign=$dstbdatasign\n";
        file_put_contents($file_name, $logStr, FILE_APPEND);

		$def_url  = '<div style="text-align:center">';
        $def_url .= '<form name="ebcpay" style="text-align:center;" method="GET" action="http://www.ebcpay.com/payment/getwaychange.html" target="_blank">';
        $def_url .= "<input type='hidden' name='merchno' value='" . $merchno . "' />";
        $def_url .= "<input type='hidden' name='dsorderid' value='" . $dsorderid . "' />";
        $def_url .= "<input type='hidden' name='amount' value='" . $amount . "' />";
        $def_url .= "<input type='hidden' name='product' value='" . $product . "' />";
        $def_url .= "<input type='hidden' name='dsyburl' value='" . $dsyburl . "' />";
        $def_url .= "<input type='hidden' name='dstburl' value='" . $dstburl . "' />";
        $def_url .= "<input type='hidden' name='dstbdatasign' value='" . $dstbdatasign . "' />";
		$def_url .= "<input type='submit' name='submit' value='".$GLOBALS['_LANG']['pay_button']."' />";
		$def_url .= "</form></div><br />";

		return $def_url;
	}
}

class DES{
    var $key;
    function DES( $key ){
        $this->key = $key;
    }

    function encrypt($encrypt) {
        $encrypt = $this->pkcs5_pad($encrypt);
        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_DES, MCRYPT_MODE_ECB), MCRYPT_RAND);
        $passcrypt = mcrypt_encrypt(MCRYPT_DES, $this->key, $encrypt, MCRYPT_MODE_ECB, $iv);
        return strtoupper( bin2hex($passcrypt) );
    }

    function decrypt($decrypt) {
        // $decoded = base64_decode($decrypt);
        $decoded = pack("H*", $decrypt);
        $iv = mcrypt_create_iv(mcrypt_get_iv_size(MCRYPT_DES, MCRYPT_MODE_ECB), MCRYPT_RAND);
        $decrypted = mcrypt_decrypt(MCRYPT_DES, $this->key, $decoded, MCRYPT_MODE_ECB, $iv);
        return $this->pkcs5_unpad($decrypted);
    }

    function pkcs5_unpad($text){
        $pad = ord($text{strlen($text)-1});

        if ($pad > strlen($text)) return $text;
        if (strspn($text, chr($pad), strlen($text) - $pad) != $pad) return $text;
        return substr($text, 0, -1 * $pad);
    }

    function pkcs5_pad($text){
        $len = strlen($text);
        $mod = $len % 8;
        $pad = 8 - $mod;
        return $text.str_repeat(chr($pad),$pad);
    }

}

?>
