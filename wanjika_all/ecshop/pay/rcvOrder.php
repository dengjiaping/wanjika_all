<?php
define('IN_ECS', true);
//==============test=====================
//$_REQUEST['merId'] ="7145";
//$_REQUEST['goodsId'] =937;
//$_REQUEST['goodsInf'] ="1#596";//商品数量
//$_REQUEST['mobileId'] ='13581753069';
//$_REQUEST['amtType'] ="02";//定值
//$_REQUEST['bankType'] ="3";//定值
//$_REQUEST['version'] ="3.0";//3.0定值
//$_REQUEST['sign'] ="Grn3L5AppupGFPfGcxpT3O8Sm5rGRCl/1gM+OHz4iAcjE9oN3PSS6UhjGGI8ICkEDNqw/NMA7FXW40T1mN1qrblebxQnXk59f9fkVDyD3/mQNrl9223DoF36ey3DS8BDccsKU3zURCRtdVLgkZrQWEa9kHkZfN7xcJFrAgADirg=";
//require('D:/workspace/wjike/ecshop/includes/init.php');
//==============test=================
//test内容为测试虚拟数据，调试时请屏蔽test之间的内容
define('IN_ECS', true);
require('/web/ecshop/includes/init.php');
require(ROOT_PATH . 'includes/lib_payment.php');
require(ROOT_PATH . 'includes/lib_order.php');
require(ROOT_PATH . 'includes/lib_clips.php');
require(ROOT_PATH . 'includes/modules/payment/umpay.php');
//记录日志，用来排查问题
$file_name = "/web/pay_log/mobOrder_".date("Ymd").".log";
$logStr = "\n[".date("Y-m-d H:i:s")."][".$_REQUEST['merId']."][".$_REQUEST['goodsId']."][".$_REQUEST['goodsInf']."][".$_REQUEST['mobileId']."][".$_REQUEST['amtType']."][".$_REQUEST['bankType']."][".$_REQUEST['version']."][".$_REQUEST['sign']."]";
file_put_contents($file_name,$logStr,FILE_APPEND);

/**
 * 参数校验：1.sign验签
 * 2.校验mobile是否有对应userid，即是否注册用户
 * 3.验证商品编号和商品数量，商品编号必须是实际存在的商品，商品数量需大于1
 * 4.生成订单，如果订单生成失败，则反馈相关信息
 */

$signStr = "merId=".$_REQUEST['merId']."&goodsId=".$_REQUEST['goodsId']."&goodsInf=".$_REQUEST['goodsInf']."&mobileId=".$_REQUEST['mobileId']."&amtType=".$_REQUEST['amtType']."&bankType=".$_REQUEST['bankType']."&version=".$_REQUEST['version'];

$umpay = new umpay();

$certfile = "/web/ecshop/includes/modules/payment/cert_2d59.cert.pem";
$result=$umpay->ssl_verify($signStr,$_REQUEST['sign'],$certfile);
//$result = true;
if(!$result)
{
	//商户在此处做相关的验签失败的处理，如果失败说明有不正常的客户端在访问支付结果通知
	//验签失败后，返回码必然是不成功的
	$sing_result = "验签失败,签名原文:"+url+"签名数据:"+sign+"<br/>";
	$retMsg="验签失败,电话4006125880";
	$retCode="1111";
	exit;
}
//$nsign = $umpay->ssl_sign($signStr,"/web/ecshop/includes/modules/payment/7145_WanJiKe.key.pem");
//if ($nsign!=$_REQUEST['sign']) {
//	echo "验签失败";
//exit;
//}

$mobile = $_REQUEST['mobileId'];
$user_id = getUserID($mobile);
//$goods_id = $_REQUEST['goodsId'] ;
//$goods_num = $_REQUEST['goodsInf'];//购买商品数量


$tmpInfo = explode('#',$_REQUEST['goodsInf']);
$goods_id = $tmpInfo[1] ;
$goods_num = 1;//购买商品数量

$order_id = 0;
if (isset($tmpInfo[2]) && !empty($tmpInfo[2]))
{
    $pid = intval($tmpInfo[2]);
    $sql = "SELECT user_id, mobile, amount, order_id FROM " . $GLOBALS['ecs']->table('pay_code_info') . " WHERE pid='$pid'";
    $pinfo = $GLOBALS['db']->getRow($sql);
    if ($mobile != $pinfo['mobile'])
    {
        echo "手机号不正确";
        exit;
    }

    $goods_num = $pinfo['amount'];

    $user_id = $pinfo['user_id'];
    $order_id = $pinfo['order_id'];
}

//注释于20130826 by qihua 要实现匿名用户可以下单的功能
//if (!$user_id) {
//	//不存在的用户
//	echo "用户不存在";
//	exit;
//}

$payment_id = 3;
$goodsAmount = getGoodsAmount($goods_id,$goods_num);
$goodsAmount += pay_fee($payment_id, $goodsAmount, 0, $mobile);
$goodsAmount = round($goodsAmount, 1);

