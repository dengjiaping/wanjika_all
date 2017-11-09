<?php
/**
 * Created by PhpStorm.
 * User: qihua
 * Date: 14-5-21
 * Time: 下午2:39
 */
define('IN_ECS', true);
require('/web/ecshop/includes/init.php');
//require('D:/workspace/ecshop/wanjike/includes/init.php');
require(ROOT_PATH . 'includes/lib_payment.php');
require(ROOT_PATH . 'includes/lib_order.php');
require(ROOT_PATH . 'includes/lib_clips.php');
require(ROOT_PATH . 'includes/lib_transaction.php');
require(ROOT_PATH . 'includes/modules/payment/umpay.php');

//$params['merId'] = 6518;
//$params['goodsId'] = 3896;
//$params['orderId'] = '201405281358530702';
//$params['payDate'] = 20140528;
//$params['amount'] = 1;
//$params['amtType'] = '03';
//$params['bankType'] = 4;
//$params['mobileId'] = 13466574426;
//$params['transType'] = 0;
//$params['settleDate'] = 20140528;
//$params['expand'] = '';
//$params['version'] = 3.2;
//$params['sign'] = 'm+Gn+GjwUrx6eCOn4BUVaBnMistAuQgIBDVilTNKIdEWpX4ZvbfsztegKliDBcfUpxygiRt4K63261vV5gtnQ52Xaaxz+mBs7qR40kQLEwrtvs4sF+Nif807/njYju0cFG60zd1r6p11d4PQcmfIdsfzHNnzFNAw9gL6qKPSyMQ=';
//var_dump($params);
//echo "<br /><br />";

//if ($_REQUEST['debug'] != 1)
//{
//    exit;
//}
//获取请求流数据
$input = file_get_contents('php://input');
if ($_REQUEST['debug'] == 1)
{
    $input = "merId=7145&goodsId=71451009&orderId=SG00000006664693&payDate=20140619&amount=1&amtType=03&bankType=4&mobileId=13910633379&transType=0&settleDate=20140619&expand=&version=3.2&sign=TYaUAi+ds7Khvjh8EicKiffdBCd+xz0LMHxTzK5KgAA1kpChjKS4SOGKGNRLYNuYRyD7uMeSukiTzOZnGDVGF9KiJOdxblreexGOyMu6uzockKvPWwIqKLhPAIOToKN2ONuQ0PXSXjyZcwIB4MucAWWoWLLhQ6Saw7R00bQSJEU=";
}

$param_arr = explode('&', $input);
$params = array();
foreach ($param_arr as $param_str)
{
    $arr = explode('=', $param_str, 2);
    $params[$arr[0]] = $arr[1];
}

//记录日志，用来排查问题
$file_name = "/web/pay_log/epsOrder_" . date("Ymd") . ".log";
$logStr = "\n[".date("Y-m-d H:i:s")."][".$params['merId']."][".$params['goodsId']."][".$params['orderId']."][".$params['payDate']."][".$params['amount']."][".$params['amtType']."][".$params['bankType']."][".$params['mobileId']."][".$params['transType']."][".$params['settleDate']."][".$params['expand']."][".$params['version']."][".$params['sign']."]";
file_put_contents($file_name, $logStr, FILE_APPEND);

$mer_id = '7145';
$ret_msg = '';
$ret_code = '';
$ng_sign = '';
$mobile = $params['mobileId'];
$user_id = 608503;        //指定一个用户，所有EPS过来的，都放入这个用户的订单里
//用户名epsuser，密码wjike001
$goods_sn = intval($params['goodsId']);   //商品ID
$goods_num = intval($params['amount']);   //购买商品数量
$eps_order_sn = $params['orderId'];
$signStr = "merId=".$params['merId']."&goodsId=".$params['goodsId']."&orderId=" . $params['orderId'] . "&payDate=".$params['payDate']."&amount=".$params['amount']."&amtType=".$params['amtType']."&bankType=".$params['bankType']."&mobileId=".$params['mobileId']."&transType=".$params['transType']."&settleDate=".$params['settleDate']."&expand=".$params['expand']."&version=".$params['version'];
$file_name = "/web/pay_log/epsphone_" . date("Ymd") . ".log";
$province = get_province_by_mobile($params['mobileId']);
file_put_contents($file_name, "{$params['mobileId']}\t$province\n", FILE_APPEND);

