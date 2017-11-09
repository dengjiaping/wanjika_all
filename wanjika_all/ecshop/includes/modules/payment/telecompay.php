<?php

if (!defined('IN_ECS'))
{
    define('IN_ECS', true);
}
include_once(dirname(__FILE__) . '/../../../includes/lib_base.php');
$payment_lang = ROOT_PATH . 'languages/' .$GLOBALS['_CFG']['lang']. '/payment/telecompay.php';

if (file_exists($payment_lang))
{
    global $_LANG;

    include_once($payment_lang);
}

if (isset($_REQUEST['step']) && $_REQUEST['step'] == 'telecom_pay')
{
    $order_sn = empty($_REQUEST['order_sn']) ? '' : trim($_REQUEST['order_sn']);
    $order_req = empty($_REQUEST['order_req']) ? '' : trim($_REQUEST['order_req']);
    $tel = empty($_REQUEST['tel']) ? '' : trim($_REQUEST['tel']);
    $amount = empty($_REQUEST['amount']) ? '' : trim($_REQUEST['amount']);
    $addtime = empty($_REQUEST['addtime']) ? '' : trim($_REQUEST['addtime']);
    $goods_name = empty($_REQUEST['goods_name']) ? '' : trim($_REQUEST['goods_name']);
    $goods_number = empty($_REQUEST['goods_number']) ? '' : trim($_REQUEST['goods_number']);
    $vcode = empty($_REQUEST['vcode']) ? '' : trim($_REQUEST['vcode']);
    $userip = real_ip();

    if (!defined('EC_CHARSET'))
    {
        $charset = 'utf-8';
    }
    else
    {
        $charset = EC_CHARSET;
    }

    if(strlen($goods_name) > 20){
        $goods_name = '万集客商品';
    }
    if(strlen($order_sn) < 10){
        $order_sn = $order_sn.$addtime;
    }
    if(strlen($order_req) < 10){
        $order_req = $order_req.$addtime;
    }

    $mac_str = "MERCHANTID=02110108040613000&MERCHANTPWD=179599&ORDERSEQ=$order_sn&ORDERREQTRANSEQ=$order_req&ORDERREQTIME=$addtime&ORDERAMOUNT=$amount&USERACCOUNT=80406130&USERIP=$userip&PHONENUM=$tel&GOODPAYTYPE=0&GOODSCODE=$charset&GOODSNUM=$goods_number&KEY=9E7BDBE099C7D1DA2C9F93D1A4E366584072FD586540C2BB";
    $mac = strtoupper(md5($mac_str));

    $parameter = array(
            'MERCHANTID'      => '02110108040613000',
            'SUBMERCHANTID'           => '',
            'MERCHANTPWD'           => '179599',
            'MERCHANTPHONE'           => '4000851115',
            'ORDERSEQ'           => $order_sn,
            'ORDERREQTRANSEQ'      => $order_req,
            'ORDERAMOUNT'             => $amount,
            'ORDERREQTIME'          => $addtime,
            'USERACCOUNT'      => '80406130',
            "USERIP"	=> $userip,
            'PHONENUM'    => $tel,
            'VERIFYCODE'     => $vcode,
            'GOODPAYTYPE' => 1,
            'GOODSCODE'    => $charset,
            'GOODSNAME'        => $goods_name,
            'GOODSNUM'        => $goods_number,
            'ORDERDESC'        => '万集客商品',
            'ATTACH'        => '',
            'BACKMERCHANTURL'        => 'http://www.wjike.com/pay/telecomRcvPay.php',
            'MAC'      => $mac
    );

    $param = '';

    foreach ($parameter AS $key => $val)
    {
        $param .= "$key=" .urlencode($val). "&";
    }

    $param = substr($param, 0, -1);

    $post_url = 'https://webpaywg.bestpay.com.cn/backBillPay.do';

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $post_url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $param);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $data = curl_exec($ch);
    curl_close($ch);


    if($data === '00'){
        ecs_header("Location: /respond.php?paycode=telecompay&total_fee=$amount&order_sn=$order_sn&order_req=$order_req\n");
    }
    else{
        record_log($order_sn,$tel,'telecom_pay');
        ecs_header("Location: /respond.php\n");
    }

}

if (isset($_REQUEST['step']) && $_REQUEST['step'] == 'get_vcode')
{
    $order_sn = empty($_REQUEST['order_sn']) ? '' : trim($_REQUEST['order_sn']);
    $order_req = empty($_REQUEST['order_req']) ? '' : trim($_REQUEST['order_req']);
    $tel = empty($_REQUEST['tel']) ? '' : trim($_REQUEST['tel']);

    $post_url = 'https://webpaywg.bestpay.com.cn/verifyCode.do';
    $mac_str = "MERCHANTID=02110108040613000&ORDERSEQ=$order_sn&ORDERREQTRANSEQ=$order_req&TELEPHONE=$tel&KEY=9E7BDBE099C7D1DA2C9F93D1A4E366584072FD586540C2BB";
    $mac = strtoupper(md5($mac_str));

    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $post_url);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "ORDERSEQ=$order_sn&ORDERREQTRANSEQ=$order_req&TELEPHONE=$tel&MERCHANTID=02110108040613000&FUNCTIONTYPE=1&MAC=$mac");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
    $data = curl_exec($ch);
    curl_close($ch);
    if($data !== '00'){
        record_log($order_sn,$tel,'get_vcode');
    }
}

