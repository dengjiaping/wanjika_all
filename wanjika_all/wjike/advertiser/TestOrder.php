<?php
header("Content-type:text/html;charset=utf-8");
include_once 'Sender.php';

/**
 * �ӿڲ�����
 * 
 * ��ʽ����ʱ��������Ʒ��Ϣ����Ҫ�ϸ���д��
 *	���ҳ������ʽGBK�ģ��������ļ���ҳ��ı�����һ�µģ����������������ļ��Ļ�
 * @var
 */

      //  $service = new Service();
$a = $_COOKIE['yiqifa'];
$a = urldecode($a);
$a = explode(':',$a);
var_dump($a);exit;
        $order_yqf= new Order();
        //$order_yqf-> setOrderNo($_POST["orderNo"]);      // ���ö������
        //$order_yqf-> setOrderNo($_GET["orderNo"]);
        $a = rand(0,999999);
        $b = rand(1,100000);
        $c = rand(0,999999);
    	$orderno = $a+$b.$c;

        $order_yqf-> setOrderNo("000000");
        $order_yqf-> setOrderTime("2015-11-15 10:09:09");  // �����µ�ʱ��
        $order_yqf-> setUpdateTime("2015-11-25 20:09:09"); // ���ö�������ʱ�䣬���û���µ�ʱ�䣬Ҫ��ǰ�Խ�����ǰ˵��
        $order_yqf-> setCampaignId("101");                 // ����ʱʹ��"101"����ʽ����֮��id����Ҫ��cookie�л�ȡ
        $order_yqf-> setFeedback("NDgwMDB8dGVzdA==");			// ����ʱʹ��"101"����ʽ����֮��id����Ҫ��cookie�л�ȡ
        $order_yqf-> setFare("10");                        // �����ʷ�
        $order_yqf-> setFavorable("30");                   // �����Ż�ȯ
		$order_yqf-> setFavorableCode("30YHM");
		$order_yqf-> setOrderStatus("active");             // ���ö���״̬
        $order_yqf-> setPaymentStatus("1");   				// ����֧��״̬
        $order_yqf-> setPaymentType("֧����");		// ֧����ʽ


        $pro = new Product();                           // ������Ʒ����1
        //$pro -> setOrderNo($order_yqf-> getOrderNo());     // ���ö�����ţ��������Ҫ���¶�Ӧ
        $pro -> setProductNo("1001");                   // ������Ʒ���
        $pro -> setName("������Ʒ6");                   // ������Ʒ����
        $pro -> setCategory("asdf");                    // ������Ʒ����
        $pro -> setCommissionType("A");                 // ����Ӷ�����ͣ��磺��ͨ��Ʒ Ӷ�������10%��Ӷ���ţ������ж���Ȼ��֪ͨ˫������A
        $pro -> setAmount("1");                         // ������Ʒ����
        $pro -> setPrice("3000");                       // ������Ʒ�۸�

        $pro1 = new Product();
       // $pro1 -> setOrderNo($order_yqf-> getOrderNo());
        $pro1 -> setProductNo("1002");
        $pro1 -> setName("������Ʒ5");
        $pro1 -> setCategory("a");
        $pro1 -> setCommissionType("B");
        $pro1 -> setAmount("3");
        $pro1 -> setPrice("100");

        $pro2 = new Product();
       // $pro2 -> setOrderNo($order_yqf-> getOrderNo());
        $pro2 -> setProductNo("1003");
        $pro2 -> setName("������Ʒ4");
        $pro2 -> setCategory("2");
        $pro2 -> setCommissionType("B");
        $pro2 -> setAmount("5");
        $pro2 -> setPrice("500");

        $products = array($pro,$pro1,$pro2);    // ʵ����Ʒ��Ϣ����
		$order_yqf-> setProducts($products);

		$sender = new Sender();
		$sender -> setOrder($order_yqf);
	    $sender -> sendOrder();

?>