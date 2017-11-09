<?php
define('IN_ECS', 1);
require(dirname(__FILE__) . '/../includes/init.php');
require_once (dirname ( __FILE__ ) .'/config/Config.php');

$mytime = time() - date('Z');

$sql = "update ecs_goods set last_update=$mytime where is_on_sale=1 and is_alone_sale=1 and is_delete=0 and is_promote=1
and promote_start_date<=$mytime and promote_end_date >=$mytime order by last_update limit 5";

$GLOBALS['db']->query($sql);

/* 清除缓存 */
clear_cache_files();
//$sql = "select * from ecs_goods where is_on_sale=1 and is_alone_sale=1 and is_delete=0 and is_promote=1
//and promote_start_date<=$mytime and promote_end_date >=$mytime order by last_update desc limit 5";
//$result = $GLOBALS['db']->getAll($sql);
//
//foreach($result as $a){
//    var_dump($a["goods_id"]);}
