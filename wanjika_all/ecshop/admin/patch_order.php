<?php
define('IN_ECS', true);

require('/web/ecshop/data/config.php');
require('/web/ecshop/includes/cls_ecshop.php');
require('/web/ecshop/includes/lib_order.php');
require('/web/ecshop/includes/cls_mysql.php');
require('/web/ecshop/includes/lib_clips.php');
require('/web/ecshop/includes/inc_constant.php');

$db = new cls_mysql($db_host, $db_user, $db_pass, $db_name);
$ecs = new ECS($db_name, $prefix);

$gdSql = "SELECT id,mobile,adress,goods_id,goods_num,status FROM " . $ecs->table('patch_order') . " where status=0";
echo date("Y-m-d H:i:s") . "\nstart:";
$gdRes = $db->getAll($gdSql);
while (list($key, $val) = each($gdRes))
{
	//插入收货人地址
 	$mobile = $val['mobile'];
	$user_id = getUserID($mobile);
	$adress = $val['adress'];
	$goods_id = $val['goods_id'];
	$goods_num = $val['goods_num'];
	echo "\n[$mobile][$user_id][$adress][$goods_id][$goods_num]";
	
	if (!$user_id)
	{
		continue;
	}
	
	$adress_id = insertUserAdress($user_id, $mobile, $adress);
	if ($adress_id)
	{
		$res_up = updateUser($user_id, $adress_id);
	}
	
	$orderid = insertOrderInfo($user_id, $mobile, $adresss, $goods_id, $goods_num);
	$res_order_goods = insertOrderGoods($orderid, $user_id, $mobile, $goods_id);
	
	$goods_amount = getGoodsAmount($goods_id,$goods_num);
	$pay_fee = getPayFee($goods_amount);
	$order_amount = $goods_amount+$pay_fee;
	
	$log_id = insert_pay_log($orderid, $order_amount, PAY_ORDER);
	//$post_data_res = postDataToUM();
	if ($res_order_goods && $orderid ) {
		updatePatchOrder($val['id']);
		
		//发送通知短信
		send_confirm_message($orderid, $mobile, $order_amount, $log_id);
		
		echo "[succ]";
	}
	else
	{
		echo "[false]";
	}
}


/*echo $gdSql = "SELECT * FROM " . $ecs->table('goods') ." where goods_id=1";
$res = $db->getAll($gdSql);
print_r($res);*/

/**
 * 发送确认短信
 * 
 * @param $orderid
 * @param $mobile
 * @param $order_amount
 * @param $log_id
 */
function send_confirm_message($orderid, $mobile, $order_amount, $log_id)
{
	$remote_server = "http://payment.umpay.com/hfwebbusi/pay/page.do";

	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $remote_server);
	$postData = get_post_params($orderid, $mobile, $order_amount, $log_id);
	curl_setopt($ch, CURLOPT_POSTFIELDS, $postData);
	curl_setopt($ch, CURLOPT_POST, true); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/umpaycookie.txt');
	$data = curl_exec($ch);  
	curl_close($ch); 
	
	$remote_server = "http://payment.umpay.com/hfwebbusi/pay/saveOrder.do";
	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $remote_server);
	curl_setopt($ch, CURLOPT_POSTFIELDS, "mobileId=$mobile");
	curl_setopt($ch, CURLOPT_POST, true); 
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/umpaycookie.txt');
	$data = curl_exec($ch);  
	curl_close($ch); 
}

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

