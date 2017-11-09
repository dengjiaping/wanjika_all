<?php

/**
 * 易极付支付插件
 */

if (!defined('IN_ECS'))
{
	die('Hacking attempt');
}

//引入语言包
$payment_lang = ROOT_PATH . 'languages/' . $GLOBALS['_CFG']['lang'] . '/payment/yijipay.php';
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
	$modules[$i]['desc'] = 'yijipay_desc';

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
	array('name' => 'partnerId', 'type' => 'text', 'value' => ''),
	array('name' => 'security_key',     'type' => 'text', 'value' => ''),
	);

	return;

}

class yijipay
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
		$this->yijipay();
	}

    function yijipay()
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
        $partnerId = '';
        $security_key = '';
        //获取配置信息中的商户号和商户安全校验码
        $pay_config = $GLOBALS['db']->getOne("SELECT pay_config FROM " . $GLOBALS['ecs']->table('payment') . " WHERE pay_code = 'yijipay' AND enabled = 1");
        $pay_config = unserialize($pay_config);
        foreach($pay_config as $v)
        {
            if ($v['name'] == 'partnerId')
            {
                $partnerId = $v['value'];
            }
            if ($v['name'] == 'security_key')
            {
                $security_key = $v['value'];
            }
        }
        $params = array();

        $params['protocol'] = 'httpPost';
        $params['service'] = "qftIntegratedPayment";
        $params['version'] = "1.0";
        $params['partnerId'] = $partnerId;
        $params['orderNo'] = $order['order_sn'] . time();
        $params['signType'] = 'MD5';
        $params['returnUrl'] = 'http://www.wjike.com/user.php?act=order_list';
        $params['notifyUrl'] = 'http://www.wjike.com/pay/yijiRcvPay.php';
        // 业务参数
        //每个服务的业务参数是不同的,具体请参考文档的请求参数
        $params["outOrderNo"] = $order['order_sn'];
        $params["tradeChannel"] = "GATEWAY";
        $params["money"] = $order['order_amount'];
        $params["moneyReal"] = $order['order_amount'];
        $params["outPayeeShopId"] = "wjk000000001";
        $params["payeeUserId"] = $partnerId;
//        $params["origin"] = $type == 'web' ? 'PC' : 'MOBILE';
//        if($params["origin"] == 'MOBILE'){
//            $params["outPayerShopId"] = '13245679801234567890';
//            $params["payerUserId"] = '13245679801234567890';
//        }
        $params["goodList"] = array();
        $order_goods = order_goods($order['order_id']);
        foreach($order_goods as $val){
            $goods_names = array(
                'title' => $val['goods_name']
            );
            array_push($params["goodList"],$goods_names);
        }
        $params["goodList"] = json_encode($params["goodList"]);
        $signStr = self::digest($params, $security_key, 'MD5');

        $def_url  = '<form name="yijipay" style="text-align:center;" method="post"'.
            'action="https://api.yiji.com">';
        foreach ($params AS $key => $val)
        {
            $def_url .= "<input type='hidden' name='" . $key . "' value='" . $val . "' />";
        }
        $def_url .= "<input type='hidden' name='sign' value='" . $signStr . "' />";
        $def_url .= "</form><script>document.forms['yijipay'].submit();</script>";

        return $def_url;
	}

    /**
     * 查询接口
     */
    function single_query($order_sn)
    {
        $partnerId = '';
        $security_key = '';
        //获取配置信息中的商户号和商户安全校验码
        $pay_config = $GLOBALS['db']->getOne("SELECT pay_config FROM " . $GLOBALS['ecs']->table('payment') . " WHERE pay_code = 'yijipay' AND enabled = 1");
        $pay_config = unserialize($pay_config);
        foreach($pay_config as $v)
        {
            if ($v['name'] == 'partnerId')
            {
                $partnerId = $v['value'];
            }
            if ($v['name'] == 'security_key')
            {
                $security_key = $v['value'];
            }
        }
        $params = array();

        $params['service'] = "qftTradeSingleQuery";
        $params['partnerId'] = $partnerId;
        $params['orderNo'] = $order_sn . time();
        $params['signType'] = 'MD5';
        $params['returnUrl'] = 'http://www.wjike.com/user.php?act=order_list';
        $params['notifyUrl'] = 'http://www.wjike.com/pay/yijiRcvPay.php';
        // 业务参数
        //每个服务的业务参数是不同的,具体请参考文档的请求参数
        $params["outOrderNo"] = $order_sn;

        $signStr = self::digest($params, $security_key, 'MD5');

        $postStr = self::buildPairs($params,$signStr);
        $result = self::doCurlPost($params, $postStr);

        return $result;
    }

    function digest(array $dataMap, $securityCheckKey, $digestAlg){
        if(is_null($dataMap)){
            throw new Exception("数据不能为空");
        }
        if(empty($dataMap)){
            return null;
        }
        if(is_null($securityCheckKey)){
            throw new Exception("安全检验码数据不能为空");
        }
        if(empty($digestAlg)){
            throw new Exception("摘要算法不能为空");
        }
        $digestStr = "";
        //需要对data map 进行a~z,A~Z排序
        ksort($dataMap);
        foreach($dataMap as $key=>$value){
            if(is_null($value)){
                throw new Exception($key + " 待签名值不能为空");
            }
            if($key  === "sign"){
                continue;
            }
            $digestStr .= $key."=".$value.'&';
        }
        $digestStr = trim($digestStr, '&');
        $digestStr .= $securityCheckKey;
        if($digestAlg === "MD5"){
            $digestStrMd5 = md5($digestStr);
        }else{
            throw new Exception("暂不支持此加密方式: " + $digestAlg);
        }

        return $digestStrMd5;
    }

    /**
     * 将参数组装成post字符串
     * @param array $param
     * @param parameters
     * @return string
     */
    function buildPairs(array $param, $sign){
        $postStr = "";
        foreach ($param as $key => $value) {
            $postStr .= $key . '=' . urlencode(mb_convert_encoding($value, "utf-8", "auto")) . '&';
        }
        $postStr .= 'sign='.$sign;
        return $postStr;
    }

    /**
     * 通curl发送post请求
     * @param array $param
     * @param $postStr
     * @return mixed
     */
    function doCurlPost(array $param, $postStr){
        $curl = curl_init();
        curl_setopt($curl, CURLOPT_URL, "https://api.yiji.com");
        curl_setopt($curl, CURLOPT_CONNECTTIMEOUT, 7); //Timeout after 7 seconds
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1); //Return data instead printing directly in Browser
        curl_setopt($curl, CURLOPT_USERAGENT, "php demo");
        curl_setopt($curl, CURLOPT_HTTPHEADER, array('Content-Type: application/x-www-form-urlencoded; charset=UTF-8','Connection: Keep-Alive'));
        curl_setopt($curl, CURLOPT_POST, count($param) + 1); //number of parameters sent
        curl_setopt($curl, CURLOPT_POSTFIELDS, $postStr); //parameters data
        $result = curl_exec($curl);
        curl_close($curl);
        return $result;
    }
}

?>