if ($goods_num<1) {
	echo "商品数量不合法";
	exit;
}

//是否合法商品
if (!$goodsAmount) {
	echo "商品不合法";
	exit;
}

$sql = "select goods_number, is_on_sale from " . $GLOBALS['ecs']->table('goods') . " where goods_id='".$goods_id."'";
$res = $GLOBALS['db']->getRow($sql);
if (intval($GLOBALS['_CFG']['use_storage']) > 0 && $res['goods_number'] < $goods_num)
{
		echo "商品已经售完！";
		exit;
}

if ($res['is_on_sale'] == 0)
{
	echo '商品已下架！';
	exit;
}

$adress = '';
$res_order_info = insertOrderInfo($user_id,$mobile,$adresss,$goods_id,$goods_num);
if (!$res_order_info) {
	echo "生成订单失败";
	exit;
}
$res_order_goods = insertOrderGoods($res_order_info,$user_id,$mobile,$goods_id);

if (isset($tmpInfo[2]) && !empty($tmpInfo[2]))
{
    $pid = intval($tmpInfo[2]);
    $sql = "UPDATE " . $GLOBALS['ecs']->table('pay_code_info') . " SET wjk_order_id='$res_order_info' WHERE pid='$pid'";
    $GLOBALS['db']->query($sql);
}

$log_id = get_paylog_id($res_order_info, PAY_ORDER);
if (!$log_id) {
	echo "订单异常";
	exit;
}

/* 如果使用库存，且下订单时减库存，则减少库存 */
if (intval($GLOBALS['_CFG']['use_storage']) == 1 && $GLOBALS['_CFG']['stock_dec_time'] == SDT_PLACE)
{
	change_order_goods_storage($res_order_info, true, SDT_PLACE);
}


$merArr = array("code"=>"umpay","log_id"=>$log_id);
$merPriv = base64_encode(serialize($merArr));

$para_goodsAmount = intval($goodsAmount * 100);

$merInfo = get_merid_and_primary_key($mobile);
$resSignStr = "{$merInfo['merId']}|".$_REQUEST['goodsId']."|".$res_order_info."|".date("Ymd")."|".$para_goodsAmount."|http://www.wjike.com/notify.php|".$merPriv."||0000|sucessful|3.0";
$resSign = $umpay->ssl_sign($resSignStr,'/web/ecshop/includes/modules/payment/' . $merInfo['primary_key']);
echo '<META NAME="MobilePayPlatform" CONTENT ="'.$resSignStr.'|'.$resSign.'">';


function updateUser($user_id,$adress_id)
{
	global $db;
	$sql = "UPDATE  `ecs_users` SET address_id = '".$adress_id."' WHERE user_id = ".$user_id;
	$res = $db->query($sql);
	return $res;
}

function updatePatchOrder($id)
{
	global $db;
	$sql = "UPDATE  `ecs_patch_order` SET status = '1' WHERE id = ".$id;
	$res = $db->query($sql);
	return $res;
}

function getUserID($mobile)
{
	global $db,$ecs;
	$sql = "SELECT user_id FROM " . $ecs->table('users')." where mobile_phone='".$mobile."' order by user_id desc limit 1";
	$res = $db->getAll($sql);
	if ($res) {
		return $res[0]['user_id'];
	}
	return false;

}

function getGoodsAmount($goods_id,$goods_num)
{
	global $db,$ecs;
	if (!$goods_id || $goods_num<0) {
		return 0;
	}
	$sql = "SELECT goods_id,shop_price FROM " . $ecs->table('goods')." where goods_id='".$goods_id."'";
	$res = $db->getAll($sql);
	if ($res) {
		return $res[0]['shop_price']*$goods_num;
	}
	return false;

}

function  getPayFee($goods_amount, $payment_id)
{
	$pay_fee = pay_fee($payment_id, 1);
	//手续费=商品总额*比率/(1-比率)
	if ($goods_amount) {
		return $goods_amount * $pay_fee / (1 - $pay_fee);
	}
	return 0;
}

function insertUserAdress($user_id,$mobile,$adress)
{
	global $db;
	//获取用户userid
	$adress = $adress;
	$mobile = $mobile;
	//email
	$email = $mobile."@139.com";
	//收货人姓名 mobile
	$consignee = $mobile;
	$country = "中国";
	$province = "北京";
	$city = "北京";
	$district = '';
	$zipcode = 100000;
	$tel = $mobile;
	$sign_building = '';
	$best_time = "";

	$sql = "INSERT INTO `ecs_user_address`
( user_id, consignee, email, country, province, city, district, address, zipcode, tel, mobile, sign_building, best_time) VALUES ('".$user_id."','".$consignee."','".$email."','".$country."','".$province."','".$city."','".$district."','".$adress."','".$zipcode."','".$tel."','".$mobile."','".$sign_building."','".$best_time."')";
	$res = $db->query($sql);
	return $db->insert_id();
}


