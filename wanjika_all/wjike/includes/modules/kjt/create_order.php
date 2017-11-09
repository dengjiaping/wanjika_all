<?php

/**
 * 跨境通下单接口
 */

if (!defined('IN_ECS'))
{
    die('Hacking attempt');
}
require_once(dirname(__FILE__) . '/../../lib_order.php');

/**
 * 类
 */
class create_order
{
    function createOrder($order, $order_goods, $repush_flag=false)
    {
        $secretkey = '5d4ab3495e44bf4aabd55e199e29e3d';
        $timestamp = date('YmdHis',gmtime());
        $nonce = rand(1,999999);
        $PayTypeSysNo = 115;//支付方式编号(112:支付宝、115:盛付通、117:银联支付、118:微信支付)
//        switch ($pay_code){
//            case 'alipay':
//                $PayTypeSysNo = 112;
//                break;
//            case 'weixinpay':
//                $PayTypeSysNo = 118;
//                break;
//        }
        $area_id = get_areacode($order['district']);
        $area_name = get_areaname($order['order_id']);
        $shipping_id = !empty($order['shipping_id']) ? intval($order['shipping_id']) : 93;
        //支付宝支付流水号由32位改成28位
        if(strlen($order['kjt_pay_tradeno']) == 32){
            $order['kjt_pay_tradeno'] = str_replace('0000','',$order['kjt_pay_tradeno']);
        }
        //特殊商品发快递干线
        $flag = false;
//        if(count($order_goods == 1) && $order_goods[0]['ProductID'] == "788TWC0369s0001"){
//            $flag = true;
//        }

        $pay_info = array(
            'ProductAmount' => number_format($order['goods_amount'], 2, '.', ''),//商品总金额
            'ShippingAmount' => 0.00,//运费总金额 kjt会重新计算，默认传0
            'TaxAmount' => 0.00,//商品行邮税总金额 kjt会重新计算，默认传0
            'CommissionAmount' => number_format($order['pay_fee'], 2, '.', ''),//支付手续费
            'PayTypeSysNo' => $PayTypeSysNo,//支付方式编号
            'PaySerialNumber' => $order['kjt_pay_tradeno'],//支付流水号
        );
        $shipping_info = array(
            'ReceiveName' => $order['consignee'],//收件人姓名
            'ReceivePhone' => $order['tel'],//收件人电话
            'ReceiveAddress' => $order['address'],//收件人收货地址，不包含省市区名称
            'ReceiveAreaCode' => $area_id,//收货地区编号
            'ShipTypeID' => $shipping_id,//订单物流运输公司编号
            'ReceiveAreaName' => $area_name,//收件省市区名称
        );
        if($flag){
            $shipping_info['ShipTypeID'] = '178';
        }
        $authentication_info = array(
            'Name' => $order['consignee'],//下单用户真实姓名
            'IDCardType' => '0',//下单用户证件类型（0 –身份证）
            'IDCardNumber' => $order['id_card'],//下单用户证件编号
            'PhoneNumber' => $order['tel'],//下单用户联系电话
            'Email' => $order['tel'].'@wjike.com',//下单用户电子邮件
        );
        $push_ordersn = $order['order_sn'];
        if($repush_flag){
            $push_ordersn = (string)($order['order_sn']+1);
        }
        $parameter = array(
            /* 输入参数 */
            'SaleChannelSysNo' => 100,//渠道编号
            'MerchantOrderID' => $push_ordersn,//商城订单号
            'WarehouseID' => 51,//仓库编号（51：浦东机场自贸仓）
            /* 订单支付信息 */
            'PayInfo' => $pay_info,
            /* 订单配送信息 */
            'ShippingInfo' => $shipping_info,
            /* 下单用户实名认证信息 */
            'AuthenticationInfo' => $authentication_info,
            /* 订单中购买商品列表 */
            'ItemList' => $order_goods
        );
        if($flag){
            $parameter['ArteryLogisticID'] = 1;
        }

        $param = array(
            /* 请求标准参数 */
            'method' => 'order.socreate',
            'version' => '1.0',
            'appid' => 'seller1470',
            'format' => 'json',
            'timestamp' => $timestamp,
            'nonce' => $nonce,
            'data' => JSON($parameter)
        );

        ksort($param);
        reset($param);

        $param_str = '';
        $post_str = '';

        foreach ($param AS $key => $val)
        {
            $param_str .= "$key=" .urlencode($val). "&";
            $post_str .= "$key=$val&";
        }

        $sign_str = $param_str.$secretkey;
        $sign = md5($sign_str);
//        $sign = bin2hex($sign);

        $ch = curl_init();
        //$testurl = 'http://preapi.kjt.com/open.api';
        $url = 'http://api.kjt.com/open.api';
        curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_POSTFIELDS, "$post_str&sign=$sign");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $data = curl_exec($ch);
        curl_close($ch);
        $rel = (array)json_decode($data);
        if($rel["Code"] == 0 && $rel["Desc"] == "SUCCESS"){
//            if(!empty($order['kjt_pay_tradeno'])){
//                //支付流水号更新为成功使用
//                pay_tradeno_used($order['kjt_pay_tradeno'],$order['order_sn'],1);
//            }
            //更新跨境通任务下单状态为成功
            change_taskstatus($order['order_sn'],CREATE_ORDER_SUCC);
            //更新跨境通任务中跨境通任务信息
            update_kjt_orderno($order['order_sn'],$rel["Code"],$rel["Desc"],$rel["Data"]->SOSysNo);
            //更新订单表跨境通返回商品金额，税费和邮费
            update_kjt_info($order['order_id'],$rel["Data"]->ProductAmount,$rel["Data"]->TaxAmount,$rel["Data"]->ShippingAmount,$rel["Data"]->ShippingTax,$rel["Data"]->PayDeclareAmount);
            return true;
        }
        else{
//            if(!empty($order['kjt_pay_tradeno'])){
//                //支付流水号更新为使用失败
//                pay_tradeno_used($order['kjt_pay_tradeno'],$order['order_sn'],0);
//            }
            //更新跨境通任务下单状态为失败
            change_taskstatus($order['order_sn'],CREATE_ORDER_FAL);
            //更新跨境通任务中跨境通任务信息
            update_kjt_orderno($order['order_sn'],$rel["Code"],$rel["Desc"]);
            return false;
        }
    }

    function get_productinfo($product_id)
    {
        $list = array();
        array_push($list,$product_id);
        $secretkey = '25d4ab3495e44bf4aabd55e199e29e3d';
        $timestamp = date('YmdHis',gmtime());
        $nonce = rand(1,999999);

        $parameter = array(
            /* 输入参数 */
            'ProductIDs' => $list,
            'SaleChannelSysNo' => 100
        );
        $param = array(
            /* 请求标准参数 */
            'method' => 'product.proudctinfobatchget',
            'version' => '1.0',
            'appid' => 'seller1470',
            'format' => 'json',
            'timestamp' => $timestamp,
            'nonce' => $nonce,
            'data' => JSON($parameter)
        );

        ksort($param);
        reset($param);

        $param_str = '';
        $post_str = '';

        foreach ($param AS $key => $val)
        {
            $param_str .= "$key=" .urlencode($val). "&";
            $post_str .= "$key=$val&";
        }

        $sign_str = $param_str.$secretkey;
        $sign = md5($sign_str);
//        $sign = bin2hex($sign);
        $ch = curl_init();
        $testurl = 'http://preapi.kjt.com/open.api';
        $url = 'http://api.kjt.com/open.api';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "$post_str&sign=$sign");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $data = curl_exec($ch);
        curl_close($ch);
        $rel = (array)json_decode($data);

        $price = $rel["Data"]->ProductList[0]->Price;
        $tariffrate = $rel["Data"]->ProductList[0]->ProductEntryInfo->TariffRate;
        if($rel["Code"] == 0 && $rel["Desc"] == "SUCCESS" && !empty($price) && !empty($tariffrate)){
            $r['code'] = "SUCCESS";
            $r['price'] = $price;
            $r['tariffrate'] = $tariffrate;
        }
        else{
            $r['code'] = "FAILE";
        }

        return $r;
    }

    function payDeclareNotice($orderno, $kjt_orderno, $tradeno, $paytime, $kjt_declare_amount)
    {
        $secretkey = '25d4ab3495e44bf4aabd55e199e29e3d';
        $timestamp = date('YmdHis',gmtime());
        $paytime = date('Y-m-d H:i:s',$paytime);
        $nonce = rand(1,999999);

        $list = array(
            'SOSysNo' => $kjt_orderno,//跨境通订单号
            'PayTransNumber' => $tradeno,//支付流水号
            'PayTime' => $paytime,//支付时间
            'PayTypeSysNo' => 115,//支付方式编号115:盛付通
            'CustomsAmount' => $kjt_declare_amount//支付申报金额
        );

        $param = array(
            /* 请求标准参数 */
            'method' => 'Order.AcquireSOPayDeclare',
            'version' => '1.0',
            'appid' => 'seller1470',
            'format' => 'json',
            'timestamp' => $timestamp,
            'nonce' => $nonce,
            'data' => json_encode(array($list))
        );

        ksort($param);
        reset($param);

        $param_str = '';
        $post_str = '';

        foreach ($param AS $key => $val)
        {
            $param_str .= "$key=" .urlencode($val). "&";
            $post_str .= "$key=$val&";
        }

        $sign_str = $param_str.$secretkey;
        $sign = md5($sign_str);
//        $sign = bin2hex($sign);
        $ch = curl_init();
        //$testurl = 'http://preapi.kjt.com/open.api';
        $url = 'http://api.kjt.com/open.api';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "$post_str&sign=$sign");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $data = curl_exec($ch);
        curl_close($ch);
        $rel = (array)json_decode($data);

        if($rel["Code"] == 0){
            //更新跨境通订单支付申报上报任务状态
            change_stf_info($orderno,DECLARE_NOTICE_SUCC);

            return true;
        }
        else{
            //更新跨境通订单支付申报上报任务状态
            change_stf_info($orderno,DECLARE_NOTICE_FAL, $rel["Desc"]."(details:".$rel["Data"][0]->Desc.")");

            return false;
        }
    }
}
/**************************************************************
 *
 *  使用特定function对数组中所有元素做处理
 *  @param  string  &$array     要处理的字符串
 *  @param  string  $function   要执行的函数
 *  @return boolean $apply_to_keys_also     是否也应用到key上
 *  @access public
 *
 *************************************************************/
