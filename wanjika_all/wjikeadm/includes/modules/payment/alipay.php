<?php

/**
 * ECSHOP 支付宝插件
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liubo $
 * $Id: alipay.php 17217 2011-01-19 06:29:08Z liubo $
 */

if (!defined('IN_ECS'))
{
    die('Hacking attempt');
}

$payment_lang = ROOT_PATH . 'languages/' .$GLOBALS['_CFG']['lang']. '/payment/alipay.php';

if (file_exists($payment_lang))
{
    global $_LANG;

    include_once($payment_lang);
}

/* 模块的基本信息 */
if (isset($set_modules) && $set_modules == TRUE)
{
    $i = isset($modules) ? count($modules) : 0;

    /* 代码 */
    $modules[$i]['code']    = basename(__FILE__, '.php');

    /* 描述对应的语言项 */
    $modules[$i]['desc']    = 'alipay_desc';

    /* 是否支持货到付款 */
    $modules[$i]['is_cod']  = '0';

    /* 是否支持在线支付 */
    $modules[$i]['is_online']  = '1';

    /* 作者 */
    $modules[$i]['author']  = 'ECSHOP TEAM';

    /* 网址 */
    $modules[$i]['website'] = 'http://www.alipay.com';

    /* 版本号 */
    $modules[$i]['version'] = '1.0.2';

    /* 配置信息 */
    $modules[$i]['config']  = array(
        array('name' => 'alipay_account',           'type' => 'text',   'value' => ''),
        array('name' => 'alipay_key',               'type' => 'text',   'value' => ''),
        array('name' => 'alipay_partner',           'type' => 'text',   'value' => ''),
//        array('name' => 'alipay_real_method',       'type' => 'select', 'value' => '0'),
//        array('name' => 'alipay_virtual_method',    'type' => 'select', 'value' => '0'),
//        array('name' => 'is_instant',               'type' => 'select', 'value' => '0')
        array('name' => 'alipay_pay_method',        'type' => 'select', 'value' => '')
    );

    return;
}

/**
 * 类
 */
