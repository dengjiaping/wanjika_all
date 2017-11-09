<?php
/**
 * Created by PhpStorm.
 * User: qihua
 * Date: 14-6-17
 * Time: 下午4:26
 */

define('IN_ECS', true);
//require('D:/workspace/ecshop/wanjike/includes/init.php');
//require('/web/ecshop/includes/init.php');
require(dirname(__FILE__) . '/includes/init.php');
require(ROOT_PATH . 'includes/lib_payment.php');
require(ROOT_PATH . 'includes/lib_order.php');
require(ROOT_PATH . 'includes/lib_clips.php');
require(ROOT_PATH . 'includes/lib_transaction.php');
require(ROOT_PATH . 'includes/modules/payment/umpay.php');

$md5_sign_key = 'wDU6KEtBRJ8qhGQOLi9wyNIqpDfD7v';

$goods_id = 3414;
$mobile = $_REQUEST['mobile'];
//订单金额，单位：分
$amount = abs(intval($_REQUEST['amount']));
$user_name = mysql_escape_string(trim($_REQUEST['merid']));
$order_id = trim($_REQUEST['order_id']);
$sign = trim($_REQUEST['sign']);

//将分转化成元
$amount_yuan = round($amount / 100);

$result = array();

//检验参数合法性&&小于1毛的就不要了
if (empty($user_name) || empty($sign) || empty($amount) || empty($mobile) || $amount_yuan < 1)
{
    $result['result'] = 'fail';
    $result['msg'] = '参数不完整';
    echo json_encode($result);
    exit;
}

$sql = "SELECT user_id, user_name, password, ec_salt FROM ecs_users WHERE user_name= '$user_name'";
$user_info = $GLOBALS['db']->getRow($sql);
if (empty($user_info))
{
    $result['result'] = 'fail';
    $result['msg'] = '商户信息错误';
    echo json_encode($result);
    exit;
}

//验证签名合法性
$sign_str = "amount=$amount&md5_key=$md5_sign_key&merid=$user_name&mobile=$mobile&order_id=$order_id";
$md5_sign_str = md5($sign_str);
if ($md5_sign_str != $sign)
{
    $result['result'] = 'fail';
    $result['msg'] = '签名错误';
    echo json_encode($result);
    exit;
}

$user_id = $user_info['user_id'];
//生成订单
$new_order_id = insertOrderInfo($user_id, $mobile, $amount_yuan);
$log_id = insert_pay_log($new_order_id, $amount, PAY_ORDER);
insertOrderGoods($new_order_id, $goods_id, $amount_yuan * 10);

//记录信息
$add_time = gmtime();
$sql = "INSERT INTO " . $ecs->table('pay_code_info') . "(
    `user_id`, `mobile`, `amount`, `order_id`, `wjk_order_id`, `add_time`)   VALUES
    ('{$user_info['user_id']}', '$mobile', '$amount_yuan', '$order_id', '$new_order_id', '$add_time')
    ";
$db->query($sql);

//去话费支付
$pay_res =  umpay_pay_fee($new_order_id, $mobile, $amount, $log_id, $user_name, $order_id, $md5_sign_key);

