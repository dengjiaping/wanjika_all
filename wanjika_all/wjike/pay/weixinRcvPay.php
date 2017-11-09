<?php
//==============test=====================
define('IN_ECS', true);
require('/web/wjike/includes/init.php');
require('/web/wjike/includes/lib_payment.php');
require('/web/wjike/includes/lib_order.php');

$file_name = "/web/pay_logs/weixinRcv_notify_oristr_".date("Ymd").".log";
file_put_contents($file_name,"\n".$GLOBALS['HTTP_RAW_POST_DATA'],FILE_APPEND);

$data = json_decode(json_encode(simplexml_load_string($GLOBALS['HTTP_RAW_POST_DATA'], 'SimpleXMLElement', LIBXML_NOCDATA)), true);
$total_fee = $data["total_fee"]/100;

$out_trade_no = $data["out_trade_no"];
$sql = 'SELECT t1.log_id FROM ' . $ecs->table('pay_log') . " t1 INNER JOIN ". $ecs->table('order_info') . " t2 on t1.order_id=t2.order_id WHERE t2.order_sn='$out_trade_no'";
$log_id = $db->getOne($sql);

/* 检查支付id */
if (empty($log_id))
{
    $logStr = "\n[PAYID ERROR]";
    file_put_contents($file_name,$logStr,FILE_APPEND);
    $rel = "<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[$logStr]]></return_msg></xml>";
    echo $rel;
    return false;
}

/* 检查支付的金额是否相符 */
if (!check_money($log_id, $total_fee))
{
    $logStr = "\n[MOENY ERROR]";
    file_put_contents($file_name,$logStr,FILE_APPEND);
    $rel = "<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[$logStr]]></return_msg></xml>";
    echo $rel;
    return false;
}

$weixin_key = '2b5k375jh4v685v83lk5joperug09d7c';
//签名步骤一：按字典序排序参数
ksort($data);

$buff = "";
foreach ($data as $k => $v)
{
    if($k != 'sign'){
        $buff .= $k . "=" . $v . "&";
    }
}
$reqPar = substr($buff, 0, strlen($buff)-1);
//echo '【string1】'.$String.'</br>';
//签名步骤二：在string后加入KEY
$String = $reqPar."&key=".$weixin_key;
//echo "【string2】".$String."</br>";
//签名步骤三：MD5加密
$String = md5($String);
//echo "【string3】 ".$String."</br>";
//签名步骤四：所有字符转为大写
$result = strtoupper($String);
/* 检查签名是否正确 */
if ($result != $data["sign"])
{
    $logStr = "\n[SIGN ERROR]";
    file_put_contents($file_name,$logStr,FILE_APPEND);
    $rel = "<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[$logStr]]></return_msg></xml>";
    echo $rel;
    return false;
}

if ($data['result_code'] == 'SUCCESS')
{
    /* 更新第三方支付接口交易流水号 */
    update_trade_no($data["out_trade_no"], $data["transaction_id"]);
    /* 改变订单状态 */
    order_paid($log_id, 2);

    $logStr = "\n[SUCCESS]";
    file_put_contents($file_name,$logStr,FILE_APPEND);
    $rel = "<xml><return_code><![CDATA[SUCCESS]]></return_code><return_msg><![CDATA[OK]]></return_msg></xml>";
    echo $rel;
    return true;
}
else
{
    $rel = "<xml><return_code><![CDATA[FAIL]]></return_code><return_msg><![CDATA[$logStr]]></return_msg></xml>";
    echo $rel;
    return false;
}
?>

