<?php

define('IN_ECS', true);
set_time_limit(0);

require(dirname(__FILE__) . '/../includes/init.php');
require(dirname(__FILE__) . '/../includes/modules/kjt/create_order.php');

$sql = "SELECT t1.order_no,t1.kjt_order_no,t1.sft_paytime,t2.kjt_declare_amount,t2.sft_pay_tradeno FROM " . $GLOBALS['ecs']->table('kjt_task'). " AS t1 inner JOIN " . $GLOBALS['ecs']->table('order_info').
    " AS t2 ON t1.order_no=t2.order_sn WHERE t1.task_status=11 and (t1.sft_status=21 or t1.sft_status=32) and t1.task_type<>2";
$tasks = $db->getAll($sql);

if(count($tasks) == 0){
    exit;
}

$obj = new create_order();
$file_name = "/web/wjike/cliscript/log/kjt_task_".date("Ymd").".log";
$suc = 0;
$fal = 0;
foreach($tasks as $task){
    sleep(1);
    $kjt_declare_amount = number_format($task['kjt_declare_amount'], 2, '.', '');
    $r = $obj->payDeclareNotice($task['order_no'],$task['kjt_order_no'],$task['sft_pay_tradeno'],$task['sft_paytime'],$kjt_declare_amount);
    if($r){
        $suc++;
    }
    else{
        $fal++;
    }
}
echo 'SUCCESS:'.$suc.',FAILED:'.$fal;
file_put_contents($file_name,$order_str,FILE_APPEND);