function umpay_pay_fee($new_order_id, $mobile, $amount, $log_id, $user_name, $order_id, $md5_sign_key)
{
    $unpay_obj = new umpay();
    $merInfo = get_merid_and_primary_key($mobile);

    //商户号
    $merId = $merInfo['merId'];
    //商品号
    $goodsId = '001';//需确认是联动商品id还是万集客商品id
    //商品信息
    $goodsInf = "wjike";
    //手机号
    $mobileId = $mobile;
    //订单号
    $orderId = $new_order_id;
    //商户下单日期
    $merDate = date("Ymd");//格式：yyyyMMdd
    //金额
    //$amount = $amount;//
    //金额类型
    $amtType = '02';//定值02 代表话费
    //银行类型
    $bankType = 3;//定值3，话费
    //网银Id
    $gateId = '';//默认空
    //返回商户URL
    $retUrl = 'http://www.wjike.com/respond.php';//?act=order_list';//返回地址设空
    //支付通知URL
    $notifyUrl = "http://www.wjike.com/notify.php";
    //商户私有信息
    $merArr = array("code" => "umpay","log_id" => $log_id);
    $merPriv = base64_encode(serialize($merArr));
    //扩展信息
    $expand = 'mer';
    //版本号
    $version = '3.0';


    /* 生成加密签名串 请务必按照如下顺序和规则组成加密串！*/
    $paramNew="";
    $paramNew =$paramNew . "merId=" . trim($merId,"\x00..\x1F");//商户号
    $paramNew =$paramNew . "&goodsId=" . trim($goodsId,"\x00..\x1F");//商品号
    if(!empty($goodsInf))
    {
        $paramNew =$paramNew . "&goodsInf=" .trim($goodsInf,"\x00..\x1F");//商品信息
    }
    if(!empty($mobileId))
    {
        $paramNew =$paramNew . "&mobileId=" . trim($mobileId,"\x00..\x1F");
    }
    $paramNew =$paramNew . "&orderId=" . trim($orderId,"\x00..\x1F");//订单号
    $paramNew =$paramNew . "&merDate=" . trim($merDate,"\x00..\x1F");//商户日期
    //单位：元转换成单位：分
    $paramNew =$paramNew . "&amount=" . trim($amount,"\x00..\x1F");//商品金额
    $paramNew =$paramNew . "&amtType=" . trim($amtType,"\x00..\x1F");//货币类型 定值02
    if(!empty($bankType))
    {
        $paramNew =$paramNew . "&bankType=" . trim($bankType,"\x00..\x1F");//银行类型 定值3
    }
    $paramNew =$paramNew . "&gateId=" . trim($gateId,"\x00..\x1F");//通道号
    $paramNew =$paramNew . "&retUrl=" . trim($retUrl,"\x00..\x1F");//返回页面地址
    if(!empty($notifyUrl))
    {
        $paramNew =$paramNew . "&notifyUrl=" . trim($notifyUrl,"\x00..\x1F");//后台通知地址
    }
    if(!empty($merPriv))
    {
        $paramNew =$paramNew . "&merPriv=" . trim($merPriv,"\x00..\x1F");//商户私有信息
    }
    if(!empty($expand))
    {
        $paramNew =$paramNew . "&expand=" . trim($expand,"\x00..\x1F");//商户扩展信息
    }
    $paramNew =$paramNew . "&version=" . trim($version,"\x00..\x1F");//版本信息 定值3.0
    $priv_key_file = $_SERVER['DOCUMENT_ROOT']."/includes/" . $merInfo['primary_key'];
    $pemSignNew = $unpay_obj->ssl_sign($paramNew, $priv_key_file);
    $pemSignNew = mb_convert_encoding($pemSignNew,"GBK");

    $file_name = "/web/pay_log/pay_fee_".date("Ymd").".log";
    $logStr = "[".$goodsId."][".$goodsInf."][".$mobileId."][".$orderId."][".$merDate."][".$amount."][".$amtType."][".$bankType."][".$gateId."][".$retUrl."][".$notifyUrl."][".$merPriv."][".$expand."][".$version."][".$pemSignNew."][".$paramNew."]";
    file_put_contents($file_name, $logStr, FILE_APPEND);

    //发请求
    $remote_server  = "http://payment.umpay.com/hfwebbusi/pay/page.do";
    $pemSignNew = urlencode($pemSignNew);
    $post_data = $paramNew.'&sign='.$pemSignNew;
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $remote_server);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $post_data);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/umpaycookie.txt');
    $res = curl_exec($ch);
    curl_close($ch);

    //发短信
    $remote_server = "http://payment.umpay.com/hfwebbusi/pay/saveOrder.do";
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $remote_server);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "mobileId=$mobile");
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/umpaycookie.txt');
    $data = curl_exec($ch);
    curl_close($ch);

    return $res;
}

