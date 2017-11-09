<?php

/**
 * ebc支付插件
 */

if (!defined('IN_ECS'))
{
    die('Hacking attempt');
}
//require("mobpay/m2.php");
//require_once("mobpay/lib_bonus.php");
header ( "content-type:text/html;charset=utf-8" );
//引入语言包
$payment_lang = ROOT_PATH . 'languages/' . $GLOBALS['_CFG']['lang'] . '/payment/ebcolpay.php';
if (file_exists($payment_lang))
{
    global $_LANG;

    include_once($payment_lang);
}
$bank_code_arr = array(
	'jsyh' => '105100000017',		//建设银行
	'gsyh' => '102100099996',			//中国工商银行
	'yzcx' => '403100000004',			//邮政储蓄
	'nyyh' => '103100000026',			//农业银行
	'pfyh' => '310290000013',			//浦发银行
	'jtyh' => '301290000007',			//交通银行
	'zsyh' => '308584000013',			//招商银行
	'msyh' => '305100000013',			//民生银行
	'gfyh' => '306581000003',			//广发银行
	'zxyh' => '302100011000',			//中信银行
	'hxyh' => '304100040000',			//华夏银行
	'xyyh' => '309391000011',			//兴业银行
	'zgyh' => '104100000004',			//中国银行
    'hbyh' => '313121006888',        //河北银行
    'dlyh' => '313222080002',        //大连银行
    'shyh' => '313290000017',        //上海银行
    //'jsyh' => '313301099999',        //江苏银行
    'szyh' => '313305066661',        //苏州银行
    'hzyh' => '313331000014',        //杭州银行
    'nbyh' => '313332082914',        //宁波银行
    'wzyh' => '313333007331',        //温州银行
    'jjyh' => '313424076706',        //九江银行
    'sryh' => '313433076801',        //上饶银行
    'qsyh' => '313453001017',        //齐商银行
    'dzyh' => '313468000015',        //德州银行
    'rzyh' => '313473200011',        //日照银行
    'gzyh' => '313581003284',        //广州银行
    'dgyh' => '313602088017',        //东莞银行
    'nxyh' => '313871000007',        //宁夏银行
    'bhyh' => '318110000014',        //渤海银行
    'zdyh' => '671581000013',        //渣打银行
    'sznsh' => '402584009991',       //深圳农商行
    'zggdyh' => '303100000006',        //中国光大银行
    'shnsyh' => '322290000011',        //上海农商银行
    'dysyyh' => '313455000018',        //东营市商业银行
    'tasyyh' => '313463000993',        //泰安市商业银行
    'whsyyh' => '313465000010',        //威海市商业银行
    'ycsyyh' => '313526099991',        //宜昌市商业银行
    'gxbbw' => '313611001018',        //广西北部湾银行
    'cdncsy' => '314651000000',        //成都农村商业银行
    'zjncxys' => '402331000007',        //浙江省农村信用社
    'wlmq' => '313881000002',        //乌鲁木齐市商业银行
    'zjgsyyh' => '314305670002',        //张家港农村商业银行
    'hnnc' => '402551080008',        //湖南省农村信用社联合社
    'sjyh' => '313221030104',        //盛京银行股份有限公司北京分行
    'sxyh' => '402177002523',        //山西省临汾市尧都区信用合作联社
);

/* 模块的基本信息 */
if (isset($set_modules) && $set_modules == true)
{
	$i = isset($modules) ? count($modules) : 0;

	/* 代码 */
	$modules[$i]['code'] = basename(__FILE__, '.php');

	/* 描述对应的语言项 */
	$modules[$i]['desc'] = 'ebcolpay_desc';

	/* 是否支持货到付款 */
	$modules[$i]['is_cod'] = '0';

	/* 是否支持在线支付 */
	$modules[$i]['is_online'] = '1';

	/* 作者 */
	$modules[$i]['author']  = 'ML';

	/* 网址 */
	$modules[$i]['website'] = '';

	/* 版本号 */
	$modules[$i]['version'] = '1.0.0';

	/* 配置信息 */
	$modules[$i]['config'] = array(
	);

	return;

}