function arrayRecursive(&$array, $function, $apply_to_keys_also = false)
{
    static $recursive_counter = 0;
    if (++$recursive_counter > 1000) {
        die('possible deep recursion attack');
    }
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            arrayRecursive($array[$key], $function, $apply_to_keys_also);
        } else {
            $array[$key] = $function($value);
        }

        if ($apply_to_keys_also && is_string($key)) {
            $new_key = $function($key);
            if ($new_key != $key) {
                $array[$new_key] = $array[$key];
                unset($array[$key]);
            }
        }
    }
    $recursive_counter--;
}

/**************************************************************
 *
 *  将数组转换为JSON字符串（兼容中文）
 *  @param  array   $array      要转换的数组
 *  @return string      转换得到的json字符串
 *  @access public
 *
 *************************************************************/
function JSON($array) {
    arrayRecursive($array, 'urlencode', true);
    $json = json_encode($array);
    return urldecode($json);
}

function get_areacode($city_id)
{
    $sql="SELECT area_id FROM " . $GLOBALS['ecs']->table('region') .  " WHERE region_id=$city_id ";
    $res = $GLOBALS['db']->getRow($sql);

    $r = '';
    if(!empty($res['area_id'])){
        $r = $res['area_id'];
    }

    return  $r;
}

function get_areaname($order_id){
    /* 取得区域名 */
    $sql = "SELECT concat(IFNULL(p.area_name, ''), " .
        "',', IFNULL(t.area_name, ''), ',', IFNULL(d.area_name, '')) AS region " .
        "FROM " . $GLOBALS['ecs']->table('order_info') . " AS o " .
        "LEFT JOIN " . $GLOBALS['ecs']->table('region') . " AS p ON o.province = p.region_id " .
        "LEFT JOIN " . $GLOBALS['ecs']->table('region') . " AS t ON o.city = t.region_id " .
        "LEFT JOIN " . $GLOBALS['ecs']->table('region') . " AS d ON o.district = d.region_id " .
        "WHERE o.order_id = '$order_id'";
    $region = $GLOBALS['db']->getOne($sql);

    return $region;
}
?>