function insertOrderGoods($order_id, $goods_id, $goods_number)
{
    global $db;

    $sql = "select goods_name, goods_sn,  market_price,shop_price,  is_real, extension_code from ecs_goods where goods_id='" . $goods_id . "'";
    $res = $db->getAll($sql);
    $goods_name = $res[0]['goods_name'];
    $goods_sn = $res[0]['goods_sn'];
    $market_price = $res[0]['market_price'];
    $goods_price = $res[0]['shop_price'];
    $is_real = $res[0]['is_real'];
    $extension_code = $res[0]['extension_code'];
    $product_id = 0;
    $goods_attr = 'default';
    $parent_id = '';
    $is_gift = 0;
    $goods_attr_id  = 0;
    $sql = "INSERT INTO `ecs_order_goods`( order_id, goods_id, goods_name, goods_sn, product_id, goods_number, market_price, goods_price, goods_attr, is_real, extension_code, parent_id, is_gift, goods_attr_id) values ('".$order_id."','". $goods_id."','". $goods_name."','". $goods_sn."','". $product_id."','". $goods_number."','". $market_price."','". $goods_price."','". $goods_attr."','". $is_real."','". $extension_code."','". $parent_id."','". $is_gift."','". $goods_attr_id."')";
    $res = $db->query($sql);

    return $db->insert_id();
}

function insertOrderInfo($user_id, $mobile, $amount)
{
    global $db;

    $order_sn = get_order_sn();
    $order_status = OS_UNCONFIRMED;
    $shipping_status = SS_UNSHIPPED;
    $pay_status = PS_UNPAYED;
    $confirm_time = gmtime();
    $pay_time = gmtime();
    $consignee = $mobile;
    $country = '1';
    $province = '2';
    $city = '52';
    $district = '';
    $zipcode = '';
    $tel = $mobile;
    $email = $mobile . "@139.com";
    $best_time = '';
    $sign_building = '';
    $postscript = '';
    $shipping_id = -1;
    $shipping_name = '';
    $pay_id = 2;
    $pay_name = '余额支付';
    $how_oos = '等待所有商品备齐后再发';
    $card_message = '';
    $inv_payee = '';
    $inv_content = '';
    $goods_amount = $amount;
    $shipping_fee = 0;
    $insure_fee = 0;
    $pay_fee = 0;
    $pack_fee = 0;
    $card_fee = 0;
    $surplus = 0;
    $integral = 0;
    $integral_money = 0;
    $bonus = 0;
    $order_amount = $goods_amount + $pay_fee;
    //订单金额四舍五入到毛
    $order_amount = number_format($order_amount, 1, '.', '');
    $from_ad = 0;
    $referers = 'MPF';
    $add_time = gmtime();
    $shipping_time = $add_time;
    $pack_id = 0;
    $card_id = 0;
    $bonus_id = 0;
    $extension_code = '';
    $agency_id = 0;
    $inv_type = 0;
    $tax = '';
    $parent_id = 0;
    $discount = '';
    $extension_id = '';

    $sql = "INSERT INTO `ecs_order_info`
        (order_sn, user_id, order_status, shipping_status, pay_status, consignee, country, province, city,
        district, address, zipcode, tel, mobile, email, best_time, sign_building, postscript, shipping_id,
        shipping_name, pay_id, pay_name, how_oos, card_message, inv_payee, inv_content, goods_amount,
        shipping_fee, insure_fee, pay_fee, pack_fee, card_fee, surplus, integral, integral_money,
        bonus, order_amount, from_ad, referer, add_time, confirm_time, pay_time, shipping_time, pack_id, card_id, bonus_id, extension_code,
        extension_id, agency_id, inv_type, tax, parent_id, discount)
        VALUES
        ('$order_sn','$user_id','$order_status','$shipping_status','$pay_status','$consignee','$country','$province','$city',
        '$district','','$zipcode','$tel','$mobile','$email','$best_time','$sign_building','$postscript','$shipping_id',
        '$shipping_name','$pay_id','$pay_name','$how_oos','$card_message','$inv_payee','$inv_content','$goods_amount',
        '$shipping_fee','$insure_fee','$pay_fee','$pack_fee','$card_fee','$surplus','$integral','$integral_money',
        '$bonus','$order_amount','$from_ad','$referers','$add_time', '$confirm_time', '$pay_time', '$shipping_time', '$pack_id','$card_id','$bonus_id','$extension_code',
        '$extension_id','$agency_id','$inv_type','$tax','$parent_id','$discount')";
    $db->query($sql);
    $order_id = $db->insert_id();

    return $order_id;
}

