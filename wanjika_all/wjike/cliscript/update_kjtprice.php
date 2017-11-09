<?php

define('IN_ECS', true);
set_time_limit(0);

require(dirname(__FILE__) . '/../includes/init.php');
require(dirname(__FILE__) . '/../includes/modules/kjt/create_order.php');

$sql = "SELECT t1.goods_id,t1.goods_name,t1.kjt_goods_id,t1.kjt_price,t1.kjt_tariffrate FROM " . $GLOBALS['ecs']->table('goods'). " AS t1 inner JOIN " . $GLOBALS['ecs']->table('goods_supplier')
    . " AS t2 ON t1.supplier_id=t2.type_id WHERE t1.is_on_sale=1 and t1.kjt_goods_id<>'' and t1.kjt_price=0 and t1.kjt_tariffrate=0 and t2.shipping_source=1 and t1.supplier_id<>38 and LENGTH(t1.kjt_goods_id) <>5 order by RAND() limit 10";
$goods_list = $db->getAll($sql);
$obj = new create_order();
$i=0;
foreach($goods_list as $goods){
    if(empty($goods['kjt_goods_id'])){
        continue;
    }
    sleep(1);
    $r = $obj->get_productinfo($goods['kjt_goods_id']);
    if($r['code'] == "SUCCESS"){
        $price = $r['price'];
        $tariffrate = $r['tariffrate'];
        $goods_id = $goods['goods_id'];

        $sql = 'UPDATE ' . $GLOBALS['ecs']->table('goods') .
            " SET kjt_price=$price,kjt_tariffrate =$tariffrate WHERE goods_id = $goods_id";

        $GLOBALS['db']->query($sql);
        $i++;
    }
}
echo '执行商品价格同步任务的商品个数为:'.count($goods_list).',任务成功个数为'.$i.'个';