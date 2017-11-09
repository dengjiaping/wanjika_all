<?php

/**
 * ECSHOP 购物流程
 * ============================================================================
 * 版权所有 2005-2010 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: douqinghua $
 * $Id: flow.php 17218 2011-01-24 04:10:41Z douqinghua $
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require(ROOT_PATH . 'includes/lib_order.php');

/* 载入语言文件 */
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/user.php');
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/shopping_flow.php');

/*------------------------------------------------------ */
//-- INPUT
/*------------------------------------------------------ */

if (!isset($_REQUEST['step']))
{
    $_REQUEST['step'] = "cart";
}

/*------------------------------------------------------ */
//-- PROCESSOR
/*------------------------------------------------------ */

assign_template();
assign_dynamic('flow');
$smarty->assign('flowphp', true);
$position = assign_ur_here(0, $_LANG['shopping_flow']);
$smarty->assign('page_title',       $position['title']);    // 页面标题
$smarty->assign('ur_here',          $position['ur_here']);  // 当前位置

$smarty->assign('categories',       get_categories_tree()); // 分类树
$smarty->assign('helps',            get_shop_help());       // 网店帮助
$smarty->assign('lang',             $_LANG);
$smarty->assign('show_marketprice', $_CFG['show_marketprice']);
$smarty->assign('data_dir',    DATA_DIR);       // 数据目录

/*------------------------------------------------------ */
//-- 添加商品到购物车
/*------------------------------------------------------ */
if ($_REQUEST['step'] == 'add_to_cart')
{
    include_once('includes/cls_json.php');
    unset($_SESSION['one_step_buy']);
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
        if (addto_cart($goods->goods_id, $goods->number, $goods->spec, $goods->parent))
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
            $result['content'] = insert_cart_info();
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
    die($json->encode($result));
}

/*------------------------------------------------------ */
//-- 直接购买
/*------------------------------------------------------ */
if ($_REQUEST['step'] == 'one_step_buy')
{
    //清除结算页session
    unset($_SESSION['flow_order']);
    include_once('includes/cls_json.php');
    $result = array('error' => 0, 'message' => '', 'content' => '', 'goods_id' => '');
    $json  = new JSON;
    if($_SESSION['user_id']<=0)
    {
        $_SESSION['back_act'] = $GLOBALS['_SERVER']['HTTP_REFERER'];
        $result['error']=-1;
        die($json->encode($result));
        exit;
    }
    else
    {
        unset($_SESSION['goods_id']);
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
                $result['content'] = insert_cart_info();
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
        die($json->encode($result));
    }
}

/*------------------------------------------------------ */
//-- 购物车商品勾选状态修改
/*------------------------------------------------------ */
elseif ($_REQUEST['step'] == 'select_cart')
{
    include_once('includes/cls_json.php');
    $json = new JSON;

    $list = json_str_iconv($_REQUEST['flow_id']);

    $r['errorcode']   = 0;
    $r['msg'] = '购物车更新成功！';

    if($list == null){
        $r['errorcode']   = 1;
        $r['msg'] = '请至少选择一件商品！';
        die($json->encode($r));
    }
    select_cart($list);

    die($json->encode($r));
}

/*------------------------------------------------------ */
//-- 购物车商品勾选统计
/*------------------------------------------------------ */
elseif ($_REQUEST['step'] == 'select_cart_count')
{
    include_once('includes/cls_json.php');
    $json = new JSON;
    $list = json_str_iconv($_REQUEST['flow_id']);


    $r['num']   = 0;
    $r['total'] = 0;

    select_cart($list);
    if($list == null){
        die($json->encode($r));
    }
    $discount = compute_discount();
    $discount['format_discount'] = $discount['discount'] > 0 ? price_format($discount['discount']) : null;
    /* 取得商品列表，计算合计 */
    $cart_goods = get_cart_goods(2, $list);
    $r['total'] =price_format($cart_goods['total']['goods_amount']-$discount['discount']);
    $r['goods_amount'] =$cart_goods['total']['goods_amount'];
    $r['num']   = $cart_goods['total']['num_count'];
    $r['discount']   = $discount;

    die($json->encode($r));
}

/*------------------------------------------------------ */
//-- 收货人信息修改
/*------------------------------------------------------ */
elseif ($_REQUEST['step'] == 'select_consignee')
{
    include_once('includes/cls_json.php');
    $json = new JSON;
    $address_id = json_decode($_REQUEST['address_id']);

    $r['errorcode']   = 0;
    $r['msg'] = '收货人地址更新成功！';

    if($address_id == null){
        $r['errorcode']   = 1;
        $r['msg'] = '收货人地址不能为空！';
        die($json->encode($r));
    }

    /* 保存到session */
    $consignee = get_selected_consignee($address_id);
    if(!empty($consignee)){
        $_SESSION['flow_consignee'] = $consignee;
    }

    die($json->encode($r));
}

