<?php

define('IN_ECS', true);
header("Content-type:text/html;charset=utf-8");
include_once(dirname(__FILE__) . '/../includes/init.php');
include_once(dirname(__FILE__) . '/../includes/cls_msgsend.php');
/**
 * 民生品翠通知商户订单出关，商户提供API
 */
$arr = $_POST;
$servic = new Service();
if(strcasecmp($_POST['method'],"Order.SOOutputCustoms")!=0 && strcasecmp($_POST['method'],"Order.SOOutputWarehouse")!=0)
{
    echo "FAILURE";
//    $result['Code']="=";
//    $result['Desc']="=";
//    //记录日志
//    $servic->ecord_log($result);
    return;
}
unset($arr['sign']);
$data =stripslashes($_POST['data']);
$arr['data']=urlencode($data);

$data = json_decode($data,true);

ksort($arr);
//array_shift($arr);
foreach($arr as $k=>$v){
    $get.= $k.'='.$v.'&';
}
//$get = substr($get,0,-1);

//系统订单编号
$SOSysNo = $data['SOSysNo'];
//订单类型
$OrderType = $data['OrderType'];
//订单物流运输公司编号
$ShipTypeID = $data['ShipTypeID'];
//订单物流编号
$TrackingNumber = $data['TrackingNumber'];
//出库时间20071117020101
$CommitTime = $data['CommitTime'];
//处理结果编码
$result['Code']="0";
//处理结果消息
$result['Desc']="";
//接口处理完毕返回的数据
$res['SOSysNo'] =$SOSysNo;
$result['Data']=$res;

//出库$flag为false，出关为true
$flag=true;
$Message="";
if(strcasecmp($_POST['method'],"Order.SOOutputCustoms")!=0)
{
    $flag=false;
}
if($flag)
{
    //出关状态
    $Status = $data['Status'];
    //说明订单出关失败的原因
    $Message = $data['Message'];
}

if (!is_numeric($ShipTypeID) || null == $TrackingNumber || !is_numeric($CommitTime)){
    $result['Code']="-1";
    $result['Desc']="请求参数错误".$Message;
    if($flag)
    {
        echo "FAILURE";
    }
    else
    {
        echo $servic->JSON($servic->object_to_array($result));
    }
    //记录日志
    $servic->ecord_log($result);
    return ;
}

//快递名称
switch($ShipTypeID)
{
    case 1:
        $ShipName="顺丰";
        $ShipTypeID=6;
        break;
    case 2:
        $ShipName="圆通";
        break;
    case 93://27 测试
        $ShipName="申通";
        $ShipTypeID=5;
        break;
    case 84://25 测试
        $ShipName="如风达";
        break;
    case 264://测试邮政小包
        $ShipName="申通";
        $ShipTypeID=5;
        break;
    default:
        $result['Code']="-11";
        $result['Desc']="请求参数错误:快递ID错误".$Message;
        if($flag)
        {
            echo "FAILURE";
        }
        else
        {
            echo $servic->JSON($servic->object_to_array($result));
        }
        //记录日志
        $servic->ecord_log($result);
        return ;
}

$sign = $_POST['sign'];//验证码
if($sign != md5($get."aa7df029365f42549a0862b020d8d196") ){
    $result['Code']="-2";
    $result['Desc']="签名校验错误".$Message;
    if($flag)
    {
        echo "FAILURE";
    }
    else
    {
        echo $servic->JSON($servic->object_to_array($result));
    }
    //记录日志
    $servic->ecord_log($result);
    return ;
}

$sql = "SELECT order_no FROM " . $GLOBALS['ecs']->table('kjt_task') .
    " WHERE kjt_order_no = '$SOSysNo'";

$MerchantOrderID = $GLOBALS['db']->getOne($sql);
if($MerchantOrderID == null){
    $result['Code']="-12";
    $result['Desc']="请求参数错误:获取商城订单号错误".$Message;
    if($flag)
    {
        echo "FAILURE";
    }
    else
    {
        echo $servic->JSON($servic->object_to_array($result));
    }
    //记录日志
    $servic->ecord_log($result);
    return ;
}

$sql="SELECT order_status,shipping_status,pay_status,user_id,tel FROM ecs_order_info WHERE order_sn=$MerchantOrderID";
$orders_status = $GLOBALS['db']->GetRow($sql);
if($orders_status['order_status']!=1 || $orders_status['pay_status']!=2 || $orders_status['shipping_status']==1 || $orders_status['shipping_status']==2)
{
    $result['Code']="-13";
    $result['Desc']="请求参数错误:订单状态错误".$Message;
    if($flag)
    {
        echo "FAILURE";
    }
    else
    {
        echo $servic->JSON($servic->object_to_array($result));
    }
    //记录日志
    $servic->ecord_log($result);
    return ;
}