$umpay = new umpay();
//echo "Sign Stdr : ", $signStr;
//echo "<br /><br />";
do
{
    /**
     * 参数校验：
     * 1.sign验签
     * 2.验证商品编号和商品数量，商品编号必须是实际存在的商品，商品数量需大于1
     * 3.生成订单，如果订单生成失败，则反馈相关信息
     */

    $sql = "SELECT goods_id FROM " . $GLOBALS['ecs']->table('goods') . " WHERE goods_sn = '$goods_sn'";
    $goods_id = $GLOBALS['db']->getOne($sql);
    if (empty($goods_id))
    {
        $ret_msg = "商品不合法";
        $ret_code = "1111";
        break;
    }

    $certfile = ROOT_PATH . 'includes/cert_2d59.cert.pem';
    $result = $umpay->ssl_verify($signStr, $params['sign'], $certfile);

    if(!$result)
    {
        //商户在此处做相关的验签失败的处理，如果失败说明有不正常的客户端在访问支付结果通知
        //验签失败后，返回码必然是不成功的
        $ret_msg = "验签失败，电话4006125880";
        $ret_code = "1111";
        break;
    }

    if ($goods_num < 1)
    {
        $ret_msg = "商品数量不合法";
        $ret_code = "1111";
        break;
    }

    $sql = "SELECT order_id, order_sn FROM " . $GLOBALS['ecs']->table('order_info') . " WHERE eps_order_sn = '$eps_order_sn'";
    $eps_order_info = $GLOBALS['db']->getRow($sql);
    $order_id = 0;
    $order_sn = '';
    //如果提交过来的订单号已经有了，就返回给他卡号和密码
    if (!empty($eps_order_info))
    {
        $order_id = $eps_order_info['order_id'];
        $order_sn = $eps_order_info['order_sn'];

        $sql = "SELECT card_id, card_sn, card_password, end_date, crc32 FROM " . $GLOBALS['ecs']->table('virtual_card') . " WHERE order_sn = '{$eps_order_info['order_sn']}'";
        $virtual_card = $GLOBALS['db']->getAll($sql);
        if (!empty($virtual_card))
        {
            /* 卡号和密码解密 */
            $ret_msg = '';
            foreach ($virtual_card as $card)
            {
                if ($card['crc32'] == 0 || $card['crc32'] == crc32(AUTH_KEY))
                {
                    $card_sn = decrypt($card['card_sn']);
                    $card_password = decrypt($card['card_password']);
                }
                elseif ($card['crc32'] == crc32(OLD_AUTH_KEY))
                {
                    $card_sn = decrypt($card['card_sn'], OLD_AUTH_KEY);
                    $card_password = decrypt($card['card_password'], OLD_AUTH_KEY);
                }
                $end_date = $card['end_date'] == 0 ? '永久' : date($GLOBALS['_CFG']['date_format'], $card['end_date']);
                $ret_msg .= "卡号 $card_sn  密码 $card_password 有效期 $end_date ";
            }

            $ret_code = '0000';
            break;
        }
    }
    else
    {
        $payment_id = 2;
        $goodsAmount = getGoodsAmount($goods_id, $goods_num);
        $goodsAmount += pay_fee($payment_id, $goodsAmount, 0, $mobile);
        $goodsAmount = round($goodsAmount, 1);

        //是否合法商品
        if ($goodsAmount <= 0)
        {
            $ret_code = '1111';
            $ret_msg = "商品不合法";
            break;
        }

        $sql = "SELECT COUNT(*) FROM ".$GLOBALS['ecs']->table('virtual_card')." WHERE goods_id = '$goods_id' AND is_saled = 0 ";
        $num = $GLOBALS['db']->GetOne($sql);
        if ($num < $goods_num)
        {
            $ret_code = '0005';
            $ret_msg = "商品缺货~";
            break;
        }

        $address = '';
        $res_order_info = insertOrderInfo($user_id, $mobile, $address, $goods_id, $goods_num, $eps_order_sn);
        if (empty($res_order_info['order_id']))
        {
            $ret_code = '1111';
            $ret_msg = "生成订单失败";
            break;
        }

        $res_order_goods = insertOrderGoods($res_order_info['order_id'], $goods_id);

        $log_id = get_paylog_id($res_order_info['order_id'], PAY_ORDER);
        if (!$log_id)
        {
            $ret_code = '1111';
            $ret_msg = "订单异常";
            break;
        }

        $order_id = $res_order_info['order_id'];
        $order_sn = $res_order_info['order_sn'];
    }
    //获取卡信息
    $virtual_card = get_card_info($goods_id, $goods_num, $order_sn, $order_id);
    if (!empty($virtual_card))
    {
        $ret_msg = '';
        foreach ($virtual_card as $card)
        {
            $end_date = ($card['end_date'] == 0 ? '永久' : date($GLOBALS['_CFG']['date_format'], $card['end_date']));
            $ret_msg .= "卡号 {$card['card_sn']}  密码 {$card['card_password']} 有效期 $end_date ";
        }

        $ret_code = '0000';
        break;
    }
    else
    {
        $ret_code = '0005';
        $ret_msg = "商品缺货";
        break;
    }
}
while (false);