elseif ($_REQUEST['step'] == 'link_buy')
{
    $goods_id = intval($_GET['goods_id']);

    if (!cart_goods_exists($goods_id,array()))
    {
        addto_cart($goods_id);
    }
    ecs_header("Location:./flow.php\n");
    exit;
}
elseif ($_REQUEST['step'] == 'login')
{
    include_once('languages/'. $_CFG['lang']. '/user.php');

    /*
     * 用户登录注册
     */
    if ($_SERVER['REQUEST_METHOD'] == 'GET')
    {
        $smarty->assign('anonymous_buy', $_CFG['anonymous_buy']);

        /* 检查是否有赠品，如果有提示登录后重新选择赠品 */
        $sql = "SELECT COUNT(*) FROM " . $ecs->table('cart') .
                " WHERE session_id = '" . SESS_ID . "' AND is_gift > 0";
        if ($db->getOne($sql) > 0)
        {
            $smarty->assign('need_rechoose_gift', 1);
        }

        /* 检查是否需要注册码 */
        $captcha = intval($_CFG['captcha']);
        if (($captcha & CAPTCHA_LOGIN) && (!($captcha & CAPTCHA_LOGIN_FAIL) || (($captcha & CAPTCHA_LOGIN_FAIL) && $_SESSION['login_fail'] > 2)) && gd_version() > 0)
        {
            $smarty->assign('enabled_login_captcha', 1);
            $smarty->assign('rand', mt_rand());
        }
        if ($captcha & CAPTCHA_REGISTER)
        {
            $smarty->assign('enabled_register_captcha', 1);
            $smarty->assign('rand', mt_rand());
        }
    }
    else
    {
        include_once('includes/lib_passport.php');
        if (!empty($_POST['act']) && $_POST['act'] == 'signin')
        {
            $captcha = intval($_CFG['captcha']);
            if (($captcha & CAPTCHA_LOGIN) && (!($captcha & CAPTCHA_LOGIN_FAIL) || (($captcha & CAPTCHA_LOGIN_FAIL) && $_SESSION['login_fail'] > 2)) && gd_version() > 0)
            {
                if (empty($_POST['captcha']))
                {
                    show_message($_LANG['invalid_captcha']);
                }

                /* 检查验证码 */
                include_once('includes/cls_captcha.php');

                $validator = new captcha();
                $validator->session_word = 'captcha_login';
                if (!$validator->check_word($_POST['captcha']))
                {
                    show_message($_LANG['invalid_captcha']);
                }
            }

            $_POST['password']=isset($_POST['password']) ? trim($_POST['password']) : '';
            if ($user->login($_POST['username'], $_POST['password'],isset($_POST['remember'])))
            {
                update_user_info();  //更新用户信息
                recalculate_price(); // 重新计算购物车中的商品价格

                /* 检查购物车中是否有商品 没有商品则跳转到首页 */
                $sql = "SELECT COUNT(*) FROM " . $ecs->table('cart') . " WHERE session_id = '" . SESS_ID . "' ";
                if ($db->getOne($sql) > 0)
                {
                    ecs_header("Location: flow.php?step=checkout\n");
                }
                else
                {
                    ecs_header("Location:index.php\n");
                }

                exit;
            }
            else
            {
                $_SESSION['login_fail']++;
                show_message($_LANG['signin_failed'], '', 'flow.php?step=login');
            }
        }
        elseif (!empty($_POST['act']) && $_POST['act'] == 'signup')
        {
            if ((intval($_CFG['captcha']) & CAPTCHA_REGISTER) && gd_version() > 0)
            {
                if (empty($_POST['captcha']))
                {
                    show_message($_LANG['invalid_captcha']);
                }

                /* 检查验证码 */
                include_once('includes/cls_captcha.php');

                $validator = new captcha();
                if (!$validator->check_word($_POST['captcha']))
                {
                    show_message($_LANG['invalid_captcha']);
                }
            }

            if (register(trim($_POST['username']), trim($_POST['password']), trim($_POST['email'])))
            {
                /* 用户注册成功 */
                ecs_header("Location: flow.php?step=consignee\n");
                exit;
            }
            else
            {
                $err->show();
            }
        }
        else
        {
            // TODO: 非法访问的处理
        }
    }
}
elseif ($_REQUEST['step'] == 'consignee')
{
    /*------------------------------------------------------ */
    //-- 收货人信息
    /*------------------------------------------------------ */
    include_once('includes/lib_transaction.php');

    if ($_SERVER['REQUEST_METHOD'] == 'GET')
    {
        /* 取得购物类型 */
        $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;

        /*
         * 收货人信息填写界面
         */

        if (isset($_REQUEST['direct_shopping']))
        {
            $_SESSION['direct_shopping'] = 1;
        }

        /* 取得国家列表、商店所在国家、商店所在国家的省列表 */
        $smarty->assign('country_list',       get_regions());
        $smarty->assign('shop_country',       $_CFG['shop_country']);
        $smarty->assign('shop_province_list', get_regions(1, $_CFG['shop_country']));

        /* 获得用户所有的收货人信息 */
        if ($_SESSION['user_id'] > 0)
        {
            $consignee_list = get_consignee_list($_SESSION['user_id']);

            if (count($consignee_list) < 5)
            {
                /* 如果用户收货人信息的总数小于 5 则增加一个新的收货人信息 */
                $consignee_list[] = array('country' => $_CFG['shop_country'], 'email' => isset($_SESSION['email']) ? $_SESSION['email'] : '');
            }
        }
        else
        {
            if (isset($_SESSION['flow_consignee'])){
                $consignee_list = array($_SESSION['flow_consignee']);
            }
            else
            {
                $consignee_list[] = array('country' => $_CFG['shop_country']);
            }
        }
        $smarty->assign('name_of_region',   array($_CFG['name_of_region_1'], $_CFG['name_of_region_2'], $_CFG['name_of_region_3'], $_CFG['name_of_region_4']));
        $smarty->assign('consignee_list', $consignee_list);

        /* 取得每个收货地址的省市区列表 */
        $province_list = array();
        $city_list = array();
        $district_list = array();
        foreach ($consignee_list as $region_id => $consignee)
        {
            $consignee['country']  = isset($consignee['country'])  ? intval($consignee['country'])  : 0;
            $consignee['province'] = isset($consignee['province']) ? intval($consignee['province']) : 0;
            $consignee['city']     = isset($consignee['city'])     ? intval($consignee['city'])     : 0;

            $province_list[$region_id] = get_regions(1, $consignee['country']);
            $city_list[$region_id]     = get_regions(2, $consignee['province']);
            $district_list[$region_id] = get_regions(3, $consignee['city']);
        }
        $smarty->assign('province_list', $province_list);
        $smarty->assign('city_list',     $city_list);
        $smarty->assign('district_list', $district_list);

        /* 返回收货人页面代码 */
        $smarty->assign('real_goods_count', exist_real_goods(0, $flow_type) ? 1 : 0);
    }
    else
    {
        /*
         * 保存收货人信息
         */
        $consignee = array(
            'address_id'    => empty($_POST['address_id']) ? 0  :   intval($_POST['address_id']),
            'consignee'     => empty($_POST['consignee'])  ? '' :   compile_str(trim($_POST['consignee'])),
            'country'       => empty($_POST['country'])    ? '' :   intval($_POST['country']),
            'province'      => empty($_POST['province'])   ? '' :   intval($_POST['province']),
            'city'          => empty($_POST['city'])       ? '' :   intval($_POST['city']),
            'district'      => empty($_POST['district'])   ? '' :   intval($_POST['district']),
            'email'         => empty($_POST['email'])      ? '' :   compile_str($_POST['email']),
            'address'       => empty($_POST['address'])    ? '' :   compile_str($_POST['address']),
            'zipcode'       => empty($_POST['zipcode'])    ? '' :   compile_str(make_semiangle(trim($_POST['zipcode']))),
            'tel'           => empty($_POST['tel'])        ? '' :   compile_str(make_semiangle(trim($_POST['tel']))),
            'mobile'        => empty($_POST['mobile'])     ? '' :   compile_str(make_semiangle(trim($_POST['mobile']))),
            'sign_building' => empty($_POST['sign_building']) ? '' :compile_str($_POST['sign_building']),
            'best_time'     => empty($_POST['best_time'])  ? '' :   compile_str($_POST['best_time']),
        );

        if ($_SESSION['user_id'] > 0)
        {
            include_once(ROOT_PATH . 'includes/lib_transaction.php');

            /* 如果用户已经登录，则保存收货人信息 */
            $consignee['user_id'] = $_SESSION['user_id'];

            save_consignee($consignee, true);
        }

        /* 保存到session */
        $_SESSION['flow_consignee'] = stripslashes_deep($consignee);

        ecs_header("Location: flow.php?step=checkout\n");
        exit;
    }
}
elseif ($_REQUEST['step'] == 'drop_consignee')
{
    /*------------------------------------------------------ */
    //-- 删除收货人信息
    /*------------------------------------------------------ */
    include_once('includes/lib_transaction.php');

    $consignee_id = intval($_GET['id']);

    if (drop_consignee($consignee_id))
    {
        ecs_header("Location: flow.php?step=consignee\n");
        exit;
    }
    else
    {
        show_message($_LANG['not_fount_consignee']);
    }
}
elseif ($_REQUEST['step'] == 'checkout')
{
    include_once('includes/lib_transaction.php');
    /*------------------------------------------------------ */
    //-- 订单确认
    /*------------------------------------------------------ */
    /* 取得购物类型 */

    $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
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
    if(is_null($flow_overseas))
    {
        $flow_overseas=0;
    }
    /* 获得用户所有的收货人信息 */
    if ($_SESSION['user_id'] > 0)
    {
        $consignee_list = get_consignee_list($_SESSION['user_id']);
        foreach($consignee_list AS $key=>$value)
        {
            $consignee_list[$key]['is_overseas']=$flow_overseas;
        }
        if (count($consignee_list)>=5 && $_SESSION['user_id'] > 0)
        {
            $smarty->assign('address_count', 1);
        }
        if(count($consignee_list)==0)
        {
            $consignee['is_overseas']=$flow_overseas;
            $smarty->assign('consignee', $consignee);
        }
    }
    else
    {
        if (isset($_SESSION['flow_consignee'])){
            $consignee_list = array($_SESSION['flow_consignee']);
        }
        else
        {
            $consignee_list[] = array('country' => $_CFG['shop_country']);
        }
    }
    $smarty->assign('consignee_list', $consignee_list);

    /* 取得国家列表、商店所在国家、商店所在国家的省列表 */
    $smarty->assign('shop_province_list', get_regions(1, $_CFG['shop_country']));
    /* 取得每个收货地址的省市区列表 */
    $province_list = array();
    $city_list = array();
    $district_list = array();
    foreach ($consignee_list as $region_id => $consignee)
    {
        $consignee['country']  = isset($consignee['country'])  ? intval($consignee['country'])  : 0;
        $consignee['province'] = isset($consignee['province']) ? intval($consignee['province']) : 0;
        $consignee['city']     = isset($consignee['city'])     ? intval($consignee['city'])     : 0;

        $province_list[$region_id] = get_regions(1, $consignee['country']);
        $city_list[$region_id]     = get_regions(2, $consignee['province']);
        $district_list[$region_id] = get_regions(3, $consignee['city']);
    }
    $smarty->assign('province_list', $province_list);
    $smarty->assign('city_list',     $city_list);
    $smarty->assign('district_list', $district_list);

    /* 团购标志 */
    if ($flow_type == CART_GROUP_BUY_GOODS)
    {
        $smarty->assign('is_group_buy', 1);
    }
    /* 积分兑换商品 */
    elseif ($flow_type == CART_EXCHANGE_GOODS)
    {
        $smarty->assign('is_exchange_goods', 1);
    }
    else
    {
        //正常购物流程  清空其他购物流程情况
        $_SESSION['flow_order']['extension_code'] = '';
    }

    //判断商品类型为虚拟商品时，跳过收货人填写
    $is_real = 1;
    if(goods_extensioncode($_SESSION['goods_id']) == 'goods_members'){
        $is_real = 0;
        select_cart($_SESSION['goods_id']);
    }
    $smarty->assign('is_real', $is_real);

    /*
     * 检查用户是否已经登录
     * 如果用户已经登录了则检查是否有默认的收货地址
     * 如果没有登录则跳转到登录和注册页面
     */
    if (empty($_SESSION['direct_shopping']) && $_SESSION['user_id'] == 0)
    {
        /* 用户没有登录且没有选定匿名购物，转向到登录页面 */
        ecs_header("Location: flow.php?step=login\n");
        exit;
    }

    /*
     * 检查用户是否为会员身份
     */
    if(!goods_extensioncode($_SESSION['goods_id']) == 'goods_members'){
        $result = check_user_members($_SESSION['user_id']);
        if (!$result)
        {
            /* 用户不是会员，转向到会员充值页面 */
            show_message('', '', '', 'info', false, 'being_members');
            exit;
        }
    }

    /* 检查购物车中是否有商品 */
    $sql = "SELECT COUNT(*) FROM " . $ecs->table('cart') .
        " WHERE session_id = '" . SESS_ID . "' " .
        "AND parent_id = 0 AND is_gift = 0 AND rec_type = '$flow_type' AND is_selected = 1";

    if ($db->getOne($sql) == 0)
    {
        show_message('请至少选择一件商品!', '', '', 'warning');
    }

    //有无默认收货人判断改为提交订单时检查
    if($is_real){
        $consignee_id = -1;
        if(has_consignee($_SESSION['user_id'])){
            $consignee = get_consignee($_SESSION['user_id']);
            if(!empty($consignee['address_id']) && $consignee['address_id'] > 0){
                $consignee_id = $consignee['address_id'];
            }
            else{
                $consignee_id = 0;
            }
        }
        $smarty->assign('consignee_id', $consignee_id);
    }

    /* 取默认地址 */
    $defaultsql = "SELECT ua.*".
        " FROM " . $GLOBALS['ecs']->table('user_address') . "AS ua, ".$GLOBALS['ecs']->table('users').' AS u '.
        " WHERE u.user_id='$_SESSION[user_id]' AND ua.address_id = u.address_id";

    $default_id = $GLOBALS['db']->getRow($defaultsql);
    $smarty->assign('default_id', $default_id['address_id']);
//    if($is_real){
//        $consignee = get_consignee($_SESSION['user_id']);
//
//        /* 检查收货人信息是否完整 */
//        if (!check_consignee_info($consignee, $flow_type))
//        {
//            /* 如果不完整则转向到收货人信息填写界面 */
//            ecs_header("Location: flow.php?step=consignee\n");
//            exit;
//        }
//
//        $_SESSION['flow_consignee'] = $consignee;
//        $smarty->assign('consignee', $consignee);
//    }

    /* 对商品信息赋值 */
    $cart_goods = cart_goods($flow_type); // 取得商品列表，计算合计
    $smarty->assign('goods_list', $cart_goods);
    //清除立即购买SESSION
    unset($_SESSION['one_step_buy']);
    /* 对是否允许修改购物车赋值 */
    if ($flow_type != CART_GENERAL_GOODS || $_CFG['one_step_buy'] == '1' || goods_extensioncode($_SESSION['goods_id']) == 'goods_members')
    {
        $smarty->assign('allow_edit_cart', 0);
    }
    else
    {
        $smarty->assign('allow_edit_cart', 1);
    }

    /*
     * 取得购物流程设置
     */
    $smarty->assign('config', $_CFG);
    /*
     * 取得订单信息
     */
    $order = flow_order_info();

    //确认上次用户使用的支付方式这次还可以不可以再用。
    $goods_ids = array();
    foreach ($cart_goods as $goods)
    {
        $goods_ids[] = $goods['goods_id'];
    }
    $goods_ids = array_unique(array_filter($goods_ids));
    $payment_list = available_payment_list_by_goods($goods_ids, 1, 0);
    $find_payment = false;
    foreach ($payment_list as $payment)
    {
        if ($payment['pay_id'] == $order['pay_id'])
        {
            $find_payment = true;
            break;
        }
    }

    if (!$find_payment)
    {
        $order['pay_id'] = 0;
    }
    $smarty->assign('order', $order);

    /* 计算折扣 */
    if ($flow_type != CART_EXCHANGE_GOODS && $flow_type != CART_GROUP_BUY_GOODS)
    {
        $discount = compute_discount($order['bonus_id']);
        $smarty->assign('discount', $discount['discount']);
        $favour_name = empty($discount['name']) ? '' : join(',', $discount['name']);
        $smarty->assign('your_discount', sprintf($_LANG['your_discount'], $favour_name, price_format($discount['discount'])));
        $smarty->assign('dis_goodslist', $discount['dis_goodslist']);
        $smarty->assign('dis_total_amount', $discount['total_amount']);
    }

    //默认选择申通快递
    $shipping_sql = 'SELECT shipping_id FROM ' . $ecs->table('shipping') . " WHERE `shipping_code` = 'sto_express' AND `enabled` = 1";
    $shipping_id = $db->getOne($shipping_sql);
    if($shipping_id == ""){
        $shipping_id = 0;
    }
    $order['shipping_id'] = $shipping_id;
    /*
     * 计算订单的费用
     */
    $total = order_fee($order, $cart_goods, $consignee);

    //积分兑换校验
    $user_info   = get_user_info($_SESSION['user_id']);
    $user_points = $user_info['pay_points']; // 用户的积分总数
    if ($total['exchange_integral'] > $user_points)
    {
        show_message($_LANG['eg_error_integral'], '', '', 'warning');
    }

    $smarty->assign('total', $total);
    $smarty->assign('shopping_money', sprintf($_LANG['shopping_money'], $total['formated_goods_price']));
    $smarty->assign('market_price_desc', sprintf($_LANG['than_market_price'], $total['formated_market_price'], $total['formated_saving'], $total['save_rate']));

    /* 取得配送列表 */
    $region            = array($consignee['country'], $consignee['province'], $consignee['city'], $consignee['district']);
    $shipping_list     = available_shipping_list($region);
    $cart_weight_price = cart_weight_price($flow_type);
    $insure_disabled   = true;
    $cod_disabled      = true;

    // 查看购物车中是否全为免运费商品，若是则把运费赋为零
    $sql = 'SELECT count(*) FROM ' . $ecs->table('cart') . " WHERE `session_id` = '" . SESS_ID. "' AND `is_selected` = 1 AND `extension_code` != 'package_buy' AND `is_shipping` = 0";
    $shipping_count = $db->getOne($sql);

    foreach ($shipping_list AS $key => $val)
    {
        $shipping_cfg = unserialize_config($val['configure']);
        $shipping_fee = ($shipping_count == 0 AND $cart_weight_price['free_shipping'] == 1) ? 0 : shipping_fee($val['shipping_code'], unserialize($val['configure']),
        $cart_weight_price['weight'], $cart_weight_price['amount'], $cart_weight_price['number']);

        $shipping_list[$key]['format_shipping_fee'] = price_format($shipping_fee, false);
        $shipping_list[$key]['shipping_fee']        = $shipping_fee;
        $shipping_list[$key]['free_money']          = price_format($shipping_cfg['free_money'], false);
        $shipping_list[$key]['insure_formated']     = strpos($val['insure'], '%') === false ?
            price_format($val['insure'], false) : $val['insure'];

        /* 当前的配送方式是否支持保价 */
        if ($val['shipping_id'] == $order['shipping_id'])
        {
            $insure_disabled = ($val['insure'] == 0);
            $cod_disabled    = ($val['support_cod'] == 0);
        }
    }

    $smarty->assign('shipping_list',   $shipping_list);
    $smarty->assign('insure_disabled', $insure_disabled);
    $smarty->assign('cod_disabled',    $cod_disabled);

    /* 取得支付列表 */
    if ($order['shipping_id'] == 0)
    {
        $cod        = true;
        $cod_fee    = 0;
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
                $cod_fee            = $shipping_area_info['pay_fee'];
            }
        }
        else
        {
            $cod_fee = 0;
        }
    }

    //获取可用的支付方式（如果有多个物品，要取多个物品公共的）- add by qihua on 20130815
    $goods_ids = array();
    foreach ($cart_goods as $goods)
    {
        $goods_ids[] = $goods['goods_id'];
    }
    $goods_ids = array_unique(array_filter($goods_ids));
