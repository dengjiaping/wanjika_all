<?php

define('IN_ECS', true);
require(dirname(__FILE__) . '/includes/init.php');


$content = '您目前的支付环境存在较高风险，如有疑问请致电：4000851115。';
show_message($content);