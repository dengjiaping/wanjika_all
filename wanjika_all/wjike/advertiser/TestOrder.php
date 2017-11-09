<?php
header("Content-type:text/html;charset=utf-8");
include_once 'Sender.php';

/**
 * 接口测试类
 * 
 * 正式调用时订单、商品信息必须要严格填写。
 *	这个页面编码格式GBK的，跟配置文件中页面的编码是一致的，所以如果调用这个文件的话
 * @var
 */

      //  $service = new Service();
$a = $_COOKIE['yiqifa'];
$a = urldecode($a);
$a = explode(':',$a);
var_dump($a);exit;
        $order_yqf= new Order();
        //$order_yqf-> setOrderNo($_POST["orderNo"]);      // 设置订单编号
        //$order_yqf-> setOrderNo($_GET["orderNo"]);
        $a = rand(0,999999);
        $b = rand(1,100000);
        $c = rand(0,999999);
    	$orderno = $a+$b.$c;

        $order_yqf-> setOrderNo("000000");
        $order_yqf-> setOrderTime("2015-11-15 10:09:09");  // 设置下单时间
        $order_yqf-> setUpdateTime("2015-11-25 20:09:09"); // 设置订单更新时间，如果没有下单时间，要提前对接人提前说明
        $order_yqf-> setCampaignId("101");                 // 测试时使用"101"，正式上线之后活动id必须要从cookie中获取
        $order_yqf-> setFeedback("NDgwMDB8dGVzdA==");			// 测试时使用"101"，正式上线之后活动id必须要从cookie中获取
        $order_yqf-> setFare("10");                        // 设置邮费
        $order_yqf-> setFavorable("30");                   // 设置优惠券
		$order_yqf-> setFavorableCode("30YHM");
		$order_yqf-> setOrderStatus("active");             // 设置订单状态
        $order_yqf-> setPaymentStatus("1");   				// 设置支付状态
        $order_yqf-> setPaymentType("支付宝");		// 支付方式


        $pro = new Product();                           // 设置商品集合1
        //$pro -> setOrderNo($order_yqf-> getOrderNo());     // 设置订单编号，订单编号要上下对应
        $pro -> setProductNo("1001");                   // 设置商品编号
        $pro -> setName("测试商品6");                   // 设置商品名称
        $pro -> setCategory("asdf");                    // 设置商品类型
        $pro -> setCommissionType("A");                 // 设置佣金类型，如：普通商品 佣金比例是10%、佣金编号（可自行定义然后通知双方商务）A
        $pro -> setAmount("1");                         // 设置商品数量
        $pro -> setPrice("3000");                       // 设置商品价格

        $pro1 = new Product();
       // $pro1 -> setOrderNo($order_yqf-> getOrderNo());
        $pro1 -> setProductNo("1002");
        $pro1 -> setName("测试商品5");
        $pro1 -> setCategory("a");
        $pro1 -> setCommissionType("B");
        $pro1 -> setAmount("3");
        $pro1 -> setPrice("100");

        $pro2 = new Product();
       // $pro2 -> setOrderNo($order_yqf-> getOrderNo());
        $pro2 -> setProductNo("1003");
        $pro2 -> setName("测试商品4");
        $pro2 -> setCategory("2");
        $pro2 -> setCommissionType("B");
        $pro2 -> setAmount("5");
        $pro2 -> setPrice("500");

        $products = array($pro,$pro1,$pro2);    // 实现商品信息集合
		$order_yqf-> setProducts($products);

		$sender = new Sender();
		$sender -> setOrder($order_yqf);
	    $sender -> sendOrder();

?>