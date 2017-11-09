<?php
define('IN_ECS', true);
require(dirname(__FILE__) . '/../includes/init.php');

$sql = "DELETE FROM " . $GLOBALS['ecs']->table('message_iprecord');
$GLOBALS['db']->query($sql);

$file_name = "/web/wjike/cliscript/log/message_iprecord_".date("Ymd").".log";
file_put_contents($file_name,'1111',FILE_APPEND);