function  getPayFee($goods_amount)
{
	global $db;
	//手续费=商品总额*比率/(1-比率)
	if ($goods_amount) {
		return $goods_amount*0.125/(1-0.125);
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
	$shop_price = $res[0]['shop_price'];
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
	$shipping_id = 3;
	$shipping_name = '申通快递';
	$pay_id = 3;
	$pay_name = '移动话费支付';
	$how_oos = '等待所有商品备齐后再发';
	$card_message = '';
	$inv_payee = '';
	$inv_content = '';
	$goods_amount = getGoodsAmount($goods_id,$goods_num);
	$shipping_fee = 0;
	$insure_fee = 0;
	$pay_fee = getPayFee($goods_amount);
	$pack_fee = 0;
	$card_fee = 0;
	$surplus = 0;
	$integral = 0;
	$integral_money = 0;
	$bonus = 0;
	$order_amount = $goods_amount+$pay_fee;
	$from_ad = 0;
	$refer = '本站';
	$add_time = time();
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
 VALUES ('".$order_sn."','". $user_id."','". $order_status."','". $shipping_status."','". $pay_status."','". $consignee."','". $country."','". $province."','". $city."','". $district."','". $address."','". $zipcode."','". $tel."','". $mobile."','". $email."','". $best_time."','". $sign_building."','". $postscript."','". $shipping_id."','". $shipping_name."','". $pay_id."','". $pay_name."','". $how_oos."','". $card_message."','". $inv_payee."','". $inv_content."','". $goods_amount."','". $shipping_fee."','". $insure_fee."','". $pay_fee."','". $pack_fee."','". $card_fee."','". $surplus."','". $integral."','". $integral_money."','". $bonus."','". $order_amount."','". $from_ad."','". $referer."','". $add_time."','". $pack_id."','". $card_id."','". $bonus_id."','". $extension_code."','". $extension_id."','". $agency_id."','". $inv_type."','". $tax."','". $parent_id."','". $discount."')";
	$res = $db->query($sql);
	return $db->insert_id();
}

function get_post_params($orderId, $mobile, $order_amount, $log_id)
{
	/*print_r($order);
	print_r($payment);
	exit;*/
    $merInfo = get_merid_and_primary_key($mobile);
	$priv_key_file = $_SERVER['DOCUMENT_ROOT']."/includes/" . $merInfo['primary_key'];

	//商户号
	$merId = $merInfo['merId'];
	//商品号
	$goodsId = '001';//需确认是联动商品id还是万集客商品id
	//商品信息
	$goodsInf = "wjike";
	//手机号
	if (preg_match("/1\d{10}/",$mobile)) {
		$mobileId = $mobile;
	}
	else {
		$mobileId = $mobile;
	}

	//商户下单日期
	$merDate = date("Ymd");//格式：yyyyMMdd
	//金额
	$amount = $order_amount*100;//订单金额，单位分
	//金额类型
	$amtType = '02';//定值02 代表话费
	//银行类型
	$bankType = 3;//定值3，话费
	//网银Id
	$gateId = '';//默认空
	//返回商户URL
	$retUrl = 'http://www.wjike.com/respond.php';//?act=order_list';//返回地址设空
	//支付通知URL
	//		$notifyUrl = $GLOBALS['ecs']->url() . 'respond.php?code=umpay&log_id='.$order['log_id'];
	$notifyUrl = 'http://www.wjike.com/notify.php';//?code=umpay&log_id='.$order['log_id'];
	//商户私有信息
	$merArr = array("code"=>"umpay","log_id"=>$log_id);
	$merPriv = base64_encode(serialize($merArr));
	//扩展信息
	$expand = 'mer';
	//版本号
	$version = '3.0';


	/* 生成加密签名串 请务必按照如下顺序和规则组成加密串！*/
	$paramNew="";
	$paramNew =$paramNew . "merId=" . trim($merId,"\x00..\x1F");//商户号
	$paramNew =$paramNew . "&goodsId=" . trim($goodsId,"\x00..\x1F");//商品号
	if(!empty($goodsInf)){
		$paramNew =$paramNew . "&goodsInf=" .trim($goodsInf,"\x00..\x1F");//商品信息
	}
	if(!empty($mobileId)){
		$paramNew =$paramNew . "&mobileId=" . trim($mobileId,"\x00..\x1F");
	}
	$paramNew =$paramNew . "&orderId=" . trim($orderId,"\x00..\x1F");//订单号
	$paramNew =$paramNew . "&merDate=" . trim($merDate,"\x00..\x1F");//商户日期
	$paramNew =$paramNew . "&amount=" . trim($amount,"\x00..\x1F");//商品金额
	$paramNew =$paramNew . "&amtType=" . trim($amtType,"\x00..\x1F");//货币类型 定值02
	if(!empty($bankType)){
		$paramNew =$paramNew . "&bankType=" . trim($bankType,"\x00..\x1F");//银行类型 定值3
	}
	//		if(!empty($gateId)){
	$paramNew =$paramNew . "&gateId=" . trim($gateId,"\x00..\x1F");//通道号
	//		}
	$paramNew =$paramNew . "&retUrl=" . trim($retUrl,"\x00..\x1F");//返回页面地址
	if(!empty($notifyUrl)){
		$paramNew =$paramNew . "&notifyUrl=" . trim($notifyUrl,"\x00..\x1F");//后台通知地址
	}
	if(!empty($merPriv)){
		$paramNew =$paramNew . "&merPriv=" . trim($merPriv,"\x00..\x1F");//商户私有信息
	}
	if(!empty($expand)){
		$paramNew =$paramNew . "&expand=" . trim($expand,"\x00..\x1F");//商户扩展信息
	}
	$paramNew =$paramNew . "&version=" . trim($version,"\x00..\x1F");//版本信息 定值3.0
	$priv_key_file = "/web/ecshop/includes/" . $merInfo['primary_key'];
	include_once('/web/ecshop/includes/modules/payment/umpay.php');
	$umpay = new umpay();
	$pemSignNew = $umpay->ssl_sign($paramNew,$priv_key_file);

	$def_url  = '';
	$def_url .= "merId=$merId";
	$def_url .= "&goodsId=$goodsId";
	$def_url .= "&goodsInf=$goodsInf";
	$def_url .= "&mobileId=$mobileId";
	$def_url .= "&orderId=$orderId";
	$def_url .= "&merDate=$merDate";
	$def_url .= "&amount=$amount";
	$def_url .= "&amtType=$amtType";
	$def_url .= "&bankType=$bankType";
	$def_url .= "&gateId=$gateId";
	$def_url .= "&retUrl=$retUrl";
	$def_url .= "&notifyUrl=$notifyUrl";
	$def_url .= "&merPriv=$merPriv";
	$def_url .= "&expand=$expand";
	$def_url .= "&version=$version";
	$def_url .= "&sign=$pemSignNew";
	$def_url .= "&sign=$paramNew";
	
	return $def_url;
}
