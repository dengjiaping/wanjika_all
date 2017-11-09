<?php
define('IN_ECS', true);
include_once(dirname(__FILE__) . '/../includes/init.php');
include_once(dirname(__FILE__) . '/../includes/lib_order.php');
include_once(dirname(__FILE__) . '/../util/Config.php');
	/**
	 *�û��Զ���ʵ�ֶ����ݿ�Ĳ���,��ȡ��������Ϣ
	 **/
class Dto{
	/**
	 * ���ݻid���µ�ʱ���ѯ������Ϣ 
	 * @param �id $campaignId
	 * @param �µ�ʱ�� $date
	 * @throws Exception
	 */
	public function getOrderByOrderTime($campaignId,$orderStatTime,$orderEndTime){
	 	if (empty($campaignId) || empty($orderStatTime)||empty($orderEndTime)){
	 		throw new Exception("campaignId ,orderStatTime or orderEndTime is null", 613, "");
	 	}
//		$date1 = date('Y-m-d',$orderStatTime);//ת����ʱ��,�����ݿ�Ĳ�ѯ
//		$date2 = date('Y-m-d',$orderEndTime);//ת����ʱ��,�����ݿ�Ĳ�ѯ
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
            $order -> setOrderTime($value['add_time']);  // �����µ�ʱ��
            $order -> setUpdateTime($value['pay_time']); // ���ö�������ʱ�䣬���û���µ�ʱ�䣬Ҫ��ǰ�Խ�����ǰ˵��
            $order -> setCampaignId($cookie_yqf[2]);                 // ����ʱʹ��"101"����ʽ����֮��id����Ҫ�����ݿ�����ȡ
            $order -> setFeedback($cookie_yqf[3]);
            $order -> setFare($value['shipping_fee']);                        // �����ʷ�
            $order -> setFavorable($value['bonus']);                   // �����Ż�ȯ

            $orderStatus = new OrderStatus();
            $orderStatus -> setOrderNo($order -> getOrderNo());
            $orderStatus -> setOrderStatus($value['order_status']);             // ���ö���״̬
            $orderStatus -> setPaymentStatus($value['pay_status']);   				// ����֧��״̬
            $orderStatus -> setPaymentType($value['pay_name']);		// ֧����ʽ

            $order -> setOrderStatus($orderStatus);
            $product = order_goods($value['order_id']);
            foreach($product as $k=>$v)
            {
                $pro = new Product();                           // ������Ʒ����1
                //$pro -> setOrderNo($order_yqf-> getOrderNo());     // ���ö�����ţ��������Ҫ���¶�Ӧ
                $pro -> setProductNo($v['goods_sn']);                   // ������Ʒ���
                $pro -> setName($v['goods_name']);                   // ������Ʒ����
                $pro -> setCategory($v['is_real']);                    // ������Ʒ����

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
                $pro -> setCommissionType($commissiontype);                 // ����Ӷ�����ͣ��磺��ͨ��Ʒ Ӷ�������10%��Ӷ���ţ������ж���Ȼ��֪ͨ˫������A
                $pro -> setAmount($v['goods_number']);                         // ������Ʒ����
                $pro -> setPrice($v['goods_price']);                       // ������Ʒ�۸�
                $products[$k]=$pro;
            }
            $order-> setProducts($products);

            $orderlist[$key]=$order;
        }
		//echo json_encode($orderlist);
		
	 	return $orderlist;
	}
	
	/**
	 * ���ݻid�Ͷ�������ʱ���ѯ������Ϣ
	 * @param �id $campaignId
	 * @param ��������ʱ�� $date
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
            $orderStatus -> setUpdateTime($value['pay_time']); // ���ö�������ʱ�䣬���û���µ�ʱ�䣬Ҫ��ǰ�Խ�����ǰ˵��
            $orderStatus -> setFeedback($cookie_yqf[3]);
            $orderStatus -> setOrderStatus($value['order_status']);             // ���ö���״̬
            $orderStatus -> setPaymentStatus($value['pay_status']);   				// ����֧��״̬
            $orderStatus -> setPaymentType($value['pay_name']);		// ֧����ʽ
            $orderStatusList[$key]=$orderStatus;
        }
		//echo json_encode($orderlist);
	 	return $orderStatusList;
	}
	
 }
?>