<?php
define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
include 'm2.php';
include 'lib_bonus.php';
//require(dirname(__FILE__) . '/includes/init.php');
//require(ROOT_PATH . 'm2.php');
//require(ROOT_PATH . 'lib_bonus.php');
header ( "content-type:text/html;charset=utf-8" );

$m2 = new m2Base ();

$params = $_POST;
unset($params['submit']);
$params ['merchno'] = $m2->m_arr ['bonuse_merchno']; // 钱包商户号
$T604_data = get_params ( $m2->m_arr ['bonuse_key'], $params );
// var_dump($post_data);die;

$data = $m2->get_url_data ( 'T604', $T604_data, "POST" );
$file_name = "/web/pay_log/ebcol_request_".date("Ymd") . ".log";
$logStr = "params=" . $params . '|T604_data=' . $T604_data. '|data=' . $data  . "\n";
file_put_contents($file_name, $logStr, FILE_APPEND);
echo $data;die;
 ?>