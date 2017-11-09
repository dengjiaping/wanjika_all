<?php

/**
 * ECSHOP 商品页
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liuhui $
 * $Id: order.php 15013 2008-10-23 09:31:42Z liuhui $
*/

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require(ROOT_PATH . 'includes/lib_order.php');
require(ROOT_PATH . 'includes/lib_payment.php');

require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/shopping_flow.php');
	$flow_type = 0;
	$_LANG['gram'] = '克';
	$_LANG['kilogram'] = '千克';
	$tips = '您的订单已经成功提交，为了能尽快发货，请您立即支付订单';
if ($_SESSION['user_id'] > 0)
{
	$smarty->assign('user_name', $_SESSION['user_name']);
    $smarty->assign('user_id', $_SESSION['user_id']);
}
else{
//    $smarty->assign('footer', get_footer());
//    $smarty->display('login.html');
//    exit;
    ecs_header("Location: user.php?act=login\n");
    exit;
}
if($_REQUEST['act'] == 'order_lise')
{
    /* 检查购物车中是否有勾选商品 */
    $sql = "SELECT COUNT(*) FROM " . $ecs->table('cart') .
        " WHERE session_id = '" . SESS_ID . "' " .
        "AND parent_id = 0 AND is_gift = 0 AND is_selected=1 AND rec_type = '$flow_type'";

    if ($db->getOne($sql) == 0)
    {
        $tips = '您的购物车中没有选中商品';
    }
    $_SESSION['flow_order']['bonus_id']='';
    $_SESSION['flow_order']['gift_id']='';
//    $_SESSION['flow_order']['bonus_sn']='';
    $bonus_id = empty($_REQUEST['bonus_id']) ? 0 : intval($_REQUEST['bonus_id']);
    $gift_id = empty($_REQUEST['gift_id']) ? 0 : intval($_REQUEST['gift_id']);
//    $bonus_sn = empty($_REQUEST['bonus_sn']) ? '' : trim($_REQUEST['bonus_sn']);
    $_SESSION['flow_order']['bonus_id']=$bonus_id;
    $_SESSION['flow_order']['gift_id']=$gift_id;
//    $_SESSION['flow_order']['bonus_sn']=$bonus_sn;
    $smarty->assign('bonus_id', $bonus_id);
    $smarty->assign('gift_id', $gift_id);
//    $smarty->assign('bonus_sn', $bonus_sn);

    $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
    $sql = "SELECT gs.is_overseas FROM " . $ecs->table('cart') .
        " AS c LEFT JOIN ". $ecs->table('goods') ." AS g ON c.goods_id=g.goods_id ".
        " LEFT JOIN ". $ecs->table('goods_supplier') . " AS gs ON g.supplier_id=gs.type_id " .
        " WHERE session_id = '" . SESS_ID . "' " .
        "AND parent_id = 0 AND is_gift = 0 AND rec_type = '$flow_type' AND is_selected = 1";
    $re = $db->getAll($sql);
    $flow_overseas = 0;
    foreach($re AS $value)
    {
        if($value['is_overseas']>0)
        {
            $flow_overseas=$value['is_overseas'];
        }
    }
    $smarty->assign('flow_overseas', $flow_overseas);
    $is_membersgoods = goods_extensioncode($_SESSION['goods_id']) == 'goods_members'? 1 : 0;
    $smarty->assign('is_membersgoods', $is_membersgoods);
    if($is_membersgoods == 0){
        //判断是否选中收货地址
//        if($_POST['add_id']>0)
//        {
//            $addid =$_POST['add_id'];
//            $defaultsql = "SELECT *".
//                " FROM " . $GLOBALS['ecs']->table('user_address') .
//                " WHERE address_id='$addid'";
//
//            $default_id = $GLOBALS['db']->getRow($defaultsql);
//            $_POST =$default_id;
//        }
//            //19:21 2013-7-16
//            $_POST['country'] = 1;//默认国家
//            $_POST['email'] = empty($_SESSION['email']) ? '' : compile_str($_SESSION['email']);
//            if(empty($_POST['province']) || empty($_POST['city'])){
//                echo '配送区域不可为空！';
//                exit;
//            }
//            if(empty($_POST['consignee']))
//            {
//                echo '收货人姓名不可为空！';
//                exit;
//            }
//
//            if(empty($_POST['address']))
//            {
//                echo '详细地址不可为空！';
//                exit;
//            }
//            if(empty($_POST['tel']))
//            {
//                echo '电话/手机不可为空！';
//                exit;
//            }
//            /*
//             * 保存收货人信息
//             */
//            $consignee = array(
//                'address_id'	=> empty($_POST['address_id']) ? 0  : intval($_POST['address_id']),
//                'consignee'	 => empty($_POST['consignee'])  ? '' : compile_str(trim($_POST['consignee'])),
//                'country'	   => empty($_POST['country'])	? '' : intval($_POST['country']),
//                'province'	  => empty($_POST['province'])   ? '' : intval($_POST['province']),
//                'city'		  => empty($_POST['city'])	   ? '' : intval($_POST['city']),
//                'district'	  => empty($_POST['district'])   ? '' : intval($_POST['district']),
//                'email'		 => empty($_POST['email'])	  ? '' : compile_str($_POST['email']),
//                'address'	   => empty($_POST['address'])	? '' : compile_str($_POST['address']),
//                'id_card'	   => empty($_POST['id_card'])	? '' : compile_str($_POST['id_card']),
//                'zipcode'	   => empty($_POST['zipcode'])	? '' : compile_str(make_semiangle(trim($_POST['zipcode']))),
//                'tel'		   => empty($_POST['tel'])		? '' : compile_str(make_semiangle(trim($_POST['tel']))),
//                'mobile'		=> empty($_POST['mobile'])	 ? '' : compile_str(make_semiangle(trim($_POST['mobile']))),
//                'sign_building' => empty($_POST['sign_building']) ? '' : compile_str($_POST['sign_building']),
//                'best_time'	 => empty($_POST['best_time'])  ? '' : compile_str($_POST['best_time']),
//            );
//            if ($_SESSION['user_id'] > 0)
//            {
//                include_once(ROOT_PATH . 'includes/lib_transaction.php');
//
//                /* 如果用户已经登录，则保存收货人信息 */
//                $consignee['user_id'] = $_SESSION['user_id'];
//                save_consignee($consignee, true);
//            }

        if(!empty($_REQUEST['address_id']))
        {
            $consignee = get_consignee_byid($_REQUEST['address_id']);
        }
        else{
            $consignee = get_consignee($_SESSION['user_id']);
        }
        /* 取得区域名 */
        $sql = "SELECT concat(IFNULL(p.region_name, ''), " .
            "'  ', IFNULL(t.region_name, ''), '  ', IFNULL(d.region_name, '')) AS region " .
            "FROM " . $ecs->table('user_address') . " AS u " .
            "LEFT JOIN " . $ecs->table('region') . " AS p ON u.province = p.region_id " .
            "LEFT JOIN " . $ecs->table('region') . " AS t ON u.city = t.region_id " .
            "LEFT JOIN " . $ecs->table('region') . " AS d ON u.district = d.region_id " .
            "WHERE u.address_id = '$consignee[address_id]'";
        $region = $db->getOne($sql);
        if(!empty($region)){
            $consignee["address_name"] = $region.' '.$consignee["address"];
        }
        /* 保存到session */
        $_SESSION['flow_consignee'] = stripslashes_deep($consignee);

        //14:07 2013-07-17
        $where = "1";
        if($consignee['city']){
            $where = " region_id = '$consignee[city]'";
        }
        if($consignee['district']){
            $where .= " OR region_id = '$consignee[district]'";
        }
        $sql = 'SELECT region_name FROM ' . $GLOBALS['ecs']->table('region') . " WHERE ".$where;
        $rnarr = $db->GetAll($sql);
//        @$consignee['address'] = $rnarr[0]['region_name'].' '.$rnarr[1]['region_name'].' '.$consignee['address'];
        //end

//        $_SESSION['flow_consignee'] = $consignee;
        $smarty->assign('consignee', $consignee);
    }

	/* 对商品信息赋值 */
	$cart_goods = cart_goods($flow_type); // 取得商品列表，计算合计
    $overseas=0;
    foreach($cart_goods AS $value)
    {
        if($value['is_overseas'])
        {
            $overseas=1;break;
        }
    }

	$smarty->assign('goods_list', $cart_goods);
	$smarty->assign('is_overseas', $overseas);


    /* 如果使用红包，取得用户可以使用的红包及用户选择的红包 */
    if ((!isset($_CFG['use_bonus']) || $_CFG['use_bonus'] == '1')
        && ($flow_type != CART_GROUP_BUY_GOODS && $flow_type != CART_EXCHANGE_GOODS))
    {
        // 取得用户可用红包
        //$user_bonus = user_bonus($_SESSION['user_id'], $total['goods_price']);
        $user_bonus = user_available_bonus($_SESSION['user_id'], $cart_goods);
        if (!empty($user_bonus))
        {
            foreach ($user_bonus AS $key => $val)
            {
                $user_bonus[$key]['bonus_money_formated'] = price_format($val['type_money'], false);
            }
            $smarty->assign('bonus_list', $user_bonus);
            foreach($user_bonus as $value)
            {
                if($_REQUEST['bonus_id'] ==$value['bonus_id'])
                {
                    $bonus_type=true;
                    $smarty->assign('bonus_type_name', '已抵用'.$value['bonus_money_formated']);
                }
            }
            if(!$bonus_type)
            {
                $smarty->assign('bonus_type_name', count($user_bonus).'张未使用');
            }
        }
        else
        {
            $smarty->assign('bonus_type_name', count($user_bonus).'张未使用');
        }
        // 能使用红包
        $smarty->assign('allow_use_bonus', 1);
    }

    /* 如果使用礼品卡，取得用户可以使用的礼品卡及用户选择的礼品卡 */
    if ((!isset($_CFG['use_gift']) || $_CFG['use_gift'] == '1')
        && ($flow_type != CART_GROUP_BUY_GOODS && $flow_type != CART_EXCHANGE_GOODS))
    {
        // 取得用户可用礼品卡
        $user_gift = user_gift($_SESSION['user_id'], $total['goods_price']);
        if (!empty($user_gift))
        {
            foreach ($user_gift AS $key => $val)
            {
                $user_gift[$key]['gift_money_formated'] = price_format($val['leave_money'], false);
            }
            $smarty->assign('gift_list', $user_gift);
            foreach($user_gift as $value)
            {
                if($_REQUEST['gift_id'] ==$value['card_id'])
                {
                    $gift_type=true;
                    $smarty->assign('gift_type_name', '已抵用'.$value['gift_money_formated']);
                }
            }
            if(!$gift_type)
            {
                $smarty->assign('gift_type_name', count($user_gift).'张未使用');
            }
        }
        else
        {
            $smarty->assign('gift_type_name', count($user_gift).'张未使用');
        }

        // 能使用礼品卡
        $smarty->assign('allow_use_gift', 1);
    }
	/*
	 * 取得订单信息
	 */
	$order = flow_order_info();
	$smarty->assign('order', $order);

	$_LANG['shopping_money'] = '购物金额小计 %s';
	$_LANG['than_market_price'] = '比市场价 %s 节省了 %s (%s)';
	/*
	 * 计算订单的费用
	 */
	$total = order_fee($order, $cart_goods, $consignee);
	$smarty->assign('shopping_money', sprintf($_LANG['shopping_money'], $total['formated_goods_price']));
	$smarty->assign('market_price_desc', sprintf($_LANG['than_market_price'], $total['formated_market_price'], $total['formated_saving'], $total['save_rate']));

	$smarty->assign('total', $total);

	/* 取得配送列表 */
	$region			= array($consignee['country'], $consignee['province'], $consignee['city'], $consignee['district']);
	$shipping_list	 = available_shipping_list($region);
	$cart_weight_price = cart_weight_price($flow_type);
	$insure_disabled   = true;
	$cod_disabled	  = true;

	// 查看购物车中是否全为免运费商品，若是则把运费赋为零
	$sql = 'SELECT count(*) FROM ' . $ecs->table('cart') . " WHERE `session_id` = '" . SESS_ID. "' AND `extension_code` != 'package_buy' AND `is_shipping` = 0";
	$shipping_count = $db->getOne($sql);

	foreach ($shipping_list AS $key => $val)
	{
		$shipping_cfg = unserialize_config($val['configure']);
		$shipping_fee = ($shipping_count == 0 AND $cart_weight_price['free_shipping'] == 1) ? 0 : shipping_fee($val['shipping_code'], unserialize($val['configure']),
		$cart_weight_price['weight'], $cart_weight_price['amount'], $cart_weight_price['number']);

		$shipping_list[$key]['format_shipping_fee'] = price_format($shipping_fee, false);
		$shipping_list[$key]['shipping_fee']		= $shipping_fee;
		$shipping_list[$key]['free_money']		  = price_format($shipping_cfg['free_money'], false);
		$shipping_list[$key]['insure_formated']	 = strpos($val['insure'], '%') === false ?
			price_format($val['insure'], false) : $val['insure'];

	}

	$smarty->assign('shipping_list',   $shipping_list);
	$smarty->assign('insure_disabled', $insure_disabled);
	$smarty->assign('cod_disabled',	$cod_disabled);
	
		/* 取得支付列表 */
	if ($order['shipping_id'] == 0)
	{
		$cod		= true;
		$cod_fee	= 0;
	}
	else
	{
		$shipping = shipping_info($order['shipping_id']);
		$cod = $shipping['support_cod'];

		if ($cod)
		{
			/* 如果是团购，且保证金大于0，不能使用货到付款 */
			if ($flow_type == CART_GROUP_BUY_GOODS)
			{
				$group_buy_id = $_SESSION['extension_id'];
				if ($group_buy_id <= 0)
				{
					show_message('error group_buy_id');
				}
				$group_buy = group_buy_info($group_buy_id);
				if (empty($group_buy))
				{
					show_message('group buy not exists: ' . $group_buy_id);
				}

				if ($group_buy['deposit'] > 0)
				{
					$cod = false;
					$cod_fee = 0;

					/* 赋值保证金 */
					$smarty->assign('gb_deposit', $group_buy['deposit']);
				}
			}

			if ($cod)
			{
				$shipping_area_info = shipping_area_info($order['shipping_id'], $region);
				$cod_fee			= $shipping_area_info['pay_fee'];
			}
		}
		else
		{
			$cod_fee = 0;
		}
	}

	// 给货到付款的手续费加<span id>，以便改变配送的时候动态显示
	$payment_list = available_payment_list(1, $cod_fee);
    $bonus_payment_list = array();
    $bonus = bonus_info(intval($_REQUEST['bonus_id']));
    $pay_ids = explode(',',$bonus['pay_ids']);
	if(isset($payment_list))
	{
		foreach ($payment_list as $key => $payment)
		{
			if ($payment['is_cod'] == '1')
			{
				$payment_list[$key]['format_pay_fee'] = '<span id="ECS_CODFEE">' . $payment['format_pay_fee'] . '</span>';
			}
			/* 如果有易宝神州行支付 如果订单金额大于300 则不显示 */
			if ($payment['pay_code'] == 'yeepayszx' && $total['amount'] > 300)
			{
				unset($payment_list[$key]);
			}
			/* 如果有余额支付 */
			if ($payment['pay_code'] == 'balance')
			{
				/* 如果未登录，不显示 */
				if ($_SESSION['user_id'] == 0)
				{
					unset($payment_list[$key]);
				}
				else
				{
					if ($_SESSION['flow_order']['pay_id'] == $payment['pay_id'])
					{
						$smarty->assign('disable_surplus', 1);
					}
				}
			}
            //过滤掉红包限制的支付
            foreach($pay_ids AS $v)
            {
                if($payment['pay_id'] == $v)
                {
                    unset($payment_list[$key]);
                }
            }
		}
	}
    //wap端过滤掉微信支付，微信端只留微信支付
    foreach ($payment_list as $key=>$val)
    {
        if(is_weixin()){
            if ($val['pay_code'] != 'weixinpay')
            {
                unset($payment_list[$key]);
            }
        }
        else{
            if ($val['pay_code'] != 'alipay' && $val['pay_code'] != 'guomeipay')
            {
                unset($payment_list[$key]);
            }
        }
    }

    $str = "";
    if($_REQUEST['bonus_id'] > 0)
    {
        $i = 0;
        foreach($payment_list AS $value)
        {
            $str .= $i == 0 ? ('(不支持' . $value['pay_name']) : (',' . $value['pay_name']);
            $i++;
        }
        if($i > 0)
        {
            $str .= ')';
        }
    }

	$smarty->assign('str', $str);
	$smarty->assign('payment_list', $payment_list);

	$user_info = user_info($_SESSION['user_id']);

	/* 如果使用余额，取得用户余额 */
	if ((!isset($_CFG['use_surplus']) || $_CFG['use_surplus'] == '1')
		&& $_SESSION['user_id'] > 0
		&& $user_info['user_money'] > 0)
	{
		// 能使用余额
		$smarty->assign('allow_use_surplus', 1);
		$smarty->assign('your_surplus', $user_info['user_money']);
	}
    /* 如果使用积分，取得用户可用积分及本订单最多可以使用的积分 */
    if ((!isset($_CFG['use_integral']) || $_CFG['use_integral'] == '1')
        && $_SESSION['user_id'] > 0
        && $user_info['pay_points'] > 0
        && ($flow_type != CART_GROUP_BUY_GOODS && $flow_type != CART_EXCHANGE_GOODS))
    {
        // 能使用积分
        $smarty->assign('allow_use_integral', 1);
        //最大积分按订单金额的20%金额
        $order_max_integral =  $total['total_order_amount'] * 20;
        if($user_info['pay_points'] < $order_max_integral)
        {
            $order_max_integral = $user_info['pay_points'];
        }
        if($user_info['pay_points'] < 1000)
        {
            $order_max_integral = 0;
        }
        $smarty->assign('order_max_integral',$order_max_integral );  // 可用积分
        $smarty->assign('your_integral',      $user_info['pay_points']); // 用户积分
    }
    if($flow_type == CART_EXCHANGE_GOODS)
    {
        $smarty->assign('your_integral',      $user_info['pay_points']); // 用户积分
    }
	/* 保存 session */
	$_SESSION['flow_order'] = $order;

	$smarty->assign('footer', get_footer());
	$smarty->display('order.html');
	exit;
}
elseif ($_REQUEST['act'] == 'select_shipping')
{
    /*------------------------------------------------------ */
    //-- 改变配送方式
    /*------------------------------------------------------ */
    include_once('includes/cls_json.php');
    $json = new JSON;
    $result = array('error' => '', 'content' => '', 'need_insure' => 0);

    /* 取得购物类型 */
    $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;

    /* 获得收货人信息 */
    $consignee = get_consignee($_SESSION['user_id']);

    /* 对商品信息赋值 */

    $cart_goods = cart_goods($flow_type); // 取得商品列表，计算合计

    if (empty($cart_goods))
    {
        $result['error'] = '您的购物车中没有商品！';
    }
    else
    {
        /* 取得购物流程设置 */
        $smarty->assign('config', $_CFG);

        /* 取得订单信息 */
        $order = flow_order_info();

        $order['shipping_id'] = intval($_REQUEST['shipping']);
        $regions = array($consignee['country'], $consignee['province'], $consignee['city'], $consignee['district']);
        $shipping_info = shipping_area_info($order['shipping_id'], $regions);

        /* 计算订单的费用 */
        $total = order_fee($order, $cart_goods, $consignee);
        $smarty->assign('total', $total);

        /* 取得可以得到的积分和红包 */
        $smarty->assign('total_integral', cart_amount(false, $flow_type) - $total['bonus'] - $total['integral_money']);
        $smarty->assign('total_bonus',    price_format(get_total_bonus(), false));

        /* 团购标志 */
        if ($flow_type == CART_GROUP_BUY_GOODS)
        {
            $smarty->assign('is_group_buy', 1);
        }

        $result['cod_fee']     = $shipping_info['pay_fee'];
        if (strpos($result['cod_fee'], '%') === false)
        {
            $result['cod_fee'] = price_format($result['cod_fee'], false);
        }
        $result['need_insure'] = ($shipping_info['insure'] > 0 && !empty($order['need_insure'])) ? 1 : 0;
        $result['content']     = $smarty->fetch('order_total.html');
    }

    echo $json->encode($result);
    exit;
}
/* 验证红包序列号 */
elseif ($_REQUEST['step'] == 'validate_bonus')
{
    require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/shopping_flow.php');
    require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/user.php');
    $bonus_sn = trim($_REQUEST['bonus_sn']);
    if (is_numeric($bonus_sn))
    {
        $bonus = bonus_info(0, $bonus_sn);
    }
    else
    {
        $bonus = array();
    }

//    if (empty($bonus) || $bonus['user_id'] > 0 || $bonus['order_id'] > 0)
//    {
//        die($_LANG['bonus_sn_error']);
//    }
//    if ($bonus['min_goods_amount'] > cart_amount())
//    {
//        die(sprintf($_LANG['bonus_min_amount_error'], price_format($bonus['min_goods_amount'], false)));
//    }
//    die(sprintf($_LANG['bonus_is_ok'], price_format($bonus['type_money'], false)));
    $bonus_kill = price_format($bonus['type_money'], false);

    include_once('includes/cls_json.php');
    $result = array('error' => '', 'content' => '');

    /* 取得购物类型 */
    $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
    $is_real = true;
    if(goods_extensioncode($_SESSION['goods_id']) == 'goods_members'){
        $is_real = false;
    }
    if($is_real){
        /* 获得收货人信息 */
        $consignee = get_consignee($_SESSION['user_id']);

        if (!check_consignee_info($consignee, $flow_type))
        {
            $result['error'] = '收货人信息不能为空';
        }
    }
    /* 对商品信息赋值 */
    $cart_goods = cart_goods($flow_type); // 取得商品列表，计算合计

    if (empty($cart_goods))
    {
        $result['error'] = $_LANG['no_goods_in_cart'];
    }
    else
    {
        /* 取得购物流程设置 */
        $smarty->assign('config', $_CFG);

        /* 取得订单信息 */
        $order = flow_order_info();


        if (((!empty($bonus) && $bonus['user_id'] == $_SESSION['user_id']) || ($bonus['type_money'] > 0 && empty($bonus['user_id']))) && $bonus['order_id'] <= 0)
        {
            //$order['bonus_kill'] = $bonus['type_money'];
            $now = gmtime();
            if ($now > $bonus['use_end_datetime'])
            {
                $order['bonus_id'] = '';
                $result['error']=$_LANG['bonus_use_expire'];
            }
            else
            {
                $order['bonus_id'] = $bonus['bonus_id'];
                $order['bonus_sn'] = $bonus_sn;
            }
        }
        else
        {
            if($bonus['user_id']==$_SESSION['user_id'])
            {
                $order['bonus_id'] = '';
                $result['error'] = $_LANG['bonus_is_used'];
            }
            elseif($bonus['user_id']>0)
            {
                $order['bonus_id'] = '';
                $result['error'] = $_LANG['bonus_is_used_by_other'];
            }
            else
            {
                //$order['bonus_kill'] = 0;
                $order['bonus_id'] = '';
                $result['error'] = $_LANG['bonus_not_exist'];
            }
        }
        /* 计算订单的费用 */
        $total = order_fee($order, $cart_goods, $consignee);

        if($total['goods_price']<$bonus['min_goods_amount'])
        {
            $order['bonus_id'] = '';
            /* 重新计算订单 */
            $total = order_fee($order, $cart_goods, $consignee);
            $result['error'] = sprintf($_LANG['bonus_min_amount_error'], price_format($bonus['min_goods_amount'], false));
        }

        $smarty->assign('total', $total);

        /* 团购标志 */
        if ($flow_type == CART_GROUP_BUY_GOODS)
        {
            $smarty->assign('is_group_buy', 1);
        }

        $result['content'] = $smarty->fetch('/order_total.html');
//        $result['content'] = $smarty->fetch('library/order_total.lbi');
    }
    $json = new JSON();
    die($json->encode($result));
}
elseif ($_REQUEST['act'] == 'change_bonus')
{
    /*------------------------------------------------------ */
    //-- 改变红包
    /*------------------------------------------------------ */
    include_once('includes/cls_json.php');
    $result = array('error' => '', 'content' => '');

    /* 取得购物类型 */
    $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;

    /* 获得收货人信息 */
    $consignee = get_consignee($_SESSION['user_id']);

    /* 对商品信息赋值 */
    $cart_goods = cart_goods($flow_type); // 取得商品列表，计算合计

    if (empty($cart_goods) || !check_consignee_info($consignee, $flow_type))
    {
        $result['error'] = $_LANG['no_goods_in_cart'];
    }
    else
    {
        /* 取得购物流程设置 */
        $smarty->assign('config', $_CFG);

        /* 取得订单信息 */
        $order = flow_order_info();

        $bonus = bonus_info(intval($_GET['bonus']));

        if ((!empty($bonus) && $bonus['user_id'] == $_SESSION['user_id']) || $_GET['bonus'] == 0)
        {
            $order['bonus_id'] = intval($_GET['bonus']);
        }
        else
        {
            $order['bonus_id'] = 0;
            $result['error'] = $_LANG['invalid_bonus'];
        }
        $gift = gift_info(intval($_GET['gift']));
        if ((!empty($gift) && $gift['member_id'] == $_SESSION['user_id']) || $_GET['gift'] == 0)
        {
            $order['gift_id'] = intval($_GET['gift']);
        }
        else
        {
            $order['gift_id'] = 0;
            $result['error'] = $_LANG['invalid_gift'];
        }
        /* 计算订单的费用 */
        $total = order_fee($order, $cart_goods, $consignee);

        $smarty->assign('total', $total);

        /* 团购标志 */
        if ($flow_type == CART_GROUP_BUY_GOODS)
        {
            $smarty->assign('is_group_buy', 1);
        }

        $result['content'] = $smarty->fetch('/order_total.html');
//        $result['content'] = $smarty->fetch('library/order_total.lbi');
    }

    $json = new JSON();
    die($json->encode($result));
}
elseif ($_REQUEST['act'] == 'select_insure')
{
    /*------------------------------------------------------ */
    //-- 选定/取消配送的保价
    /*------------------------------------------------------ */

    include_once('includes/cls_json.php');
    $json = new JSON;
    $result = array('error' => '', 'content' => '', 'need_insure' => 0);

    /* 取得购物类型 */
    $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;

    /* 获得收货人信息 */
    $consignee = get_consignee($_SESSION['user_id']);

    /* 对商品信息赋值 */
    $cart_goods = cart_goods($flow_type); // 取得商品列表，计算合计

    if (empty($cart_goods))
    {
        $result['error'] = '您的购物车中没有商品！';
    }
    else
    {
        /* 取得购物流程设置 */
        $smarty->assign('config', $_CFG);

        /* 取得订单信息 */
        $order = flow_order_info();

        $order['need_insure'] = intval($_REQUEST['insure']);

        /* 保存 session */
        $_SESSION['flow_order'] = $order;

        /* 计算订单的费用 */
        $total = order_fee($order, $cart_goods, $consignee);
        $smarty->assign('total', $total);

        /* 取得可以得到的积分和红包 */
        $smarty->assign('total_integral', cart_amount(false, $flow_type) - $total['bonus'] - $total['integral_money']);
        $smarty->assign('total_bonus',    price_format(get_total_bonus(), false));

        /* 团购标志 */
        if ($flow_type == CART_GROUP_BUY_GOODS)
        {
            $smarty->assign('is_group_buy', 1);
        }

        $result['content'] = $smarty->fetch('/order_total.html');
    }

    echo $json->encode($result);
    exit;
}
elseif ($_REQUEST['act'] == 'select_payment')
{
    /*------------------------------------------------------ */
    //-- 改变支付方式
    /*------------------------------------------------------ */

    include_once('includes/cls_json.php');
    $json = new JSON;
    $result = array('error' => '', 'content' => '', 'need_insure' => 0, 'payment' => 1);

    /* 取得购物类型 */
    $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;

    /* 获得收货人信息 */
    $consignee = get_consignee($_SESSION['user_id']);

    /* 对商品信息赋值 */
    $cart_goods = cart_goods($flow_type); // 取得商品列表，计算合计

    if (empty($cart_goods))
    {
        $result['error'] = '您的购物车中没有商品！';
    }
    else
    {
        /* 取得购物流程设置 */
        $smarty->assign('config', $_CFG);

        /* 取得订单信息 */
        $order = flow_order_info();

        $order['pay_id'] = intval($_REQUEST['payment']);
        $payment_info = payment_info($order['pay_id']);
        $result['pay_code'] = $payment_info['pay_code'];

        /* 保存 session */
        $_SESSION['flow_order'] = $order;

        /* 计算订单的费用 */
        $total = order_fee($order, $cart_goods, $consignee);
        $smarty->assign('total', $total);

        /* 取得可以得到的积分和红包 */
        $smarty->assign('total_integral', cart_amount(false, $flow_type) - $total['bonus'] - $total['integral_money']);
        $smarty->assign('total_bonus',    price_format(get_total_bonus(), false));

        /* 团购标志 */
        if ($flow_type == CART_GROUP_BUY_GOODS)
        {
            $smarty->assign('is_group_buy', 1);
        }

        $result['content'] = $smarty->fetch('order_total.html');
    }

    echo $json->encode($result);
    exit;
}
elseif ($_REQUEST['act'] == 'change_surplus')
{
    /*------------------------------------------------------ */
    //-- 改变余额
    /*------------------------------------------------------ */
    include_once('includes/cls_json.php');

    $surplus   = floatval($_GET['surplus']);
    $user_info = user_info($_SESSION['user_id']);
    if ($user_info['user_money'] + $user_info['credit_line'] < $surplus)
    {
        $result['error'] = '您的购物车中没有商品！';
    }
    else
    {
        /* 取得购物类型 */
        $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;

        /* 取得购物流程设置 */
        $smarty->assign('config', $_CFG);

        /* 获得收货人信息 */
        $consignee = get_consignee($_SESSION['user_id']);

        /* 对商品信息赋值 */
        $cart_goods = cart_goods($flow_type); // 取得商品列表，计算合计

        if (empty($cart_goods))
        {
            $result['error'] = '您的购物车中没有商品！';
        }
        else
        {
            /* 取得订单信息 */
            $order = flow_order_info();
            $order['surplus'] = $surplus;

            /* 计算订单的费用 */
            $total = order_fee($order, $cart_goods, $consignee);
            $smarty->assign('total', $total);

            /* 团购标志 */
            if ($flow_type == CART_GROUP_BUY_GOODS)
            {
                $smarty->assign('is_group_buy', 1);
            }

            $result['content'] = $smarty->fetch('order_total.html');
        }
    }

    $json = new JSON();
    die($json->encode($result));
}

