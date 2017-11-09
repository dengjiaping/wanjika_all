<?php
define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require(ROOT_PATH . 'includes/lib_order.php');

/* 载入语言文件 */
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/user.php');
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/shopping_flow.php');

include_once('includes/lib_clips.php');
include_once('includes/lib_payment.php');

$arr = array('result' => '','msg' => '');
if(empty($_REQUEST['id']))
{
    $arr['result'] = 'failed';
    $arr['msg'] = '请输入ID';
    echo json_encode($arr);
    exit;
}

if(empty($_REQUEST['pay_id']))
{
    $arr['result'] = 'failed';
    $arr['msg'] = '请输入支付ID';
    echo json_encode($arr);
    exit;
}
if(empty($_REQUEST['adm']))
{
    $arr['result'] = 'failed';
    $arr['msg'] = '请输入帐号';
    echo json_encode($arr);
    exit;
}

if(empty($_REQUEST['pw']))
{
    $arr['result'] = 'failed';
    $arr['msg'] = '请输入密码';
    echo json_encode($arr);
    exit;
}

$user_name = mysql_escape_string($_REQUEST['adm']);
$sql = "select user_id, user_name,password, ec_salt ".
    "FROM  ". $GLOBALS['ecs']->table('users') .
    " WHERE user_name='$user_name'";
$user = $db->getRow($sql);
if(empty($user))
{
    $arr['result'] = 'failed';
    $arr['msg'] = '用户名不存在';
    echo json_encode($arr);
    exit;
}
if(empty($user['ec_salt']))
{
    $password = md5($_REQUEST['pw']);
}
else
{
    $password = md5(md5($_REQUEST['pw']).$user['ec_salt']);
}

if($password !== $user['password'])
{
    $arr['result'] = 'failed';
    $arr['msg'] = '密码不正确';
    echo json_encode($arr);
    exit;
}
$pay_id = $_REQUEST['pay_id'];
if($pay_id == 3)
{
    $pay_name = '移动话费支付';
}
elseif($pay_id == 6)
{
    $pay_name = '手机支付';
}
else
{
    $arr['result'] = 'failed';
    $arr['msg'] = '请填写正确支付ID';
    echo json_encode($arr);
    exit;
}

/* 取得商品信息 */
$goods_id = intval($_REQUEST['id']);
$sql = "select * FROM  ".
    $GLOBALS['ecs']->table('goods') .
    " WHERE goods_id='$goods_id'";
$goods = $db->getRow($sql);
if(empty($goods))
{
    $arr['result'] = 'failed';
    $arr['msg'] = '商品不存在';
    echo json_encode($arr);
    exit;
}
/* 取默认地址 */
$user_id = $user['user_id'];
$sql = "select * FROM  ".
    $GLOBALS['ecs']->table('user_address') .
    " WHERE user_id='$user_id'";
$consignee = $GLOBALS['db']->getRow($sql);
//检测刷单商品数量
$number = intval($_REQUEST['num']);
$number = $number > 0 ? $number : 1;
//往ecs_order_info表插入数据
$order = array(
    'shipping_id'     => 5,
    'consignee'     => $consignee['consignee'],
    'country'     => $consignee['country'],
    'province'     => $consignee['province'],
    'city'     => $consignee['city'],
    'address'     => $consignee['address'],
    'address_id'     => $consignee['address_id'],
    'tel'     => $consignee['tel'],
    'mobile'     => $consignee['mobile'],
    'email'     => $consignee['email'],
    'how_oos'     => '等待所有商品备齐后再发',
    'goods_amount'     => $goods['shop_price'] * $number,
    'district'     => $consignee['district'],
    'referer'         => !empty($_SESSION['referer']) ? addslashes($_SESSION['referer']) : '' ,
    'shipping_name'     => '申通快递',
    'order_brush'     => 1,
    'pay_id'          => $pay_id,
    'pay_name'        => $pay_name,
    'pack_id'         => 0,
    'card_id'         => 0,
    'surplus'         => 0.00,
    'integral'        => 0,
    'bonus_id'        => 0,
    'gift_id'        => 0,
    'need_inv'        => 0,
    'inv_type'        => '',
    'need_insure'     => 0,
    'user_id'         => $user['user_id'],
    'add_time'        => gmtime(),
    'order_status'    => OS_UNCONFIRMED,
    'shipping_status' => SS_UNSHIPPED,
    'pay_status'      => PS_UNPAYED,
    'agency_id'       => 0
);
$goods['subtotal'] = $goods['shop_price'];
$goods['goods_number2'] = $goods['goods_number'];
$goods['goods_number'] = $number;
$goods['goods_price'] = $goods['shop_price'];

$total = order_fee($order, array($goods), $consignee);
/* 支付方式 */
$order['pay_fee'] = $total['pay_fee'];
$order['cod_fee'] = $total['cod_fee'];

//订单金额四舍五入到毛
$order['order_amount']  = number_format($total['amount'], 1, '.', '');

//查看物品是否有货和上架
$goods_is_lack = false;
$lack_goods_name = '';
if ($goods['goods_number2'] <= 0 || $goods['is_on_sale'] == 0)
{
    $goods_is_lack = true;
    $lack_goods_name = $goods['goods_name'];
}
if ($goods_is_lack)
{
    $arr['result'] = 'failed';
    $arr['msg'] = '商品库存不足或下架';
    echo json_encode($arr);
    exit;
}

//得到一个新的订单号
$error_no = 0;
do
{
    $order['order_sn'] = get_order_sn(); //获取新订单号
    $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('order_info'), $order, 'INSERT');

    $error_no = $GLOBALS['db']->errno();

    if ($error_no > 0 && $error_no != 1062)
    {
        die($GLOBALS['db']->errorMsg());
    }
}
while ($error_no == 1062); //如果是订单号重复则重新提交数据
$new_order_id = $db->insert_id();


/* 插入订单商品 */
$sql = "INSERT INTO " . $ecs->table('order_goods') . "( " .
    "order_id, goods_id, goods_name, goods_sn, product_id, goods_number, market_price, ".
    "goods_price, is_real, extension_code, parent_id, is_gift, goods_attr_id) ".
    " VALUES ('{$new_order_id}', '{$goods['goods_id']}', '{$goods['goods_name']}', '{$goods['goods_sn']}', 0, '{$number}', '{$goods['market_price']}', ".
    " '{$goods['shop_price']}', '{$goods['is_real']}', '{$goods['extension_code']}', 0, 0, 0)";
$db->query($sql);

/* 如果使用库存，且下订单时减库存，则减少库存 */
if ($_CFG['use_storage'] == '1' && $_CFG['stock_dec_time'] == SDT_PLACE)
{
    change_order_goods_storage($new_order_id, true, SDT_PLACE);
}
/* 清除缓存*/
clear_all_files();
if($pay_id == 6)
{
    include_once(dirname(__FILE__) . '/includes/modules/payment/mobpay.php');
    $order['log_id'] = insert_pay_log($new_order_id, $order['order_amount'], PAY_ORDER);
    $order['order_id'] = $new_order_id;
    $order_data = new mobpay();
    $order_data->get_post_data($order);
}
$arr['result'] = 'succeed';
$arr['msg'] = '下单成功';
echo json_encode($arr);


?>