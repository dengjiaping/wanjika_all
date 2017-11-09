<?php
/**
 * Created by PhpStorm.
 * User: qihua
 * Date: 14-6-17
 * Time: 下午4:26
 */

define('IN_ECS', true);
//require('D:/workspace/ecshop/wanjike/includes/init.php');
require('/web/ecshop/includes/init.php');

$goods_id = 4331;
$md5_sign_key = 'Ro4fawcWmaOOZGHjjfqzMzN9uDxBIDLn';

require(ROOT_PATH . 'includes/lib_payment.php');
require(ROOT_PATH . 'includes/lib_order.php');
require(ROOT_PATH . 'includes/lib_clips.php');
require(ROOT_PATH . 'includes/lib_transaction.php');
require(ROOT_PATH . 'includes/modules/payment/umpay.php');

$mobile = trim($_REQUEST['mobile']);
$user_name = mysql_escape_string(trim($_REQUEST['merid']));
$password = trim($_REQUEST['key']);
$amount = abs(intval($_REQUEST['amount']));
$order_id = trim($_REQUEST['order_id']);
$sign = trim($_REQUEST['sign']);

//参数合法性
if (empty($user_name) || empty($password) || empty($amount) || empty($mobile))
{
    echo '';
    exit;
}

//验证金额
if ($amount < 10)
{
    echo '';
    exit;
}

//验证签名
$sign_str = "merid=$user_name&key=$password&mobile=$mobile&amount=$amount&order_id=$order_id";
$sign_str .= $md5_sign_key;
if (md5($sign_str) != $sign)
{
    echo '';
    exit;
}

$province_arr = array('北京', '上海','黑龙江','吉林','湖北','河南');
//是否开通
$province = get_province_by_mobile($mobile);
if (!in_array($province, $province_arr))
{
    echo '';
    exit;
};

$sql = "SELECT user_id, user_name, password, ec_salt FROM ecs_users WHERE user_name= '$user_name'";
$user_info = $GLOBALS['db']->getRow($sql);
if (empty($user_info))
{
    echo '';
    exit;
}

if ($user_info['password'] != MD5($password. $user_info['ec_salt']))
{
    echo '';
    exit;
}

//记录信息
$add_time = gmtime();
$sql = "INSERT INTO " . $ecs->table('pay_code_info') . "(
    `user_id`, `mobile`, `amount`, `order_id`, `add_time`)   VALUES
    ('{$user_info['user_id']}', '$mobile', '$amount', '$order_id', '$add_time')
    ";
$res = $db->query($sql);
if (!$res)
{
    echo '';
    exit;
}
$pid = $db->insert_id();

echo "001#$goods_id#$pid";