elseif ($_REQUEST['act'] == 'change_integral')
{
    /*------------------------------------------------------ */
    //-- 改变积分
    /*------------------------------------------------------ */
    include_once('includes/cls_json.php');

    $points    = floatval($_GET['points']);
    $is_real_amount    = floatval($_GET['amount']);
    $user_info = user_info($_SESSION['user_id']);

    /* 取得订单信息 */
    $order = flow_order_info();

    //最大积分按订单金额的20%金额
//    $order_max_integral = flow_available_points();
    $order_max_integral =  $is_real_amount * 20;
    if($user_info['pay_points'] < $order_max_integral)
    {
        $order_max_integral = $user_info['pay_points'];
    }
    if($user_info['pay_points'] < 1000)
    {
        $order_max_integral = 0;
    }
    $flow_points = $order_max_integral;  // 该订单允许使用的积分
    $user_points = $user_info['pay_points']; // 用户的积分总数

    if ($points > $user_points)
    {
        $result['error'] = $_LANG['integral_not_enough'];
    }
    elseif ($points > $flow_points)
    {
        $result['error'] = sprintf($_LANG['integral_too_much'], $flow_points);
    }
    else
    {
        /* 取得购物类型 */
        $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;

        $order['integral'] = $points;
        $is_real = true;
        if(goods_extensioncode($_SESSION['goods_id']) == 'goods_members'){
            $is_real = false;
        }
        if($is_real){
            /* 获得收货人信息 */
            $consignee = get_consignee($_SESSION['user_id']);

            if (!check_consignee_info($consignee, $flow_type))
            {
                $result['error'] = '收货人信息不能为空';
            }
        }
        /* 对商品信息赋值 */
        $cart_goods = cart_goods($flow_type); // 取得商品列表，计算合计

        if (empty($cart_goods))
        {
            $result['error'] = $_LANG['no_goods_in_cart'];
        }
        else
        {
            /* 计算订单的费用 */
            $total = order_fee($order, $cart_goods, $consignee);
            $smarty->assign('total',  $total);
            $smarty->assign('config', $_CFG);

            /* 团购标志 */
            if ($flow_type == CART_GROUP_BUY_GOODS)
            {
                $smarty->assign('is_group_buy', 1);
            }

            $result['content'] = $smarty->fetch('order_total.html');
            $result['error'] = '';
        }
    }

    $json = new JSON();
    die($json->encode($result));
}
elseif($_REQUEST['act'] = 'done')
{
	/*------------------------------------------------------ */
	//-- 完成所有订单操作，提交到数据库
	/*------------------------------------------------------ */

	include_once('includes/lib_clips.php');

    //屏蔽pc微信客户端
    if(is_weixin() && !is_mobile_source())
    {
        show_message('暂不支持PC微信客户端下单');
    }
	/* 检查购物车中是否有商品 */
	$sql = "SELECT COUNT(*) FROM " . $ecs->table('cart') .
		" WHERE session_id = '" . SESS_ID . "' " .
		"AND parent_id = 0 AND is_gift = 0 AND rec_type = '$flow_type' AND is_selected=1";
	if ($db->getOne($sql) == 0)
	{
		$tips = '您的购物车中没有已选中商品';
		exit($tips);
	}
    $is_membersgoods=1;
    /*
     * 检查用户是否为会员身份
     */
    if(!goods_extensioncode($_SESSION['goods_id']) == 'goods_members'){
        $is_membersgoods=0;
        $result = check_user_members($_SESSION['user_id']);
        if (!$result)
        {
            $tips = '您还未成为本站付费会员，无法购买商品<a href="http://www.wjike.com/mobile/vip.php?act=vip_info" style="color: red;">立即成为会员</a>';
            exit($tips);
        }
    }

    /* 订单中的商品 */
    $cart_goods = cart_goods($flow_type);

	$consignee = get_consignee($_SESSION['user_id']);
    if(!$is_membersgoods)
    {
        if($consignee['address_id'] <= 0)
        {
            show_message('请填写收货地址！');
        }
    }
	$_POST['how_oos'] = isset($_POST['how_oos']) ? intval($_POST['how_oos']) : 0;
	$_POST['card_message'] = isset($_POST['card_message']) ? htmlspecialchars($_POST['card_message']) : '';
	$_POST['inv_type'] = !empty($_POST['inv_type']) ? htmlspecialchars($_POST['inv_type']) : '';
	$_POST['inv_payee'] = isset($_POST['inv_payee']) ? htmlspecialchars($_POST['inv_payee']) : '';
	$_POST['inv_content'] = isset($_POST['inv_content']) ? htmlspecialchars($_POST['inv_content']) : '';
	$_POST['postscript'] = isset($_POST['postscript']) ? htmlspecialchars($_POST['postscript']) : '';

    //默认选择申通快递
    $shipping_sql = 'SELECT shipping_id FROM ' . $ecs->table('shipping') . " WHERE `shipping_code` = 'sto_express' AND `enabled` = 1";
    $shipping_id = $db->getOne($shipping_sql);
    if($shipping_id == ""){
        $shipping_id = 0;
    }

    //支付方式默认选择支付宝
    $pay_sql = 'SELECT pay_id FROM ' . $ecs->table('payment') . " WHERE `pay_code` = 'alipay'";
    $payment_id = $db->getOne($pay_sql);

	$order = array(
		'shipping_id'	 => $shipping_id,
		'pay_id'		  => intval($payment_id),
		'pack_id'		 => isset($_POST['pack']) ? intval($_POST['pack']) : 0,
		'card_id'		 => isset($_POST['card']) ? intval($_POST['card']) : 0,
		'card_message'	=> trim($_POST['card_message']),
		'surplus'		 => isset($_REQUEST['surplus']) ? floatval($_REQUEST['surplus']) : 0.00,
		'integral'		=> isset($_REQUEST['integral']) ? intval($_REQUEST['integral']) : 0,
		'bonus_id'		=> isset($_REQUEST['bonus']) ? intval($_REQUEST['bonus']) : 0,
        'gift_id'        => isset($_REQUEST['gift']) ? intval($_REQUEST['gift']) : 0,
		'need_inv'		=> empty($_POST['need_inv']) ? 0 : 1,
		'inv_type'		=> $_POST['inv_type'],
		'inv_payee'	   => trim($_POST['inv_payee']),
		'inv_content'	 => $_POST['inv_content'],
		'postscript'	  => trim($_POST['postscript']),
		'how_oos'		 => isset($_LANG['oos'][$_POST['how_oos']]) ? addslashes($_LANG['oos'][$_POST['how_oos']]) : '',
		'need_insure'	 => isset($_POST['need_insure']) ? intval($_POST['need_insure']) : 0,
		'user_id'		 => $_SESSION['user_id'],
		'add_time'		=> gmtime(),
		'order_status'	=> OS_UNCONFIRMED,
		'shipping_status' => SS_UNSHIPPED,
		'pay_status'	  => PS_UNPAYED,
		'agency_id'	   => get_agency_by_regions(array($consignee['country'], $consignee['province'], $consignee['city'], $consignee['district']))
		);
	/* 扩展信息 */
	if (isset($_SESSION['flow_type']) && intval($_SESSION['flow_type']) != CART_GENERAL_GOODS)
	{
		$order['extension_code'] = $_SESSION['extension_code'];
		$order['extension_id'] = $_SESSION['extension_id'];
	}
	else
	{
		$order['extension_code'] = '';
		$order['extension_id'] = 0;
	}

	/* 检查积分余额是否合法 */
	$user_id = $_SESSION['user_id'];
	if ($user_id > 0)
	{
		$user_info = user_info($user_id);

		$order['surplus'] = min($order['surplus'], $user_info['user_money'] + $user_info['credit_line']);
		if ($order['surplus'] < 0)
		{
			$order['surplus'] = 0;
		}
	}
	else
	{
		$order['surplus']  = 0;
		$order['integral'] = 0;
	}


    /* 检查红包是否存在 */
    if ($order['bonus_id'] > 0)
    {
        $bonus = bonus_info($order['bonus_id']);
        $now = gmtime();
        if (empty($bonus) || $bonus['user_id'] != $user_id || $bonus['order_id'] > 0 || $bonus['min_goods_amount'] > cart_amount(true, $flow_type))
        {
            $order['bonus_id'] = 0;
        }
    }
    elseif (isset($_REQUEST['bonus_sn']))
    {
        $bonus_sn = trim($_REQUEST['bonus_sn']);
        $bonus = bonus_info(0, $bonus_sn);
        $order['bonus_id'] = $bonus['bonus_id'];

        $now = gmtime();
        if (empty($bonus) || ($bonus['user_id'] > 0 && $bonus['order_id'] > 0) || $bonus['min_goods_amount'] > cart_amount(true, $flow_type) || $now > $bonus['use_end_datetime'])
        {
            $order['bonus_id'] = 0;
        }
        else
        {
            if ($user_id > 0)
            {
                $sql = "UPDATE " . $ecs->table('user_bonus') . " SET user_id = '$user_id' WHERE bonus_id = '$bonus[bonus_id]' LIMIT 1";
                $db->query($sql);
            }
            $order['bonus_id'] = $bonus['bonus_id'];
            $order['bonus_sn'] = $bonus_sn;
        }
    }
    /* 检查礼品卡是否存在 */
    if ($order['gift_id'] > 0)
    {
        $gift = gift_info($order['gift_id']);
        //print_r($gift);
        if (empty($gift) || $gift['member_id'] != $user_id)
        {
            $order['gift_id'] = 0;
        }
    }
    elseif (isset($_POST['gift_sn']))
    {
        $gift_sn = trim($_POST['gift_sn']);
        $gift = gift_info(0, $gift_sn);
        $now = gmtime();
        if (empty($gift) || $gift['member_id'] > 0 || $gift['order_id'] > 0 || $now > $gift['end_date'])
        {
        }
        else
        {
            if ($user_id > 0)
            {
                $sql = "UPDATE " . $ecs->table('gift_card') . " SET use_status = 3,leave_money=leave_money-".$_POST['use_gift']." WHERE card_id = '$gift[card_id]' LIMIT 1";
                $db->query($sql);
            }
            $order['gift_id'] = $gift['card_id'];
        }
    }
	if (empty($cart_goods))
	{
		$tips = '您的购物车中没有已选中商品';
	}
    foreach($cart_goods AS $key=>$value)
    {
        //注释促销活动时间内只允许购买一件商品的规则
//        $only_one_time = gmtime();
//        if($value['promote_start_date']< $only_one_time && $only_one_time < $value['promote_end_date'])
//        {
//            $goods_count = get_promote_count($value);
//            if($goods_count>0)
//            {
//                show_message(sprintf("非常抱歉，您选择的商品 %s 仅限购买1件", $value['goods_name']), '', '', 'warning');
//            }
//        }
        //查看商品数量、其中是否包含立即购买商品
        if($value['max_number']!=0)
        {
            if($value['goods_number']>$value['max_number'])
            {
                show_message('您购买的商品中超过最大购买数量，请到购物车修改重新提交订单！');
            }
        }
        if(count($cart_goods)>1)
        {
            if($value['is_immediately']==1)
            {
                show_message('您购买的商品中有限制商品，请到购物车修改重新提交订单！');
            }
        }
        if($value['is_overseas']==1)
        {
            $flowoveseas=1;
        }
    }

    //查看物品是否有货和上架
    $goods_is_lack = false;
    $lack_goods_name = '';
    foreach ($cart_goods as $goods)
    {
        if (($goods['goods_number2'] <= 0 || $goods['is_on_sale'] == 0) && $goods["extension_code"] != "package_buy")
        {
            $goods_is_lack = true;
            $lack_goods_name = $goods['goods_name'];
            break;
        }
        elseif ($goods['extension_code'] == 'package_buy')
        {
            if (judge_package_stock($goods['goods_id'], $goods['goods_number']))
            {
                show_message('非常抱歉，您选择的组合购买商品库存不足。');
                exit;
            }
        }
    }

    if ($goods_is_lack)
    {
        show_message(sprintf("非常抱歉，您选择的商品 %s 已下架", $lack_goods_name), '', '', 'warning');
    }

    /* 检查商品总额是否达到最低限购金额 */
    if ($flow_type == CART_GENERAL_GOODS && cart_amount(true, CART_GENERAL_GOODS) < $_CFG['min_goods_amount'])
    {
        show_message(sprintf($_LANG['goods_amount_not_enough'], price_format($_CFG['min_goods_amount'], false)));
    }
	/* 收货人信息 */
	foreach ($consignee as $key => $value)
	{
		$order[$key] = addslashes($value);
	}
    if(!$is_membersgoods)
    {
        if (!preg_match("/^[\x{4e00}-\x{9fa5}]+$/u",$consignee['consignee']))
        {
            $tips = '收货人姓名必须为中文';
            exit($tips);
        }
        if (!preg_match("/^[\x{4e00}-\x{9fa5}a-zA-Z0-9_\-]+$/u",$consignee['address']))
        {
            $tips = '详细地址有误';
            exit($tips);
        }
    }
    if($flowoveseas==1)
    {
        // 身份证号码为15位或者18位，15位时全为数字，18位前17位为数字，最后一位是校验位，可能为数字或字符X
        $reg = '/(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/';
                if(preg_match($reg,$consignee['id_card']) == false)
                {
                    $tips = '身份证错误';
                    exit($tips);
                }
    }
    /* 判断是不是实体商品 */
    foreach ($cart_goods AS $val)
    {
        //有海淘必须填写身份证
        if($val['is_overseas']==1)
        {
            if(empty($order['id_card']))
            {
                show_message('请填写身份证号码！');
            }
            else
            {
                $IDCard = new IDCard();
                if(!$IDCard->isCard($order['id_card']))
                {
                    show_message('请输入正确的身份证号码。');
                }
            }
        }
        /* 统计实体商品的个数 */
        if ($val['is_real'])
        {
            $is_real_good=1;
        }
    }
    if(isset($is_real_good))
    {
        $sql="SELECT shipping_id FROM " . $ecs->table('shipping') . " WHERE shipping_id=".$order['shipping_id'] ." AND enabled =1";
        if(!$db->getOne($sql))
        {
            show_message($_LANG['flow_no_shipping']);
        }
    }
	/* 订单中的总额 */
	$total = order_fee($order, $cart_goods, $consignee);

    if($user_id > 0 )
    {
        // 查询用户有多少积分
        $flow_points = $total['total_order_amount'] * 20;  // 该订单允许使用的积分
        $user_points = $user_info['pay_points']; // 用户的积分总数

        $order['integral'] = min($order['integral'], $user_points, $flow_points);
        if ($order['integral'] < 0)
        {
            $order['integral'] = 0;
        }
    }
	$order['bonus']		= $total['bonus'];
	$order['goods_amount'] = $total['goods_price'];
	$order['discount']	 = $total['discount'];
	$order['surplus']	  = $total['surplus'];
	$order['tax']		  = $total['tax'];

    // 购物车中的商品能享受红包支付的总额
    $discount_amout = compute_discount_amount();
    // 红包和积分最多能支付的金额为商品总额
    $temp_amout = $order['goods_amount'] - $discount_amout;
    if ($temp_amout <= 0)
    {
        $order['bonus_id'] = 0;
    }


	/* 配送方式 */
	if ($order['shipping_id'] > 0)
	{
		$shipping = shipping_info($order['shipping_id']);
		$order['shipping_name'] = addslashes($shipping['shipping_name']);
	}
	$order['shipping_fee'] = $total['shipping_fee'];
	$order['insure_fee']   = $total['shipping_insure'];

	/* 支付方式 */
	if ($order['pay_id'] > 0)
	{
		$payment = payment_info($order['pay_id']);
		$order['pay_name'] = addslashes($payment['pay_name']);
	}
	$order['pay_fee'] = $total['pay_fee'];
	$order['cod_fee'] = $total['cod_fee'];

	$order['integral_money']   = $total['integral_money'];
	$order['integral']		 = $total['integral'];

	if ($order['extension_code'] == 'exchange_goods')
	{
		$order['integral_money']   = 0;
		$order['integral']		 = $total['exchange_integral'];
	}

	$order['from_ad']		  = !empty($_SESSION['from_ad']) ? $_SESSION['from_ad'] : '0';
	$order['referer']		  = !empty($_SESSION['referer']) ? addslashes($_SESSION['referer']) : '';

	$order['order_amount']  = number_format($total['amount'], 2, '.', '');
	/* 如果全部使用余额支付，检查余额是否足够 */
	if ($payment['pay_code'] == 'balance' && $order['order_amount'] > 0)
	{
		if($order['surplus'] >0) //余额支付里如果输入了一个金额
		{
			$order['order_amount'] = $order['order_amount'] + $order['surplus'];
			$order['surplus'] = 0;
		}
		if ($order['order_amount'] > ($user_info['user_money'] + $user_info['credit_line']))
		{
			$tips = '您的余额不足以支付整个订单，请选择其他支付方式';
			echo $tips;
			exit;
		}
		else
		{
			$order['surplus'] = $order['order_amount'];
			$order['order_amount'] = 0;
		}
	}

    //订单金额四舍五入到毛
//    $order['order_amount']  = number_format($total['amount'], 1, '.', '');

	 /* 如果订单金额为0（使用余额或积分或红包支付），修改订单状态为已确认、已付款 */
	if ($order['order_amount'] <= 0)
	{
		$order['order_status'] = OS_CONFIRMED;
		$order['confirm_time'] = gmtime();
		$order['pay_status']   = PS_PAYED;
		$order['pay_time']	 = gmtime();
		$order['order_amount'] = 0;
	}

	$order['integral_money']   = $total['integral_money'];
	$order['integral']		 = $total['integral'];

	if ($order['extension_code'] == 'exchange_goods')
	{
		$order['integral_money']   = 0;
		$order['integral']		 = $total['exchange_integral'];
	}

	$order['from_ad']		  = !empty($_SESSION['from_ad']) ? $_SESSION['from_ad'] : '0';
	$order['referer']		  = !empty($_SESSION['referer']) ? addslashes($_SESSION['referer']) : '';

	/* 记录扩展信息 */
	if ($flow_type != CART_GENERAL_GOODS)
	{
		$order['extension_code'] = $_SESSION['extension_code'];
		$order['extension_id'] = $_SESSION['extension_id'];
	}

	$affiliate = unserialize($_CFG['affiliate']);

    //标记会员充值订单
    if(goods_extensioncode($_SESSION['goods_id']) == 'goods_members'){
        $order['extension_code'] = 'goods_members';
    }

    $warn_goods = array();
    /* 检查商品库存 */
    /* 如果使用库存，且下订单时减库存，则减少库存 */
    if ($_CFG['use_storage'] == '1' && $_CFG['stock_dec_time'] == SDT_PLACE)
    {
        $cart_goods_stock = get_cart_goods();
        $_cart_goods_stock = array();
        foreach ($cart_goods as $value)
        {
            $goods_info = get_goods_info($value["goods_id"]);
            //商品低于预警库存发邮件提醒
            if($goods_info["goods_number"] - $value["goods_number"] <= $goods_info["warn_number"]){
                $warn_array["goods_id"] = $value["goods_id"];
                $warn_array["goods_name"] = $value["goods_name"];
                $goods_id = $value["goods_id"];
                $kjtsql = "SELECT kjt_goods_id FROM " . $GLOBALS['ecs']->table('goods') .
                    " WHERE goods_id = '$goods_id'";
                $kjt_goods_id = $GLOBALS['db']->getOne($kjtsql);
                if(!empty($kjt_goods_id)){
                    $warn_array["kjt_goods_id"] = $kjt_goods_id;
                }
                else{
                    $warn_array["kjt_goods_id"] = '无';
                }
                array_push($warn_goods,$warn_array);
            }
            $_cart_goods_stock[$value['rec_id']] = $value['goods_number'];
        }
        flow_cart_stock($_cart_goods_stock);
        unset($cart_goods_stock, $_cart_goods_stock);
    }


    //添加订单来源
    $order['order_source'] = 'mobile';
    if(is_weixin())
    {
        $order['order_source'] = 'wechat';
    }

	/* 插入订单表 */
	$error_no = 0;
	do
	{
		$order['order_sn'] = get_order_sn(); //获取新订单号
		$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('order_info'), $order, 'INSERT');

		$error_no = $GLOBALS['db']->errno();

		if ($error_no > 0 && $error_no != 1062)
		{
			die($GLOBALS['db']->errorMsg());
		}
	}
	while ($error_no == 1062); //如果是订单号重复则重新提交数据

	$new_order_id = $db->insert_id();
	$order['order_id'] = $new_order_id;

	/* 插入订单商品 */
	$sql = "INSERT INTO " . $ecs->table('order_goods') . "( " .
				"order_id, goods_id, goods_name, goods_sn, goods_number, market_price, ".
				"goods_price, goods_attr, is_real, extension_code, parent_id, is_gift, goods_attr_id, supplier_id) ".
            " SELECT '$new_order_id', t1.goods_id, t1.goods_name, t1.goods_sn, t1.goods_number, t1.market_price, ".
                "t1.goods_price, t1.goods_attr, t1.is_real, t1.extension_code, t1.parent_id, t1.is_gift, t1.goods_attr_id, t2.supplier_id".
            " FROM " .$ecs->table('cart') . " AS t1 " . " LEFT JOIN " .$ecs->table('goods') . " AS t2 ON t1.goods_id=t2.goods_id " .
			" WHERE session_id = '".SESS_ID."' AND is_selected = 1 AND rec_type = '$flow_type'";
	$db->query($sql);

    //组合购买订单商品表字段赋值
    $exist_packsql = "SELECT * FROM " . $GLOBALS['ecs']->table('order_goods') .
        " WHERE order_id = $new_order_id and extension_code = 'package_buy'";

    $pack_order_goods = $GLOBALS['db']->getAll($exist_packsql);
    if(!empty($pack_order_goods)){
        foreach($pack_order_goods as $pog){
            $package_rec_id = $pog["rec_id"];
            $package = get_package_info($pog["goods_id"]);
            $package_price = $package["package_price"];
            $package_goods_id = $package['goods_list'][0]["goods_id"];
            $supplier_sql = "SELECT supplier_id FROM " . $GLOBALS['ecs']->table('goods') .
                " WHERE goods_id = '$package_goods_id'";

            $package_supplier_id = $GLOBALS['db']->getOne($supplier_sql);
            $sql = "UPDATE ". $ecs->table('order_goods') ." SET supplier_id='$package_supplier_id',goods_price='$package_price' WHERE rec_id=$package_rec_id";
            $db->query($sql);
        }
    }

    /* 如果使用库存，且下订单时减库存，则减少库存 */
    if ($_CFG['use_storage'] == '1' && $_CFG['stock_dec_time'] == SDT_PLACE)
    {
        change_order_goods_storage($order['order_id'], true, SDT_PLACE);
    }
    //拆单和海掏逻辑
    $warehouse_type = order_split($order['order_id']);
    if(count($warehouse_type) > 1)
    {
        $sql = "UPDATE ". $ecs->table('order_info') ." SET supplier_status=".SS_NEED." WHERE order_id=".$order['order_id'];
        $db->query($sql);
    }
    else
    {
        $sql = "UPDATE ". $ecs->table('order_info') ." SET supplier_status=".SS_UNNEED." WHERE order_id=".$order['order_id'];
        $db->query($sql);

        //判断是否是海淘订单
        $is_overseas = is_overseas($order['order_id']);
        if($is_overseas){
            $source = get_shipping_source($order['order_id']);
            if($source == 1){
                //创建海淘任务
                create_kjt_task($order['order_sn']);
            }
            //更新订单是否为跨境通标识位
            set_kjt_order($order['order_id']);
        }
    }

    $goods_detail = array();
    $dis_goodslist = array();
    $bonus_goodslist = array();
    $dis_total_amount = 0;
    $bonus_total_amount = 0;
    $discount = compute_discount();
    if(!empty($discount['dis_goodslist']) && $discount['total_amount'] > 0){
        $dis_goodslist = explode(',',$discount['dis_goodslist']);
        $dis_total_amount = intval($discount['total_amount']);
    }
    if($order["bonus"] > 0 && $order["bonus_id"] > 0){
        $str_bonus_goodsids = get_use_goodsids($order["bonus_id"]);
        if(!empty($str_bonus_goodsids)){
            $bonus_goodslist = explode(',',$str_bonus_goodsids);
            foreach($cart_goods as $goods){
                if(in_array($goods["goods_id"],$bonus_goodslist)){
                    $bonus_total_amount += $goods["goods_price"] * $goods["goods_number"];
                }
            }
        }
        else{
            $bonus_total_amount = $order["goods_amount"];
        }
    }

    foreach($cart_goods as $goods){
        $return_price = $goods["goods_price"];
        $goods_detail[$goods["goods_id"]]['discount'] = 0;
        $goods_detail[$goods["goods_id"]]['bonus'] = 0;
        $goods_detail[$goods["goods_id"]]['gift'] = 0;
        $goods_detail[$goods["goods_id"]]['money'] = 0;
        if($order["discount"] > 0 && in_array($goods["goods_id"],$dis_goodslist)){
            $discount = intval($goods["goods_price"]) / $dis_total_amount * $order["discount"];
            $discount = number_format($discount - 0.05, 1, '.', '');
            $goods_detail[$goods["goods_id"]]['discount'] = $discount;
            $return_price -= $discount;
        }
        if($order["bonus"] > 0 && (in_array($goods["goods_id"],$bonus_goodslist) || count($bonus_goodslist) == 0)){
            $refund_bonus = intval($goods["goods_price"]) / $bonus_total_amount * $order["bonus"];
            $refund_bonus = number_format($refund_bonus - 0.05, 1, '.', '');
            $goods_detail[$goods["goods_id"]]['bonus'] = $refund_bonus;
            $return_price -= $refund_bonus;
        }
        if($total['use_gift'] > 0){
            if($order["order_amount"] == 0){
                $goods_detail[$goods["goods_id"]]['gift'] = $return_price;
                $goods_detail[$goods["goods_id"]]['money'] = 0;
            }
            else{
                $gift = $total['use_gift'] / ($total['use_gift'] + $order["order_amount"]) * $return_price;
                $goods_detail[$goods["goods_id"]]['gift'] = number_format($gift, 1, '.', '');
                $goods_detail[$goods["goods_id"]]['money'] = $return_price - $goods_detail[$goods["goods_id"]]['gift'];
            }
        }
        else{
            $goods_detail[$goods["goods_id"]]['money'] = $return_price;
        }
    }
    foreach($goods_detail as $k=>$v){
        $discount = $v['discount'];
        $refund_bonus = $v['bonus'];
        $gift = $v['gift'];
        $money = $v['money'];
        $goods_id = $k;
        $sql = "UPDATE ". $ecs->table('order_goods') ." SET refund_discount=$discount,refund_bonus=$refund_bonus,refund_gift=$gift,refund_money=$money,refund_num=goods_number WHERE goods_id=$goods_id";
        $db->query($sql);
    }

	/* 处理余额、积分、红包 */
	if ($order['user_id'] > 0 && $order['surplus'] > 0)
	{
		log_account_change($order['user_id'], $order['surplus'] * (-1), 0, 0, 0, sprintf('支付订单 %s', $order['order_sn']));
	}
	if ($order['user_id'] > 0 && $order['integral'] > 0)
	{
		log_account_change($order['user_id'], 0, 0, 0, $order['integral'] * (-1), sprintf('支付订单 %s', $order['order_sn']));
	}

    if ($order['bonus_id'] > 0 && $temp_amout > 0)
    {
        use_bonus($order['bonus_id'], $new_order_id);
    }

    if ($order['gift_id'] > 0 && $temp_amout > 0)
    {
        //		print_r($total);
        use_gift($order,$total);
    }

    if(!empty($warn_goods)){
        foreach($warn_goods as $value){
            /* 发送邮件 */
            $smarty->assign('goods',$value);

            $tpl = get_mail_template('warn_number');
            $content = $smarty->fetch('str:' . $tpl['template_content']);
            //send_mail('张滨', 'zhangbin@wjike.com', $tpl['template_subject'], $content, $tpl['is_html']);
            //send_mail('时凯', 'shikai@wjike.com', $tpl['template_subject'], $content, $tpl['is_html']);
            send_mail('马卉', 'mahui@wjike.com', $tpl['template_subject'], $content, $tpl['is_html']);
            //send_mail('张舒仙', 'zhangshuxian@wjike.com', $tpl['template_subject'], $content, $tpl['is_html']);
        }
    }
    /* 如果订单金额为0 处理虚拟卡 */
    if ($order['order_amount'] <= 0)
    {
        //虚拟卡处理
        $sql = "SELECT goods_id, goods_name, goods_number AS num FROM ".
            $GLOBALS['ecs']->table('cart') .
            " WHERE is_real = 0 AND extension_code = 'virtual_card'".
            " AND session_id = '".SESS_ID."' AND rec_type = '$flow_type'";

        $res = $GLOBALS['db']->getAll($sql);

        $virtual_goods = array();
        foreach ($res AS $row)
        {
            $virtual_goods['virtual_card'][] = array('goods_id' => $row['goods_id'], 'goods_name' => $row['goods_name'], 'num' => $row['num']);
        }

        if ($virtual_goods AND $flow_type != CART_GROUP_BUY_GOODS)
        {
            /* 虚拟卡发货 */
            if (virtual_goods_ship($virtual_goods,$msg, $order['order_sn'], true))
            {
                /* 如果没有实体商品，修改发货状态，送积分和红包 */
                $sql = "SELECT COUNT(*)" .
                    " FROM " . $ecs->table('order_goods') .
                    " WHERE order_id = '$order[order_id]' " .
                    " AND is_real = 1";
                if ($db->getOne($sql) <= 0)
                {
                    /* 修改订单状态 */
                    update_order($order['order_id'], array('shipping_status' => SS_SHIPPED, 'shipping_time' => gmtime()));

                    /* 如果订单用户不为空，计算积分，并发给用户；发红包 */
                    if ($order['user_id'] > 0)
                    {
                        /* 取得用户信息 */
                        $user = user_info($order['user_id']);

                        /* 计算并发放积分 */
                        $integral = integral_to_give($order);
                        log_account_change($order['user_id'], 0, 0, intval($integral['rank_points']), intval($integral['custom_points']), sprintf($_LANG['order_gift_integral'], $order['order_sn']));

                        /* 发放红包 */
                        send_order_bonus($order['order_id']);
                    }
                }
            }
        }

        //会员充值商品处理
        $sql = "SELECT t1.goods_id, t1.goods_name, t2.keywords, t1.goods_number AS num FROM ".
            $GLOBALS['ecs']->table('cart') .
            " t1,".$GLOBALS['ecs']->table('goods') ." t2 WHERE t1.goods_id=t2.goods_id AND t1.is_real = 0 AND t1.extension_code = 'goods_members'".
            " AND t1.session_id = '".SESS_ID."' AND t1.rec_type = '$flow_type'";

        $res = $GLOBALS['db']->getAll($sql);

        $goods_members = array();
        foreach ($res AS $row)
        {
            $goods_members['goods_members'][] = array('goods_id' => $row['goods_id'], 'user_id' => $order['user_id']);
        }
        if ($goods_members AND $flow_type != CART_GROUP_BUY_GOODS)
        {
            /* 会员充值处理 */
            if (members_charge_ship($goods_members))
            {
                /* 修改订单状态 */
                update_order($order['order_id'], array('shipping_status' => SS_RECEIVED, 'order_status' => OS_SPLITED, 'shipping_time' => gmtime()));

                /* 如果订单用户不为空，计算积分，并发给用户；发红包 */
                if ($order['user_id'] > 0)
                {
                    /* 取得用户信息 */
                    $user = user_info($order['user_id']);

                    /* 计算并发放积分 */
                    $integral = integral_to_give($order);
                    log_account_change($order['user_id'], 0, 0, intval($integral['rank_points']), intval($integral['custom_points']), sprintf($_LANG['order_gift_integral'], $order['order_sn']));

                    /* 发放红包 */
                    send_order_bonus($order['order_id']);
                }
            }
        }

        $goods_names = '';
        foreach ($cart_goods as $goods)
        {
            $goods_names .= $goods['goods_name'];
        }
        $smarty->assign('goods_names', $goods_names);
    }


    /* 是否需要在线支付 */
    $pay_online = false;
    if ($order['order_amount'] > 0)
    {
        $order['log_id'] = insert_pay_log($new_order_id, $order['order_amount'], PAY_ORDER);
        $pay_online = true;

        $goods_ids = array();
        $goods_names = '';
        foreach ($cart_goods as $goods)
        {
            $goods_ids[] = $goods['goods_id'];
            $goods_names .= $goods['goods_name'];
        }
        $goods_ids = array_unique(array_filter($goods_ids));
        //获取可用的支付方式（如果有多个物品，要取多个物品公共的）- add by qihua on 20130816
        $payment_list = available_payment_list_by_goods($goods_ids, false, 0, true);
        if($order['bonus_id'] > 0)
        {
            $bonus_payment_list=array();
            $pay_ids = explode(',',$bonus['pay_ids']);
        }
        //wap端过滤掉微信支付，微信端只留微信支付
        foreach ($payment_list as $key=>$val)
        {
            if(is_weixin()){
                if ($val['pay_code'] != 'weixinpay' && $val['pay_code'] != 'guomeipay')
                {
                    unset($payment_list[$key]);
                }
                //过滤国美支付
                if ($order['order_amount'] < 300 && $val['pay_code'] == 'guomeipay')
                {
                    unset($payment_list[$key]);
                }
                $smarty->assign('is_wx', true);
            }
            else{
                if ($val['pay_code'] != 'alipay' && $val['pay_code'] != 'guomeipay')
                {
                    unset($payment_list[$key]);
                }
                //过滤国美支付
                if ($order['order_amount'] < 300 && $val['pay_code'] == 'guomeipay')
                {
                    unset($payment_list[$key]);
                }
            }
            if ($order['order_amount'] < 300 && $val['pay_code'] == 'guomeipay')
            {
            }
            else
            {
                //过滤掉红包限制的支付
                foreach($pay_ids AS $v)
                {
                    if($val['pay_id'] == $v)
                    {
                        if($payment_list[$key] != null)
                        {
                            $bonus_payment_list[]=$payment_list[$key];
                        }
                    }
                }
            }
        }
        $smarty->assign('goods_names', $goods_names);
        if($order['bonus_id'] > 0)
        {
            $smarty->assign('payment_list', $bonus_payment_list);
        }
        else
        {
            $smarty->assign('payment_list', $payment_list);
        }
    }
    $smarty->assign('pay_online', $pay_online);

	/* 清空购物车 */
	clear_cart($flow_type,false);
	/* 清除缓存，否则买了商品，但是前台页面读取缓存，商品数量不减少 */
	clear_all_files();

	if(!empty($order['shipping_name']))
	{
		$order['shipping_name']=trim(stripcslashes($order['shipping_name']));
	}
	/* 取得支付信息，生成支付代码 */