if($flag)
{
    if($Status==1)
    {//出关成功
        $res = $servic->orders_customs($MerchantOrderID,$ShipTypeID,$ShipName,$TrackingNumber,$CommitTime);
    }
    elseif($Status== -1)
    {//出关失败
        $result['Desc']="失败".$Message;
        echo "SUCCESS";
        //通知出关逻辑
        $sql="UPDATE ecs_kjt_task SET customs_desc='$Message' WHERE order_no=$MerchantOrderID";
        $res = $GLOBALS['db']->query($sql);
        //记录日志
        $servic->ecord_log($result);
        return ;
    }
    else
    {
        $result['Code']="-14";
        $result['Desc']="Status异常".$Message;
//    echo $servic->JSON($servic->object_to_array($result));
        echo "FAILURE";
        //记录日志
        $servic->ecord_log($result);
        return;
    }
}
else
{
    $res = $servic->orders_delivery($MerchantOrderID,$ShipTypeID,$ShipName,$TrackingNumber,$CommitTime);
}

if($res)
{
    $mr = true;
    if($orders_status["user_id"] != 1406833){
        $msgsend = new msgsend();
        $msg = '您好，您的订单'. $MerchantOrderID .'已发货，申通运单号为：'. $TrackingNumber .'。您可以登录访问 http://www.wjike.com或打开“万集客”微信号点击进入商城，在“我的订单”-“待收货订单”中查看物流信息。';
        $mr = $msgsend->send($orders_status["tel"],$msg,"minspc");
    }
    $result['Desc']="成功".$Message;
    if(!$mr){
        $result['Desc']="message faild.成功".$Message;
    }
    if($flag)
    {
        echo "FAILURE";
    }
    else
    {
        echo $servic->JSON($servic->object_to_array($result));
    }
    //记录日志
    $servic->ecord_log($result);
}
else
{
    $result['Code']="-15";
    $result['Desc']="请求参数错误:修改失败".$Message;
    if($flag)
    {
        echo "FAILURE";
    }
    else
    {
        echo $servic->JSON($servic->object_to_array($result));
    }
    //记录日志
    $servic->ecord_log($result);
}





class Service{

    function JSON($array) {
        $this->arrayRecursive($array, 'urlencode', true);
        $json = json_encode($array);
        return urldecode($json);
    }
    function object_to_array($obj)
    {
        $_arr = is_object($obj) ? get_object_vars($obj) : $obj;
        foreach ($_arr as $key => $val)
        {
            $val = (is_array($val) || is_object($val)) ? $this->object_to_array($val) : $val;
            $arr[$key] = $val;
        }
        return $arr;
    }
    function arrayRecursive(&$array, $function, $apply_to_keys_also = false)
    {
        static $recursive_counter = 0;
        if (++$recursive_counter > 1000) {
            die('possible deep recursion attack');
        }
        foreach ($array as $key => $value) {
            if (is_array($value)) {
                $this->arrayRecursive($array[$key], $function, $apply_to_keys_also);
            } else {
                $array[$key] = $function($value);
            }

            if ($apply_to_keys_also && is_string($key)) {
                $new_key = $function($key);
                if ($new_key != $key) {
                    $array[$new_key] = $array[$key];
                    unset($array[$key]);
                }
            }
        }
        $recursive_counter--;
    }
    function orders_customs($ordersn,$shiptypeid,$shipname,$trackingnumber,$CommitTime)
    {
        //出关时间
        $CommitTime=local_strtotime($CommitTime);
        //通知出关逻辑
        $sql="UPDATE ecs_order_info SET order_status=5,shipping_status=1,shipping_id=$shiptypeid,shipping_name='$shipname',invoice_no=$trackingnumber,shipping_time=$CommitTime WHERE order_sn=$ordersn";
        $res = $GLOBALS['db']->query($sql);
        return $res;
    }
    function orders_delivery($ordersn,$shiptypeid,$shipname,$trackingnumber,$CommitTime)
    {
        //出库时间
//        $CommitTime=local_strtotime($CommitTime);
        //通知出库逻辑
        $sql="UPDATE ecs_order_info SET shipping_status=7,shipping_id=$shiptypeid,shipping_name='$shipname',invoice_no=$trackingnumber WHERE order_sn=$ordersn";
        $res = $GLOBALS['db']->query($sql);
        return $res;
    }
    function ecord_log($result)
    {
        $kjt_log = "result==".json_encode($_POST)."&Code==" .$result['Code']."&Desc==".$result['Desc']."&errortime==".date('Y-m-d H:i:s',time())."\n";
        $file_name = "/web/kjt_log/minorder_log_".date("Ymd") . ".log";
        file_put_contents($file_name,$kjt_log,FILE_APPEND);
    }
}
?>