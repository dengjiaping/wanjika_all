<?php

define('IN_ECS', true);
set_time_limit(0);

require(dirname(__FILE__) . '/../includes/init.php');
require(dirname(__FILE__) . '/../includes/lib_order.php');

$sql = "select order_id from ecs_order_info where supplier_status=2 and pay_status=2 and order_status<>2";
$list = $db->getAll($sql);

if(count($list) == 0){
    exit;
}

foreach($list as $item){
    $order_id = $item['order_id'];

    //获取订单信息
    $order_sp = get_kjtorderinfo($order_id);

    //获取子订单所占比例数组
    $price_list = get_rate_by_lib($order_id);
    //拆单上限金额
    $price_limit = 2000;
    $i = 1;
    //构造最后订单分摊金额数组
    $last_amount["shipping_fee"] = $order_sp["shipping_fee"];
    $last_amount["pay_fee"] = $order_sp["pay_fee"];
    $last_amount["surplus"] = $order_sp["surplus"];
    $last_amount["bonus"] = $order_sp["bonus"];
    $last_amount["gift_money"] = $order_sp["gift_money"];
    $last_amount["discount"] = $order_sp["discount"];
    $last_amount["money_paid"] = $order_sp["money_paid"];
    $last_amount["integral_money"] = $order_sp["integral_money"];
    $last_amount["integral"] = $last_amount["integral_money"] * 100;
    $lib_order = array();
    $split_id_list = array();
    foreach($price_list as $value){
        //$rate = number_format($value['total_price'] / $order_sp['goods_amount'], 2, '.', '');
        $rate = $value['total_price'] / $order_sp['goods_amount'];
        $child_order = array();
        //第一个做订单的更新操作
        if($i == 1){
            $child_order = get_separate_order($order_sp,$value['total_price'],$rate,$order_sp['order_id'],false);
            //记录最后一个订单分单金额数组
            $last_amount["shipping_fee"] -= $child_order["shipping_fee"];
            $last_amount["pay_fee"] -= $child_order["pay_fee"];
            $last_amount["surplus"] -= $child_order["surplus"];
            $last_amount["bonus"] -= $child_order["bonus"];
            $last_amount["gift_money"] -= $child_order["gift_money"];
            $last_amount["discount"] -= $child_order["discount"];
            $last_amount["money_paid"] -= $child_order["money_paid"];
            $last_amount["integral_money"] -= $child_order["integral_money"];
            $last_amount["integral"] = $last_amount["integral_money"] * 100;
            $child_order["is_overseas"] = $value["is_overseas"];
            $child_order['supplier_status']=SS_ALREADY;
            if($value["is_overseas"] && $child_order["money_paid"] == 0){
                $child_order['kjt_pay_tradeno'] = get_paytradeno();
                if(!empty($child_order['kjt_pay_tradeno'])){
                    //支付流水号更新为成功使用
                    pay_tradeno_used($child_order['kjt_pay_tradeno'],$child_order['order_sn'],1);
                }
            }
//                if($value["is_overseas"]){
//                    $child_order['kjt_pay_tradeno'] = $child_order['pay_trade_no'];
//                }
            $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('order_info'), $child_order, 'UPDATE', "order_id = '$child_order[order_id]'");
        }
        //分摊金额除不尽的累加到最后一个
        else if ($i == count($price_list)){
            $child_order = get_separate_order($order_sp,$value['total_price'],$rate,$order_sp['order_id']);
            unset($child_order['order_id']);
            $child_order["shipping_fee"] = $last_amount["shipping_fee"];
            $child_order["pay_fee"] = $last_amount["pay_fee"];
            $child_order["surplus"] = $last_amount["surplus"];
            $child_order["bonus"] = $last_amount["bonus"];
            $child_order["gift_money"] = $last_amount["gift_money"];
            $child_order["discount"] = $last_amount["discount"];
            $child_order["money_paid"] = $last_amount["money_paid"];
            $child_order["integral_money"] = $last_amount["integral_money"];
            $child_order["integral"] = $child_order["integral_money"] * 100;
            $child_order["is_overseas"] = $value["is_overseas"];
            $child_order['supplier_status']=SS_ALREADY;
            if($value["is_overseas"]){
                $child_order['kjt_pay_tradeno'] = get_paytradeno();
                if(!empty($child_order['kjt_pay_tradeno'])){
                    //支付流水号更新为成功使用
                    pay_tradeno_used($child_order['kjt_pay_tradeno'],$child_order['order_sn'],1);
                }
            }
            $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('order_info'), $child_order, 'INSERT');
            $child_order["order_id"] = $GLOBALS['db']->insert_id();
        }
        else{
            $child_order = get_separate_order($order_sp,$value['total_price'],$rate,$order_sp['order_id']);
            unset($child_order['order_id']);
            //记录最后一个订单分单金额数组
            $last_amount["shipping_fee"] -= $child_order["shipping_fee"];
            $last_amount["pay_fee"] -= $child_order["pay_fee"];
            $last_amount["surplus"] -= $child_order["surplus"];
            $last_amount["bonus"] -= $child_order["bonus"];
            $last_amount["gift_money"] -= $child_order["gift_money"];
            $last_amount["discount"] -= $child_order["discount"];
            $last_amount["money_paid"] -= $child_order["money_paid"];
            $last_amount["integral_money"] -= $child_order["integral_money"];
            $last_amount["integral"] = $last_amount["integral_money"] * 100;
            $child_order["is_overseas"] = $value["is_overseas"];
            $child_order['supplier_status']=SS_ALREADY;
            if($value["is_overseas"]){
                $child_order['kjt_pay_tradeno'] = get_paytradeno();
                if(!empty($child_order['kjt_pay_tradeno'])){
                    //支付流水号更新为成功使用
                    pay_tradeno_used($child_order['kjt_pay_tradeno'],$child_order['order_sn'],1);
                }
            }
            $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('order_info'), $child_order, 'INSERT');
            $child_order["order_id"] = $GLOBALS['db']->insert_id();
        }
        //海淘订单创建跨境通订单推送任务
        if($value["is_overseas"]){
            if($value["shipping_source"] == 1){
                //创建海淘任务
                create_kjt_task($child_order['order_sn']);
            }
            if($child_order["goods_amount"] > $price_limit){
                array_push($split_id_list,$child_order["order_id"]);
            }
        }
        //添加到支付流水表
        $insert_order_info = order_info($child_order["order_id"]);
        $pay_statistics['order_id']=$child_order['order_id'];
        $pay_statistics['surplus']=$child_order['surplus'];
        $pay_statistics['bonus']=$child_order['bonus'];
        $pay_statistics['gift']=$child_order['gift_money'];
        $pay_statistics['pay_fee']=$child_order['pay_fee'];
        $pay_statistics['order_amount']=$insert_order_info['total_fee'];
        $pay_statistics['pay_id']=$insert_order_info['pay_id'];
        $pay_statistics['pay_transaction']=$insert_order_info['pay_trade_no'];
        $flag = exist_pay_statistics($child_order["order_id"]);
        if($flag){
            $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('pay_statistics'), $pay_statistics, 'UPDATE', "order_id = '$child_order[order_id]'");
        }
        else{
            $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('pay_statistics'), $pay_statistics, 'INSERT');
        }

        $lib_order[$value['warehouse_type']] = $child_order["order_id"];
        $i++;
    }
    $goods = get_ordergoods($order_id);
    //更新商品订单表
    foreach($goods as $good){
        $lib_type = getlib($good['supplier_id']);
        update_ordergoods($good['rec_id'],$lib_order[$lib_type]);
    }
    //2000拆单
    if(count($split_id_list) > 0){
        foreach($split_id_list as $oid){
            //构造订单拆分数组
            $split_price_list = array();
            //构造订单商品拆分数组
            $split_order_goods = array();
            $goods_list = get_ordergoods($oid);
            foreach($goods_list as $goods){
                if($goods['goods_price'] > 2000){
                    for($i = 1;$i <= $goods['goods_number'];$i++){
                        $arr['total_price'] = $goods['goods_price'];
                        $arr['goods_id'] = $goods['goods_id'];
                        $arr['goods_number'] = 1;
                        array_push($split_price_list,$arr);
                    }
                }
                elseif($goods['goods_price'] * $goods['goods_number'] > $price_limit){
                    $num = floor($price_limit / $goods['goods_price']);
                    $group = ceil($goods['goods_number'] / $num);
                    for($j = 1;$j <= $group;$j++){
                        $arr['goods_id'] = $goods['goods_id'];
                        $arr['total_price'] = $goods['goods_price'] * $num;
                        $arr['goods_number'] = $num;
                        if($j == $group){
                            $last_num = $goods['goods_number'] - $num * ($j - 1);
                            $arr['total_price'] = $goods['goods_price'] * $last_num;
                            $arr['goods_number'] = $last_num;
                        }
                        array_push($split_price_list,$arr);
                    }
                }
                else{
                    $arr['total_price'] = $goods['goods_price'] * $goods['goods_number'];
                    $arr['goods_id'] = $goods['goods_id'];
                    $arr['goods_number'] = $goods['goods_number'];
                    array_push($split_price_list,$arr);
                }
            }

            //获取订单信息
            $order_sp = get_kjtorderinfo($oid);
            $i = 1;
            //构造最后订单分摊金额数组
            $last_amount["shipping_fee"] = $order_sp["shipping_fee"];
            $last_amount["pay_fee"] = $order_sp["pay_fee"];
            $last_amount["surplus"] = $order_sp["surplus"];
            $last_amount["bonus"] = $order_sp["bonus"];
            $last_amount["gift_money"] = $order_sp["gift_money"];
            $last_amount["discount"] = $order_sp["discount"];
            $last_amount["money_paid"] = $order_sp["money_paid"];
            $last_amount["integral_money"] = $order_sp["integral_money"];
            $last_amount["integral"] = $last_amount["integral_money"] * 100;
            foreach($split_price_list as $value){
                $split_order_goods[$value['goods_id']] = array();
            }

            foreach($split_price_list as $value){
                $rate = $value['total_price'] / $order_sp['goods_amount'];
                $child_order = array();
                //第一个做订单的更新操作
                if($i == 1){
                    $child_order = get_separate_order($order_sp,$value['total_price'],$rate,$order_sp['order_id'],false);
                    //记录最后一个订单分单金额数组
                    $last_amount["shipping_fee"] -= $child_order["shipping_fee"];
                    $last_amount["pay_fee"] -= $child_order["pay_fee"];
                    $last_amount["surplus"] -= $child_order["surplus"];
                    $last_amount["bonus"] -= $child_order["bonus"];
                    $last_amount["gift_money"] -= $child_order["gift_money"];
                    $last_amount["discount"] -= $child_order["discount"];
                    $last_amount["money_paid"] -= $child_order["money_paid"];
                    $last_amount["integral_money"] -= $child_order["integral_money"];
                    $last_amount["integral"] = $last_amount["integral_money"] * 100;

//                        $child_order['kjt_pay_tradeno'] = $child_order['pay_trade_no'];
                    $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('order_info'), $child_order, 'UPDATE', "order_id = '$child_order[order_id]'");
                }
                //分摊金额除不尽的累加到最后一个
                else if ($i == count($split_price_list)){
                    $child_order = get_separate_order($order_sp,$value['total_price'],$rate,$order_sp['order_id']);
                    unset($child_order['order_id']);
                    $child_order["shipping_fee"] = $last_amount["shipping_fee"];
                    $child_order["pay_fee"] = $last_amount["pay_fee"];
                    $child_order["surplus"] = $last_amount["surplus"];
                    $child_order["bonus"] = $last_amount["bonus"];
                    $child_order["gift_money"] = $last_amount["gift_money"];
                    $child_order["discount"] = $last_amount["discount"];
                    $child_order["money_paid"] = $last_amount["money_paid"];
                    $child_order["integral_money"] = $last_amount["integral_money"];
                    $child_order["integral"] = $child_order["integral_money"] * 100;

                    $child_order['kjt_pay_tradeno'] = get_paytradeno();
                    if(!empty($child_order['kjt_pay_tradeno'])){
                        //支付流水号更新为成功使用
                        pay_tradeno_used($child_order['kjt_pay_tradeno'],$child_order['order_sn'],1);
                    }
                    $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('order_info'), $child_order, 'INSERT');
                    $child_order["order_id"] = $GLOBALS['db']->insert_id();
                }
                else{
                    $child_order = get_separate_order($order_sp,$value['total_price'],$rate,$order_sp['order_id']);
                    unset($child_order['order_id']);
                    //记录最后一个订单分单金额数组
                    $last_amount["shipping_fee"] -= $child_order["shipping_fee"];
                    $last_amount["pay_fee"] -= $child_order["pay_fee"];
                    $last_amount["surplus"] -= $child_order["surplus"];
                    $last_amount["bonus"] -= $child_order["bonus"];
                    $last_amount["gift_money"] -= $child_order["gift_money"];
                    $last_amount["discount"] -= $child_order["discount"];
                    $last_amount["money_paid"] -= $child_order["money_paid"];
                    $last_amount["integral_money"] -= $child_order["integral_money"];
                    $last_amount["integral"] = $last_amount["integral_money"] * 100;

                    $child_order['kjt_pay_tradeno'] = get_paytradeno();
                    if(!empty($child_order['kjt_pay_tradeno'])){
                        //支付流水号更新为成功使用
                        pay_tradeno_used($child_order['kjt_pay_tradeno'],$child_order['order_sn'],1);
                    }
                    $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('order_info'), $child_order, 'INSERT');
                    $child_order["order_id"] = $GLOBALS['db']->insert_id();
                }
                $source = get_shipping_source_bygid($value['goods_id']);
                if($source == 1){
                    //创建海淘任务
                    create_kjt_task($child_order['order_sn']);
                }

                //添加到支付流水表
                $insert_order_info = order_info($child_order["order_id"]);
                $pay_statistics['order_id']=$child_order['order_id'];
                $pay_statistics['surplus']=$child_order['surplus'];
                $pay_statistics['bonus']=$child_order['bonus'];
                $pay_statistics['gift']=$child_order['gift_money'];
                $pay_statistics['pay_fee']=$child_order['pay_fee'];
                $pay_statistics['order_amount']=$insert_order_info['total_fee'];
                $pay_statistics['pay_id']=$insert_order_info['pay_id'];
                $pay_statistics['pay_transaction']=$insert_order_info['pay_trade_no'];
                $flag = exist_pay_statistics($child_order["order_id"]);
                if($flag){
                    $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('pay_statistics'), $pay_statistics, 'UPDATE', "order_id = '$child_order[order_id]'");
                }
                else{
                    $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('pay_statistics'), $pay_statistics, 'INSERT');
                }

                $order_goods['order_id'] = $child_order["order_id"];
                $order_goods['goods_number'] = $value['goods_number'];
                array_push($split_order_goods[$value['goods_id']],$order_goods);

                $i++;
            }

            //更新商品订单表
            foreach($goods_list as $goods){
                $count = count($split_order_goods[$goods['goods_id']]);
                if($count == 1){
                    update_ordergoods($goods['rec_id'],$split_order_goods[$goods['goods_id']][0]['order_id']);
                }
                else{
                    for($i = 0;$i < $count;$i++){
                        if($i == 0){
                            update_ordergoods($goods['rec_id'],$split_order_goods[$goods['goods_id']][$i]['order_id'],$split_order_goods[$goods['goods_id']][$i]['goods_number']);
                        }
                        else{
                            unset($goods['rec_id']);
                            $goods['order_id'] = $split_order_goods[$goods['goods_id']][$i]['order_id'];
                            $goods['goods_number'] = $split_order_goods[$goods['goods_id']][$i]['goods_number'];
                            $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('order_goods'), $goods, 'INSERT');
                        }
                    }
                }
            }
        }
    }

    /* 记录log */
    order_action($order['order_sn'], $order['order_status'], $order['shipping_status'], $order['pay_status'], '自动拆单');
}