//	if ($order['order_amount'] > 0)
//	{
//		$payment = payment_info($order['pay_id']);
//
//		include_once('includes/modules/payment/' . $payment['pay_code'] . '.php');
//
//		$pay_obj	= new $payment['pay_code'];
//		$order['log_id'] = insert_pay_log($new_order_id, $order['order_amount'], PAY_ORDER);
//        $pay_mobile = $db->getOne("SELECT  mobile_phone FROM " .$ecs->table('users'). " WHERE user_id = '" . $_SESSION['user_id'] . "' ");
//        $order['pay_mobile'] = ($pay_mobile ? $pay_mobile : $order['tel']);
//
//
//        $pay_online = $pay_obj->get_code($order, unserialize_config($payment['pay_config']),'mobile');
//        if ($payment['pay_code'] == 'weixinpay')
//        {
//            $jspara = $pay_obj->get_jspara($order, unserialize_config($payment['pay_config']),$openid);
//            $smarty->assign('jspara', $jspara);
//        }
//		$order['pay_desc'] = $payment['pay_desc'];
//
//		$smarty->assign('pay_online', $pay_online);
//	}

	/* 订单信息 */
	$smarty->assign('order',	  $order);
	$smarty->assign('total',	  $total);
    //广告投放参数
    foreach($cart_goods as $key=>$val){
        $info = get_cat_info($val['goods_id']);
        $cart_goods[$key]['cat_id'] = $info['cat_id'];
        $cart_goods[$key]['cat_name'] = $info['cat_name'];
    }
    $smarty->assign('goods_list', $cart_goods);
	$smarty->assign('order_submit_back', sprintf('您可以 %s 或去 %s', '<a href="index.php">返回首页</a>', '<a href="user.php">用户中心</a>')); // 返回提示

	unset($_SESSION['flow_consignee']); // 清除session中保存的收货人信息
	unset($_SESSION['flow_order']);
	unset($_SESSION['direct_shopping']);
    unset($_SESSION['goods_id']);

	if ($_SESSION['user_id'] > 0)
	{
		$smarty->assign('user_name', $_SESSION['user_name']);
	}
	$smarty->assign('footer', get_footer());
	$smarty->assign('tips', $tips);
	$smarty->display('order_done.html');
	exit;

}