function insertOrderGoods($order_id,$user_id,$mobile,$goods_id)
{
	global $db;
	//获取用户userid
	$order_id = $order_id;
	$goods_id = $goods_id;

	$sql = "select goods_name, goods_sn,  market_price,shop_price,  is_real, extension_code from ecs_goods where goods_id='".$goods_id."'";
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
	$goods_number = 1;
	$is_gift = 0;
	$goods_attr_id  = 0;
	$sql = "INSERT INTO `ecs_order_goods`( order_id, goods_id, goods_name, goods_sn, product_id, goods_number, market_price, goods_price, goods_attr, is_real, extension_code, parent_id, is_gift, goods_attr_id) values ('".$order_id."','". $goods_id."','". $goods_name."','". $goods_sn."','". $product_id."','". $goods_number."','". $market_price."','". $goods_price."','". $goods_attr."','". $is_real."','". $extension_code."','". $parent_id."','". $is_gift."','". $goods_attr_id."')";
	$res = $db->query($sql);
	return $db->insert_id();
}


function insertOrderInfo($user_id,$mobile,$adress,$goods_id,$goods_num)
{
	global $db;
	$user_id = $user_id;
	$order_sn = get_order_sn();
	$order_status = 0;
	$shipping_status = 0;
	$pay_status = 0;
	$consignee = $mobile;
	$country = '1';
	$province = '2';
	$city = '52';
	$district = '';
	$adress = $adress;
	$zipcode = '';
	$tel = $mobile;
	$mobile = $mobile;
	$email = $mobile."@139.com";
	$best_time = '';
	$sign_building = '';
	$postscript = '';
	$sql = "select goods_name, goods_sn,  market_price,shop_price,  is_real, extension_code from ecs_goods where goods_id='".$goods_id."'";
	$res = $db->getAll($sql);
	$is_real = $res[0]['is_real'];
	if ($is_real) {
		$shipping_id = 3;
		$shipping_name = '申通快递';
	}
	else {
		$shipping_id = -1;
		$shipping_name = '';
	}

	$pay_id = 3;
	$pay_name = '移动话费支付';
	$how_oos = '等待所有商品备齐后再发';
	$card_message = '';
	$inv_payee = '';
	$inv_content = '';
	$goods_amount = getGoodsAmount($goods_id,$goods_num);
	$shipping_fee = 0;
	$insure_fee = 0;
	$pay_fee = pay_fee($pay_id, $goods_amount, 0, $mobile);
	$pack_fee = 0;
	$card_fee = 0;
	$surplus = 0;
	$integral = 0;
	$integral_money = 0;
	$bonus = 0;
	$order_amount = $goods_amount+$pay_fee;
    //订单金额四舍五入到毛
    $order_amount = number_format($order_amount, 1, '.', '');
	$from_ad = 0;
	$referers = '短信';
	$add_time = gmtime();
	$pack_id = 0;
	$card_id = 0;
	$bonus_id = 0;
	$extension_code = '';
	$agency_id = 0;
	$inv_type = 0;
	$tax = '';
	$parent_id = 0;
	$discount = '';
	
		
	$sql = "INSERT INTO `ecs_order_info`
(order_sn, user_id, order_status, shipping_status, pay_status, consignee, country, province, city, district, address, zipcode, tel, mobile, email, best_time, sign_building, postscript, shipping_id, shipping_name, pay_id, pay_name, how_oos, card_message, inv_payee, inv_content, goods_amount, shipping_fee, insure_fee, pay_fee, pack_fee, card_fee, surplus, integral, integral_money, bonus, order_amount, from_ad, referer, add_time, pack_id, card_id, bonus_id, extension_code, extension_id, agency_id, inv_type, tax, parent_id, discount)
 VALUES ('".$order_sn."','". $user_id."','". $order_status."','". $shipping_status."','". $pay_status."','". $consignee."','". $country."','". $province."','". $city."','". $district."','". $address."','". $zipcode."','". $tel."','". $mobile."','". $email."','". $best_time."','". $sign_building."','". $postscript."','". $shipping_id."','". $shipping_name."','". $pay_id."','". $pay_name."','". $how_oos."','". $card_message."','". $inv_payee."','". $inv_content."','". $goods_amount."','". $shipping_fee."','". $insure_fee."','". $pay_fee."','". $pack_fee."','". $card_fee."','". $surplus."','". $integral."','". $integral_money."','". $bonus."','". $order_amount."','". $from_ad."','". $referers."','". $add_time."','". $pack_id."','". $card_id."','". $bonus_id."','". $extension_code."','". $extension_id."','". $agency_id."','". $inv_type."','". $tax."','". $parent_id."','". $discount."')";
	$res = $db->query($sql);
	$order_id = $db->insert_id();
	insert_pay_log($order_id, $order_amount, PAY_ORDER);
	return $order_id;
}
?>