function record_log($order_sn,$tel,$msg) {
    $file_name = "/web/pay_log/telecompay_getvcode_".date("Ymd").".log";
    $logStr = "\n[".date("Y-m-d H:i:s")."][".$order_sn."][".$tel."][".$msg."]";
    file_put_contents($file_name,$logStr,FILE_APPEND);
}

/* 模块的基本信息 */
if (isset($set_modules) && $set_modules == TRUE)
{
    $i = isset($modules) ? count($modules) : 0;

    /* 代码 */
    $modules[$i]['code']    = basename(__FILE__, '.php');

    /* 描述对应的语言项 */
    $modules[$i]['desc']    = 'telecompay_desc';

    /* 是否支持货到付款 */
    $modules[$i]['is_cod']  = '0';

    /* 是否支持在线支付 */
    $modules[$i]['is_online']  = '1';

    /* 作者 */
    $modules[$i]['author']  = 'MIAO';

    /* 网址 */
    $modules[$i]['website'] = '';

    /* 版本号 */
    $modules[$i]['version'] = '1.0.1';

    /* 配置信息 */
    $modules[$i]['config']  = array(
        array('name' => 'telecompay_partner', 'type' => 'text', 'value' => ''),
    );

    return;
}

/**
 * 类
 */
class telecompay
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
        $this->telecompay();
    }

    function telecompay()
    {
    }

    /**
     * 生成支付代码
     * @param   array   $order      订单信息
     * @param   array   $payment    支付方式信息
     */
    function get_code($order, $payment)
    {
        $userip = real_ip();
        $order_sn = $order['order_sn'];
        $order_req = $order['order_sn'] . $order['log_id'];
        $addtime = local_date("YmdHis",$order['add_time']);
        $overtime = local_date("Y-m-d H:i:s",$order['add_time'] + 86400);
        $amount = $order['order_amount']*100;

        if (!defined('EC_CHARSET'))
        {
            $charset = 'utf-8';
        }
        else
        {
            $charset = EC_CHARSET;
        }

        if(strlen($order['goods_name']) > 20){
            $order['goods_name'] = '万集客商品';
        }
        if(strlen($order_sn) < 10){
            $order_sn = $order_sn.$addtime;
        }
        if(strlen($order_req) < 10){
            $order_req = $order_req.$addtime;
        }

        $mac_str = "MERCHANTID=02110108040613000&ORDERSEQ=$order_sn&ORDERDATE=$addtime&ORDERAMOUNT=$amount&CLIENTIP=$userip&KEY=9E7BDBE099C7D1DA2C9F93D1A4E366584072FD586540C2BB";
        $mac = strtoupper(md5($mac_str));
        $parameter = array(
            'MERCHANTID'      => '02110108040613000',
            'SUBMERCHANTID'           => '',
            'ORDERSEQ'           => $order_sn,
            'ORDERREQTRANSEQ'      => $order_req,
            'ORDERDATE'          => $addtime,
            'ORDERAMOUNT'             => $amount,
            'PRODUCTAMOUNT'             => $amount,
            'ATTACHAMOUNT'             => 0,
            'CURTYPE'           => 'RMB',
            'ENCODETYPE'           => 1,
            'MERCHANTURL'        => 'http://www.wjike.com/respond.php',
            'BACKMERCHANTURL'        => 'http://www.wjike.com/pay/telecomRcvPay.php',
            'ATTACH'        => '',
            'BUSICODE'        => '0001',
            'PRODUCTID'        => '08',
            'TMNUM'        => $order['user_phone'],
            'CUSTOMERID'        => $order['user_name'],
            'PRODUCTDESC'        => '万集客商品',
            'MAC'      => $mac,
            'DIVDETAILS'      => '',
            'GMTOVERTIME'      => $overtime,
            'GOODPAYTYPE' => 0,
            'GOODSCODE'    => $charset,
            'GOODSNAME'        => $order['goods_name'],
            'GOODSNUM'        => $order['goods_number'],
            "CLIENTIP"	=> $userip

        );

        $param = '';

        foreach ($parameter AS $key => $val)
        {
            $param .= "$key=" .urlencode($val). "&";
        }

        $param = substr($param, 0, -1);

        $button = '<div style="text-align:center"><input type="button" onclick="window.open(\'https://webpaywg.bestpay.com.cn/payWeb.do?'.$param.'\')" value="' .$GLOBALS['_LANG']['pay_button']. '" /></div>';
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
        $order_sn = str_replace($_GET['ORDERSEQ'], '', $_GET['ORDERREQTRANSEQ']);
        $total_fee = $_GET['ORDERAMOUNT'] / 100;

        /* 检查支付的金额是否相符 */
        if (!check_money($order_sn, $total_fee))
        {
            return false;
        }

        //order_paid($order_sn);

        return true;
    }
}

?>