function flow_available_points()
{
	$sql = "SELECT SUM(g.integral * c.goods_number) ".
			"FROM " . $GLOBALS['ecs']->table('cart') . " AS c, " . $GLOBALS['ecs']->table('goods') . " AS g " .
			"WHERE c.session_id = '" . SESS_ID . "' AND c.goods_id = g.goods_id AND c.is_gift = 0 AND g.integral > 0 " .
			"AND c.rec_type = '" . CART_GENERAL_GOODS . "'";

	$val = intval($GLOBALS['db']->getOne($sql));

	return integral_of_value($val);
}

/**
 * 检查订单中商品库存
 *
 * @access  public
 * @param   array   $arr
 *
 * @return  void
 */
function flow_cart_stock($arr)
{
	foreach ($arr AS $key => $val)
	{
		$val = intval(make_semiangle($val));
		if ($val <= 0)
		{
			continue;
		}

		$sql = "SELECT `goods_id`, `goods_attr_id`, `extension_code` FROM" .$GLOBALS['ecs']->table('cart').
			   " WHERE rec_id='$key' AND session_id='" . SESS_ID . "'";
		$goods = $GLOBALS['db']->getRow($sql);

		$sql = "SELECT g.goods_name, g.goods_number, c.product_id ".
				"FROM " .$GLOBALS['ecs']->table('goods'). " AS g, ".
					$GLOBALS['ecs']->table('cart'). " AS c ".
				"WHERE g.goods_id = c.goods_id AND c.rec_id = '$key'";
		$row = $GLOBALS['db']->getRow($sql);

		//系统启用了库存，检查输入的商品数量是否有效
		if (intval($GLOBALS['_CFG']['use_storage']) > 0 && $goods['extension_code'] != 'package_buy')
		{
			if ($row['goods_number'] < $val)
			{
				show_message(sprintf($GLOBALS['_LANG']['stock_insufficiency'], $row['goods_name'],
				$row['goods_number'], $row['goods_number']));
				exit;
			}

			/* 是货品 */
			$row['product_id'] = trim($row['product_id']);
			if (!empty($row['product_id']))
			{
				$sql = "SELECT product_number FROM " .$GLOBALS['ecs']->table('products'). " WHERE goods_id = '" . $goods['goods_id'] . "' AND product_id = '" . $row['product_id'] . "'";
				$product_number = $GLOBALS['db']->getOne($sql);
				if ($product_number < $val)
				{
					show_message(sprintf($GLOBALS['_LANG']['stock_insufficiency'], $row['goods_name'],
					$row['goods_number'], $row['goods_number']));
					exit;
				}
			}
		}
		elseif (intval($GLOBALS['_CFG']['use_storage']) > 0 && $goods['extension_code'] == 'package_buy')
		{
			if (judge_package_stock($goods['goods_id'], $val))
			{
				show_message($GLOBALS['_LANG']['package_stock_insufficiency']);
				exit;
			}
		}
	}

}
/**
 * 获得分类的信息
 *
 * @param   integer $goods_id
 *
 * @return  void
 */
function get_cat_info($goods_id)
{    $sql = "SELECT g.cat_id, c.cat_name " .
    "FROM " . $GLOBALS['ecs']->table('goods') . " AS g " .
    "INNER JOIN " . $GLOBALS['ecs']->table('category') . " AS c ON g.cat_id = c.cat_id " .
    "WHERE g.goods_id = '$goods_id'";
    return $GLOBALS['db']->getRow($sql);
}
?>