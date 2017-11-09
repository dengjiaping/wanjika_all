<?php

/**
 * 微信扫码支付
 */

if (!defined('IN_ECS'))
{
	die('Hacking attempt');
}

/* 模块的基本信息 */
if (isset($set_modules) && $set_modules == true)
{
	$i = isset($modules) ? count($modules) : 0;

	/* 代码 */
	$modules[$i]['code'] = basename(__FILE__, '.php');

	/* 描述对应的语言项 */
	$modules[$i]['desc'] = 'weixin_desc';

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

class weixinpay
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
		$this->weixinpay();
	}

    function weixinpay()
    {
    }

	/**
     * 生成支付代码
     * @param   array   $order  订单信息
     * @param   array   $payment    支付方式信息
     */
	function get_code($order, $payment,$type = 'web')
	{
        $button = '<input class="btn-pay cp" type="button" onclick="callpay()" value="去支付" />';

        return $button;
	}

    function get_jspara($order, $payment, $openid)
    {
        $notify_url = 'http://www.wjike.com/pay/weixinRcvPay.php';

        include_once(dirname(__FILE__) . '/weixinpay/WxPay.JsApiPay.php');
        $tools = new JsApiPay();

        $input = new WxPayUnifiedOrder();
        $input->SetBody("万集客商品");
        $input->SetOut_trade_no($order['order_sn']);
        $input->SetTotal_fee($order['order_amount']*100);
        $input->SetTime_start(date("YmdHis"));
        $input->SetTime_expire(date("YmdHis", time() + 600));
        $input->SetNotify_url($notify_url);
        $input->SetTrade_type("JSAPI");
        $input->SetProduct_id($order['order_sn']);
        $input->SetOpenid($openid);
        $order = WxPayApi::unifiedOrder($input);
        $jsApiParameters = $tools->GetJsApiParameters($order);

        return $jsApiParameters;
    }

    function get_Openid()
    {
        include_once(dirname(__FILE__) . '/weixinpay/WxPay.JsApiPay.php');
        $tools = new JsApiPay();
        $openId = $tools->GetOpenid();

        return $openId;
    }

    /**
     *
     * 产生随机字符串，不长于32位
     * @param int $length
     * @return 产生的随机字符串
     */
    public function getNonceStr($length = 32)
    {
        $chars = "abcdefghijklmnopqrstuvwxyz0123456789";
        $str ="";
        for ( $i = 0; $i < $length; $i++ )  {
            $str .= substr($chars, mt_rand(0, strlen($chars)-1), 1);
        }
        return $str;
    }
}
?>
