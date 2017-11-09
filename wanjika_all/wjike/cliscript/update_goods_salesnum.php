<?php
define('IN_ECS', true);
require(dirname(__FILE__) . '/../includes/init.php');
require(dirname(__FILE__) . '/../includes/lib_order.php');

$sql = "SELECT g.goods_id,og.goods_num FROM " . $GLOBALS['ecs']->table('goods'). " as g inner join  (SELECT goods_id,SUM(goods_number) AS goods_num FROM "
    . $GLOBALS['ecs']->table('order_goods'). " t1 INNER JOIN " . $GLOBALS['ecs']->table('order_info'). " t2 on t1.order_id=t2.order_id where t2.order_status<>2 and t2.pay_status=2 GROUP BY goods_id) as og on g.goods_id=og.goods_id";
$results = $GLOBALS['db']->getAll($sql);
if(count($results) == 0){
    exit;
}

foreach($results as $value){
    $goods_num = $value["goods_num"];
    $goods_id = $value["goods_id"];
    
    $sql = 'UPDATE ' . $GLOBALS['ecs']->table('goods') .
        " SET salesnum=$goods_num WHERE goods_id = $goods_id";

    $GLOBALS['db']->query($sql);
}

echo "SUCCESS";
