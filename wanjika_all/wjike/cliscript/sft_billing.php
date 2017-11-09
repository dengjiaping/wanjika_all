<?php

define('IN_ECS', true);
set_time_limit(0);

require(dirname(__FILE__) . '/../includes/init.php');
require(dirname(__FILE__) . '/../includes/lib_transaction.php');
require(dirname(__FILE__) . '/../includes/modules/kjt/shengfutong.php');

//$sql = "SELECT order_no FROM " . $GLOBALS['ecs']->table('kjt_task'). " WHERE task_status = 11 and (sft_status=0 or sft_status=12)";
$sql = "SELECT t2.order_id FROM " . $GLOBALS['ecs']->table('kjt_task'). " AS t1 inner JOIN " . $GLOBALS['ecs']->table('order_info').
    " AS t2 ON t1.order_no=t2.order_sn WHERE t1.task_status=11 and (t1.sft_status=0 or t1.sft_status=12) and t1.task_type<>2";
$tasks = $db->getAll($sql);

if(count($tasks) == 0){
    exit;
}

$stfobj = new sft_transfer();

//不缓存wsdl文件, soap版本为1.1
$options = array('trace'=>true,
    'cache_wsdl'=>WSDL_CACHE_NONE,
    'soap_version'=> SOAP_1_1);
$client = new SoapClient("http://mas.shengpay.com/api-acquire-channel/services/trans?wsdl", $options);

$file_name = "/web/wjike/cliscript/log/kjt_task_".date("Ymd").".log";
$suc = 0;
$fal = 0;
foreach($tasks as $task){
    $order = get_order_detail($task['order_id']);
    $br = $stfobj->Billing($order,$client);
    if($br === false){
        $fal++;
    }
    else{
        sleep(1);
        $r1 = $stfobj->payDeclare($order,$br);
        if($r1){
            $suc++;
        }
        else{
            $fal++;
        }
    }
}
echo 'SUCCESS:'.$suc.',FAILED:'.$fal;
file_put_contents($file_name,$order_str,FILE_APPEND);