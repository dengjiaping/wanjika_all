<?php

/**
 * 盛付通即时到账转账/代扣接口
 */

if (!defined('IN_ECS'))
{
    die('Hacking attempt');
}
require_once(dirname(__FILE__) . '/../../lib_order.php');

/**
 * 类
 */
class sft_transfer
{
    function Billing($order,$client, $repush_flag=false)
    {
        header("Content-type: text/html; charset=UTF-8");//设置提交服务器字符编码
        date_default_timezone_set('PRC');//设置默认时区
        $key = "Wjike2016888";//密钥
        $orderId = $order['order_sn'];

        $payer=array(//付款人
            'MemberId'=>"10110545@sfb.mer",//盛大通行证或钱包账户
            'MemberIdType'=>"PtId"//MemberId的类型，枚举：通行证或账户（限定的类型值为：PtId,AccountId,memberId）
        );
        $toPayer=array(//收款方
            'MemberId'=>"10110563@sfb.mer",//盛大通行证或钱包账户
            'MemberIdType'=>"PtId"//MemberId的类型，枚举：通行证或账户（限定的类型值为：PtId,AccountId,memberId）
        );
        $kvpList=array(//说明：如果为外币转账或者跨境商户转账，需要在ReqBody的扩展字段中传入实名信息，字段分别是：realName, idNo, mobile，invokeIp
            array("Key"=>"invokeIp","Value"=>real_ip()),
            array("Key"=>"idNo","Value"=>$order['id_card']),
            array("Key"=>"realName","Value"=>$order['consignee']),
            array("Key"=>"mobile","Value"=>$order['tel'])
        );
        $push_ordersn = $orderId;
        if($repush_flag){
            $push_ordersn = (string)($orderId+1);
        }
        $reqBody=array(
            'Amount'=>sprintf("%.2f", $order['kjt_declare_amount']),//金额
            'Currency'=>"Rmb",//货币类型：枚举（限定的值为：Rmb,CNY-人民币;USD-美元;GBP-英镑;HKD-港币;SGD-新加坡元;JPY-日元;CAD-加拿大元;AUD-澳元;EUR-欧元;CHF-瑞士法郎）
            'MerchantOrderId'=>$push_ordersn,//订单号.唯一
            'ProductDesc'=>$push_ordersn,//产品备注,如不为空，会做为钱包帐户明细备注
            "Payer"=>$payer,
            "ToPayer"=>$toPayer,
            "Ext"=>$kvpList
        );

        $reqTrans= array(
            'Version'=>"1.0",//接口版本号,传1.0
            'InterfaceType'=>"Billing",//接口类型：Transfer-转账 Billing-代扣
            'AppId'=>"",//接口编号（保留字段，默认不用填写）
            'MerchantNo'=>"10110563",//商户号
            'ReqBody'=>$reqBody,
            'MachineName'=>"",
            'Summary'=>"",//摘要信息
            'SignType'=>"2",//签名类型 1-rsa 2-md5
            'Mac'=>""
        );
        //		待签名串：
        //		signStr= Version + | + InterfaceType + | + MerchantNo + | + AppId + | + ReqBody.Amount(2位小数，四舍五入) +
        //     | + ReqBody.Currency(全大写) + | + ReqBody.MerchantOrderId + | + ReqBody.Payer.MemberId + | + ReqBody.Payer.MemberIdType(全大写) +
        //     | + ReqBody.ToPayer.MemberId + | + ReqBody.ToPayer.MemberIdType(全大写) + | + MachineName + | + Summary。
        $signMessage=$reqTrans["Version"]."|".$reqTrans["InterfaceType"]."|".$reqTrans["MerchantNo"]."|".$reqTrans["AppId"]."|".$reqBody["Amount"]."|"
            .strtoupper($reqBody["Currency"])."|".$reqBody["MerchantOrderId"]."|".$payer["MemberId"]."|".strtoupper($payer["MemberIdType"])
            ."|".$toPayer["MemberId"]."|".strtoupper($toPayer["MemberIdType"])."|".$reqTrans["MachineName"]."|".$reqTrans["Summary"].$key;
        $sign= mb_convert_encoding($signMessage, 'gbk', 'utf-8');
        $reqTrans["Mac"]= strtoupper(md5($sign));

        $respTrans="";

        $transResponse= $client->__soapCall('Transfer', array(array('request'=>$reqTrans)),
            array('location' => 'http://mas.shengpay.com/api-acquire-channel/services/trans'));
        $respTrans= $transResponse->TransferResult;

        $rel = (array)$respTrans;

        if($rel["Code"] == 0 && $rel["Message"] == "success"){
            //更新盛付通代扣任务状态
            change_stf_info($order['order_sn'],BILLING_SUCC, $rel["Message"]);
            //更新订单表盛付通代扣支付流水号
            update_sft_tradeno($order['order_id'],$rel["RespBody"]->SerialNo);
            //更新订单表盛付通代扣支付时间
            update_sft_paytime($order['order_sn'],strtotime($rel["RespBody"]->PayTime));

            return $rel["RespBody"]->SerialNo;
        }
        else{
            //更新盛付通代扣任务状态
            change_stf_info($order['order_sn'],BILLING_FAL, $rel["Message"]);
            if(abs($rel["Code"]) == 1204701117){
                delete_order($order['order_sn'],get_kjt_orderno($order['order_sn']));
            }

            return false;
        }
    }