class ebcolpay
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
        $this->ebcolpay();
    }

	function ebcolpay()
	{
	}

	/**
     * 生成支付代码
     * @param   array   $order  订单信息
     * @param   array   $payment    支付方式信息
     */
	function get_code($order, $payment, $bank_val, $bank_code_arr)
	{
        $usertype=0;//企业网银付款填写1.要用个人网银付款填写0
        $transcode='T604';//交易码
        $version='0140';//版本号
        $ordersn=$order['order_sn'];//流水号，唯一
        $platform='01';//01 网站02 手机03 接口
        $ownerid='EBC';//运营商EBC
        $merchno='611100000310430';//商户号611100000308132
        $dsorderid=$order['order_sn'];//商户订单号
        $product='wjike';//商户名称
        $productdesc='';//电商商品描述
        $userno='c9dd95b1a243418bba7a9aef74733c35';//用户ID$order['user_id']
        $mediumno='0100980018766605';//钱包ID0100980002951178
        $cardno='9001990020030070';//卡号9001990003087988
        $currency='CNY';//币种
        $transcurrency='CNY';//扣款币种
        $amount= $order['order_amount'];//金额
        $bank_code = !empty($bank_code_arr[$bank_val]) ? $bank_code_arr[$bank_val] : '102100099996';//扣款银行ID
//        $bankcard='';//用户账号/银行卡
//        $payusername='';//用户名称
//        $userbankcustom='';//网银客户号
        $cardtype='01';//卡类型
        $usertype='0';//用户类型
        $banktburl=$GLOBALS['ecs']->url() . 'user.php?act=order_list';//页面通知地址
        $dstburl=$GLOBALS['ecs']->url() . 'pay/ebcolRcvPay.php';//后台通知地址
        $actionurl=$GLOBALS['ecs']->url() . 'includes/modules/payment/ebcolpay/order.php';

        $def_url  = '<div style="text-align:center"><form name="ebcolpay" style="text-align:center;" method="post"'.
            'action="'.$actionurl.'" target="_blank">';
        $def_url .= "<input type='hidden' name='ebcbankid' value='" . $bank_code . "' />";
        $def_url .= "<input type='hidden' name='transcode' value='" . $transcode . "' />";
        $def_url .= "<input type='hidden' name='version' value='" . $version . "' />";
        $def_url .= "<input type='hidden' name='ordersn' value='" . $ordersn . "' />";
        $def_url .= "<input type='hidden' name='platform' value='" . $platform . "' />";
        $def_url .= "<input type='hidden' name='ownerid' value='" . $ownerid . "' />";
        $def_url .= "<input type='hidden' name='merchno' value='" . $merchno . "' />";
        $def_url .= "<input type='hidden' name='dsorderid' value='" . $dsorderid . "' />";
        $def_url .= "<input type='hidden' name='product' value='" . $product . "' />";
        $def_url .= "<input type='hidden' name='userno' value='" . $userno . "' />";
        $def_url .= "<input type='hidden' name='mediumno' value='" . $mediumno . "' />";
        $def_url .= "<input type='hidden' name='cardno' value='" . $cardno . "' />";
        $def_url .= "<input type='hidden' name='currency' value='" . $currency . "' />";
        $def_url .= "<input type='hidden' name='transcurrency' value='" . $transcurrency . "' />";
        $def_url .= "<input type='hidden' name='amount' value='" . $amount . "' />";
        $def_url .= "<input type='hidden' name='cardtype' value='" . $cardtype . "' />";
        $def_url .= "<input type='hidden' name='usertype' value='" . $usertype . "' />";
        $def_url .= "<input type='hidden' name='banktburl' value='" . $banktburl . "' />";
        $def_url .= "<input type='hidden' name='dsyburl' value='" . $dstburl . "' />";
        $def_url .= "<input class='btn-pay cp' onclick='add();' type='submit' name='submit' value='去支付' />";
        $def_url .= "</form></div><br />";

        return $def_url;
	}

    /**
     * 支付跳转
     * @param   array   $order  订单信息
     * @param   array   $payment    支付方式信息
     */
    function redirect_pay($order, $payment, $bank_val, $bank_code_arr)
    {
        $usertype=0;//企业网银付款填写1.要用个人网银付款填写0
        $transcode='T604';//交易码
        $version='0140';//版本号
        $ordersn=$order['order_sn'];//流水号，唯一
        $platform='01';//01 网站02 手机03 接口
        $ownerid='EBC';//运营商EBC
        $merchno='611100000310430';//商户号611100000308132
        $dsorderid=$order['order_sn'];//商户订单号
        $product='wjike';//商户名称
        $productdesc='';//电商商品描述
        $userno='c9dd95b1a243418bba7a9aef74733c35';//用户ID$order['user_id']
        $mediumno='0100980018766605';//钱包ID0100980002951178
        $cardno='9001990020030070';//卡号9001990003087988
        $currency='CNY';//币种
        $transcurrency='CNY';//扣款币种
        $amount= $order['order_amount'];//金额
        $bank_code = !empty($bank_code_arr[$bank_val]) ? $bank_code_arr[$bank_val] : '102100099996';//扣款银行ID
//        $bankcard='';//用户账号/银行卡
//        $payusername='';//用户名称
//        $userbankcustom='';//网银客户号
        $cardtype='01';//卡类型
        $usertype='0';//用户类型
        $banktburl=$GLOBALS['ecs']->url() . 'user.php?act=order_list';//页面通知地址
        $dstburl=$GLOBALS['ecs']->url() . 'pay/ebcolRcvPay.php';//后台通知地址
        $actionurl=$GLOBALS['ecs']->url() . 'includes/modules/payment/ebcolpay/order.php';

        $def_url  = '<div style="text-align:center"><form name="ebcolpay" style="text-align:center;" method="post"'.
            'action="'.$actionurl.'">';
        $def_url .= "<input type='hidden' name='ebcbankid' value='" . $bank_code . "' />";
        $def_url .= "<input type='hidden' name='transcode' value='" . $transcode . "' />";
        $def_url .= "<input type='hidden' name='version' value='" . $version . "' />";
        $def_url .= "<input type='hidden' name='ordersn' value='" . $ordersn . "' />";
        $def_url .= "<input type='hidden' name='platform' value='" . $platform . "' />";
        $def_url .= "<input type='hidden' name='ownerid' value='" . $ownerid . "' />";
        $def_url .= "<input type='hidden' name='merchno' value='" . $merchno . "' />";
        $def_url .= "<input type='hidden' name='dsorderid' value='" . $dsorderid . "' />";
        $def_url .= "<input type='hidden' name='product' value='" . $product . "' />";
        $def_url .= "<input type='hidden' name='userno' value='" . $userno . "' />";
        $def_url .= "<input type='hidden' name='mediumno' value='" . $mediumno . "' />";
        $def_url .= "<input type='hidden' name='cardno' value='" . $cardno . "' />";
        $def_url .= "<input type='hidden' name='currency' value='" . $currency . "' />";
        $def_url .= "<input type='hidden' name='transcurrency' value='" . $transcurrency . "' />";
        $def_url .= "<input type='hidden' name='amount' value='" . $amount . "' />";
        $def_url .= "<input type='hidden' name='cardtype' value='" . $cardtype . "' />";
        $def_url .= "<input type='hidden' name='usertype' value='" . $usertype . "' />";
        $def_url .= "<input type='hidden' name='banktburl' value='" . $banktburl . "' />";
        $def_url .= "<input type='hidden' name='dsyburl' value='" . $dstburl . "' />";
        $def_url .= "</form><script>document.forms['ebcolpay'].submit();</script>";

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
