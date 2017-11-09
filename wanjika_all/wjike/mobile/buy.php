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
 * $Author: testyang $
 * $Id: buy.php 15013 2008-10-23 09:31:42Z testyang $
*/

define('IN_ECS', true);

setcookie("buy", "1", time()+3600)
;
include_once(dirname(__FILE__) . '/includes/init.php');
include_once(ROOT_PATH . 'includes/lib_order.php');

$act = isset($_REQUEST['act']) ? $_REQUEST['act'] : '';
$cp = isset($_REQUEST['cp']) ? $_REQUEST['cp'] : '';
if ($_SESSION['user_id'] > 0)
{
	$smarty->assign('user_name', $_SESSION['user_name']);
}
if($act == 'checkout' || $act == 'consignee')
{
    if($_SESSION['one_step_buy']!=1)
    {
        //将购物车商品状态改为选中状态
        $sql = "UPDATE " . $GLOBALS['ecs']->table('cart') .
            " SET is_selected = 1 WHERE session_id = '" . SESS_ID . "' AND rec_type = '$type'";
        $GLOBALS['db']->query($sql);
    }
    $sql = "SELECT gs.is_overseas FROM " . $ecs->table('cart') .
        " AS c LEFT JOIN ". $ecs->table('goods') ." AS g ON c.goods_id=g.goods_id ".
        " LEFT JOIN ". $ecs->table('goods_supplier') . " AS gs ON g.supplier_id=gs.type_id " .
        " WHERE session_id = '" . SESS_ID . "' " .
        "AND parent_id = 0 AND is_gift = 0 AND rec_type = '$flow_type' AND is_selected = 1";
    $re = $db->getAll($sql);
    foreach($re AS $value)
    {
        if($value['is_overseas']>0)
        {
            $flow_overseas=$value['is_overseas'];
        }
    }
	if ($_SESSION['user_id'] > 0)
	{
		$act = 'consignee';
	}
	if($act == 'consignee')
	{
        $buy_type = 1;
        /*
         * 检查用户是否为会员身份
         */
        $is_membersgoods = goods_extensioncode($_SESSION['goods_id']) == 'goods_members';
        if(!$is_membersgoods){
            $result = check_user_members($_SESSION['user_id']);
            if (!$result)
            {
                $buy_type = 2;
            }

            include_once('includes/lib_transaction.php');
            /*
             * 收货人信息填写界面
             */
            if (isset($_REQUEST['direct_shopping']))
            {
                $_SESSION['direct_shopping'] = 1;
            }

            $consignee_list = get_consignee_list($_SESSION['user_id']);
            if( 5>= count($consignee_list) && count($consignee_list)>0)
            {
                foreach($consignee_list AS $k=>$v)
                {
                    $consignee_list[$k]['is_overseas']=$flow_overseas;
                }
                if($_GET['addaddress']==1)
                {
                    $province_list = array();
                    $city_list = array();
                    $district_list = array();
                    $city_list	 = get_regions(2, 0);
                    $district_list = get_regions(3, 0);
                    $consignee['is_overseas']=$flow_overseas;
                    $smarty->assign('province_list', get_regions(1, $_CFG['shop_country']));
                    $smarty->assign('city_list',	 $city_list);
                    $smarty->assign('consignee',	 $consignee);
                    $smarty->assign('district_list', $district_list);
                    $buy_type=1;
                    $smarty->assign('buy_type', $buy_type);
                }
                else
                {
                    foreach ($consignee_list AS $region_id => $consignee)
                    {
                        $sql = "SELECT concat(IFNULL(p.region_name, ''), " .
                            "'  ', IFNULL(t.region_name, ''), '  ', IFNULL(d.region_name, '')) AS region " .
                            "FROM " . $ecs->table('user_address') . " AS u " .
                            "LEFT JOIN " . $ecs->table('region') . " AS p ON u.province = p.region_id " .
                            "LEFT JOIN " . $ecs->table('region') . " AS t ON u.city = t.region_id " .
                            "LEFT JOIN " . $ecs->table('region') . " AS d ON u.district = d.region_id " .
                            "WHERE u.address_id = '$consignee[address_id]'";
                        $region = $db->getOne($sql);
                        if(!empty($region)){
                            $consignee_list[$region_id]["address_name"] = $region.' '.$consignee["address"];
                        }
                    }
                    $smarty->assign('buy_type', 3);
                    $smarty->assign('countconsignee', count($consignee_list));
                    $smarty->assign('consignee_list', $consignee_list);
                }
            }
            else
            {
                $consignee = get_consignee($_SESSION['user_id']);
                $consignee['is_overseas']=$flow_overseas;
                /* 取得国家列表、商店所在国家、商店所在国家的省列表 */
                //$smarty->assign('country_list',	   get_regions());
                $smarty->assign('shop_country',	   $_CFG['shop_country']);
                $smarty->assign('shop_province_list', get_regions(1, $_CFG['shop_country']));
                $smarty->assign('consignee', $consignee);
                //$consignee_list = get_consignee_list($_SESSION['user_id']);
                /* 取得每个收货地址的省市区列表 */
                $province_list = array();
                $city_list = array();
                $district_list = array();
    //		foreach ($consignee_list as $region_id => $consignee)
    //		{
                $consignee['country']  = isset($consignee['country'])  ? intval($consignee['country'])  : 0;
                $consignee['province'] = isset($consignee['province']) ? intval($consignee['province']) : 0;
                $consignee['city']	 = isset($consignee['city'])	 ? intval($consignee['city'])	 : 0;

                $province_list = get_regions(1, $consignee['country']);
                $city_list	 = get_regions(2, $consignee['province']);
                $district_list = get_regions(3, $consignee['city']);
    //		}
                $smarty->assign('buy_type', $buy_type);
                $smarty->assign('province_list', get_regions(1, $_CFG['shop_country']));
                $smarty->assign('city_list',	 $city_list);
                $smarty->assign('district_list', $district_list);
            }
        }
        else{
            ecs_header("Location: order.php?act=order_lise\n");
            exit;
        }
	}
}
/* 编辑收货地址的处理 */
elseif ($act == 'edit_address')
{

    $sql = "SELECT gs.is_overseas FROM " . $ecs->table('cart') .
        " AS c LEFT JOIN ". $ecs->table('goods') ." AS g ON c.goods_id=g.goods_id ".
        " LEFT JOIN ". $ecs->table('goods_supplier') . " AS gs ON g.supplier_id=gs.type_id " .
        " WHERE session_id = '" . SESS_ID . "' " .
        "AND parent_id = 0 AND is_gift = 0 AND rec_type = '$flow_type' AND is_selected = 1";
    $re = $db->getAll($sql);
    foreach($re AS $value)
    {
        if($value['is_overseas']>0)
        {
            $flow_overseas=$value['is_overseas'];
        }
    }
    $consignee = get_selected_consignee($_GET['id']);
    $consignee['is_overseas']=$flow_overseas;
    /* 取得国家列表、商店所在国家、商店所在国家的省列表 */
    //$smarty->assign('country_list',	   get_regions());
    $smarty->assign('shop_country',	   $_CFG['shop_country']);
    $smarty->assign('shop_province_list', get_regions(1, $_CFG['shop_country']));
    $smarty->assign('consignee', $consignee);
    //$consignee_list = get_consignee_list($_SESSION['user_id']);
    /* 取得每个收货地址的省市区列表 */
    $province_list = array();
    $city_list = array();
    $district_list = array();
    //		foreach ($consignee_list as $region_id => $consignee)
    //		{
    $consignee['country']  = isset($consignee['country'])  ? intval($consignee['country'])  : 0;
    $consignee['province'] = isset($consignee['province']) ? intval($consignee['province']) : 0;
    $consignee['city']	 = isset($consignee['city'])	 ? intval($consignee['city'])	 : 0;

    $province_list = get_regions(1, $consignee['country']);
    $city_list	 = get_regions(2, $consignee['province']);
    $district_list = get_regions(3, $consignee['city']);
    //		}
    $smarty->assign('buy_type', 1);
    $smarty->assign('province_list', get_regions(1, $_CFG['shop_country']));
    $smarty->assign('city_list',	 $city_list);
    $smarty->assign('district_list', $district_list);
}
/* 删除收货地址 */
elseif ($act == 'drop_consignee')
{
    include_once('includes/lib_transaction.php');

    $consignee_id = intval($_GET['id']);
    if($consignee_id == $_SESSION['flow_consignee']['address_id']){
        unset($_SESSION['flow_consignee']);
    }
    $flowconsignee_id = intval($_GET['flowconsignee_id']);
    if($flowconsignee_id==1)
    {
        if (drop_consignee($consignee_id))
        {
            ecs_header("Location: buy.php?act=checkout\n");
            exit;
        }
        else
        {
            show_message($_LANG['del_address_false']);
        }
    }
    else
    {
        if (drop_consignee($consignee_id))
        {
            ecs_header("Location: user.php?act=address_list\n");
            exit;
        }
        else
        {
            show_message($_LANG['del_address_false']);
        }
    }
}
elseif ($act  == 'add_to_cart')
{
    include_once('includes/cls_json.php');
    unset($_SESSION['goods_id']);
    $_POST['goods']=strip_tags(urldecode($_POST['goods']));
    $_POST['goods'] = json_str_iconv($_POST['goods']);

    if (!empty($_REQUEST['goods_id']) && empty($_POST['goods']))
    {
        if (!is_numeric($_REQUEST['goods_id']) || intval($_REQUEST['goods_id']) <= 0)
        {
            ecs_header("Location:./\n");
        }
        $goods_id = intval($_REQUEST['goods_id']);
        exit;
    }

    $result = array('error' => 0, 'message' => '', 'content' => '', 'goods_id' => '');
    $json  = new JSON;

    if (empty($_POST['goods']))
    {
        $result['error'] = 1;
        die($json->encode($result));
    }

    $goods = $json->decode($_POST['goods']);

    /* 检查：如果商品有规格，而post的数据没有规格，把商品的规格属性通过JSON传到前台 */
    if (empty($goods->spec) AND empty($goods->quick))
    {
        $sql = "SELECT a.attr_id, a.attr_name, a.attr_type, ".
            "g.goods_attr_id, g.attr_value, g.attr_price " .
        'FROM ' . $GLOBALS['ecs']->table('goods_attr') . ' AS g ' .
        'LEFT JOIN ' . $GLOBALS['ecs']->table('attribute') . ' AS a ON a.attr_id = g.attr_id ' .
        "WHERE a.attr_type != 0 AND g.goods_id = '" . $goods->goods_id . "' " .
        'ORDER BY a.sort_order, g.attr_price, g.goods_attr_id';

        $res = $GLOBALS['db']->getAll($sql);

        if (!empty($res))
        {
            $spe_arr = array();
            foreach ($res AS $row)
            {
                $spe_arr[$row['attr_id']]['attr_type'] = $row['attr_type'];
                $spe_arr[$row['attr_id']]['name']     = $row['attr_name'];
                $spe_arr[$row['attr_id']]['attr_id']     = $row['attr_id'];
                $spe_arr[$row['attr_id']]['values'][] = array(
                                                            'label'        => $row['attr_value'],
                                                            'price'        => $row['attr_price'],
                                                            'format_price' => price_format($row['attr_price'], false),
                                                            'id'           => $row['goods_attr_id']);
            }
            $i = 0;
            $spe_array = array();
            foreach ($spe_arr AS $row)
            {
                $spe_array[]=$row;
            }
            $result['error']   = ERR_NEED_SELECT_ATTR;
            $result['goods_id'] = $goods->goods_id;
            $result['parent'] = $goods->parent;
            $result['message'] = $spe_array;

            die($json->encode($result));
        }
    }

    /* 更新：如果是一步购物，先清空购物车 */
    if ($_CFG['one_step_buy'] == '1' || goods_extensioncode($goods->goods_id) == 'goods_members')
    {
        $_SESSION['goods_id'] = $goods->goods_id;
        clear_cart();
    }

    //如果购物车中有会员充值商品，先清空购物车
    $sql = "SELECT COUNT(*) FROM " . $ecs->table('cart') .
        " WHERE session_id = '" . SESS_ID . "' " .
        "AND extension_code = 'goods_members'";
    if ($db->getOne($sql) > 0)
    {
        clear_cart();
    }

    /* 检查：商品数量是否合法 */
    if (!is_numeric($goods->number) || intval($goods->number) <= 0)
    {
        $result['error']   = 1;
        $result['message'] = "对不起，您输入了一个非法的商品数量。";
    }
    /* 更新：购物车 */
    else
    {
        // 更新：添加到购物车
		$_LANG['no_basic_goods'] = '对不起，您希望将该商品做为配件购买，可是购物车中还没有该商品的基本件。';
		$_LANG['not_on_sale'] = '对不起，该商品已经下架。';
		$_LANG['cannt_alone_sale'] = '对不起，该商品不能单独销售。';
		$_LANG['shortage'] = "对不起，该商品已经库存不足暂停销售。\n你现在要进行缺货登记来预订该商品吗？";
        if (addto_cart($goods->goods_id, $goods->number, $goods->spec, $goods->parent))
        {
            if ($_CFG['cart_confirm'] > 2)
            {
                $result['message'] = '';
            }
            else
            {
                $result['message'] = $_CFG['cart_confirm'] == 1 ? "该商品已添加到购物车，您现在还需要继续购物吗？\n如果您希望马上结算，请点击“确定”按钮。\n如果您希望继续购物，请点击“取消”按钮。" : "该商品已添加到购物车，您现在还需要继续购物吗？\n如果您希望继续购物，请点击“确定”按钮。\n如果您希望马上结算，请点击“取消”按钮。";
            }

            //$result['content'] = insert_cart_info();
            $result['one_step_buy'] = $_CFG['one_step_buy'];
        }
        else
        {
            $result['message']  = $err->last_message();
            $result['error']    = $err->error_no;
            $result['goods_id'] = stripslashes($goods->goods_id);
            if (is_array($goods->spec))
            {
                $result['product_spec'] = implode(',', $goods->spec);
            }
            else
            {
                $result['product_spec'] = $goods->spec;
            }
        }
    }

    $result['confirm_type'] = !empty($_CFG['cart_confirm']) ? $_CFG['cart_confirm'] : 2;
	if($cp == "add_cart"){
		$result['ctype'] = 1;
	}else{
		$result['ctype'] = 2;
	}
    die($json->encode($result));
}
/*------------------------------------------------------ */
//-- 添加礼包到购物车
/*------------------------------------------------------ */
elseif ($act == 'add_package_to_cart')
{
    include_once('includes/cls_json.php');
    $_POST['package_info'] = json_str_iconv($_POST['package_info']);

    $_LANG['no_basic_goods'] = '对不起，您希望将该商品做为配件购买，可是购物车中还没有该商品的基本件。';
    $_LANG['not_on_sale'] = '对不起，该商品已经下架。';
    $_LANG['cannt_alone_sale'] = '对不起，该商品不能单独销售。';
    $_LANG['shortage'] = "对不起，该礼包已经库存不足暂停销售。";
    $result = array('error' => 0, 'message' => '', 'content' => '', 'package_id' => '');
    $json  = new JSON;

    if (empty($_POST['package_info']))
    {
        $result['error'] = 1;
        die($json->encode($result));
    }

    $package = $json->decode($_POST['package_info']);
    /* 商品数量是否合法 */
    if (!is_numeric($package->number) || intval($package->number) <= 0)
    {
        $result['error']   = 1;
        $result['message'] = $_LANG['invalid_number'];
    }
    else
    {
        /* 添加到购物车 */
        if (add_package_to_cart($package->package_id, $package->number))
        {
            if ($_CFG['cart_confirm'] > 2)
            {
                $result['message'] = '';
            }
            else
            {
                $result['message'] = $_CFG['cart_confirm'] == 1 ? $_LANG['addto_cart_success_1'] : $_LANG['addto_cart_success_2'];
            }

//            $result['content'] = insert_cart_info();
            $result['one_step_buy'] = $_CFG['one_step_buy'];
        }
        else
        {
            $result['message']    = $err->last_message();
            $result['error']      = $err->error_no;
            $result['package_id'] = stripslashes($package->package_id);
        }
    }
    $result['confirm_type'] = !empty($_CFG['cart_confirm']) ? $_CFG['cart_confirm'] : 2;
    die($json->encode($result));
}
/*------------------------------------------------------ */
//-- 直接购买
/*------------------------------------------------------ */
elseif($act == 'one_step_buy')
{
    include_once('includes/cls_json.php');
    unset($_SESSION['goods_id']);
    unset($_SESSION['flow_order']);
    /* 删除is_immediately=1的商品 */
    $sql = "DELETE FROM " . $GLOBALS['ecs']->table('cart') ."
            WHERE session_id = '" . SESS_ID . "'
            AND is_immediately=1";
    $GLOBALS['db']->query($sql);
    $_POST['goods']=strip_tags(urldecode($_POST['goods']));
    $_POST['goods'] = json_str_iconv($_POST['goods']);

    if (!empty($_REQUEST['goods_id']) && empty($_POST['goods']))
    {
        if (!is_numeric($_REQUEST['goods_id']) || intval($_REQUEST['goods_id']) <= 0)
        {
            ecs_header("Location:./\n");
        }
        $goods_id = intval($_REQUEST['goods_id']);
        exit;
    }

    $result = array('error' => 0, 'message' => '', 'content' => '', 'goods_id' => '');
    $json  = new JSON;

    if (empty($_POST['goods']))
    {
        $result['error'] = 1;
        die($json->encode($result));
    }

    $goods = $json->decode($_POST['goods']);

    /* 检查：如果商品有规格，而post的数据没有规格，把商品的规格属性通过JSON传到前台 */
    if (empty($goods->spec) AND empty($goods->quick))
    {
        $sql = "SELECT a.attr_id, a.attr_name, a.attr_type, ".
            "g.goods_attr_id, g.attr_value, g.attr_price " .
            'FROM ' . $GLOBALS['ecs']->table('goods_attr') . ' AS g ' .
            'LEFT JOIN ' . $GLOBALS['ecs']->table('attribute') . ' AS a ON a.attr_id = g.attr_id ' .
            "WHERE a.attr_type != 0 AND g.goods_id = '" . $goods->goods_id . "' " .
            'ORDER BY a.sort_order, g.attr_price, g.goods_attr_id';

        $res = $GLOBALS['db']->getAll($sql);

        if (!empty($res))
        {
            $spe_arr = array();
            foreach ($res AS $row)
            {
                $spe_arr[$row['attr_id']]['attr_type'] = $row['attr_type'];
                $spe_arr[$row['attr_id']]['name']     = $row['attr_name'];
                $spe_arr[$row['attr_id']]['attr_id']     = $row['attr_id'];
                $spe_arr[$row['attr_id']]['values'][] = array(
                    'label'        => $row['attr_value'],
                    'price'        => $row['attr_price'],
                    'format_price' => price_format($row['attr_price'], false),
                    'id'           => $row['goods_attr_id']);
            }
            $i = 0;
            $spe_array = array();
            foreach ($spe_arr AS $row)
            {
                $spe_array[]=$row;
            }
            $result['error']   = ERR_NEED_SELECT_ATTR;
            $result['goods_id'] = $goods->goods_id;
            $result['parent'] = $goods->parent;
            $result['message'] = $spe_array;

            die($json->encode($result));
        }
    }
    $_SESSION['goods_number']=$goods->number;
    $_SESSION['one_step_buy']=1;
    $_SESSION['goods_id'] = $goods->goods_id;
    /* 更新：如果是一步购物，先清空购物车 */
    if ($_CFG['one_step_buy'] == '1' || goods_extensioncode($goods->goods_id) == 'goods_members')
    {
        $_SESSION['goods_id'] = $goods->goods_id;
//        clear_cart();
    }

    //如果购物车中有会员充值商品，先清空购物车
    $sql = "SELECT COUNT(*) FROM " . $ecs->table('cart') .
        " WHERE session_id = '" . SESS_ID . "' " .
        "AND extension_code = 'goods_members'";
    if ($db->getOne($sql) > 0)
    {
        clear_cart();
    }

    /* 检查：商品数量是否合法 */
    if (!is_numeric($goods->number) || intval($goods->number) <= 0)
    {
        $result['error']   = 1;
        $result['message'] = $_LANG['invalid_number'];
    }
    /* 更新：购物车 */
    else
    {
        if(!empty($goods->spec))
        {
            foreach ($goods->spec as  $key=>$val )
            {
                $goods->spec[$key]=intval($val);
            }
        }
        // 更新：添加到购物车
        if (addto_cart($goods->goods_id, $goods->number, $goods->spec, $goods->parent,true))
        {
            if ($_CFG['cart_confirm'] > 2)
            {
                $result['message'] = '';
            }
            else
            {
                $result['message'] = $_CFG['cart_confirm'] == 1 ? $_LANG['addto_cart_success_1'] : $_LANG['addto_cart_success_2'];
            }

            $result['goods_id'] = stripslashes($goods->goods_id);
//            $result['content'] = insert_cart_info();
            $result['one_step_buy'] = 1;
        }
        else
        {
            $result['message']  = $err->last_message();
            $result['error']    = $err->error_no;
            $result['goods_id'] = stripslashes($goods->goods_id);
            if (is_array($goods->spec))
            {
                $result['product_spec'] = implode(',', $goods->spec);
            }
            else
            {
                $result['product_spec'] = $goods->spec;
            }
        }
    }

    $result['confirm_type'] = !empty($_CFG['cart_confirm']) ? $_CFG['cart_confirm'] : 2;
    die($json->encode($result));
}
else
{
	$goods_id = isset($_REQUEST['id']) ? $_REQUEST['id']:'';
	if($goods_id)
	{
		//16:25 2013-07-13
		//clear_cart();
		$_LANG['shortage'] = "对不起，该商品已经库存不足暂停销售。\n你现在要进行缺货登记来预订该商品吗？";
		if(!addto_cart($goods_id))
		{
			//16:25 2013-07-13
			echo '购买失败，请重新购买!';
			exit;
		}
		else
		{
			$goods_order = 1;
			//16:25 2013-07-13
			if($cp=="add_cart"){
				$Loaction = 'cart.php';
			}else{
				$Loaction = 'buy.php?act=checkout';
			}
			ecs_header("Location: $Loaction\n");
			exit;
		}

	}
	else
	{
		ecs_header("Location:index.php\n");
		exit;
	}

}
$smarty->assign('footer', get_footer());
$smarty->display('buy.html');
?>