    function payDeclare($order, $pay_tradeno, $repush_flag=false)
    {
        $push_ordersn = $order['order_sn'];
        if($repush_flag){
            $push_ordersn = (string)($order['order_sn']+1);
        }
        $param = array(
            /* 请求标准参数 */
            'requestNo' => "563".$push_ordersn,
            'customsType' => 'SHANGHAI',
            'businessMode' => 'BONDED',
            'merchantOrderNo' => $push_ordersn,
            'payOrderNo' => $pay_tradeno,
            'orderAmount' => number_format($order['kjt_declare_amount'], 2, '.', ''),
            'paymentAmount' => number_format($order['kjt_declare_amount'], 2, '.', ''),
            'expressFee' => number_format($order['kjt_shipping_amount'], 2, '.', ''),
            'tax' => number_format($order['kjt_tax_amount'], 2, '.', ''),
            'merchantNo' => '10110563',
            'companyCustomsCode' => '312264002S',
            'companyCustomsName' => '上海跨境通国际贸易有限公司'
        );

        $post_str = '';

        foreach ($param AS $key => $val)
        {
            $post_str .= "$key=$val&";
        }
        $post_str = substr($post_str, 0, -1);

        $ch = curl_init();
        $url = 'http://global.shengpay.com/fexchange-customs/rest/submitApply';
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "$post_str");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $data = curl_exec($ch);
        curl_close($ch);
        $rel = (array)json_decode($data);

        if($rel["status"] == 2){
            change_stf_info($order['order_sn'],DECLARE_FAL);

            return false;
        }
        else{
            change_stf_info($order['order_sn'],DECLARE_SUCC, $rel["receiptNo"]);

            return true;
        }
    }
}

function delete_order($orderno, $kjt_orderno)
{
    $secretkey = '25d4ab3495e44bf4aabd55e199e29e3d';
    $nonce = rand(1,999999);
    $timestamp = date('YmdHis',gmtime());
    $list = array();
    array_push($list,intval($kjt_orderno));

    $parameter = array(
        /* 输入参数 */
        'OrderIds' => $list,//跨境通订单号
        'SalesChannelSysNo' => 100//渠道编号
    );

    $param = array(
        /* 请求标准参数 */
        'method' => 'Order.SOVoid',
        'version' => '1.0',
        'appid' => 'seller1470',
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
        change_taskstatus($orderno,DELETE_ORDER_SUCC);
        return true;
    }
    else{
        return false;
    }
}
?>