$mer_date = date('Ymd');
//echo "ret_msg = $ret_msg<br />";
$ret_msg = iconv(EC_CHARSET, 'gbk', $ret_msg);
$ret_msg = base64_encode($ret_msg);
$content = "$mer_id|$goods_id|$eps_order_sn|$goods_num|$mer_date|$ret_code|$ret_msg||3.2";
$ng_sign = $umpay->ssl_sign($content, ROOT_PATH . 'includes/7145_WanJiKe.key.pem');
$return_str = '<META NAME="MobilePayPlatform" CONTENT ="' . $content . '|' . $ng_sign . '">';
echo $return_str;
//echo htmlspecialchars($return_str);

function get_card_info($goods_id, $amount, $order_sn, $order_id)
{
    /* 取出卡片信息 */
    $sql = "SELECT card_id, card_sn, card_password, end_date, crc32 FROM ".$GLOBALS['ecs']->table('virtual_card')." WHERE goods_id = '$goods_id' AND is_saled = 0  LIMIT " . $amount;
    $arr = $GLOBALS['db']->getAll($sql);

    $cards = array();
    $card_ids = array();
    foreach ($arr as $virtual_card)
    {
        $card_info = array();
        /* 卡号和密码解密 */
        if ($virtual_card['crc32'] == 0 || $virtual_card['crc32'] == crc32(AUTH_KEY))
        {
            $card_info['card_sn'] = decrypt($virtual_card['card_sn']);
            $card_info['card_password'] = decrypt($virtual_card['card_password']);
        }
        elseif ($virtual_card['crc32'] == crc32(OLD_AUTH_KEY))
        {
            $card_info['card_sn'] = decrypt($virtual_card['card_sn'], OLD_AUTH_KEY);
            $card_info['card_password'] = decrypt($virtual_card['card_password'], OLD_AUTH_KEY);
        }
        else
        {
            return false;
        }
        $card_info['end_date'] = $virtual_card['end_date'];
        $card_ids[] = $virtual_card['card_id'];
        $cards[] = $card_info;
    }

    /* 标记已经取出的卡片 */
    $sql = "UPDATE " . $GLOBALS['ecs']->table('virtual_card') . " SET " .
        "is_saled = 1 ," .
        "order_sn = '$order_sn' " .
        "WHERE " . db_create_in($card_ids, 'card_id');
    if (!$GLOBALS['db']->query($sql, 'SILENT'))
    {
        return false;
    }

    /* 更新库存 */
    $sql = "UPDATE " . $GLOBALS['ecs']->table('goods') . " SET goods_number = goods_number - '$amount' WHERE goods_id = '$goods_id'";
    $GLOBALS['db']->query($sql);

    $sql = "UPDATE ".$GLOBALS['ecs']->table('order_goods'). "
        SET send_number = '" . $amount . "'
        WHERE order_id = '" . $order_id . "'
        AND goods_id = '" . $goods_id . "' ";

    if (!$GLOBALS['db']->query($sql, 'SILENT'))
    {
        return false;
    }

    return $cards;
}