//    $payment_list = available_payment_list_by_goods($goods_ids, 1, $cod_fee);
    $payment_list = available_payment_list(0, $cod_fee);
    $bonus = bonus_info(intval($order['bonus_id']));
    $pay_ids = explode(',',$bonus['pay_ids']);
    // 给货到付款的手续费加<span id>，以便改变配送的时候动态显示
//    $payment_list = available_payment_list(1, $cod_fee);
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
    //过滤掉微信支付
    foreach ($payment_list as $key=>$val)
    {
        if ($val['pay_code'] == 'weixinpay')
        {
            unset($payment_list[$key]);
        }
    }
    $str = "";
    if($order['bonus_id'] > 0)
    {
        $i = 0;
        foreach($payment_list AS $value)
        {
            $str .= $i == 0 ? ('(不支持' . $value['pay_name']) : (',' . $value['pay_name']);
            $i++;
        }
        if(count($payment_list) > 0)
        {
            $str .= ')';
        }
    }

    $smarty->assign('str', $str);
    $smarty->assign('payment_list', $payment_list);

    /* 取得包装与贺卡 */
    if ($total['real_goods_count'] > 0)
    {
        /* 只有有实体商品,才要判断包装和贺卡 */
        if (!isset($_CFG['use_package']) || $_CFG['use_package'] == '1')
        {
            /* 如果使用包装，取得包装列表及用户选择的包装 */
            $smarty->assign('pack_list', pack_list());
        }

        /* 如果使用贺卡，取得贺卡列表及用户选择的贺卡 */
        if (!isset($_CFG['use_card']) || $_CFG['use_card'] == '1')
        {
            $smarty->assign('card_list', card_list());
        }
    }

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
//        $order_max_integral = flow_available_points();
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
                $user_gift[$key]['gift_money_formated'] = price_format($val['type_money'], false);
            }
            $smarty->assign('gift_list', $user_gift);
        }

        // 能使用礼品卡
        $smarty->assign('allow_use_gift', 1);
    }

    /* 如果使用缺货处理，取得缺货处理列表 */
    if (!isset($_CFG['use_how_oos']) || $_CFG['use_how_oos'] == '1')
    {
        if (is_array($GLOBALS['_LANG']['oos']) && !empty($GLOBALS['_LANG']['oos']))
        {
            $smarty->assign('how_oos_list', $GLOBALS['_LANG']['oos']);
        }
    }

    /* 如果能开发票，取得发票内容列表 */
    if ((!isset($_CFG['can_invoice']) || $_CFG['can_invoice'] == '1')
        && isset($_CFG['invoice_content'])
        && trim($_CFG['invoice_content']) != '' && $flow_type != CART_EXCHANGE_GOODS)
    {
        $inv_content_list = explode("\n", str_replace("\r", '', $_CFG['invoice_content']));
        $smarty->assign('inv_content_list', $inv_content_list);

        $inv_type_list = array();
        foreach ($_CFG['invoice_type']['type'] as $key => $type)
        {
            if (!empty($type))
            {
                $inv_type_list[$type] = $type . ' [' . floatval($_CFG['invoice_type']['rate'][$key]) . '%]';
            }
        }
        $smarty->assign('inv_type_list', $inv_type_list);
    }

    /* 保存 session */
    $_SESSION['flow_order'] = $order;
}
elseif ($_REQUEST['step'] == 'select_shipping')
{
    /*------------------------------------------------------ */
    //-- 改变配送方式
    /*------------------------------------------------------ */
    include_once('includes/cls_json.php');
    $json = new JSON;
    $result = array('error' => '', 'content' => '', 'need_insure' => 0);

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
        $result['content']     = $smarty->fetch('library/order_total.lbi');
    }

    echo $json->encode($result);
    exit;
}
elseif ($_REQUEST['step'] == 'select_insure')
{
    /*------------------------------------------------------ */
    //-- 选定/取消配送的保价
    /*------------------------------------------------------ */

    include_once('includes/cls_json.php');
    $json = new JSON;
    $result = array('error' => '', 'content' => '', 'need_insure' => 0);

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

        $result['content'] = $smarty->fetch('library/order_total.lbi');
    }

    echo $json->encode($result);
    exit;
}
elseif ($_REQUEST['step'] == 'select_payment')
{
    /*------------------------------------------------------ */
    //-- 改变支付方式
    /*------------------------------------------------------ */

    include_once('includes/cls_json.php');
    $json = new JSON;
    $result = array('error' => '', 'content' => '', 'need_insure' => 0, 'payment' => 1);

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

        $result['content'] = $smarty->fetch('library/order_total.lbi');
    }

    echo $json->encode($result);
    exit;
}
elseif ($_REQUEST['step'] == 'select_pack')
{
    /*------------------------------------------------------ */
    //-- 改变商品包装
    /*------------------------------------------------------ */

    include_once('includes/cls_json.php');
    $json = new JSON;
    $result = array('error' => '', 'content' => '', 'need_insure' => 0);

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

        $order['pack_id'] = intval($_REQUEST['pack']);

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

        $result['content'] = $smarty->fetch('library/order_total.lbi');
    }

    echo $json->encode($result);
    exit;
}
elseif ($_REQUEST['step'] == 'select_card')
{
    /*------------------------------------------------------ */
    //-- 改变贺卡
    /*------------------------------------------------------ */

    include_once('includes/cls_json.php');
    $json = new JSON;
    $result = array('error' => '', 'content' => '', 'need_insure' => 0);

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

        $order['card_id'] = intval($_REQUEST['card']);

        /* 保存 session */
        $_SESSION['flow_order'] = $order;

        /* 计算订单的费用 */
        $total = order_fee($order, $cart_goods, $consignee);
        $smarty->assign('total', $total);

        /* 取得可以得到的积分和红包 */
        $smarty->assign('total_integral', cart_amount(false, $flow_type) - $order['bonus'] - $total['integral_money']);
        $smarty->assign('total_bonus',    price_format(get_total_bonus(), false));

        /* 团购标志 */
        if ($flow_type == CART_GROUP_BUY_GOODS)
        {
            $smarty->assign('is_group_buy', 1);
        }

        $result['content'] = $smarty->fetch('library/order_total.lbi');
    }

    echo $json->encode($result);
    exit;
}
elseif ($_REQUEST['step'] == 'change_surplus')
{
    /*------------------------------------------------------ */
    //-- 改变余额
    /*------------------------------------------------------ */
    include_once('includes/cls_json.php');

    $surplus   = floatval($_GET['surplus']);
    $user_info = user_info($_SESSION['user_id']);

    if ($user_info['user_money'] + $user_info['credit_line'] < $surplus)
    {
        $result['error'] = $_LANG['surplus_not_enough'];
    }
    else
    {
        /* 取得购物类型 */
        $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;

        /* 取得购物流程设置 */
        $smarty->assign('config', $_CFG);
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

            $result['content'] = $smarty->fetch('library/order_total.lbi');
        }
    }

    $json = new JSON();
    die($json->encode($result));
}
elseif ($_REQUEST['step'] == 'change_integral')
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

            $result['content'] = $smarty->fetch('library/order_total.lbi');
            $result['error'] = '';
        }
    }

    $json = new JSON();
    die($json->encode($result));
}
elseif ($_REQUEST['step'] == 'change_bonus')
{
    /*------------------------------------------------------ */
    //-- 改变红包
    /*------------------------------------------------------ */
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

        $payment_list = available_payment_list(0, $cod_fee);
        $bonus_payment_list = array();
        $bonus = bonus_info(intval($_GET['bonus']));
        $pay_ids = explode(',',$bonus['pay_ids']);
        //过滤掉微信支付
        foreach ($payment_list as $key=>$val)
        {
            if ($val['pay_code'] == 'weixinpay')
            {
                unset($payment_list[$key]);
            }
            //过滤掉红包限制的支付
            foreach($pay_ids AS $v)
            {
                if($val['pay_id'] == $v)
                {
                    unset($payment_list[$key]);
                }
            }

        }

        /* 过滤掉余额支付方式 */
        if(is_array($payment_list))
        {
            foreach ($payment_list as $key => $payment)
            {
                if ($payment['pay_code'] == 'balance')
                {
                    unset($payment_list[$key]);
                }
            }
        }
        $str = "";
        if($_GET['bonus'] > 0)
        {
            $i = 0;
            foreach($payment_list AS $value)
            {
                $str .= $i == 0 ? ('(不支持' . $value['pay_name']) : (',' . $value['pay_name']);
                $i++;
            }
            if(count($payment_list) > 0)
            {
                $str .= ')';
            }
        }
        $result['msg']=$str;
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

        $result['content'] = $smarty->fetch('library/order_total.lbi');
    }

    $json = new JSON();
    die($json->encode($result));
}
//礼品卡
elseif ($_REQUEST['step'] == 'change_gift')
{
    /*------------------------------------------------------ */
    //-- 改变红包
    /*------------------------------------------------------ */
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
        //		echo "<br />";
        //		print_r($total);
        $smarty->assign('total', $total);
        //print_r($total);
        /* 团购标志 */
        if ($flow_type == CART_GROUP_BUY_GOODS)
        {
            $smarty->assign('is_group_buy', 1);
        }

        $result['content'] = $smarty->fetch('library/order_total.lbi');
    }

    $json = new JSON();
    die($json->encode($result));
}
elseif ($_REQUEST['step'] == 'change_needinv')
{
    /*------------------------------------------------------ */
    //-- 改变发票的设置
    /*------------------------------------------------------ */
    include_once('includes/cls_json.php');
    $result = array('error' => '', 'content' => '');
    $json = new JSON();
    $_GET['inv_type'] = !empty($_GET['inv_type']) ? json_str_iconv(urldecode($_GET['inv_type'])) : '';
    $_GET['invPayee'] = !empty($_GET['invPayee']) ? json_str_iconv(urldecode($_GET['invPayee'])) : '';
    $_GET['inv_content'] = !empty($_GET['inv_content']) ? json_str_iconv(urldecode($_GET['inv_content'])) : '';

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
            die($json->encode($result));
        }
    }
    /* 对商品信息赋值 */
    $cart_goods = cart_goods($flow_type); // 取得商品列表，计算合计

    if (empty($cart_goods))
    {
        $result['error'] = $_LANG['no_goods_in_cart'];
        die($json->encode($result));
    }
    else
    {
        /* 取得购物流程设置 */
        $smarty->assign('config', $_CFG);

        /* 取得订单信息 */
        $order = flow_order_info();

        if (isset($_GET['need_inv']) && intval($_GET['need_inv']) == 1)
        {
            $order['need_inv']    = 1;
            $order['inv_type']    = trim(stripslashes($_GET['inv_type']));
            $order['inv_payee']   = trim(stripslashes($_GET['inv_payee']));
            $order['inv_content'] = trim(stripslashes($_GET['inv_content']));
        }
        else
        {
            $order['need_inv']    = 0;
            $order['inv_type']    = '';
            $order['inv_payee']   = '';
            $order['inv_content'] = '';
        }

        /* 计算订单的费用 */
        $total = order_fee($order, $cart_goods, $consignee);
        $smarty->assign('total', $total);

        /* 团购标志 */
        if ($flow_type == CART_GROUP_BUY_GOODS)
        {
            $smarty->assign('is_group_buy', 1);
        }

        die($smarty->fetch('library/order_total.lbi'));
    }
}
elseif ($_REQUEST['step'] == 'change_oos')
{
    /*------------------------------------------------------ */
    //-- 改变缺货处理时的方式
    /*------------------------------------------------------ */

    /* 取得订单信息 */
    $order = flow_order_info();

    $order['how_oos'] = intval($_GET['oos']);

    /* 保存 session */
    $_SESSION['flow_order'] = $order;
}
elseif ($_REQUEST['step'] == 'check_surplus')
{
    /*------------------------------------------------------ */
    //-- 检查用户输入的余额
    /*------------------------------------------------------ */
    $surplus   = floatval($_GET['surplus']);
    $user_info = user_info($_SESSION['user_id']);

    if (($user_info['user_money'] + $user_info['credit_line'] < $surplus))
    {
        die($_LANG['surplus_not_enough']);
    }

    exit;
}
elseif ($_REQUEST['step'] == 'check_integral')
{
    /*------------------------------------------------------ */
    //-- 检查用户输入的余额
    /*------------------------------------------------------ */
    $points      = floatval($_GET['integral']);
    $is_real_amount    = floatval($_GET['amount']);
    $user_info   = user_info($_SESSION['user_id']);

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
        die($_LANG['integral_not_enough']);
    }

    if ($points > $flow_points)
    {
        die(sprintf($_LANG['integral_too_much'], $flow_points));
    }

    exit;
}
/*------------------------------------------------------ */
//-- 完成所有订单操作，提交到数据库
/*------------------------------------------------------ */
elseif ($_REQUEST['step'] == 'done')
{
    include_once('includes/lib_clips.php');
    include_once('includes/lib_payment.php');

    /* 取得购物类型 */
    $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;

    /* 检查购物车中是否有商品 */
    $sql = "SELECT COUNT(*) FROM " . $ecs->table('cart') .
        " WHERE session_id = '" . SESS_ID . "' " .
        "AND parent_id = 0 AND is_gift = 0 AND rec_type = '$flow_type' AND is_selected = 1";
    if ($db->getOne($sql) == 0)
    {
        show_message($_LANG['no_goods_in_cart'], '', '', 'warning');
    }

    /*
     * 检查用户是否已经登录
     * 如果用户已经登录了则检查是否有默认的收货地址
     * 如果没有登录则跳转到登录和注册页面
     */
    if (empty($_SESSION['direct_shopping']) && $_SESSION['user_id'] == 0)
    {
        /* 用户没有登录且没有选定匿名购物，转向到登录页面 */
        ecs_header("Location: flow.php?step=login\n");
        exit;
    }
    /*
     * 检查用户是否为会员身份
     */
    if(!goods_extensioncode($_SESSION['goods_id']) == 'goods_members'){
        $result = check_user_members($_SESSION['user_id']);
        if (!$result)
        {
            /* 用户不是会员，转向到会员充值页面 */
            ecs_header("Location:./\n");
            exit;
        }
    }

    //判断商品类型为虚拟商品时，跳过收货人填写
    $is_real = true;
    if(goods_extensioncode($_SESSION['goods_id']) == 'goods_members'){
        $is_real = false;
    }
    if($is_real){
        $consignee = get_consignee($_SESSION['user_id']);

        /* 检查收货人信息是否完整 */
        if (!check_consignee_info($consignee, $flow_type))
        {
            show_message('收货人信息不能为空', '', '', 'warning');
            /* 如果不完整则转向到收货人信息填写界面 */
//            ecs_header("Location: flow.php?step=consignee\n");
//            exit;
        }
    }

    $_POST['how_oos'] = isset($_POST['how_oos']) ? intval($_POST['how_oos']) : 0;
    $_POST['card_message'] = isset($_POST['card_message']) ? compile_str($_POST['card_message']) : '';
    $_POST['inv_type'] = !empty($_POST['inv_type']) ? compile_str($_POST['inv_type']) : '';
    $_POST['inv_payee'] = isset($_POST['inv_payee']) ? compile_str($_POST['inv_payee']) : '';
    $_POST['inv_content'] = isset($_POST['inv_content']) ? compile_str($_POST['inv_content']) : '';
    $_POST['postscript'] = isset($_POST['postscript']) ? compile_str($_POST['postscript']) : '';

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
        'shipping_id'     => $shipping_id,
        'pay_id'          => intval($payment_id),
        'pack_id'         => isset($_POST['pack']) ? intval($_POST['pack']) : 0,
        'card_id'         => isset($_POST['card']) ? intval($_POST['card']) : 0,
        'card_message'    => trim($_POST['card_message']),
        'surplus'         => isset($_POST['surplus']) ? floatval($_POST['surplus']) : 0.00,
        'integral'        => isset($_POST['integral']) ? intval($_POST['integral']) : 0,
        'bonus_id'        => isset($_POST['bonus']) ? intval($_POST['bonus']) : 0,
        'gift_id'        => isset($_POST['gift']) ? intval($_POST['gift']) : 0,
        'need_inv'        => empty($_POST['need_inv']) ? 0 : 1,
        'inv_type'        => $_POST['inv_type'],
        'inv_payee'       => trim($_POST['inv_payee']),
        'inv_content'     => $_POST['inv_content'],
        'postscript'      => trim($_POST['postscript']),
        'how_oos'         => isset($_LANG['oos'][$_POST['how_oos']]) ? addslashes($_LANG['oos'][$_POST['how_oos']]) : '',
        'need_insure'     => isset($_POST['need_insure']) ? intval($_POST['need_insure']) : 0,
        'user_id'         => $_SESSION['user_id'],
        'add_time'        => gmtime(),
        'order_status'    => OS_UNCONFIRMED,
        'shipping_status' => SS_UNSHIPPED,
        'pay_status'      => PS_UNPAYED,
        'agency_id'       => get_agency_by_regions(array($consignee['country'], $consignee['province'], $consignee['city'], $consignee['district']))
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
    elseif (isset($_POST['bonus_sn']))
    {
        $bonus_sn = trim($_POST['bonus_sn']);
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
                $sql = "UPDATE " . $ecs->table('user_bonus') . " SET user_id = '$user_id',binding_time = '$now',use_start_datetime = '$bonus[use_start_date]',use_end_datetime = '$bonus[use_end_date]' WHERE bonus_id = '$bonus[bonus_id]' LIMIT 1";
                $db->query($sql);
                if($bonus['use_datetime'] == 1)
                {
                    //按起止日期计算
                    $use_effective_date = $now+$bonus['use_effective_date']*24 * 3600;
                    $m_sql = "UPDATE " .$ecs->table('user_bonus'). " SET ".
                        "use_start_datetime  = '$now', ".
                        "use_end_datetime    = '$use_effective_date' ".
                        "WHERE bonus_id   = '$bonus[type_id]'";
                    $db->query($m_sql);
                }
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
    /* 订单中的商品 */
    $cart_goods = cart_goods($flow_type);

    if (empty($cart_goods))
    {
        show_message($_LANG['no_goods_in_cart'], $_LANG['back_home'], './', 'warning');
    }

    //查看物品是否有货和上架
    $goods_is_lack = false;
    $lack_goods_name = '';
    foreach ($cart_goods as $goods)
    {
        //注释促销活动时间内只允许购买一件商品的规则
//        $only_one_time = gmtime();
//        if($goods['promote_start_date']< $only_one_time && $only_one_time < $goods['promote_end_date'])
//        {
//            $goods_count = get_promote_count($goods);
//            if($goods_count>0)
//            {
//                show_message(sprintf("非常抱歉，您选择的商品 %s 仅限购买1件", $goods['goods_name']), '', '', 'warning');
//            }
//        }
        //查看商品数量、其中是否包含立即购买商品
        if($goods['max_number']!=0)
        {
            if($goods['goods_number']>$goods['max_number'])
            {
                show_message('您购买的商品中超过最大购买数量，请到购物车修改重新提交订单！');
            }
        }
        if(count($cart_goods)>1)
        {
            if($goods['is_immediately']==1)
            {
                show_message('您购买的商品中有限制商品，请到购物车修改重新提交订单！');
            }
        }
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
    if($is_real){
        if (!preg_match("/^[\x{4e00}-\x{9fa5}]+$/u",$consignee['consignee']))
        {
            show_message('收货人姓名必须为中文！');
        }
        if (!preg_match("/^[\x{4e00}-\x{9fa5}a-zA-Z0-9_\-]+$/u",$consignee['address']))
        {
            show_message('详细地址有误！');
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
    $order['bonus']        = $total['bonus'];
    $order['goods_amount'] = $total['goods_price'];
    $order['discount']     = $total['discount'];
    $order['surplus']      = $total['surplus'];
    $order['tax']          = $total['tax'];

    //积分兑换校验
    $user_info   = get_user_info($_SESSION['user_id']);
    $user_points = $user_info['pay_points']; // 用户的积分总数
    if ($total['exchange_integral'] > $user_points)
    {
        show_message($_LANG['eg_error_integral'], '', '', 'warning');
    }

    // 购物车中的商品能享受红包支付的总额
    $discount_amout = compute_discount_amount($order['bonus_id']);
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

    /* 商品包装 */
    if ($order['pack_id'] > 0)
    {
        $pack               = pack_info($order['pack_id']);
        $order['pack_name'] = addslashes($pack['pack_name']);
    }
    $order['pack_fee'] = $total['pack_fee'];

    /* 祝福贺卡 */
    if ($order['card_id'] > 0)
    {
        $card               = card_info($order['card_id']);
        $order['card_name'] = addslashes($card['card_name']);
    }
    $order['card_fee']      = $total['card_fee'];

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
            show_message($_LANG['balance_not_enough']);
        }
        else
        {
            $order['surplus'] = $order['order_amount'];
            $order['order_amount'] = 0;
        }
    }

    //订单金额四舍五入到毛
    $order['order_amount']  = number_format($total['amount'], 2, '.', '');
    $show_pay=0;
    /* 如果订单金额为0（使用余额或积分或红包支付），修改订单状态为已确认、已付款 */
    if ($order['order_amount'] <= 0)
    {
        $show_pay=1;
        $order['order_status'] = OS_CONFIRMED;
        $order['confirm_time'] = gmtime();
        $order['pay_status']   = PS_PAYED;
        $order['pay_time']     = gmtime();
        $order['order_amount'] = 0;
    }
    $smarty->assign('show_pay', $show_pay);
    if($order['pay_id']==18)
    {
        $order['order_status'] = OS_CONFIRMED;
    }

    $order['integral_money']   = $total['integral_money'];
    $order['integral']         = $total['integral'];

    if ($order['extension_code'] == 'exchange_goods')
    {
        $order['integral_money']   = 0;
        $order['integral']         = $total['exchange_integral'];
    }

    $order['from_ad']          = !empty($_SESSION['from_ad']) ? $_SESSION['from_ad'] : '0';
    $order['referer']          = !empty($_SESSION['referer']) ? addslashes($_SESSION['referer']) : '';

    /* 记录扩展信息 */
    if ($flow_type != CART_GENERAL_GOODS)
    {
        $order['extension_code'] = $_SESSION['extension_code'];
        $order['extension_id'] = $_SESSION['extension_id'];
    }

    $affiliate = unserialize($_CFG['affiliate']);
    if(isset($affiliate['on']) && $affiliate['on'] == 1 && $affiliate['config']['separate_by'] == 1)
    {
        //推荐订单分成
        $parent_id = get_affiliate();
        if($user_id == $parent_id)
        {
            $parent_id = 0;
        }
    }
    elseif(isset($affiliate['on']) && $affiliate['on'] == 1 && $affiliate['config']['separate_by'] == 0)
    {
        //推荐注册分成
        $parent_id = 0;
    }
    else
    {
        //分成功能关闭
        $parent_id = 0;
    }
    $order['parent_id'] = $parent_id;
    //标记会员充值订单
    if(!$is_real){
        $order['extension_code'] = 'goods_members';
    }

    $warn_goods = array();
    /* 检查商品库存 */
    /* 如果使用库存，且下订单时减库存，则减少库存 */
    if ($_CFG['use_storage'] == '1' && $_CFG['stock_dec_time'] == SDT_PLACE)
    {
        $cart_goods_stock = get_cart_goods(1);
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
    $order['order_source'] = 'web';
    //应赠送积分倍数
    $rate = get_member_integralrate($_SESSION['user_id']);
    $order['integrals'] = $rate;

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
                "order_id, goods_id, goods_name, goods_sn, product_id, goods_number, market_price, ".
                "goods_price, goods_attr, is_real, extension_code, parent_id, is_gift, goods_attr_id, supplier_id) ".
            " SELECT '$new_order_id', t1.goods_id, t1.goods_name, t1.goods_sn, t1.product_id, t1.goods_number, t1.market_price, ".
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
    $warehouse_type = order_split($order['order_id']);
    if(count($warehouse_type) > 1)
    {
        $sql = "UPDATE ". $ecs->table('order_info') ." SET supplier_status=".SS_NEED." WHERE order_id=".$order['order_id'];
        $db->query($sql);
    }
    else
    {
        $status = SS_UNNEED;
        //判断是否是海淘订单
        $is_overseas = is_overseas($order['order_id']);
        if($is_overseas){
            $goods_number = get_ordergoods_num($order['order_id']);
            if($goods_number == 1 || $order['goods_amount'] <= 2000){
                $source = get_shipping_source($order['order_id']);
                if($source == 1){
                    //创建海淘任务
                    create_kjt_task($order['order_sn']);
                }
            }
            else{
                $status = SS_NEED;
            }
            //更新订单是否为跨境通标识位
            set_kjt_order($order['order_id']);
        }
        $sql = "UPDATE ". $ecs->table('order_info') ." SET supplier_status=".$status." WHERE order_id=".$order['order_id'];
        $db->query($sql);
    }

    $goods_detail = array();
    $dis_goodslist = array();
    $bonus_goodslist = array();
    $dis_total_amount = 0;
    $bonus_total_amount = 0;
    if(!empty($_POST['dis_goodslist']) && $_POST['dis_total_amount'] > 0){
        $dis_goodslist = explode(',',$_POST['dis_goodslist']);
        $dis_total_amount = intval($_POST['dis_total_amount']);
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
            $bonus = intval($goods["goods_price"]) / $bonus_total_amount * $order["bonus"];
            $bonus = number_format($bonus - 0.05, 1, '.', '');
            $goods_detail[$goods["goods_id"]]['bonus'] = $bonus;
            $return_price -= $bonus;
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
        $bonus = $v['bonus'];
        $gift = $v['gift'];
        $money = $v['money'];
        $goods_id = $k;
        $sql = "UPDATE ". $ecs->table('order_goods') ." SET refund_discount=$discount,refund_bonus=$bonus,refund_gift=$gift,refund_money=$money,refund_num=goods_number WHERE goods_id=$goods_id";
        $db->query($sql);
    }
    /* 修改拍卖活动状态 */
    if ($order['extension_code']=='auction')
    {
        $sql = "UPDATE ". $ecs->table('goods_activity') ." SET is_finished='2' WHERE act_id=".$order['extension_id'];
        $db->query($sql);
    }


    if ($order['gift_id'] > 0 && $temp_amout > 0)
    {
        //		print_r($total);
        use_gift($order,$total);
    }
    /*********增加亿起发代码start*********/
    if($_COOKIE['yiqifa'] !=null)
    {
        $cookie_yqf = urldecode($_COOKIE['yiqifa']);
        $cookie_yqf = explode(':',$cookie_yqf);
        if($cookie_yqf[2] == 18432)
        {
            $sql = "INSERT INTO " .$GLOBALS['ecs']->table('yqf_cookie'). " (cookie, order_id)" .
                " VALUES ('yiqifa', '$new_order_id')";
            $db->query($sql);
            include_once(dirname(__FILE__).'/advertiser/Sender.php');
            $order_yqf= new Order();
            $yqffare=$order['bonus']+$order['gift_money'];
            $order_yqf-> setOrderNo($order['order_sn']);
            $order_yqf-> setOrderTime($order['add_time']);  // 设置下单时间
            $order_yqf-> setUpdateTime($order['pay_time']); // 设置订单更新时间，如果没有下单时间，要提前对接人提前说明
            $order_yqf-> setCampaignId($cookie_yqf[2]);                 // 测试时使用"101"，正式上线之后活动id必须要从cookie中获取
            $order_yqf-> setFeedback($cookie_yqf[3]);			// 测试时使用"101"，正式上线之后活动id必须要从cookie中获取
            $order_yqf-> setFare($order['shipping_fee']);                        // 设置邮费
            $order_yqf-> setFavorable($yqffare);                   // 设置优惠券
//            $order_yqf-> setFavorableCode("30YHM");
            $order_yqf-> setOrderStatus($order['order_status']);             // 设置订单状态
            $order_yqf-> setPaymentStatus($order['pay_status']);   				// 设置支付状态
            $order_yqf-> setPaymentType($order['pay_name']);		// 支付方式

            $product = order_goods($order['order_id']);
            foreach($product as $key=>$value)
            {
                $pro = new Product();                           // 设置商品集合1
                //$pro -> setOrderNo($order_yqf-> getOrderNo());     // 设置订单编号，订单编号要上下对应
                $pro -> setProductNo($value['goods_sn']);                   // 设置商品编号
                $pro -> setName($value['goods_name']);                   // 设置商品名称
                $pro -> setCategory($value['is_real']);                    // 设置商品类型
                $order_goods_id= $value['goods_id'];
                $goods_sql="SELECT cat_id FROM " .$ecs->table('goods') . " WHERE goods_id='$order_goods_id'";
                $order_cat_id = $db->getOne($goods_sql);
                $test= get_parent_cats($order_cat_id);
                foreach ($test AS $val)
                {
                    $good_name=$val['cat_id'];
                }
                switch($good_name)
                {
                    case 572:
                        array_pop($test);
                        foreach ($test AS $val)
                        {
                            $good_name=$val['cat_id'];
                        }
                        switch($good_name)
                        {
                            case 577:
                                $commissiontype='A';
                                break;
                            case 575:
                                $commissiontype='B';
                                break;
                            case 574:
                                $commissiontype='C';
                                break;
                            default:
                                $commissiontype='Z';
                                break;
                        }
                        break;
                    case 44:
                        $commissiontype='D';
                        break;
                    case 139:
                        $commissiontype='E';
                        break;
                    case 311:
                        $commissiontype='F';
                        break;
                    default:
                        $commissiontype='Z';
                }
                $pro -> setCommissionType($commissiontype);                 // 设置佣金类型，如：普通商品 佣金比例是10%、佣金编号（可自行定义然后通知双方商务）A
                $pro -> setAmount($value['goods_number']);                         // 设置商品数量
                $pro -> setPrice($value['goods_price']);                       // 设置商品价格
                $products[$key]=$pro;
            }
            $order_yqf-> setProducts($products);

            $sender = new Sender();
            $sender -> setOrder($order_yqf);
            $sender -> sendOrder();
        }
    }
    /*********增加亿起发代码end*********/

    /* 处理余额、积分、红包 */
    if ($order['user_id'] > 0 && $order['surplus'] > 0)
    {
        log_account_change($order['user_id'], $order['surplus'] * (-1), 0, 0, 0, sprintf($_LANG['pay_order'], $order['order_sn']));
    }
    if ($order['user_id'] > 0 && $order['integral'] > 0)
    {
        log_account_change($order['user_id'], 0, 0, 0, $order['integral'] * (-1), sprintf($_LANG['pay_order'], $order['order_sn']));
    }


    if ($order['bonus_id'] > 0 && $temp_amout > 0)
    {
        use_bonus($order['bonus_id'], $new_order_id);
    }


    /* 给商家发邮件 */
    /* 增加是否给客服发送邮件选项 */
    if ($_CFG['send_service_email'] && $_CFG['service_email'] != '')
    {
        $tpl = get_mail_template('remind_of_new_order');
        $smarty->assign('order', $order);
        $smarty->assign('goods_list', $cart_goods);
        $smarty->assign('shop_name', $_CFG['shop_name']);
        $smarty->assign('send_date', date($_CFG['time_format']));
        $content = $smarty->fetch('str:' . $tpl['template_content']);
        send_mail($_CFG['shop_name'], $_CFG['service_email'], $tpl['template_subject'], $content, $tpl['is_html']);
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

    /* 如果需要，发短信 */
    if ($_CFG['sms_order_placed'] == '1' && $_CFG['sms_shop_mobile'] != '')
    {
        include_once('includes/cls_sms.php');
        $sms = new sms();
        $msg = $order['pay_status'] == PS_UNPAYED ?
            $_LANG['order_placed_sms'] : $_LANG['order_placed_sms'] . '[' . $_LANG['sms_paid'] . ']';
        $sms->send($_CFG['sms_shop_mobile'], sprintf($msg, $order['consignee'], $order['tel']),'', 13,1);
    }

    /* 如果订单金额为0 处理虚拟卡 */
    if ($order['order_amount'] <= 0)
    {
        //礼品卡购买海淘商品取支付流水号
        if(count($warehouse_type) == 1 && is_overseas($order['order_id']))
        {
            $tradeno = get_pay_tradeno();
            update_trade_no($order['order_sn'], $tradeno);
            pay_tradeno_used($tradeno,$order['order_sn'],1);
        }
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
                        log_account_change($order['user_id'], 0, 0, intval($integral['rank_points']), intval($integral['custom_points']), sprintf($_LANG['order_gift_integral'], $order['order_sn']),99,$order['integrals'],$order['order_sn']);

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
                    log_account_change($order['user_id'], 0, 0, intval($integral['rank_points']), intval($integral['custom_points']), sprintf($_LANG['order_gift_integral'], $order['order_sn']),99,$order['integrals'],$order['order_sn']);

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
        /* 插入支付日志 */
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
        $pay_ids=null;
        if($order['bonus_id'] > 0)
        {
            $bonus_payment_list=array();
            $bonus = bonus_info(intval($order['bonus_id']));
            if($bonus['pay_ids']!=null)
            {
                $pay_ids = explode(',',$bonus['pay_ids']);
            }
        }
        //过滤掉微信支付
        foreach ($payment_list as $key=>$val)
        {
            if ($val['pay_code'] == 'weixinpay')
            {
                unset($payment_list[$key]);
            }
            //过滤国美支付
            if ($order['order_amount'] < 300 && $val['pay_code'] == 'guomeipay')
            {
                unset($payment_list[$key]);
            }
            else
            {
                if($pay_ids != null)
                {
                    //过滤掉红包限制的支付
                    foreach($pay_ids AS $v)
                    {
                        if($val['pay_id'] == $v)
                        {
                            if(isset($payment_list[$key]))
                            {
                                $bonus_payment_list[]=$payment_list[$key];
                            }
                        }
                    }
                }
            }
        }

        /* 过滤掉余额支付方式 */
        if(is_array($payment_list))
        {
            foreach ($payment_list as $key => $payment)
            {
                if ($payment['pay_code'] == 'balance')
                {
                    unset($payment_list[$key]);
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

    //收货地址
    /* 取得区域名 */
    $sql = "SELECT concat(IFNULL(p.region_name, ''), " .
        "'  ', IFNULL(t.region_name, ''), '  ', IFNULL(d.region_name, '')) AS region " .
        "FROM " . $ecs->table('order_info') . " AS u " .
        "LEFT JOIN " . $ecs->table('region') . " AS p ON u.province = p.region_id " .
        "LEFT JOIN " . $ecs->table('region') . " AS t ON u.city = t.region_id " .
        "LEFT JOIN " . $ecs->table('region') . " AS d ON u.district = d.region_id " .
        "WHERE u.order_id = '$order[order_id]'";
    $region = $db->getOne($sql);
    if($is_real){
        if(!empty($region)){
            $order["address_name"] = $region.' '.$order["address"];
        }
    }
    /* 清空购物车 */
    clear_cart($flow_type,false);
    /* 清除缓存，否则买了商品，但是前台页面读取缓存，商品数量不减少 */
    clear_all_files();


    /* 取得支付信息，生成支付代码 */
//    if ($order['order_amount'] > 0)
//    {
//        $payment = payment_info($order['pay_id']);
//
//        include_once('includes/modules/payment/' . $payment['pay_code'] . '.php');
//
//        $pay_obj    = new $payment['pay_code'];
//
//
//        if ($payment['pay_code'] == 'olpay' || $payment['pay_code'] == 'ebcolpay')
//        {
//            $bankradios = '';
//            if(isset($_POST['bankradios'])){
//                $bankradios = $_POST['bankradios'];
//            }
//            $sql = "UPDATE ". $ecs->table('order_info') ." SET inv_content='$bankradios' WHERE order_id=".$new_order_id;
//            $db->query($sql);
//            $pay_online = $pay_obj->get_code($order, unserialize_config($payment['pay_config']), $_POST['bankradios'], $bank_code_arr);
//        }
//        else
//        {
//            $pay_online = $pay_obj->get_code($order, unserialize_config($payment['pay_config']));
//        }
//
//        $order['pay_desc'] = $payment['pay_desc'];
//
//        $smarty->assign('pay_online', $pay_online);
//    }



    if(!empty($order['shipping_name']))
    {
        $order['shipping_name']=trim(stripcslashes($order['shipping_name']));
    }

    /* 订单信息 */
    $smarty->assign('order',      $order);
    $smarty->assign('total',      $total);
    $smarty->assign('is_real', $is_real);
    //广告投放参数
    foreach($cart_goods as $key=>$val){
        $info = get_cat_info($val['goods_id']);
        $cart_goods[$key]['cat_id'] = $info['cat_id'];
        $cart_goods[$key]['cat_name'] = $info['cat_name'];
    }
    $smarty->assign('user_id', $user_id);
    $smarty->assign('goods_list', $cart_goods);
    $smarty->assign('order_submit_back', sprintf($_LANG['order_submit_back'], $_LANG['back_home'], $_LANG['goto_user_center'])); // 返回提示

    user_uc_call('add_feed', array($order['order_id'], BUY_GOODS)); //推送feed到uc
    unset($_SESSION['flow_consignee']); // 清除session中保存的收货人信息
    unset($_SESSION['flow_order']);
    unset($_SESSION['direct_shopping']);
    unset($_SESSION['goods_id']);
}

/*------------------------------------------------------ */
//-- 更新购物车
/*------------------------------------------------------ */

elseif ($_REQUEST['step'] == 'update_cart')
{
    if (isset($_POST['goods_number']) && is_array($_POST['goods_number']))
    {
        flow_update_cart($_POST['goods_number']);
    }
    ecs_header("Location: flow.php?step=cart\n");
//    show_message($_LANG['update_cart_notice'], $_LANG['back_to_cart'], 'flow.php');
    exit;
}
/*------------------------------------------------------ */
//-- 删除购物车中的商品
/*------------------------------------------------------ */

elseif ($_REQUEST['step'] == 'drop_goods')
{
    $rec_id = intval($_GET['id']);
    flow_drop_cart_goods($rec_id);

    ecs_header("Location: flow.php\n");
    exit;
}

/* 把优惠活动加入购物车 */
elseif ($_REQUEST['step'] == 'add_favourable')
{
    /* 取得优惠活动信息 */
    $act_id = intval($_POST['act_id']);
    $favourable = favourable_info($act_id);
    if (empty($favourable))
    {
        show_message($_LANG['favourable_not_exist']);
    }

    /* 判断用户能否享受该优惠 */
    if (!favourable_available($favourable))
    {
        show_message($_LANG['favourable_not_available']);
    }

    /* 检查购物车中是否已有该优惠 */
    $cart_favourable = cart_favourable();
    if (favourable_used($favourable, $cart_favourable))
    {
        show_message($_LANG['favourable_used']);
    }

    /* 赠品（特惠品）优惠 */
    if ($favourable['act_type'] == FAT_GOODS)
    {
        /* 检查是否选择了赠品 */
        if (empty($_POST['gift']))
        {
            show_message($_LANG['pls_select_gift']);
        }

        /* 检查是否已在购物车 */
        $sql = "SELECT goods_name" .
                " FROM " . $ecs->table('cart') .
                " WHERE session_id = '" . SESS_ID . "'" .
                " AND rec_type = '" . CART_GENERAL_GOODS . "'" .
                " AND is_gift = '$act_id'" .
                " AND goods_id " . db_create_in($_POST['gift']);
        $gift_name = $db->getCol($sql);
        if (!empty($gift_name))
        {
            show_message(sprintf($_LANG['gift_in_cart'], join(',', $gift_name)));
        }

        /* 检查数量是否超过上限 */
        $count = isset($cart_favourable[$act_id]) ? $cart_favourable[$act_id] : 0;
        if ($favourable['act_type_ext'] > 0 && $count + count($_POST['gift']) > $favourable['act_type_ext'])
        {
            show_message($_LANG['gift_count_exceed']);
        }

        /* 添加赠品到购物车 */
        foreach ($favourable['gift'] as $gift)
        {
            if (in_array($gift['id'], $_POST['gift']))
            {
                add_gift_to_cart($act_id, $gift['id'], $gift['price']);
            }
        }
    }
    elseif ($favourable['act_type'] == FAT_DISCOUNT)
    {
        add_favourable_to_cart($act_id, $favourable['act_name'], cart_favourable_amount($favourable) * (100 - $favourable['act_type_ext']) / 100);
    }
    elseif ($favourable['act_type'] == FAT_PRICE)
    {
        add_favourable_to_cart($act_id, $favourable['act_name'], $favourable['act_type_ext']);
    }

    /* 刷新购物车 */
    ecs_header("Location: flow.php\n");
    exit;
}
elseif ($_REQUEST['step'] == 'clear')
{
    $sql = "DELETE FROM " . $ecs->table('cart') . " WHERE session_id='" . SESS_ID . "'";
    $db->query($sql);

    ecs_header("Location:./\n");
}
elseif ($_REQUEST['step'] == 'drop_to_collect')
{
    if ($_SESSION['user_id'] > 0)
    {
        $rec_id = intval($_GET['id']);
        $goods_id = $db->getOne("SELECT  goods_id FROM " .$ecs->table('cart'). " WHERE rec_id = '$rec_id' AND session_id = '" . SESS_ID . "' ");
        $count = $db->getOne("SELECT goods_id FROM " . $ecs->table('collect_goods') . " WHERE user_id = '$_SESSION[user_id]' AND goods_id = '$goods_id'");
        if (empty($count))
        {
            $time = gmtime();
            $sql = "INSERT INTO " .$GLOBALS['ecs']->table('collect_goods'). " (user_id, goods_id, add_time)" .
                    "VALUES ('$_SESSION[user_id]', '$goods_id', '$time')";
            $db->query($sql);
        }
        flow_drop_cart_goods($rec_id);
    }
    ecs_header("Location: flow.php\n");
    exit;
}
//
///* 验证红包序列号 */
//elseif ($_REQUEST['step'] == 'validate_bonus')
//{
//    $bonus_sn = trim($_REQUEST['bonus_sn']);
//    if (is_numeric($bonus_sn))
//    {
//        $bonus = bonus_info(0, $bonus_sn);
//    }
//    else
//    {
//        $bonus = array();
//    }
//
////    if (empty($bonus) || $bonus['user_id'] > 0 || $bonus['order_id'] > 0)
////    {
////        die($_LANG['bonus_sn_error']);
////    }
////    if ($bonus['min_goods_amount'] > cart_amount())
////    {
////        die(sprintf($_LANG['bonus_min_amount_error'], price_format($bonus['min_goods_amount'], false)));
////    }
////    die(sprintf($_LANG['bonus_is_ok'], price_format($bonus['type_money'], false)));
//    $bonus_kill = price_format($bonus['type_money'], false);
//
//    include_once('includes/cls_json.php');
//    $result = array('error' => '', 'content' => '');
//
//    /* 取得购物类型 */
//    $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
//    $is_real = true;
//    if(goods_extensioncode($_SESSION['goods_id']) == 'goods_members'){
//        $is_real = false;
//    }
//    if($is_real){
//        /* 获得收货人信息 */
//        $consignee = get_consignee($_SESSION['user_id']);
//
//        if (!check_consignee_info($consignee, $flow_type))
//        {
//            $result['error'] = '收货人信息不能为空';
//        }
//    }
//    /* 对商品信息赋值 */
//    $cart_goods = cart_goods($flow_type); // 取得商品列表，计算合计
//
//    if (empty($cart_goods))
//    {
//        $result['error'] = $_LANG['no_goods_in_cart'];
//    }
//    else
//    {
//        /* 取得购物流程设置 */
//        $smarty->assign('config', $_CFG);
//
//        /* 取得订单信息 */
//        $order = flow_order_info();
//
//
//        if (((!empty($bonus) && $bonus['user_id'] == $_SESSION['user_id']) || ($bonus['type_money'] > 0 && empty($bonus['user_id']))) && $bonus['order_id'] <= 0)
//        {
//            //$order['bonus_kill'] = $bonus['type_money'];
//            $now = gmtime();
//            if ($now > $bonus['use_end_date'])
//            {
//                $order['bonus_id'] = '';
//                $result['error']=$_LANG['bonus_use_expire'];
//            }
//            else
//            {
//                $order['bonus_id'] = $bonus['bonus_id'];
//                $order['bonus_sn'] = $bonus_sn;
//            }
//        }
//        else
//        {
//            if($bonus['user_id']==$_SESSION['user_id'])
//            {
//                $order['bonus_id'] = '';
//                $result['error'] = $_LANG['bonus_is_used'];
//            }
//            elseif($bonus['user_id']>0)
//            {
//                $order['bonus_id'] = '';
//                $result['error'] = $_LANG['bonus_is_used_by_other'];
//            }
//            else
//            {
//                //$order['bonus_kill'] = 0;
//                $order['bonus_id'] = '';
//                $result['error'] = $_LANG['bonus_not_exist'];
//            }
//        }
//
//        /* 计算订单的费用 */
//        $total = order_fee($order, $cart_goods, $consignee);
//
//        if($total['goods_price']<$bonus['min_goods_amount'])
//        {
//         $order['bonus_id'] = '';
//         /* 重新计算订单 */
//         $total = order_fee($order, $cart_goods, $consignee);
//         $result['error'] = sprintf($_LANG['bonus_min_amount_error'], price_format($bonus['min_goods_amount'], false));
//        }
//
//        $smarty->assign('total', $total);
//
//        /* 团购标志 */
//        if ($flow_type == CART_GROUP_BUY_GOODS)
//        {
//            $smarty->assign('is_group_buy', 1);
//        }
//
//        $result['content'] = $smarty->fetch('library/order_total.lbi');
//    }
//    $json = new JSON();
//    die($json->encode($result));
//}
/*------------------------------------------------------ */
//-- 添加礼包到购物车
/*------------------------------------------------------ */
elseif ($_REQUEST['step'] == 'add_package_to_cart')
{
    include_once('includes/cls_json.php');
    $_POST['package_info'] = json_str_iconv($_POST['package_info']);

    $result = array('error' => 0, 'message' => '', 'content' => '', 'package_id' => '');
    $json  = new JSON;

    if (empty($_POST['package_info']))
    {
        $result['error'] = 1;
        die($json->encode($result));
    }

    $package = $json->decode($_POST['package_info']);

    /* 如果是一步购物，先清空购物车 */
    if ($_CFG['one_step_buy'] == '1' || goods_extensioncode($_SESSION['goods_id']) == 'goods_members')
    {
        clear_cart();
    }

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

            $result['content'] = insert_cart_info();
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
else
{
    //清除结算页session
    unset($_SESSION['flow_order']);
    //先清空禁止加入购物车的商品
    clear_immediately_cart();
    /* 标记购物流程为普通商品 */
    $_SESSION['flow_type'] = CART_GENERAL_GOODS;

//    //购物车当中不允许有海淘商品start
//    $sql="SELECT c.goods_id FROM (". $GLOBALS['ecs']->table('cart') ." AS c LEFT JOIN " . $GLOBALS['ecs']->table('goods') ." AS g ON c.goods_id=g.goods_id) "
//        ." LEFT JOIN ". $GLOBALS['ecs']->table('goods_supplier') . " AS gs ON g.supplier_id=gs.type_id WHERE gs.is_overseas=1";
//    $overseas = $GLOBALS['db']->getAll($sql);
//    $str="";
//    if(count($overseas)>0)
//    {
//        for ($i=0; $i<count($overseas); $i++)
//        {
//            if($i==0)
//            {
//                $str=" WHERE goods_id=".$overseas[$i]['goods_id'];
//            }
//            else
//            {
//                $str.=" or goods_id=".$overseas[$i]['goods_id'];
//            }
//
//        }
//        $sql="DELETE FROM ". $GLOBALS['ecs']->table('cart') .$str;
//        $GLOBALS['db']->query($sql);
//    }
//    //购物车当中不允许有海淘商品end
    /* 如果是一步购物，跳到结算中心 */
    if ($_CFG['one_step_buy'] == '1' || goods_extensioncode($_SESSION['goods_id']) == 'goods_members')
    {
        ecs_header("Location: flow.php?step=checkout\n");
        exit;
    }
    $sql = 'SELECT c.goods_id,c.goods_number,c.is_selected,g.max_number ' .
        ' FROM ' . $GLOBALS['ecs']->table('cart') .
        " AS c LEFT JOIN ". $GLOBALS['ecs']->table('goods') . " AS g ON c.goods_id=g.goods_id WHERE c.session_id = '" . SESS_ID . "' AND c.rec_type = '" . CART_GENERAL_GOODS . "' AND g.is_immediately=0";
    $row = $GLOBALS['db']->GetAll($sql);

    $sql_pb = 'SELECT p.goods_id,p.goods_number,p.max_number ' .
        ' FROM ' . $GLOBALS['ecs']->table('cart') ." AS c LEFT JOIN (select t1.goods_id,t1.goods_number,t1.package_id,t2.max_number from "
        . $GLOBALS['ecs']->table('package_goods') . " as t1 left join". $GLOBALS['ecs']->table('goods') .
        " as t2 on t1.goods_id=t2.goods_id) AS p ON c.goods_id=p.package_id WHERE c.session_id = '" . SESS_ID
        . "' AND c.rec_type = '" . CART_GENERAL_GOODS . "' AND c.extension_code='package_buy'";
    $row_pb = $GLOBALS['db']->GetAll($sql_pb);
    if(!empty($row_pb)){
        foreach($row_pb as $v){
            array_push($row,$v);
        }
    }

    if ($row)
    {
        foreach ($row as $key => $value)
        {
            if($value['max_number']!=0)
            {
                if($value['goods_number']>$value['max_number'])
                {
                    $number += $value['is_selected'] == 1 ? $value['max_number'] : 0;
                    //限购商品数量重新赋值
                    $sql = 'UPDATE ' .
                         $GLOBALS['ecs']->table('cart') .
                        " SET goods_number=$value[max_number] WHERE user_id = $_SESSION[user_id] AND goods_id = $value[goods_id] AND rec_type = '" . CART_GENERAL_GOODS . "' AND is_immediately=0";
                    $GLOBALS['db']->query($sql);
                }
                else
                {
                    $number += $value['is_selected'] == 1 ? $value['goods_number'] : 0;
                }
            }
            else
            {
                $number += $value['is_selected'] == 1 ? $value['goods_number'] : 0;
            }
        }
        $number = intval($number);
    }
    else
    {
        $number = 0;
    }
    if(count($row)==0)
    {
        show_message('购物车没有商品!','','','warning',false);
    }
    /* 取得商品列表，计算合计 */
    $cart_goods = get_cart_goods();
    foreach($cart_goods['goods_list'] as $key=>$val){
//        if($val['is_overseas']==1)
//        {
//            $g = 800/$val['goods_price_str'];
//            if($g<1)
//            {
//                $cart_goods['goods_list'][$key]['goods_number'] = 1;
//            }
//            else
//            {
//                if($cart_goods['goods_list'][$key]['goods_number']>intval($g))
//                {
//                    $cart_goods['goods_list'][$key]['goods_number'] = intval($g);
//                }
//            }
//        }
        $info = get_cat_info($val['goods_id']);
        $cart_goods['goods_list'][$key]['cat_id'] = $info['cat_id'];
        $cart_goods['goods_list'][$key]['cat_name'] = $info['cat_name'];
    }
    $smarty->assign('goods_list', $cart_goods['goods_list']);
    foreach ($cart_goods['goods_list'] AS $val)
    {
        $id_list .= $val['goods_id']. ",";
    }

    $id_list = substr($id_list, 0, -1);
    $smarty->assign('id_list', $id_list);

    $smarty->assign('total', $cart_goods['total']);
    $smarty->assign('str',$number);
    /* 计算折扣 */
    $discount = compute_discount();
    $discount['format_discount'] = $discount['discount'] > 0 ? price_format($discount['discount']) : null;
    $smarty->assign('formatdiscount', $discount['format_discount']);
    $smarty->assign('discount', price_format($discount['discount']));
    //购物车的描述的格式化
    $smarty->assign('shopping_money',         price_format($cart_goods['total']['goods_amount']-$discount['discount']));
    $smarty->assign('market_price_desc',      sprintf($_LANG['than_market_price'],
    $cart_goods['total']['market_price'], $cart_goods['total']['saving'], $cart_goods['total']['save_rate']));

    // 显示收藏夹内的商品
    if ($_SESSION['user_id'] > 0)
    {
        require_once(ROOT_PATH . 'includes/lib_clips.php');
        $collection_goods = get_collection_goods($_SESSION['user_id']);
        $smarty->assign('collection_goods', $collection_goods);
    }

    /* 取得优惠活动 */
    $favourable_list = favourable_list($_SESSION['user_rank']);
    usort($favourable_list, 'cmp_favourable');

    $smarty->assign('favourable_list', $favourable_list);

    /* 增加是否在购物车里显示商品图 */
    $smarty->assign('show_goods_thumb', $GLOBALS['_CFG']['show_goods_in_cart']);

    /* 增加是否在购物车里显示商品属性 */
    $smarty->assign('show_goods_attribute', $GLOBALS['_CFG']['show_attr_in_cart']);

    /* 购物车中商品配件列表 */
    //取得购物车中基本件ID
    $sql = "SELECT goods_id " .
            "FROM " . $GLOBALS['ecs']->table('cart') .
            " WHERE session_id = '" . SESS_ID . "' " .
            "AND rec_type = '" . CART_GENERAL_GOODS . "' " .
            "AND is_gift = 0 " .
            "AND extension_code <> 'package_buy' " .
            "AND parent_id = 0 ";
    $parent_list = $GLOBALS['db']->getCol($sql);
    $fittings_list = get_goods_fittings($parent_list);

    $smarty->assign('fittings_list', $fittings_list);
}

$smarty->assign('currency_format', $_CFG['currency_format']);
$smarty->assign('integral_scale',  $_CFG['integral_scale']);
$smarty->assign('step',            $_REQUEST['step']);
assign_dynamic('shopping_flow');

$smarty->display('flow.dwt');

/*------------------------------------------------------ */
//-- PRIVATE FUNCTION
/*------------------------------------------------------ */

/**
 * 获得用户的可用积分
 *
 * @access  private
 * @return  integral
 */
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
 * 更新购物车中的商品数量
 *
 * @access  public
 * @param   array   $arr
 * @return  void
 */
function flow_update_cart($arr)
{
    /* 处理 */
    foreach ($arr AS $key => $val)
    {
        $val['goods_number'] = intval(make_semiangle($val['goods_number']));
        if ($val['goods_number'] <= 0 || !is_numeric($key))
        $val = intval(make_semiangle($val));
        if ($val <= 0 || !is_numeric($key))
        {
            continue;
        }
        if($val['max_number']>0)
        {
            if($val['goods_number']>$val['max_number'])
            {
                $val['goods_number']=$val['max_number'];
            }
        }
//        if($val['is_overseas']==1)
//        {
//            $g = 800/$val['goods_price_str'];
//            if($g<1)
//            {
//                $val['goods_number'] = 1;
//            }
//            else
//            {
//                if($val['goods_number']>intval($g))
//                {
//                    $val['goods_number'] = intval($g);
//                }
//            }
//        }
        //查询：
        $sql = "SELECT `goods_id`, `goods_attr_id`, `product_id`, `extension_code` FROM" .$GLOBALS['ecs']->table('cart').
               " WHERE rec_id='$key' AND session_id='" . SESS_ID . "'";
        $goods = $GLOBALS['db']->getRow($sql);

        $sql = "SELECT g.goods_name, g.goods_number ".
                "FROM " .$GLOBALS['ecs']->table('goods'). " AS g, ".
                    $GLOBALS['ecs']->table('cart'). " AS c ".
                "WHERE g.goods_id = c.goods_id AND c.rec_id = '$key'";
        $row = $GLOBALS['db']->getRow($sql);

        //查询：系统启用了库存，检查输入的商品数量是否有效
        if (intval($GLOBALS['_CFG']['use_storage']) > 0 && $goods['extension_code'] != 'package_buy')
        {
            if ($row['goods_number'] < $val['goods_number'])
            {
                 //更新购物车中超出商品最大数量更改为最大数量
                $sql = "UPDATE " .$GLOBALS['ecs']->table('cart').
                    " SET goods_number = '$row[goods_number]' WHERE rec_id='$key' AND session_id='" . SESS_ID . "'";
                $GLOBALS['db']->query($sql);
                show_message(sprintf($GLOBALS['_LANG']['stock_insufficiency'], $row['goods_name'],
                $row['goods_number'], $row['goods_number']),"","flow.php");
                exit;
            }
            /* 是货品 */
            $goods['product_id'] = trim($goods['product_id']);
            if (!empty($goods['product_id']))
            {
                $sql = "SELECT product_number FROM " .$GLOBALS['ecs']->table('products'). " WHERE goods_id = '" . $goods['goods_id'] . "' AND product_id = '" . $goods['product_id'] . "'";

                $product_number = $GLOBALS['db']->getOne($sql);
                if ($product_number < $val['goods_number'])
                {
                    show_message(sprintf($GLOBALS['_LANG']['stock_insufficiency'], $row['goods_name'],
                    $product_number['product_number'], $product_number['product_number']));
                    exit;
                }
            }
        }
        elseif (intval($GLOBALS['_CFG']['use_storage']) > 0 && $goods['extension_code'] == 'package_buy')
        {
            if (judge_package_stock($goods['goods_id'], $val['goods_number']))
            {
                show_message($GLOBALS['_LANG']['package_stock_insufficiency']);
                exit;
            }
        }

        /* 查询：检查该项是否为基本件 以及是否存在配件 */
        /* 此处配件是指添加商品时附加的并且是设置了优惠价格的配件 此类配件都有parent_id goods_number为1 */
        $sql = "SELECT b.goods_number, b.rec_id
                FROM " .$GLOBALS['ecs']->table('cart') . " a, " .$GLOBALS['ecs']->table('cart') . " b
                WHERE a.rec_id = '$key'
                AND a.session_id = '" . SESS_ID . "'
                AND a.extension_code <> 'package_buy'
                AND b.parent_id = a.goods_id
                AND b.session_id = '" . SESS_ID . "'";

        $offers_accessories_res = $GLOBALS['db']->query($sql);

        //订货数量大于0
        if ($val['goods_number'] > 0)
        {
            /* 判断是否为超出数量的优惠价格的配件 删除*/
            $row_num = 1;
            while ($offers_accessories_row = $GLOBALS['db']->fetchRow($offers_accessories_res))
            {
                if ($row_num > $val['goods_number'])
                {
                    $sql = "DELETE FROM " . $GLOBALS['ecs']->table('cart') .
                            " WHERE session_id = '" . SESS_ID . "' " .
                            "AND rec_id = '" . $offers_accessories_row['rec_id'] ."' LIMIT 1";
                    $GLOBALS['db']->query($sql);
                }

                $row_num ++;
            }

            /* 处理超值礼包 */
            if ($goods['extension_code'] == 'package_buy')
            {
                //更新购物车中的商品数量
                $sql = "UPDATE " .$GLOBALS['ecs']->table('cart').
                        " SET goods_number = '$val[goods_number]' WHERE rec_id='$key' AND session_id='" . SESS_ID . "'";
            }
            /* 处理普通商品或非优惠的配件 */
            else
            {
                $attr_id    = empty($goods['goods_attr_id']) ? array() : explode(',', $goods['goods_attr_id']);
                $goods_price = get_final_price($goods['goods_id'], $val['goods_number'], true, $attr_id);

                //更新购物车中的商品数量
                $sql = "UPDATE " .$GLOBALS['ecs']->table('cart').
                        " SET goods_number = '$val[goods_number]', goods_price = '$goods_price' WHERE rec_id='$key' AND session_id='" . SESS_ID . "'";
            }
        }
        //订货数量等于0
        else
        {
            /* 如果是基本件并且有优惠价格的配件则删除优惠价格的配件 */
            while ($offers_accessories_row = $GLOBALS['db']->fetchRow($offers_accessories_res))
            {
                $sql = "DELETE FROM " . $GLOBALS['ecs']->table('cart') .
                        " WHERE session_id = '" . SESS_ID . "' " .
                        "AND rec_id = '" . $offers_accessories_row['rec_id'] ."' LIMIT 1";
                $GLOBALS['db']->query($sql);
            }

            $sql = "DELETE FROM " .$GLOBALS['ecs']->table('cart').
                " WHERE rec_id='$key' AND session_id='" .SESS_ID. "'";
        }

        $GLOBALS['db']->query($sql);
    }

    /* 删除所有赠品 */
    $sql = "DELETE FROM " . $GLOBALS['ecs']->table('cart') . " WHERE session_id = '" .SESS_ID. "' AND is_gift <> 0";
    $GLOBALS['db']->query($sql);
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
        if ($val <= 0 || !is_numeric($key))
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
 * 删除购物车中的商品
 *
 * @access  public
 * @param   integer $id
 * @return  void
 */
function flow_drop_cart_goods($id)
{
    /* 取得商品id */
    $sql = "SELECT * FROM " .$GLOBALS['ecs']->table('cart'). " WHERE rec_id = '$id'";
    $row = $GLOBALS['db']->getRow($sql);
    if ($row)
    {
        //如果是超值礼包
        if ($row['extension_code'] == 'package_buy')
        {
            $sql = "DELETE FROM " . $GLOBALS['ecs']->table('cart') .
                    " WHERE session_id = '" . SESS_ID . "' " .
                    "AND rec_id = '$id' LIMIT 1";
        }

        //如果是普通商品，同时删除所有赠品及其配件
        elseif ($row['parent_id'] == 0 && $row['is_gift'] == 0)
        {
            /* 检查购物车中该普通商品的不可单独销售的配件并删除 */
            $sql = "SELECT c.rec_id
                    FROM " . $GLOBALS['ecs']->table('cart') . " AS c, " . $GLOBALS['ecs']->table('group_goods') . " AS gg, " . $GLOBALS['ecs']->table('goods'). " AS g
                    WHERE gg.parent_id = '" . $row['goods_id'] . "'
                    AND c.goods_id = gg.goods_id
                    AND c.parent_id = '" . $row['goods_id'] . "'
                    AND c.extension_code <> 'package_buy'
                    AND gg.goods_id = g.goods_id
                    AND g.is_alone_sale = 0";
            $res = $GLOBALS['db']->query($sql);
            $_del_str = $id . ',';
            while ($id_alone_sale_goods = $GLOBALS['db']->fetchRow($res))
            {
                $_del_str .= $id_alone_sale_goods['rec_id'] . ',';
            }
            $_del_str = trim($_del_str, ',');

            $sql = "DELETE FROM " . $GLOBALS['ecs']->table('cart') .
                    " WHERE session_id = '" . SESS_ID . "' " .
                    "AND (rec_id IN ($_del_str) OR parent_id = '$row[goods_id]' OR is_gift <> 0)";
        }

        //如果不是普通商品，只删除该商品即可
        else
        {
            $sql = "DELETE FROM " . $GLOBALS['ecs']->table('cart') .
                    " WHERE session_id = '" . SESS_ID . "' " .
                    "AND rec_id = '$id' LIMIT 1";
        }

        $GLOBALS['db']->query($sql);
    }

    flow_clear_cart_alone();
}

/**
 * 删除购物车中不能单独销售的商品
 *
 * @access  public
 * @return  void
 */
function flow_clear_cart_alone()
{
    /* 查询：购物车中所有不可以单独销售的配件 */
    $sql = "SELECT c.rec_id, gg.parent_id
            FROM " . $GLOBALS['ecs']->table('cart') . " AS c
                LEFT JOIN " . $GLOBALS['ecs']->table('group_goods') . " AS gg ON c.goods_id = gg.goods_id
                LEFT JOIN" . $GLOBALS['ecs']->table('goods') . " AS g ON c.goods_id = g.goods_id
            WHERE c.session_id = '" . SESS_ID . "'
            AND c.extension_code <> 'package_buy'
            AND gg.parent_id > 0
            AND g.is_alone_sale = 0";
    $res = $GLOBALS['db']->query($sql);
    $rec_id = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $rec_id[$row['rec_id']][] = $row['parent_id'];
    }

    if (empty($rec_id))
    {
        return;
    }

    /* 查询：购物车中所有商品 */
    $sql = "SELECT DISTINCT goods_id
            FROM " . $GLOBALS['ecs']->table('cart') . "
            WHERE session_id = '" . SESS_ID . "'
            AND extension_code <> 'package_buy'";
    $res = $GLOBALS['db']->query($sql);
    $cart_good = array();
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $cart_good[] = $row['goods_id'];
    }

    if (empty($cart_good))
    {
        return;
    }

    /* 如果购物车中不可以单独销售配件的基本件不存在则删除该配件 */
    $del_rec_id = '';
    foreach ($rec_id as $key => $value)
    {
        foreach ($value as $v)
        {
            if (in_array($v, $cart_good))
            {
                continue 2;
            }
        }

        $del_rec_id = $key . ',';
    }
    $del_rec_id = trim($del_rec_id, ',');

    if ($del_rec_id == '')
    {
        return;
    }

    /* 删除 */
    $sql = "DELETE FROM " . $GLOBALS['ecs']->table('cart') ."
            WHERE session_id = '" . SESS_ID . "'
            AND rec_id IN ($del_rec_id)";
    $GLOBALS['db']->query($sql);
}

/**
 * 比较优惠活动的函数，用于排序（把可用的排在前面）
 * @param   array   $a      优惠活动a
 * @param   array   $b      优惠活动b
 * @return  int     相等返回0，小于返回-1，大于返回1
 */
function cmp_favourable($a, $b)
{
    if ($a['available'] == $b['available'])
    {
        if ($a['sort_order'] == $b['sort_order'])
        {
            return 0;
        }
        else
        {
            return $a['sort_order'] < $b['sort_order'] ? -1 : 1;
        }
    }
    else
    {
        return $a['available'] ? -1 : 1;
    }
}

/**
 * 取得某用户等级当前时间可以享受的优惠活动
 * @param   int     $user_rank      用户等级id，0表示非会员
 * @return  array
 */
function favourable_list($user_rank)
{
    /* 购物车中已有的优惠活动及数量 */
    $used_list = cart_favourable();

    /* 当前用户可享受的优惠活动 */
    $favourable_list = array();
    $user_rank = ',' . $user_rank . ',';
    $now = gmtime();
    $sql = "SELECT * " .
            "FROM " . $GLOBALS['ecs']->table('favourable_activity') .
            " WHERE CONCAT(',', user_rank, ',') LIKE '%" . $user_rank . "%'" .
            " AND start_time <= '$now' AND end_time >= '$now'" .
            " AND act_type = '" . FAT_GOODS . "'" .
            " ORDER BY sort_order";
    $res = $GLOBALS['db']->query($sql);
    while ($favourable = $GLOBALS['db']->fetchRow($res))
    {
        $favourable['start_time'] = local_date($GLOBALS['_CFG']['time_format'], $favourable['start_time']);
        $favourable['end_time']   = local_date($GLOBALS['_CFG']['time_format'], $favourable['end_time']);
        $favourable['formated_min_amount'] = price_format($favourable['min_amount'], false);
        $favourable['formated_max_amount'] = price_format($favourable['max_amount'], false);
        $favourable['gift']       = unserialize($favourable['gift']);

        foreach ($favourable['gift'] as $key => $value)
        {
            $favourable['gift'][$key]['formated_price'] = price_format($value['price'], false);
            $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('goods') . " WHERE is_on_sale = 1 AND goods_id = ".$value['id'];
            $is_sale = $GLOBALS['db']->getOne($sql);
            if(!$is_sale)
            {
                unset($favourable['gift'][$key]);
            }
        }

        $favourable['act_range_desc'] = act_range_desc($favourable);
        $favourable['act_type_desc'] = sprintf($GLOBALS['_LANG']['fat_ext'][$favourable['act_type']], $favourable['act_type_ext']);

        /* 是否能享受 */
        $favourable['available'] = favourable_available($favourable);
        if ($favourable['available'])
        {
            /* 是否尚未享受 */
            $favourable['available'] = !favourable_used($favourable, $used_list);
        }

        $favourable_list[] = $favourable;
    }

    return $favourable_list;
}

/**
 * 根据购物车判断是否可以享受某优惠活动
 * @param   array   $favourable     优惠活动信息
 * @return  bool
 */
function favourable_available($favourable)
{
    /* 会员等级是否符合 */
    $user_rank = $_SESSION['user_rank'];
    if (strpos(',' . $favourable['user_rank'] . ',', ',' . $user_rank . ',') === false)
    {
        return false;
    }

    /* 优惠范围内的商品总额 */
    $amount = cart_favourable_amount($favourable);

    /* 金额上限为0表示没有上限 */
    return $amount >= $favourable['min_amount'] &&
        ($amount <= $favourable['max_amount'] || $favourable['max_amount'] == 0);
}

/**
 * 取得优惠范围描述
 * @param   array   $favourable     优惠活动
 * @return  string
 */
function act_range_desc($favourable)
{
    if ($favourable['act_range'] == FAR_BRAND)
    {
        $sql = "SELECT brand_name FROM " . $GLOBALS['ecs']->table('brand') .
                " WHERE brand_id " . db_create_in($favourable['act_range_ext']);
        return join(',', $GLOBALS['db']->getCol($sql));
    }
    elseif ($favourable['act_range'] == FAR_CATEGORY)
    {
        $sql = "SELECT cat_name FROM " . $GLOBALS['ecs']->table('category') .
                " WHERE cat_id " . db_create_in($favourable['act_range_ext']);
        return join(',', $GLOBALS['db']->getCol($sql));
    }
    elseif ($favourable['act_range'] == FAR_GOODS)
    {
        $sql = "SELECT goods_name FROM " . $GLOBALS['ecs']->table('goods') .
                " WHERE goods_id " . db_create_in($favourable['act_range_ext']);
        return join(',', $GLOBALS['db']->getCol($sql));
    }
    else
    {
        return '';
    }
}

/**
 * 取得购物车中已有的优惠活动及数量
 * @return  array
 */
function cart_favourable()
{
    $list = array();
    $sql = "SELECT is_gift, COUNT(*) AS num " .
            "FROM " . $GLOBALS['ecs']->table('cart') .
            " WHERE session_id = '" . SESS_ID . "'" .
            " AND rec_type = '" . CART_GENERAL_GOODS . "'" .
            " AND is_gift > 0" .
            " GROUP BY is_gift";
    $res = $GLOBALS['db']->query($sql);
    while ($row = $GLOBALS['db']->fetchRow($res))
    {
        $list[$row['is_gift']] = $row['num'];
    }

    return $list;
}

/**
 * 购物车中是否已经有某优惠
 * @param   array   $favourable     优惠活动
 * @param   array   $cart_favourable购物车中已有的优惠活动及数量
 */
function favourable_used($favourable, $cart_favourable)
{
    if ($favourable['act_type'] == FAT_GOODS)
    {
        return isset($cart_favourable[$favourable['act_id']]) &&
            $cart_favourable[$favourable['act_id']] >= $favourable['act_type_ext'] &&
            $favourable['act_type_ext'] > 0;
    }
    else
    {
        return isset($cart_favourable[$favourable['act_id']]);
    }
}

/**
 * 添加优惠活动（赠品）到购物车
 * @param   int     $act_id     优惠活动id
 * @param   int     $id         赠品id
 * @param   float   $price      赠品价格
 */
function add_gift_to_cart($act_id, $id, $price)
{
    $sql = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . " (" .
                "user_id, session_id, goods_id, goods_sn, goods_name, market_price, goods_price, ".
                "goods_number, is_real, extension_code, parent_id, is_gift, rec_type ) ".
            "SELECT '$_SESSION[user_id]', '" . SESS_ID . "', goods_id, goods_sn, goods_name, market_price, ".
                "'$price', 1, is_real, extension_code, 0, '$act_id', '" . CART_GENERAL_GOODS . "' " .
            "FROM " . $GLOBALS['ecs']->table('goods') .
            " WHERE goods_id = '$id'";
    $GLOBALS['db']->query($sql);
}

/**
 * 添加优惠活动（非赠品）到购物车
 * @param   int     $act_id     优惠活动id
 * @param   string  $act_name   优惠活动name
 * @param   float   $amount     优惠金额
 */
function add_favourable_to_cart($act_id, $act_name, $amount)
{
    $sql = "INSERT INTO " . $GLOBALS['ecs']->table('cart') . "(" .
                "user_id, session_id, goods_id, goods_sn, goods_name, market_price, goods_price, ".
                "goods_number, is_real, extension_code, parent_id, is_gift, rec_type ) ".
            "VALUES('$_SESSION[user_id]', '" . SESS_ID . "', 0, '', '$act_name', 0, ".
                "'" . (-1) * $amount . "', 1, 0, '', 0, '$act_id', '" . CART_GENERAL_GOODS . "')";
    $GLOBALS['db']->query($sql);
}

/**
 * 取得购物车中某优惠活动范围内的总金额
 * @param   array   $favourable     优惠活动
 * @return  float
 */
function cart_favourable_amount($favourable)
{
    /* 查询优惠范围内商品总额的sql */
    $sql = "SELECT SUM(c.goods_price * c.goods_number) " .
            "FROM " . $GLOBALS['ecs']->table('cart') . " AS c, " . $GLOBALS['ecs']->table('goods') . " AS g " .
            "WHERE c.goods_id = g.goods_id " .
            "AND c.session_id = '" . SESS_ID . "' " .
            "AND c.rec_type = '" . CART_GENERAL_GOODS . "' " .
            "AND c.is_gift = 0 " .
            "AND c.goods_id > 0 ";

    /* 根据优惠范围修正sql */
    if ($favourable['act_range'] == FAR_ALL)
    {
        // sql do not change
    }
    elseif ($favourable['act_range'] == FAR_CATEGORY)
    {
        /* 取得优惠范围分类的所有下级分类 */
        $id_list = array();
        $cat_list = explode(',', $favourable['act_range_ext']);
        foreach ($cat_list as $id)
        {
            $id_list = array_merge($id_list, array_keys(cat_list(intval($id), 0, false)));
        }

        $sql .= "AND g.cat_id " . db_create_in($id_list);
    }
    elseif ($favourable['act_range'] == FAR_BRAND)
    {
        $id_list = explode(',', $favourable['act_range_ext']);

        $sql .= "AND g.brand_id " . db_create_in($id_list);
    }
    else
    {
        $id_list = explode(',', $favourable['act_range_ext']);

        $sql .= "AND g.goods_id " . db_create_in($id_list);
    }

    /* 优惠范围内的商品总额 */
    return $GLOBALS['db']->getOne($sql);
}

function goods_extensioncode($good_id)
{
    if($good_id > 0){
        $sql = "SELECT extension_code FROM " . $GLOBALS['ecs']->table('goods') .
            " WHERE goods_id = " . $good_id;
        $res = $GLOBALS['db']->getOne($sql);

        return $res;
    }
    return '';
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