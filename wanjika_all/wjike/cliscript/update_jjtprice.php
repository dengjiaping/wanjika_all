<?php

define('IN_ECS', true);
set_time_limit(0);

require(dirname(__FILE__) . '/../includes/init.php');
require(dirname(__FILE__) . '/../includes/modules/jjt/create_order.php');

$obj = new create_jorder();
$r = $obj->get_productlist();

foreach($r['productlist'] as $goods){
    $price = $goods->Price;
    $tariffrate = $goods->ProductTaxes;
    $kjt_goodsid = $goods->ProductID;
    $sql = 'UPDATE ' . $GLOBALS['ecs']->table('goods') .
    " SET kjt_price=$price,kjt_tariffrate =$tariffrate WHERE kjt_goods_id = '$kjt_goodsid'";

    $GLOBALS['db']->query($sql);
}