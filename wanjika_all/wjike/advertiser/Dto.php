<?php
define('IN_ECS', true);
include_once(dirname(__FILE__) . '/../includes/init.php');
include_once(dirname(__FILE__) . '/../includes/lib_order.php');
include_once(dirname(__FILE__) . '/../util/Config.php');
	/**
	 *用户自定义实现对数据库的操作,获取订单的信息
	 **/
class Dto{
	/**
	 * 根据活动id和下单时间查询订单信息 
	 * @param 活动id $campaignId
	 * @param 下单时间 $date
	 * @throws Exception
	 */
	public function getOrderByOrderTime($campaignId,$orderStatTime,$orderEndTime){
	 	if (empty($campaignId) || empty($orderStatTime)||empty($orderEndTime)){
	 		throw new Exception("campaignId ,orderStatTime or orderEndTime is null", 613, "");
	 	}
//		$date1 = date('Y-m-d',$orderStatTime);//转化成时间,到数据库的查询
//		$date2 = date('Y-m-d',$orderEndTime);//转化成时间,到数据库的查询
//        $orderStatTime= strtotime($orderStatTime);
//        $orderEndTime= strtotime($orderEndTime);
        $sql = "SELECT t2.order_id,t2.order_sn,t2.add_time,t2.pay_time,t2.shipping_fee,t2.bonus,t2.order_status,t2.pay_status,t2.pay_name FROM ecs_yqf_cookie AS t1 LEFT JOIN ecs_order_info AS t2 " . " ON t1.order_id=t2.order_id" . " WHERE t2.add_time >='$orderStatTime' AND t2.add_time<='$orderEndTime'";

        $res = $GLOBALS['db']->getAll($sql);
//		$orderlist [] = null;

        $cookie_yqf = urldecode($_COOKIE['yiqifa']);
        $cookie_yqf = explode(':',$cookie_yqf);
        foreach ($res as $key=>$value) {
            $order = new Order();

            $order -> setOrderNo($value['order_sn']);
            $order -> setOrderTime($value['add_time']);  // 设置下单时间
            $order -> setUpdateTime($value['pay_time']); // 设置订单更新时间，如果没有下单时间，要提前对接人提前说明
            $order -> setCampaignId($cookie_yqf[2]);                 // 测试时使用"101"，正式上线之后活动id必须要从数据库里面取
            $order -> setFeedback($cookie_yqf[3]);
            $order -> setFare($value['shipping_fee']);                        // 设置邮费
            $order -> setFavorable($value['bonus']);                   // 设置优惠券

            $orderStatus = new OrderStatus();
            $orderStatus -> setOrderNo($order -> getOrderNo());
            $orderStatus -> setOrderStatus($value['order_status']);             // 设置订单状态
            $orderStatus -> setPaymentStatus($value['pay_status']);   				// 设置支付状态
            $orderStatus -> setPaymentType($value['pay_name']);		// 支付方式

            $order -> setOrderStatus($orderStatus);
            $product = order_goods($value['order_id']);
            foreach($product as $k=>$v)
            {
                $pro = new Product();                           // 设置商品集合1
                //$pro -> setOrderNo($order_yqf-> getOrderNo());     // 设置订单编号，订单编号要上下对应
                $pro -> setProductNo($v['goods_sn']);                   // 设置商品编号
                $pro -> setName($v['goods_name']);                   // 设置商品名称
                $pro -> setCategory($v['is_real']);                    // 设置商品类型

                $order_goods_id= $v['goods_id'];
                $goods_sql="SELECT cat_id FROM " . "ecs_goods" . " WHERE goods_id='$order_goods_id'";
                $order_cat_id = $GLOBALS['db']->getOne($goods_sql);
                $test= get_parent_cats($order_cat_id);
                foreach ($test AS $val)
                {
                    $goods_id=$val['cat_id'];
                }
                switch($goods_id)
                {
                    case 572:
                        $commissiontype='A';
                        break;
                    case 44:
                        $commissiontype='B';
                        break;
                    case 139:
                        $commissiontype='C';
                        break;
                    case 311:
                        $commissiontype='D';
                        break;
                    default:
                        $commissiontype='Z';
                }
                $pro -> setCommissionType($commissiontype);                 // 设置佣金类型，如：普通商品 佣金比例是10%、佣金编号（可自行定义然后通知双方商务）A
                $pro -> setAmount($v['goods_number']);                         // 设置商品数量
                $pro -> setPrice($v['goods_price']);                       // 设置商品价格
                $products[$k]=$pro;
            }
            $order-> setProducts($products);

            $orderlist[$key]=$order;
        }
		//echo json_encode($orderlist);
		
	 	return $orderlist;
	}
	
	/**
	 * 根据活动id和订单更新时间查询订单信息
	 * @param 活动id $campaignId
	 * @param 订单更新时间 $date
	 */
	public function getOrderByUpdateTime($campaignId,$updateStatTime,$updateEndTime){
	 	if (empty($campaignId) || empty($updateStatTime)||empty($updateEndTime)){
	 		throw new Exception("CampaignId or date is null!", 648, "");
	 	}
//        $updateStatTime= strtotime($updateStatTime);
//        $updateEndTime= strtotime($updateEndTime);
        $sql = "SELECT t2.order_id,t2.order_sn,t2.add_time,t2.pay_time,t2.shipping_fee,t2.bonus,t2.order_status,t2.pay_status,t2.pay_name FROM ecs_yqf_cookie AS t1 LEFT JOIN ecs_order_info AS t2 " . " ON t1.order_id=t2.order_id" . " WHERE t2.pay_time >='$updateStatTime' AND t2.pay_time<='$updateEndTime'";

        $res = $GLOBALS['db']->getAll($sql);
        $cookie_yqf = urldecode($_COOKIE['yiqifa']);
        $cookie_yqf = explode(':',$cookie_yqf);
//	 	$orderStatusList [] = null;

        foreach ($res as $key=>$value) {
            $orderStatus = new OrderStatus();
            $orderStatus -> setOrderNo($value['order_sn']);
            $orderStatus -> setUpdateTime($value['pay_time']); // 设置订单更新时间，如果没有下单时间，要提前对接人提前说明
            $orderStatus -> setFeedback($cookie_yqf[3]);
            $orderStatus -> setOrderStatus($value['order_status']);             // 设置订单状态
            $orderStatus -> setPaymentStatus($value['pay_status']);   				// 设置支付状态
            $orderStatus -> setPaymentType($value['pay_name']);		// 支付方式
            $orderStatusList[$key]=$orderStatus;
        }
		//echo json_encode($orderlist);
	 	return $orderStatusList;
	}
	
 }
?>