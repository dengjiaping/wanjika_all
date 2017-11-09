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
class create_jorder
{
    function createJOrder($order, $order_goods, $pay_code, $repush_flag=false)
    {
        $secretkey = 'aa7df029365f42549a0862b020d8d196';
        $timestamp = date('YmdHis',time());
        $nonce = intval(rand(1,9).rand(1,9).rand(1,9).rand(1,9));
        $PayTypeSysNo = 112;//支付方式编号(112:支付宝、115:盛付通、117:银联支付、118:微信支付)
        switch ($pay_code){
            case 'alipay':
                $PayTypeSysNo = 112;
                break;
            case 'weixinpay':
                $PayTypeSysNo = 118;
                break;
        }
        $area_id = get_Jareacode($order['district']);
        $area_name = get_Jareaname($order['order_id']);
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
            'CommissionAmount' => 0.00,//支付手续费
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
            'MerchantOrderID' => $push_ordersn,//商城订单号
            /* 订单支付信息 */
            'PayInfo' => $pay_info,
            /* 订单配送信息 */
            'ShippingInfo' => $shipping_info,
            /* 下单用户实名认证信息 */
            'AuthenticationInfo' => $authentication_info,
            /* 订单中购买商品列表 */
            'ItemList' => $order_goods
        );

        $param = array(
            /* 请求标准参数 */
            'method' => 'SubscribeOrder.Create',
            'version' => '1.0',
            'appid' => 'wjike',
            'format' => 'json',
            'timestamp' => $timestamp,
            'nonce' => $nonce,
            'data' => JJT_JSON($parameter)
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

        $ch = curl_init();
        $url = 'http://www.minspc.com/Api/api';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "$post_str"."sign=$sign");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $data = curl_exec($ch);
        curl_close($ch);
        $rel = (array)json_decode($data);

        if($rel["Code"] == 0 && $rel["Desc"] == "SUCCESS"){
            //更新跨境通任务下单状态为成功
            change_taskstatus($order['order_sn'],CREATE_ORDER_SUCC);
            //更新跨境通任务中跨境通任务信息
            update_kjt_orderno($order['order_sn'],$rel["Code"],$rel["Desc"],$rel["Data"][0]->SOSysNo);
            //临时用billing_desc记录嘉境通订单类型
            change_stf_info($order['order_sn'],0,$rel["Data"][0]->OrderType);
            //更新订单表跨境通返回商品金额，税费和邮费
            update_kjt_info($order['order_id'],0,0,$rel["Data"][0]->ShippingAmount,0,0);
            return true;
        }
        else{
            //更新跨境通任务下单状态为失败
            change_taskstatus($order['order_sn'],CREATE_ORDER_FAL);
            //更新跨境通任务中跨境通任务信息
            update_kjt_orderno($order['order_sn'],$rel["Code"],$rel["Desc"]);
            return false;
        }
    }

    function get_productlist()
    {
        $secretkey = 'aa7df029365f42549a0862b020d8d196';
        $timestamp = date('YmdHis',time());
        $nonce = intval(rand(1,9).rand(1,9).rand(1,9).rand(1,9));

        $parameter = array(
            /* 输入参数 */
            'Status' => '-1'
        );
        $param = array(
            /* 请求标准参数 */
            'method' => 'Subscribe.ProductList',
            'version' => '1.0',
            'appid' => 'wjike',
            'format' => 'json',
            'timestamp' => $timestamp,
            'nonce' => $nonce,
            'data' => JJT_JSON($parameter)
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

        $ch = curl_init();
        $url = 'http://www.minspc.com/Api/api';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "$post_str"."sign=$sign");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $data = curl_exec($ch);
        curl_close($ch);
        $rel = (array)json_decode($data);

        if($rel["Code"] == 0 && $rel["Desc"] == "SUCCESS" && !empty($rel["ProductList"])){
            $r['code'] = "SUCCESS";
            $r['productlist'] = $rel["ProductList"];
        }
        else{
            $r['code'] = "FAILE";
        }

        return $r;
    }

    function delete_order($orderno, $kjt_orderno, $ordertype)
    {
        $secretkey = 'aa7df029365f42549a0862b020d8d196';
        $timestamp = date('YmdHis',time());
        $nonce = intval(rand(1,9).rand(1,9).rand(1,9).rand(1,9));

        $parameter = array(
            /* 输入参数 */
            'OrderIds' => $kjt_orderno,//平台订单号
            'OrderType' => "$ordertype"//订单类型
        );

        $param = array(
            /* 请求标准参数 */
            'method' => 'Order.SOVoid',
            'version' => '1.0',
            'appid' => 'wjike',
            'format' => 'json',
            'timestamp' => $timestamp,
            'nonce' => $nonce,
            'data' => json_encode($parameter)
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

        $ch = curl_init();
        $url = 'http://www.minspc.com/Api/api';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "$post_str"."sign=$sign");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $data = curl_exec($ch);
        curl_close($ch);
        $rel = (array)json_decode($data);

        if($rel["Code"] == 0 && $rel["Data"][0]->status != "-1"){
            change_taskstatus($orderno,DELETE_ORDER_SUCC);
            return true;
        }
        else{
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
function arrayJRecursive(&$array, $function, $apply_to_keys_also = false)
{
    static $recursive_counter = 0;
    if (++$recursive_counter > 1000) {
        die('possible deep recursion attack');
    }
    foreach ($array as $key => $value) {
        if (is_array($value)) {
            arrayJRecursive($array[$key], $function, $apply_to_keys_also);
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
function JJT_JSON($array) {
    arrayJRecursive($array, 'urlencode', true);
    $json = json_encode($array);
    return urldecode($json);
}

function get_Jareacode($city_id)
{
    $sql="SELECT area_id FROM " . $GLOBALS['ecs']->table('region') .  " WHERE region_id=$city_id ";
    $res = $GLOBALS['db']->getRow($sql);

    $r = '';
    if(!empty($res['area_id'])){
        $r = $res['area_id'];
    }

    return  $r;
}

function get_Jareaname($order_id){
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