function getGoodsAmount($goods_id, $goods_num)
{
    global $db, $ecs;
    if (!$goods_id || $goods_num < 0)
    {
        return 0;
    }

    $sql = "SELECT goods_id, shop_price FROM " . $ecs->table('goods') . " where goods_id='" . $goods_id . "'";
    $res = $db->getAll($sql);
    if ($res)
    {
        return $res[0]['shop_price'] * $goods_num;
    }

    return false;
}

function  getPayFee($goods_amount, $payment_id)
{
    $pay_fee = pay_fee($payment_id, 1);
    //手续费=商品总额*比率/(1-比率)
    if ($goods_amount)
    {
        return $goods_amount * $pay_fee / (1 - $pay_fee);
    }
    return 0;
}

function insertOrderGoods($order_id, $goods_id)
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
    $goods_number = 1;
    $is_gift = 0;
    $goods_attr_id  = 0;
    $sql = "INSERT INTO `ecs_order_goods`( order_id, goods_id, goods_name, goods_sn, product_id, goods_number, market_price, goods_price, goods_attr, is_real, extension_code, parent_id, is_gift, goods_attr_id) values ('".$order_id."','". $goods_id."','". $goods_name."','". $goods_sn."','". $product_id."','". $goods_number."','". $market_price."','". $goods_price."','". $goods_attr."','". $is_real."','". $extension_code."','". $parent_id."','". $is_gift."','". $goods_attr_id."')";
    $res = $db->query($sql);

    return $db->insert_id();
}


function insertOrderInfo($user_id, $mobile, $address, $goods_id, $goods_num, $eps_order_sn)
{
    global $db;

    $order_sn = get_order_sn();
    $order_status = OS_CONFIRMED;
    $shipping_status = SS_SHIPPED;
    $pay_status = PS_PAYED;
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
    $goods_amount = getGoodsAmount($goods_id, $goods_num);
    $shipping_fee = 0;
    $insure_fee = 0;
    $pay_fee = pay_fee($pay_id, $goods_amount, 0, $mobile);
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
    $referers = 'EPS';
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
        extension_id, agency_id, inv_type, tax, parent_id, discount, eps_order_sn)
        VALUES
        ('$order_sn','$user_id','$order_status','$shipping_status','$pay_status','$consignee','$country','$province','$city',
        '$district','$address','$zipcode','$tel','$mobile','$email','$best_time','$sign_building','$postscript','$shipping_id',
        '$shipping_name','$pay_id','$pay_name','$how_oos','$card_message','$inv_payee','$inv_content','$goods_amount',
        '$shipping_fee','$insure_fee','$pay_fee','$pack_fee','$card_fee','$surplus','$integral','$integral_money',
        '$bonus','$order_amount','$from_ad','$referers','$add_time', '$confirm_time', '$pay_time', '$shipping_time', '$pack_id','$card_id','$bonus_id','$extension_code',
        '$extension_id','$agency_id','$inv_type','$tax','$parent_id','$discount', '$eps_order_sn')";
    $res = $db->query($sql);
    $order_id = $db->insert_id();

    insert_pay_log($order_id, $order_amount, PAY_ORDER);

    return array('order_id' => $order_id, 'order_sn' => $order_sn);
}