class alipay
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
        $this->alipay();
    }

    function alipay()
    {
    }



    function query_timestamp($payment) {
        $url = "https://mapi.alipay.com/gateway.do?service=query_timestamp&partner=".trim(strtolower($payment['alipay_partner']))."&_input_charset=".trim(strtolower('utf-8'));
        $encrypt_key = "";

        $doc = new DOMDocument();
        $doc->load($url);
        $itemEncrypt_key = $doc->getElementsByTagName( "encrypt_key" );
        $encrypt_key = $itemEncrypt_key->item(0)->nodeValue;

        return $encrypt_key;
    }

    function record_log($result ,$risk_level ,$order) {
        $file_name = "/web/pay_log/alipay_security_".date("Ymd").".log";
        $logStr = "\n[".date("Y-m-d H:i:s")."][".$result."][".$risk_level."][".$order['order_sn']."][".$order['order_amount']."][".local_date("Y-m-d H:i:s",$order['add_time'])."]";
        file_put_contents($file_name,$logStr,FILE_APPEND);
    }

    function security_risk($order, $payment ,$charset) {
        $order_amount = number_format($order['order_amount'], 2, '.', '');

        date_default_timezone_set(PRC);
        $timestamp = date("Y-m-d H:i:s");
        $order_createdtime = local_date("Y-m-d H:i:s",$order['add_time']);

        $parameter = array(
            'service'           => 'alipay.security.risk.detect',
            'partner'           => $payment['alipay_partner'],
            '_input_charset'    => $charset,
            'timestamp'    => $timestamp,
            'terminal_type'    => 'WEB',
            'order_no'           => $order['order_sn'],
            'order_credate_time'      => $order_createdtime,
            'order_category'      => $order['goods_category'],
            'order_item_name'        => $order['goods_name'],
            'order_amount'           => $order_amount,
            'scene_code'      => 'PAYMENT',
            'buyer_account_no'             => $order['user_name'],
            'buyer_scene_mobile'             => $order['user_phone'],
            'buyer_scene_email'             => $order['user_email'],
            'buyer_reg_date'          => $order['user_regtime']
        );

        ksort($parameter);
        reset($parameter);

        $param = '';
        $sign  = '';

        foreach ($parameter AS $key => $val)
        {
            $param .= "$key=" .urlencode($val). "&";
            $sign  .= "$key=$val&";
        }

        $param = substr($param, 0, -1);
        $sign  = substr($sign, 0, -1). $payment['alipay_key'];

        $url = 'https://mapi.alipay.com/gateway.do?'.$param. '&sign='.md5($sign).'&sign_type=MD5';

        $info = file_get_contents($url);
        $xmlData = (array)simplexml_load_string($info);

        return $xmlData;
    }

    /**
     * 生成支付代码
     * @param   array   $order      订单信息
     * @param   array   $payment    支付方式信息
     */
    function get_code($order, $payment)
    {
        if (!defined('EC_CHARSET'))
        {
            $charset = 'utf-8';
        }
        else
        {
            $charset = EC_CHARSET;
        }

        $xmlData = $this->security_risk($order, $payment ,$charset);
//        if (empty($payment['is_instant']))
//        {
//            /* 未开通即时到帐 */
//            $service = 'trade_create_by_buyer';
//        }
//        else
//        {
//            if (!empty($order['order_id']))
//            {
//                /* 检查订单是否全部为虚拟商品 */
//                $sql = "SELECT COUNT(*) FROM " .$GLOBALS['ecs']->table('order_goods').
//                        " WHERE is_real=1 AND order_id='$order[order_id]'";
//
//                if ($GLOBALS['db']->getOne($sql) > 0)
//                {
//                    /* 订单中存在实体商品 */
//                    $service =  (!empty($payment['alipay_real_method']) && $payment['alipay_real_method'] == 1) ?
//                        'create_direct_pay_by_user' : 'trade_create_by_buyer';
//                }
//                else
//                {
//                    /* 订单中全部为虚拟商品 */
//                    $service = (!empty($payment['alipay_virtual_method']) && $payment['alipay_virtual_method'] == 1) ?
//                        'create_direct_pay_by_user' : 'create_digital_goods_trade_p';
//                }
//            }
//            else
//            {
//                /* 非订单方式，按照虚拟商品处理 */
//                $service = (!empty($payment['alipay_virtual_method']) && $payment['alipay_virtual_method'] == 1) ?
//                    'create_direct_pay_by_user' : 'create_digital_goods_trade_p';
//            }
//        }

        $real_method = $payment['alipay_pay_method'];

        switch ($real_method){
            case '0':
                $service = 'trade_create_by_buyer';
                break;
            case '1':
                $service = 'create_partner_trade_by_buyer';
                break;
            case '2':
                $service = 'create_direct_pay_by_user';
                break;
        }

        $extend_param = 'isv^sh22';
        $exter_invoke_ip = real_ip();
        $anti_phishing_key = $this->query_timestamp($payment);

        $parameter = array(
            'extend_param'      => $extend_param,
            'service'           => $service,
            'partner'           => $payment['alipay_partner'],
            //'partner'           => ALIPAY_ID,
            '_input_charset'    => $charset,
            'notify_url'        => return_url(basename(__FILE__, '.php')),
            'return_url'        => return_url(basename(__FILE__, '.php')),
            /* 业务参数 */
            'subject'           => $order['order_sn'],
            'out_trade_no'      => $order['order_sn'] . $order['log_id'],
            'price'             => $order['order_amount'],
            'quantity'          => 1,
            'payment_type'      => 1,
            "anti_phishing_key"	=> $anti_phishing_key,
            "exter_invoke_ip"	=> $exter_invoke_ip,
            /* 物流参数 */
            'logistics_type'    => 'EXPRESS',
            'logistics_fee'     => 0,
            'logistics_payment' => 'BUYER_PAY_AFTER_RECEIVE',
            /* 买卖双方信息 */
            'seller_email'      => $payment['alipay_account']
        );

        ksort($parameter);
        reset($parameter);

        $param = '';
        $sign  = '';

        foreach ($parameter AS $key => $val)
        {
            $param .= "$key=" .urlencode($val). "&";
            $sign  .= "$key=$val&";
        }

        $param = substr($param, 0, -1);
        $sign  = substr($sign, 0, -1). $payment['alipay_key'];
        //$sign  = substr($sign, 0, -1). ALIPAY_AUTH;

        $response = (array)$xmlData['response'];
        $risk_level = intval($response['alipay.security.risk.detect']->risk_level);
        $button = '';
        if($xmlData['is_success'] == 'F'){
            $this->record_log($xmlData['is_success'],$risk_level,$order);
            $button = '<div style="text-align:center"><input id="checkF" type="button" onclick="window.open(\'http://www.wjike.com/showmsg.php\')" value="' .$GLOBALS['_LANG']['pay_button']. '" /></div>';
        }
        else{
            if($risk_level >= 8){
                $this->record_log($xmlData['is_success'],$risk_level,$order);
                $button = '<div style="text-align:center"><input id="checkHighLevel" type="button" onclick="window.open(\'http://www.wjike.com/showmsg.php\')" value="' .$GLOBALS['_LANG']['pay_button']. '" /></div>';
            }
            else{
                $button = '<div style="text-align:center"><input type="button" onclick="window.open(\'https://www.alipay.com/cooperate/gateway.do?'.$param. '&sign='.md5($sign).'&sign_type=MD5\')" value="' .$GLOBALS['_LANG']['pay_button']. '" /></div>';
            }
        }

        return $button;
    }

    /**
     * 响应操作
     */
    function respond()
    {
        if (!empty($_POST))
        {
            foreach($_POST as $key => $data)
            {
                $_GET[$key] = $data;
            }
        }
        $payment  = get_payment($_GET['code']);
        $seller_email = rawurldecode($_GET['seller_email']);
        $order_sn = str_replace($_GET['subject'], '', $_GET['out_trade_no']);
        $order_sn = trim($order_sn);

        /* 检查支付的金额是否相符 */
        if (!check_money($order_sn, $_GET['total_fee']))
        {
            return false;
        }

        /* 检查数字签名是否正确 */
        ksort($_GET);
        reset($_GET);

        $sign = '';
        foreach ($_GET AS $key=>$val)
        {
            if ($key != 'sign' && $key != 'sign_type' && $key != 'code')
            {
                $sign .= "$key=$val&";
            }
        }

        $sign = substr($sign, 0, -1) . $payment['alipay_key'];
        //$sign = substr($sign, 0, -1) . ALIPAY_AUTH;
        if (md5($sign) != $_GET['sign'])
        {
            return false;
        }

        if ($_GET['trade_status'] == 'WAIT_SELLER_SEND_GOODS')
        {
            /* 改变订单状态 */
            order_paid($order_sn, 2);

            return true;
        }
        elseif ($_GET['trade_status'] == 'TRADE_FINISHED')
        {
            /* 改变订单状态 */
            order_paid($order_sn);

            return true;
        }
        elseif ($_GET['trade_status'] == 'TRADE_SUCCESS')
        {
            /* 改变订单状态 */
            order_paid($order_sn, 2);

            return true;
        }
        else
        {
            return false;
        }
    }
}

?>