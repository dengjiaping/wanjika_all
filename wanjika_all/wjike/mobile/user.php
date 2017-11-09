<?php

/**
 * ECSHOP 用户中心
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liubo $
 * $Id: user.php 16643 2009-09-08 07:02:13Z liubo $
*/

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
include_once(ROOT_PATH . 'includes/lib_order.php');
require(dirname(__FILE__) . '/../includes/modules/payment/weixinpay.php');
/* 载入语言文件 */
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/user.php');
$user_id = $_SESSION['user_id'];
cart_count();

if ($_SESSION['user_id'] > 0)
{
    $smarty->assign('user_name', $_SESSION['user_name']);
    $smarty->assign('user_id', $_SESSION['user_id']);
}
$act = isset($_REQUEST['act']) ? $_REQUEST['act'] : '';
$smarty->assign('shop_name', $_CFG['shop_name']);
$smarty->assign('is_local',is_local());
/* 用户登陆 */
if ($act == 'do_login')
{
	$user_name = !empty($_POST['username']) ? $_POST['username'] : '';
	$pwd = !empty($_POST['pwd']) ? $_POST['pwd'] : '';
    $back_act = isset($_POST['back_act']) ? trim($_POST['back_act']) : '';
    $weixin_binding = isset($_POST['weixin_binding']) ? trim($_POST['weixin_binding']) : '';
	if (empty($user_name) || empty($pwd))
	{
		$login_faild = 1;
	}
	else
	{
		if ($user->check_user($user_name, $pwd) > 0)
		{
			$user->set_session($user_name);
			$user->set_cookie($user_name);
			update_user_info();
			//show_user_center();
            $location_url = "";
            $sql = "SELECT COUNT(*) FROM " . $ecs->table('cart') . " WHERE session_id = '" . SESS_ID . "' " . "AND parent_id = 0 AND is_gift = 0 AND rec_type = 0";
            if ($db->getOne($sql) > 0){
                $location_url = "order.php?act=order_lise";
            }else{
                $location_url = "user.php";
            }

            if($weixin_binding == "1"){
                $ubind = is_user_binding($user_name);
                if($ubind){
                    if(!empty($back_act))
                    {
                        $location_url = $back_act;
                    }
                    show_message('该万集客账号已被绑定', '', "$location_url", 'error');
                }
                $is_weixin = is_weixin();
                if($is_weixin){
                    $openid = $_SESSION['openid'];
                    $is_binding = is_binding($openid);
                    if(!$is_binding && !empty($openid)){
                        $r = binding_weixin($openid,$_SESSION['user_id'],$user_name);
                        if($r){
                            show_message('微信绑定成功', '', "$location_url", 'info');
                        }
                        else{
                            show_message('微信绑定失败', '', "$location_url", 'error');
                        }
                    }
                }
            }

            if(!empty($back_act))
            {
                if (!empty($_SESSION['back_act']))
                {
                     $_SESSION['back_act'] = '';
                }
                ecs_header("Location:$back_act\n");
                exit;
            }
            ecs_header("Location:$location_url\n");
            exit;
		}
		else
		{
			$login_faild = 1;
		}
	}
//	$smarty->assign('login_faild', $login_faild);
//	$smarty->assign('user_name', $user_name);
//	$smarty->display('login.html');
//	exit;
    ecs_header("Location: user.php?act=login&login_faild=$login_faild&user_name=$user_name\n");
    exit;
}
if ($act == 'weixin_login')
{
    $user_name = '';
    if(is_weixin()){
        $openid = $_SESSION['openid'];
        $user_name = get_binding_user($openid);
    }
    if(empty($user_name)){
        show_message('请先用微信绑定万集客账号再使用此登录功能', '', '', 'error');
    }
    $user->set_session($user_name);
    $user->set_cookie($user_name);
    update_user_info();

    $back_act = isset($_REQUEST['back_act']) ? trim($_REQUEST['back_act']) : '';
    if(!empty($back_act))
    {
        ecs_header("Location:$back_act\n");
        exit;
    }
    $sql = "SELECT COUNT(*) FROM " . $ecs->table('cart') . " WHERE session_id = '" . SESS_ID . "' " . "AND parent_id = 0 AND is_gift = 0 AND rec_type = 0";
    if ($db->getOne($sql) > 0){
        ecs_header("Location:order.php?act=order_lise\n");
        exit;
    }else{
        ecs_header("Location:user.php\n");
        exit;
    }
}
elseif ($act == 'login')
{
    $flag = isset($_REQUEST['flag']) ? $_REQUEST['flag'] : '';
    $is_binding = false;
    $is_weixin = is_weixin();
    if($is_weixin){
        $pay_obj = new weixinpay();
        $openid = $pay_obj->get_Openid();
        if(!empty($openid)){
            $_SESSION['openid'] = $openid;
        }
        $is_binding = is_binding($openid);
    }
    if (!empty($_SESSION['back_act']))
    {
        $back_act = $_SESSION['back_act'];
//        $_SESSION['back_act'] = '';
    }
    else
    {
        $back_act = isset($_REQUEST['back_act']) ? trim($_REQUEST['back_act']) : '';
    }
    if (empty($back_act))
    {
        if (empty($back_act) && isset($GLOBALS['_SERVER']['HTTP_REFERER']))
        {
            $back_act = strpos($GLOBALS['_SERVER']['HTTP_REFERER'], 'user.php') ? './index.php' : $GLOBALS['_SERVER']['HTTP_REFERER'];
        }
        else
        {
            $back_act = 'user.php';
        }
    }
    if($back_act == 'user.php' && $_SESSION['goods_id'] == '11441'){
        $back_act = 'http://www.wjike.com/mobile/julyvipbuy.php';
    }
    if($back_act == 'user.php' && $_SESSION['goods_id'] == '11443'){
        $back_act = 'http://www.wjike.com/mobile/goods.php?id=11443';
    }
    $smarty->assign('back_act', $back_act);
    if(!$is_binding || $flag == 'true'){
        $smarty->assign('login_faild', $_REQUEST['login_faild']);
        $smarty->assign('user_name', $_REQUEST['user_name']);
        $smarty->assign('is_binding', $is_binding);
        $smarty->assign('is_weixin', $is_weixin);
        $smarty->display('login.html');
    }
    else{
        $user_name = get_binding_user($openid);
        $smarty->assign('user_name', $user_name);
        $smarty->display('bind_login.html');
    }
}
//我的礼品卡
elseif ($act == 'gift_card')
{
    if(!$_SESSION['user_id']){
        $smarty->assign('footer', get_footer());
        $smarty->display('login.html');
        exit;
    }
    $smarty->assign('show_name', "我的礼品卡");
    $user_type=1;
    if($_REQUEST['add_gift']=='add_gift')
    {
        $user_type=4;
        $smarty->assign('user_type', $user_type);
        $smarty->display('gift_cart.html');exit;
    }
    include_once(ROOT_PATH . 'includes/lib_clips.php');

    if($_REQUEST['order_flow']==1)
    {
        // 取得用户可用礼品卡
        $user_gift = user_gift($_SESSION['user_id'], $total['goods_price']);
        if (!empty($user_gift))
        {
            foreach ($user_gift AS $key => $val)
            {
                $user_gift[$key]['end_date']     = local_date($GLOBALS['_CFG']['date_format'], $val['end_date']);
                $user_gift[$key]['gift_money_formated'] = price_format($val['type_money'], false);
            }
            $smarty->assign('gift', $user_gift);
        }

        $user_type=5;
        $address_id = ($_REQUEST['address_id'])?$_REQUEST['address_id']:0;
        $bonus_id = ($_REQUEST['bonus_id']>0)?$_REQUEST['bonus_id']:0;
        $smarty->assign('address_id', $address_id);
        $smarty->assign('bonus_id', $bonus_id);
    }

    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    $record_count = $db->getOne("SELECT COUNT(*) FROM " .$ecs->table('gift_card'). " WHERE member_id = '$user_id'");

    $page_num = '10';
    $page = !empty($_GET['page']) ? intval($_GET['page']) : 1;
    $pages = ceil($record_count / $page_num);

    if ($page <= 0)
    {
        $page = 1;
    }
    if ($pages == 0)
    {
        $pages = 1;
    }
    if ($page > $pages)
    {
        $page = $pages;
    }
    $pagebar = get_wap_pager($record_count, $page_num, $page, 'user.php?act=gift_card', 'page');
    $pager = get_pager('user.php', array('act' => $action), $record_count, $page);
    $gift = get_user_gift_list($user_id, $pager['size'], $pager['start']);
    $smarty->assign('pager', $pagebar);
    if($_REQUEST['order_flow']!=1)
    {
        $smarty->assign('gift', $gift);
    }
    $smarty->assign('user_type', $user_type);
    $smarty->display('gift_cart.html');
}
//我的优惠券
elseif ($act == 'coupons')
{
    if(!$_SESSION['user_id']){
//        $smarty->assign('footer', get_footer());
//        $smarty->display('login.html');
//        exit;
        ecs_header("Location: user.php?act=login\n");
        exit;
    }
    $smarty->assign('show_name', "我的优惠券");
    $user_type=2;
    if($_REQUEST['add_coupons']=='add_coupons')
    {
        $user_type=6;
        $smarty->assign('user_type', $user_type);
        $smarty->display('gift_cart.html');exit;
    }
    include_once(ROOT_PATH . 'includes/lib_clips.php');
    if($_REQUEST['order_flow']==1)
    {
        $flow_type = isset($_SESSION['flow_type']) ? intval($_SESSION['flow_type']) : CART_GENERAL_GOODS;
        $sql = "SELECT gs.is_overseas FROM " . $ecs->table('cart') .
            " AS c LEFT JOIN ". $ecs->table('goods') ." AS g ON c.goods_id=g.goods_id ".
            " LEFT JOIN ". $ecs->table('goods_supplier') . " AS gs ON g.supplier_id=gs.type_id " .
            " WHERE session_id = '" . SESS_ID . "' " .
            "AND parent_id = 0 AND is_gift = 0 AND rec_type = '$flow_type' AND is_selected = 1";
        $re = $db->getAll($sql);
        // 取得用户可用红包
        //$user_bonus = user_bonus($_SESSION['user_id'], $total['goods_price']);
        /* 对商品信息赋值 */
        $cart_goods = cart_goods($flow_type);
        $user_bonus = user_available_bonus($_SESSION['user_id'], $cart_goods);
        if (!empty($user_bonus))
        {
            foreach ($user_bonus AS $key => $val)
            {
                $user_bonus[$key]['bonus_money_formated'] = price_format($val['type_money'], false);
            }
        }
        $user_type=3;
        $address_id = ($_REQUEST['address_id'])?$_REQUEST['address_id']:0;
        $gift_id = ($_REQUEST['gift']>0)?$_REQUEST['gift']:0;
        $smarty->assign('address_id', $address_id);
        $smarty->assign('gift_id', $gift_id);
    }
    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    $status = isset($_REQUEST['status']) ? $_REQUEST['status'] : 'avl';

    $day = getdate();
    $cur_date = local_mktime(23, 59, 59, $day['mon'], $day['mday'], $day['year']);

    $used_count = $db->getOne("SELECT COUNT(*) FROM " .$ecs->table('user_bonus'). " WHERE order_id > 0 and user_id = '$user_id'");
    $unavl_count = $db->getOne("SELECT COUNT(*) FROM " .$ecs->table('user_bonus'). " AS u ,".
        $ecs->table('bonus_type'). " AS b".
        " WHERE u.bonus_type_id = b.type_id AND u.use_end_datetime < $cur_date AND order_id = 0 AND u.user_id = '" .$user_id. "'");
    $avl_count = $db->getOne("SELECT COUNT(*) FROM " .$ecs->table('user_bonus'). " AS u ,".
        $ecs->table('bonus_type'). " AS b".
        " WHERE u.bonus_type_id = b.type_id AND u.use_end_datetime >= $cur_date AND order_id = 0 AND u.user_id = '" .$user_id. "'");

    $record_count = 0;
    switch ($status)
    {
        case 'used' :
            $record_count = $used_count;
            break;
        case 'unavl' :
            $record_count = $unavl_count;
            break;
        default:
            $record_count = $avl_count;
            break;
    }
    $page_num = '10';
    $page = !empty($_GET['page']) ? intval($_GET['page']) : 1;
    $pages = ceil($record_count / $page_num);

    if ($page <= 0)
    {
        $page = 1;
    }
    if ($pages == 0)
    {
        $pages = 1;
    }
    if ($page > $pages)
    {
        $page = $pages;
    }
    $pagebar = get_wap_pager($record_count, $page_num, $page, 'user.php?act=coupons&status='.$status, 'page');
    $pager = get_pager('user.php', array('act' => $action,'status' => $status), $record_count, $page);
    if($_REQUEST['order_flow']!=1)
    {
        $bonus = get_user_bouns_lists($user_id, $pager['size'], $pager['start'], $status);
    }
    else
    {
        $bonus= $user_bonus;
    }
    $smarty->assign('status', $status);
    $smarty->assign('used_count', $used_count);
    $smarty->assign('unavl_count', $unavl_count);
    $smarty->assign('avl_count', $avl_count);
    $smarty->assign('pager', $pagebar);
    $smarty->assign('bonus', $bonus);
    $smarty->assign('user_type', $user_type);
    $smarty->display('gift_cart.html');
}
//收货地址管理
elseif ($act == 'address_list')
{
    /*
             * 收货人信息填写界面
             */
    if (isset($_REQUEST['direct_shopping']))
    {
        $_SESSION['direct_shopping'] = 1;
    }
    include_once('includes/lib_transaction.php');
    $consignee_list = get_consignee_list($_SESSION['user_id']);
    if( 5>= count($consignee_list) && count($consignee_list)>0)
    {
        if($_GET['addaddress']==1)
        {
            $province_list = array();
            $city_list = array();
            $district_list = array();
            $city_list	 = get_regions(2, 0);
            $district_list = get_regions(3, 0);
            $smarty->assign('province_list', get_regions(1, $_CFG['shop_country']));
            $smarty->assign('city_list',	 $city_list);
            $smarty->assign('district_list', $district_list);
            $buy_type=1;
            $smarty->assign('buy_type', $buy_type);
        }
        else
        {
            foreach ($consignee_list AS $region_id => $consignee)
            {
                $consignee_list[$region_id]['tel']=hidtel($consignee['tel']);//隐藏号码
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
        $smarty->assign('buy_type', 2);
        $smarty->assign('province_list', get_regions(1, $_CFG['shop_country']));
        $smarty->assign('city_list',	 $city_list);
        $smarty->assign('district_list', $district_list);
    }
    /* 获取默认收货ID */
    $address_id  = $db->getOne("SELECT address_id FROM " .$ecs->table('users'). " WHERE user_id='$user_id'");
    $smarty->assign('address_id', $address_id);
    $smarty->assign('footer', get_footer());


    $bonus_id = empty($_REQUEST['bonus_id']) ? 0 : intval($_REQUEST['bonus_id']);
    $gift_id = empty($_REQUEST['gift_id']) ? 0 : intval($_REQUEST['gift_id']);
    $is_overseas = empty($_REQUEST['is_overseas']) ? 0 : intval($_REQUEST['is_overseas']);
    if($_REQUEST['address_flow'])
    {
        $smarty->assign('buy_type', 4);
        $smarty->assign('bonus_id', $bonus_id);
        $smarty->assign('gift_id', $gift_id);
        $smarty->assign('is_overseas', $is_overseas);
    }
    $smarty->display('address_list.html');
}
/*  我的积分 */
elseif ($act == 'integral')
{
    include_once(ROOT_PATH . 'includes/lib_clips.php');
    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

    $account_type = 'pay_points';

    /* 获取记录条数 */
    $sql = "SELECT COUNT(*) FROM " .$ecs->table('account_log').
        " WHERE user_id = '$user_id'" .
        " AND $account_type <> 0 ";
    $record_count = $db->getOne($sql);

    //分页函数
    $pager = get_pager('user.php', array('act' => $action), $record_count, $page);

    $page_num = '10';
    $page = !empty($_GET['page']) ? intval($_GET['page']) : 1;
    $pages = ceil($record_count / $page_num);
    if ($page <= 0)
    {
        $page = 1;
    }
    if ($pages == 0)
    {
        $pages = 1;
    }
    if ($page > $pages)
    {
        $page = $pages;
    }
    //获取积分
    $sql_integral = "SELECT pay_points FROM " .$ecs->table('users').
        " WHERE user_id = '$user_id'";
    $integral = $db->getOne($sql_integral);
    if (empty($integral))
    {
        $integral = 0;
    }

    //获取余额记录
    $account_log = array();
    $sql = "SELECT * FROM " . $ecs->table('account_log') .
        " WHERE user_id = '$user_id'" .
        " AND $account_type <> 0 " .
        " ORDER BY log_id DESC";
    $res = $GLOBALS['db']->selectLimit($sql, $pager['size'], $pager['start']);
    while ($row = $db->fetchRow($res))
    {
        $row['change_time'] = local_date('Y-m-d H:i:s', $row['change_time']);
        $row['type'] = $row[$account_type] > 0 ? 0 : 1;
        $row['frozen_money'] = price_format(abs($row['frozen_money']), false);
        $row['rank_points'] = abs($row['rank_points']);
        $row['pay_points'] = $row['pay_points'];
        $row['short_change_desc'] = sub_str($row['change_desc'], 60);
        $row['amount'] = $row[$account_type];
        $account_log[] = $row;
    }

    $pagebar = get_wap_pager($record_count, $page_num, $page, 'user.php?act=integral', 'page');
    //模板赋值
    $smarty->assign('user_type', 7);
    $smarty->assign('integral', $integral);
    $smarty->assign('account_log',    $account_log);
    $smarty->assign('pager',          $pagebar);
    $smarty->assign('footer', get_footer());
    $smarty->assign('show_name', "我的积分");
    $smarty->display('gift_cart.html');
}
/* 编辑收货地址的处理 */
elseif ($act == 'edit_address')
{
    $consignee = get_selected_consignee($_GET['id']);
    /* 取得国家列表、商店所在国家、商店所在国家的省列表 */
    //$smarty->assign('country_list',	   get_regions());
//    $consignee['tel']=hidtel($consignee['tel']);
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
    $flow_order=0;
    $bonus_id=0;
    $gift_id=0;
    if($_REQUEST['flow_order'])
    {
        $flow_order = 1;
    }
    $bonus_id = empty($_REQUEST['bonus_id']) ? 0 : intval($_REQUEST['bonus_id']);
    $gift_id = empty($_REQUEST['gift_id']) ? 0 : intval($_REQUEST['gift_id']);
    $is_overseas = empty($_REQUEST['is_overseas']) ? 0 : intval($_REQUEST['is_overseas']);
    $smarty->assign('flow_order', $flow_order);
    $smarty->assign('bonus_id', $bonus_id);
    $smarty->assign('gift_id', $gift_id);
    $smarty->assign('is_overseas', $is_overseas);
    $smarty->assign('buy_type', 1);
    $smarty->assign('province_list', get_regions(1, $_CFG['shop_country']));
    $smarty->assign('city_list',	 $city_list);
    $smarty->assign('district_list', $district_list);
    $smarty->display('address_list.html');
}
/* 删除收货地址 */
elseif ($act == 'drop_consignee')
{
    include_once('includes/lib_transaction.php');

    $consignee_id = intval($_GET['id']);
    if($consignee_id == $_SESSION['flow_consignee']['address_id']){
        unset($_SESSION['flow_consignee']);
    }
    $bonus_id = empty($_REQUEST['bonus_id']) ? 0 : intval($_REQUEST['bonus_id']);
    $gift_id = empty($_REQUEST['gift_id']) ? 0 : intval($_REQUEST['gift_id']);
    $flowconsignee_id = intval($_GET['flowconsignee_id']);
    if($flowconsignee_id==1)
    {
        if (drop_consignee($consignee_id))
        {
            ecs_header("Location: user.php?act=address_list&address_flow=1&bonus_id=$bonus_id&gift_id=$gift_id\n");
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
    $smarty->display('address_list.html');
}
/* 修改默认收货人信息 */
elseif ($act == 'act_updateconsignee')
{
    include_once('includes/cls_json.php');
    $json = new JSON;

    $r['errorcode']   = 0;
    $r['msg'] = '修改默认收货人成功！';

    $address_id = isset($_POST['address_id']) ? intval($_POST['address_id']) : 0;
    $user_id = $_SESSION['user_id'];
    if($address_id <= 0){
        $r['errorcode']   = 1;
        $r['msg'] = '收货人ID不能为空!';
    }
    if($user_id <= 0){
        $r['errorcode']   = 2;
        $r['msg'] = '用户ID不能为空!';
    }

    if($address_id > 0 && $_SESSION['user_id'] > 0){
        $sql = 'UPDATE ' . $ecs->table('users') . " SET `address_id`='$address_id' WHERE `user_id`='$user_id'";
        $result = $db->query($sql);
        if(!$result){
            $r['errorcode']   = 3;
            $r['msg'] = '更新操作失败!';
        }
    }

    die($json->encode($r));
}
elseif ($act == 'address_save')
{
    if($_POST['address_id']>0)
    {
        $addid =$_POST['address_id'];
        $defaultsql = "SELECT *".
            " FROM " . $GLOBALS['ecs']->table('user_address') .
            " WHERE address_id='$addid'";

        $default_id = $GLOBALS['db']->getRow($defaultsql);
    }
    //19:21 2013-7-16
    $_POST['country'] = 1;//默认国家
    $_POST['email'] = empty($_SESSION['email']) ? '' : compile_str($_SESSION['email']);
    if(empty($_POST['province']) || empty($_POST['city'])){
        echo '配送区域不可为空！';
        exit;
    }
    if(empty($_POST['consignee']))
    {
        echo '收货人姓名不可为空！';
        exit;
    }

    if(empty($_POST['address']))
    {
        echo '详细地址不可为空！';
        exit;
    }
    if(empty($_POST['tel']))
    {
        echo '电话/手机不可为空！';
        exit;
    }
    /*
     * 保存收货人信息
     */
    $consignee = array(
        'address_id'	=> empty($_POST['address_id']) ? 0  : intval($_POST['address_id']),
        'consignee'	 => empty($_POST['consignee'])  ? '' : compile_str(trim($_POST['consignee'])),
        'country'	   => empty($_POST['country'])	? '' : intval($_POST['country']),
        'province'	  => empty($_POST['province'])   ? '' : intval($_POST['province']),
        'city'		  => empty($_POST['city'])	   ? '' : intval($_POST['city']),
        'district'	  => empty($_POST['district'])   ? '' : intval($_POST['district']),
        'email'		 => empty($_POST['email'])	  ? '' : compile_str($_POST['email']),
        'address'	   => empty($_POST['address'])	? '' : compile_str($_POST['address']),
        'zipcode'	   => empty($_POST['zipcode'])	? '' : compile_str(make_semiangle(trim($_POST['zipcode']))),
        'tel'		   => empty($_POST['tel'])		? '' : compile_str(make_semiangle(trim($_POST['tel']))),
        'mobile'		=> empty($_POST['mobile'])	 ? '' : compile_str(make_semiangle(trim($_POST['mobile']))),
        'sign_building' => empty($_POST['sign_building']) ? '' : compile_str($_POST['sign_building']),
        'id_card'	 => empty($_POST['id_card'])  ? '' : compile_str($_POST['id_card']),
    );
    if ($_SESSION['user_id'] > 0)
    {
        include_once(ROOT_PATH . 'includes/lib_transaction.php');

        /* 如果用户已经登录，则保存收货人信息 */
        $consignee['user_id'] = $_SESSION['user_id'];
        save_consignee($consignee, false);

        $smarty->assign('buy_type', 2);

        $bonus_id = empty($_REQUEST['bonus_id']) ? 0 : intval($_REQUEST['bonus_id']);
        $gift_id = empty($_REQUEST['gift_id']) ? 0 : intval($_REQUEST['gift_id']);
        $flow_order = empty($_REQUEST['flow_order']) ? 0 : trim($_REQUEST['flow_order']);
        if($flow_order > 0)
        {
            if($_POST['is_overseas'] == 1)
            {
                if(empty($_POST['id_card']))
                {
                    show_message('请填写身份证号码！');
                }
                else
                {
                    $IDCard = new IDCard();
                    if(!$IDCard->isCard($_POST['id_card']))
                    {
                        show_message('请输入正确的身份证号码。');
                    }
                }
            }
            $consignee['address_id']=$_SESSION['session_address_id'];
            unset($_SESSION['session_address_id']);
            $smarty->assign('buy_type', 4);
            ecs_header("Location: order.php?act=order_lise&address_flow=1&bonus_id=$bonus_id&gift_id=$gift_id&address_id=$consignee[address_id]\n");
            exit;
        }
        ecs_header("Location: user.php?act=address_list\n");
        exit;
    }
    else
    {
        $smarty->assign('footer', get_footer());
        $smarty->display('login.html');
        exit;
    }
}
elseif ($act == 'order_list')
{
	if(!$_SESSION['user_id']){
//		$smarty->assign('footer', get_footer());
//		$smarty->display('login.html');
//		exit;
        ecs_header("Location: user.php?act=login\n");
        exit;
	}
    $status = isset($_REQUEST['status']) ? $_REQUEST['status'] : 'all';
    $where = ' and 1';
    switch($status)
    {
        case 'unpay' :
            $where .= ' and pay_status=0 and order_status<>2 and order_status<>4';
            break;
        case 'deliveryd' :
            $where .= ' and pay_status=2 and shipping_status<>2';
            break;
        case 'complete' :
            $where .= ' and pay_status=2 and shipping_status=2';
            break;
        default:
    }
	$record_count = $db->getOne("SELECT COUNT(*) FROM " .$ecs->table('order_info'). " WHERE user_id = {$_SESSION['user_id']}".$where);
	if ($record_count > 0)
	{
		include_once(ROOT_PATH . 'includes/lib_transaction.php');
		$page_num = '10';
		$page = !empty($_GET['page']) ? intval($_GET['page']) : 1;
		$pages = ceil($record_count / $page_num);

		if ($page <= 0)
		{
			$page = 1;
		}
		if ($pages == 0)
		{
			$pages = 1;
		}
		if ($page > $pages)
		{
			$page = $pages;
		}
		$pagebar = get_wap_pager($record_count, $page_num, $page, 'user.php?act=order_list&status='.$status, 'page');
		$smarty->assign('pagebar' , $pagebar);
		/* 订单状态 */
		$_LANG['os'][OS_UNCONFIRMED] = '未确认';
		$_LANG['os'][OS_CONFIRMED] = '已确认';
		$_LANG['os'][OS_SPLITED] = '已确认';
		$_LANG['os'][OS_SPLITING_PART] = '已确认';
		$_LANG['os'][OS_CANCELED] = '已取消';
		$_LANG['os'][OS_INVALID] = '无效';
		$_LANG['os'][OS_RETURNED] = '退货';

		$_LANG['ss'][SS_UNSHIPPED] = '未发货';
		$_LANG['ss'][SS_PREPARING] = '配货中';
		$_LANG['ss'][SS_SHIPPED] = '已发货';
		$_LANG['ss'][SS_RECEIVED] = '收货确认';
		$_LANG['ss'][SS_SHIPPED_PART] = '已发货(部分商品)';
		$_LANG['ss'][SS_SHIPPED_ING] = '配货中'; // 已分单

		$_LANG['ps'][PS_UNPAYED] = '未付款';
		$_LANG['ps'][PS_PAYING] = '付款中';
		$_LANG['ps'][PS_PAYED] = '已付款';
		$_LANG['cancel'] = '取消订单';
		$_LANG['pay_money'] = '付款';
		$_LANG['view_order'] = '查看订单';
		$_LANG['received'] = '确认收货';
		$_LANG['ss_received'] = '已完成';
		$_LANG['confirm_received'] = '你确认已经收到货物了吗？';
		$_LANG['confirm_cancel'] = '您确认要取消该订单吗？取消后此订单将视为无效订单';

		$orders = get_user_orders($_SESSION['user_id'], $page_num, $page_num * ($page - 1),$where);
		if (!empty($orders))
		{
			foreach ($orders as $key => $val)
			{
				$orders[$key]['total_fee'] = encode_output($val['total_fee']);
				$orders[$key]['mobile_sta'] = $orders[$key]['pay_status']==2 ? 1: 0;
                $orders[$key]['mobile_status'] = get_mobile_orderstatus($orders[$key]['ori_order_status'],$orders[$key]['pay_status'],$orders[$key]['shipping_status']);
			}
		}
		//$merge  = get_user_merge($_SESSION['user_id']);

		$smarty->assign('orders', $orders);
	}
    /* 订单商品 */
    $goods_list = order_goods($order_id);
    $smarty->assign('goods_list', $goods_list);
	$smarty->assign('footer', get_footer());
	$smarty->assign('status', $status);
	$smarty->display('order_list.html');
	exit;
}
/* 订单详情 */
elseif($act=='order_info'){
	if(!$_SESSION['user_id']){
//		$smarty->assign('footer', get_footer());
//		$smarty->display('login.html');
//		exit;
        ecs_header("Location: user.php?act=login\n");
        exit;
	}
	$id= isset($_GET['id']) ? intval($_GET['id']) : 0;
	include_once(ROOT_PATH . 'includes/lib_transaction.php');
	include_once(ROOT_PATH . 'includes/lib_payment.php');
	include_once(ROOT_PATH . 'includes/lib_order.php');
	include_once(ROOT_PATH . 'includes/lib_clips.php');
	/* 订单详情 */
	$order = get_order_detail($id, $_SESSION['user_id'],'mobile');
	if ($order === false)
	{
		exit("对不起，该订单不存在");
	}
	require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/user.php');
	/* 订单商品 */
	$goods_list = order_goods($id);
	if (empty($goods_list))
	{
		exit("订单错误");
	}
	foreach ($goods_list AS $key => $value)
	{
		$goods_list[$key]['market_price'] = price_format($value['market_price'], false);
		$goods_list[$key]['goods_price']  = price_format($value['goods_price'], false);
		$goods_list[$key]['subtotal']	 = price_format($value['subtotal'], false);
	}
    /* 取得区域名 */
    $sql = "SELECT concat(IFNULL(p.region_name, ''), " .
        "'  ', IFNULL(t.region_name, ''), '  ', IFNULL(d.region_name, '')) AS region " .
        "FROM " . $ecs->table('order_info') . " AS u " .
        "LEFT JOIN " . $ecs->table('region') . " AS p ON u.province = p.region_id " .
        "LEFT JOIN " . $ecs->table('region') . " AS t ON u.city = t.region_id " .
        "LEFT JOIN " . $ecs->table('region') . " AS d ON u.district = d.region_id " .
        "WHERE u.order_id = '$order[order_id]'";
    $region = $db->getOne($sql);
    if(!empty($region)){
        $order["address_name"] = $region.' '.$order["address"];
    }
    $is_membersgoods = goods_extensioncode($goods_list[0]['goods_id']) == 'goods_members'? 1 : 0;
    $smarty->assign('is_membersgoods', $is_membersgoods);

    //获取物流信息
    $postcom = get_shipping_code($order["shipping_name"]);
    $getNu = $order['shipping_no'];
    $url = "http://www.kuaidi100.com/query?type=$postcom&postid=$getNu";
    $json_info = array(json_decode(file_get_contents($url)));
    $shipping_info['status'] = 0;
    $shipping_info['data'] = '';
    $data = array();
    if($json_info[0]->status == 200){
        $shipping_info['status'] = 1;
        foreach($json_info[0]->data as $val){
            $arr['time'] = $val->time;
            $arr['context'] = $val->context;
            array_push($data,$arr);
        }
        $shipping_info['data'] = $data;
    }
    $smarty->assign('shipping_info',      $shipping_info);

	/* 订单 支付 配送 状态语言项 */
    $order['mobile_status'] = get_mobile_orderstatus($order['order_status'],$order['pay_status'],$order['shipping_status'],false);
	$order['order_status'] = $_LANG['os'][$order['order_status']];
	$order['pay_status'] = $_LANG['ps'][$order['pay_status']];
	$order['shipping_status'] = $_LANG['ss'][$order['shipping_status']];
	$smarty->assign('order',	  $order);
    $smarty->assign('jspara', $order['jspara']);
	$smarty->assign('goods_list', $goods_list);
	$smarty->assign('lang',	   $_LANG);
	$smarty->assign('footer', get_footer());
	$smarty->assign('cat_name', "订单详情");
	$smarty->display('order_info.html');
	exit();
}
/* 取消订单 */
elseif ($act == 'cancel_order')
{
	if(!$_SESSION['user_id']){
//		$smarty->assign('footer', get_footer());
//		$smarty->display('login.html');
//		exit;
        ecs_header("Location: user.php?act=login\n");
        exit;
	}
	include_once(ROOT_PATH . 'includes/lib_transaction.php');
	include_once(ROOT_PATH . 'includes/lib_order.php');

	$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
	if (cancel_order($order_id, $_SESSION['user_id']))
	{
		ecs_header("Location: user.php?act=order_list\n");
		exit;
	}
}

/* 确认收货 */
elseif ($act == 'affirm_received')
{
	if(!$_SESSION['user_id']){
//		$smarty->assign('footer', get_footer());
//		$smarty->display('login.html');
//		exit;
        ecs_header("Location: user.php?act=login\n");
        exit;
	}
	include_once(ROOT_PATH . 'includes/lib_transaction.php');

	$order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;
	$_LANG['buyer'] = '买家';
	if (affirm_received($order_id, $_SESSION['user_id']))
	{
        include_once(ROOT_PATH . 'includes/lib_order.php');
        $order = order_info($order_id);
        /* 计算并发放积分 */
        $integral = integral_to_give($order);

        log_account_change($order['user_id'], 0, 0, intval($integral['rank_points']), intval($integral['custom_points']), "订单".$order['order_sn']."赠送的积分",99,$order['integrals'],$order['order_sn']);

        ecs_header("Location: user.php?act=order_list&status=deliveryd\n");
		exit;
	}

}

/* 退出会员中心 */
elseif ($act == 'logout')
{
	if (!isset($back_act) && isset($GLOBALS['_SERVER']['HTTP_REFERER']))
	{
		$back_act = strpos($GLOBALS['_SERVER']['HTTP_REFERER'], 'user.php') ? './index.php' : $GLOBALS['_SERVER']['HTTP_REFERER'];
	}
	$user->logout();
	$Loaction = 'index.php';
	ecs_header("Location: $Loaction\n");

}
/* 显示会员注册界面 */
elseif ($act == 'register')
{
    if (!empty($_SESSION['back_act']))
    {
        $back_act = $_SESSION['back_act'];
    }
    else
    {
        $back_act = isset($_REQUEST['back_act']) ? trim($_REQUEST['back_act']) : '';
    }
    if (empty($back_act))
    {
        if (empty($back_act) && isset($GLOBALS['_SERVER']['HTTP_REFERER']))
        {
            $back_act = strpos($GLOBALS['_SERVER']['HTTP_REFERER'], 'user.php') ? './index.php' : $GLOBALS['_SERVER']['HTTP_REFERER'];
        }
        else
        {
            $back_act = 'user.php';
        }
    }
    if($back_act == 'user.php' && $_SESSION['goods_id'] == '11441'){
        $back_act = 'http://www.wjike.com/mobile/julyvipbuy.php';
    }
    if($back_act == 'user.php' && $_SESSION['goods_id'] == '11443'){
        $back_act = 'http://www.wjike.com/mobile/goods.php?id=11443';
    }
	if($_SESSION['user_id'] > 0){
		echo '<meta http-equiv="refresh" content="0;URL='.$back_act.'" />';
		exit;
	}
	/* 取出注册扩展字段 */
	$sql = 'SELECT * FROM ' . $ecs->table('reg_fields') . ' WHERE type < 2 AND display = 1 ORDER BY dis_order, id';
	$extend_info_list = $db->getAll($sql);
	$smarty->assign('extend_info_list', $extend_info_list);

    /* 验证码相关设置 */
    if ((intval($_CFG['captcha']) & CAPTCHA_REGISTER) && gd_version() > 0)
    {
        $smarty->assign('enabled_captcha', 1);
        $smarty->assign('rand',            mt_rand());
    }
    /* 注册错误信息 */
    if(!empty($_SESSION['errormsg'])){
        $smarty->assign('errormsg', $_SESSION['errormsg']);
        $_SESSION['errormsg'] = '';
    }
	$smarty->assign('footer', get_footer());
	$smarty->assign('back_act', $back_act);
	$smarty->display('user_passport.html');
}
/* 注册会员的处理 */
elseif ($act == 'act_register')
{
    if($_CFG['shop_reg_closed']){
        $smarty->assign('action',     'register');
        $smarty->assign('shop_reg_closed', $_CFG['shop_reg_closed']);
        $smarty->display('user_passport.dwt');
    }
    else{
        include_once(ROOT_PATH . 'includes/lib_passport.php');

        $username = isset($_POST['phone']) ? trim($_POST['phone']) : '';
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';
        $confirm_password = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';
        $vcode = isset($_POST['vcode']) ? trim($_POST['vcode']) : '';
        $email	= isset($_POST['email']) ? trim($_POST['email']) : '';
        $other['msn'] = isset($_POST['extend_field1']) ? $_POST['extend_field1'] : '';
        $other['qq'] = isset($_POST['extend_field2']) ? $_POST['extend_field2'] : '';
        $other['office_phone'] = isset($_POST['extend_field3']) ? $_POST['extend_field3'] : '';
        $other['home_phone'] = isset($_POST['extend_field4']) ? $_POST['extend_field4'] : '';
        $other['mobile_phone'] = isset($_POST['phone']) ? $_POST['phone'] : '';
        $sel_question = empty($_POST['sel_question']) ? '' : $_POST['sel_question'];
        $passwd_answer = isset($_POST['passwd_answer']) ? trim($_POST['passwd_answer']) : '';

        $back_act = isset($_POST['back_act']) ? trim($_POST['back_act']) : '';
        if (strlen($username) < 11)
        {
            $_SESSION['errormsg'] = '<span style="background-color: #ffa200;padding: 0px 6px;border-radius: 8px;color: #fff;margin-right: 4px;">!</span>输入手机号位数少于11位';
            ecs_header("Location:./user.php?act=register\n");
        }

        if (strlen($password) < 6)
        {
            $_SESSION['errormsg'] = '<span style="background-color: #ffa200;padding: 0px 6px;border-radius: 8px;color: #fff;margin-right: 4px;">!</span>密码长度不能少于6位。';
            ecs_header("Location:./user.php?act=register\n");
        }

        if (strpos($password, ' ') > 0)
        {
            $_SESSION['errormsg'] = '<span style="background-color: #ffa200;padding: 0px 6px;border-radius: 8px;color: #fff;margin-right: 4px;">!</span>'.$_LANG['passwd_balnk'];
            ecs_header("Location:./user.php?act=register\n");
        }

        if (strlen($vcode) < 6)
        {
            $_SESSION['errormsg'] = '<span style="background-color: #ffa200;padding: 0px 6px;border-radius: 8px;color: #fff;margin-right: 4px;">!</span>手机验证码错误';
            ecs_header("Location:./user.php?act=register\n");
        }

        if ($password != $confirm_password)
        {
            $_SESSION['errormsg'] = '<span style="background-color: #ffa200;padding: 0px 6px;border-radius: 8px;color: #fff;margin-right: 4px;">!</span>两次输入密码不一致';
            ecs_header("Location:./user.php?act=register\n");
        }

        /* 手机验证码检查 */
        $vcode_str = $username.$vcode;
        if($_SESSION['vcode_str'] != $vcode_str){
            $_SESSION['errormsg'] = '<span style="background-color: #ffa200;padding: 0px 6px;border-radius: 8px;color: #fff;margin-right: 4px;">!</span>手机验证码错误';
            ecs_header("Location:./user.php?act=register\n");
        }
        else{
            unset($_SESSION['vcode_str']);
        }

        /* 验证码检查 */
        if ((intval($_CFG['captcha']) & CAPTCHA_REGISTER) && gd_version() > 0)
        {
            if (empty($_POST['captcha']))
            {
                show_regmessage($_LANG['invalid_captcha'], $_LANG['sign_up'], 'user.php?act=register', 'error');
            }

            /* 检查验证码 */
            include_once('includes/cls_captcha.php');

            $validator = new captcha();
            if (!$validator->check_word($_POST['captcha']))
            {
                show_regmessage($_LANG['invalid_captcha'], $_LANG['sign_up'], 'user.php?act=register', 'error');
            }
        }

        if (m_register($username, $password, $email, $other) !== false)
        {
            /*把新注册用户的扩展信息插入数据库*/
            $sql = 'SELECT id FROM ' . $ecs->table('reg_fields') . ' WHERE type = 0 AND display = 1 ORDER BY dis_order, id';   //读出所有自定义扩展字段的id
            $fields_arr = $db->getAll($sql);

            $extend_field_str = '';	//生成扩展字段的内容字符串
            foreach ($fields_arr AS $val)
            {
                $extend_field_index = 'extend_field' . $val['id'];
                if(!empty($_POST[$extend_field_index]))
                {
                    $temp_field_content = strlen($_POST[$extend_field_index]) > 100 ? mb_substr($_POST[$extend_field_index], 0, 99) : $_POST[$extend_field_index];
                    $extend_field_str .= " ('" . $_SESSION['user_id'] . "', '" . $val['id'] . "', '" . compile_str($temp_field_content) . "'),";
                }
            }
            $extend_field_str = substr($extend_field_str, 0, -1);

            if ($extend_field_str)	  //插入注册扩展数据
            {
                $sql = 'INSERT INTO '. $ecs->table('reg_extend_info') . ' (`user_id`, `reg_field_id`, `content`) VALUES' . $extend_field_str;
                $db->query($sql);
            }

            /* 写入密码提示问题和答案 */
            if (!empty($passwd_answer) && !empty($sel_question))
            {
                $sql = 'UPDATE ' . $ecs->table('users') . " SET `passwd_question`='$sel_question', `passwd_answer`='$passwd_answer'  WHERE `user_id`='" . $_SESSION['user_id'] . "'";
                $db->query($sql);
            }

            $ucdata = empty($user->ucdata)? "" : $user->ucdata;
            //注册成功直接成为体验会员
            $members_deadline = gmtime() + (90 * 24 * 3600);
            $time = gmtime();
            $msql  = "UPDATE " .$GLOBALS['ecs']->table('users'). " SET is_members='1', members_deadline='$members_deadline'".
                " WHERE user_id='" . $_SESSION['user_id'] . "'";
            $result = $GLOBALS['db']->query($msql);
            if($result){
                $sql = "INSERT INTO " .$GLOBALS['ecs']->table('freemembers_log'). " (user_id, has_get, add_time)" .
                    "VALUES ('$_SESSION[user_id]', '1', '$time')";

                $GLOBALS['db']->query($sql);
            }
            //新注册用户发放优惠券
            send_bonuns($_SESSION['user_id']);
            if ($_SESSION['user_id'] > 0)
            {
                $smarty->assign('user_name', $_SESSION['user_name']);
            }

            if (!empty($_SESSION['back_act']))
            {
                $_SESSION['back_act'] = '';
            }
            show_regmessage('注册成功！', $_LANG['sign_up'], $back_act, 'error');
        }
        else
        {
            show_regmessage('注册失败！', $_LANG['sign_up'], 'user.php?act=register', 'error');
        }
    }
}

/* 获取短信验证码 */
elseif ($act == 'get_vcode')
{
    include_once(ROOT_PATH . 'includes/cls_msgsend.php');
    include_once(ROOT_PATH . 'includes/cls_json.php');

    $msgsend = new msgsend();
    $json = new JSON;
    $phone = $_POST['phone'];

    $r['errorcode']   = 0;
    $r['msg'] = '校验码发送成功！';
    /* 网站验证码检查 */
    if ((intval($_CFG['captcha']) & CAPTCHA_REGISTER) && gd_version() > 0)
    {
        /* 检查验证码 */
        include_once(ROOT_PATH.'includes/cls_captcha.php');

        $validator = new captcha();
        if (empty($_POST['captcha']) || !$validator->check_word($_POST['captcha']))
        {
            $r['errorcode']   = 5;
            $r['msg'] = '请输入正确的图片验证码';
            die($json->encode($r));
        }
    }
    if(strpos($_SERVER['HTTP_REFERER'],'http://'.$_SERVER['HTTP_HOST'].'/mobile/wactivity.php?act=register&openid=') === false
        && strpos($_SERVER['HTTP_REFERER'],'http://'.$_SERVER['HTTP_HOST'].'/mobile/user.php?act=register') === false){
        $r['errorcode']   = 3;
        $r['msg'] = '短信接口错误';
        die($json->encode($r));
    }

    //检查短信ip记录表记录是否存在
    $sql = "SELECT COUNT(*) FROM " . $ecs->table('message_iprecord') .
        " WHERE user_ip = '" . real_ip() . "'";
    if ($db->getOne($sql) > 0)
    {
        $r['errorcode']   = 4;
        $r['msg'] =  '请勿频繁发送(两分钟内只能发一次)';
        die($json->encode($r));
    }

    //检查用户名是否存在
    $sql = "SELECT COUNT(*) FROM " . $ecs->table('users') .
        " WHERE user_name = '" . $phone . "' or mobile_phone = '" . $phone . "'";
    if ($db->getOne($sql) > 0)
    {
        $r['errorcode']   = 2;
        $r['msg'] = '<span style="background-color: #ffa200;padding: 0px 6px;border-radius: 8px;color: #fff;margin-right: 4px;">!</span>该手机号已经注册';
        die($json->encode($r));
    }

    $vcode = rand(100000,999999);
    $_SESSION['vcode_str'] = $phone.$vcode;
    $msg = '尊敬的用户您好，您本次的短信验证码为：'. $vcode .'。请妥善保管，不要告诉他人';
    $result = $msgsend->send($phone,$msg,"mobilereg");

    //插入短信发送ip记录
    $db->query("INSERT INTO ".$ecs->table('message_iprecord')." (user_ip) VALUES('".real_ip()."')");

    if(!$result['status']){
        $r['errorcode']   = 1;
//        $r['msg'] = '<span style="background-color: #ffa200;padding: 0px 6px;border-radius: 8px;color: #fff;margin-right: 4px;">!</span>'.$result['server_errors'];
        $r['msg'] = '<span style="background-color: #ffa200;padding: 0px 6px;border-radius: 8px;color: #fff;margin-right: 4px;">!</span>'.'请输入';
    }
    die($json->encode($r));
}

/* 显示忘记密码界面 */
elseif ($act == 'forget_password')
{
    if ((!isset($back_act)||empty($back_act)) && isset($GLOBALS['_SERVER']['HTTP_REFERER']))
    {
        $back_act = strpos($GLOBALS['_SERVER']['HTTP_REFERER'], 'user.php') ? './index.php' : $GLOBALS['_SERVER']['HTTP_REFERER'];
    }

    /* 验证码相关设置 */
    if ((intval($_CFG['captcha']) & CAPTCHA_REGISTER) && gd_version() > 0)
    {
        $smarty->assign('enabled_captcha', 1);
        $smarty->assign('rand',            mt_rand());
    }
    /* 注册错误信息 */
    if(!empty($_SESSION['errormsg'])){
        $smarty->assign('errormsg', $_SESSION['errormsg']);
        $_SESSION['errormsg'] = '';
    }
    $smarty->display('forget_passport.html');
}

/* 忘记会员密码 */
elseif ($act == 'act_forgetpassword')
{
    include_once(ROOT_PATH . 'includes/lib_passport.php');
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $confirm_password = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';
    $vcode = isset($_POST['vcode']) ? trim($_POST['vcode']) : '';

    $GLOBALS['smarty']->assign('mobile_show', 2);
    if (strlen($phone) < 11)
    {
        $_SESSION['errormsg'] = '<span style="background-color: #ffa200;padding: 0px 6px;border-radius: 8px;color: #fff;margin-right: 4px;">!</span>输入手机号位数少于11位';
        ecs_header("Location:./user.php?act=forget_password\n");
    }

    //检查用户名是否存在
    $sql = "SELECT user_id FROM " . $ecs->table('users') .
        " WHERE mobile_phone = '" . $phone . "' ";
    $user_id = $db->getOne($sql);

    if ($user_id <= 0)
    {
        $_SESSION['errormsg'] = '<span style="background-color: #ffa200;padding: 0px 6px;border-radius: 8px;color: #fff;margin-right: 4px;">!</span>该手机号注册用户名不存在';
        ecs_header("Location:./user.php?act=forget_password\n");
    }

    if (strlen($password) < 6)
    {
        $_SESSION['errormsg'] = '<span style="background-color: #ffa200;padding: 0px 6px;border-radius: 8px;color: #fff;margin-right: 4px;">!</span>'.$_LANG['passport_js']['password_shorter'];
        ecs_header("Location:./user.php?act=forget_password\n");
    }

    if (strpos($password, ' ') > 0)
    {
        $_SESSION['errormsg'] = '<span style="background-color: #ffa200;padding: 0px 6px;border-radius: 8px;color: #fff;margin-right: 4px;">!</span>'.$_LANG['passwd_balnk'];
        ecs_header("Location:./user.php?act=forget_password\n");
    }

    if (strlen($vcode) < 6)
    {
        $_SESSION['errormsg'] = '<span style="background-color: #ffa200;padding: 0px 6px;border-radius: 8px;color: #fff;margin-right: 4px;">!</span>手机验证码错误';
        ecs_header("Location:./user.php?act=forget_password\n");
    }

    if ($password != $confirm_password)
    {
        $_SESSION['errormsg'] = '<span style="background-color: #ffa200;padding: 0px 6px;border-radius: 8px;color: #fff;margin-right: 4px;">!</span>两次输入密码不一致';
        ecs_header("Location:./user.php?act=forget_password\n");
    }

    /* 手机验证码检查 */
    $vcode_str = $phone.$vcode;
    if($_SESSION['vcode_str'] != $vcode_str){
        $_SESSION['errormsg'] = '<span style="background-color: #ffa200;padding: 0px 6px;border-radius: 8px;color: #fff;margin-right: 4px;">!</span>手机验证码错误';
        ecs_header("Location:./user.php?act=forget_password\n");
    }
    else{
        unset($_SESSION['vcode_str']);
    }

    /* 网站验证码检查 */
//    if ((intval($_CFG['captcha']) & CAPTCHA_REGISTER) && gd_version() > 0)
//    {
//        if (empty($_POST['captcha']))
//        {
//            $_SESSION['errormsg'] = $_LANG['invalid_captcha'];
//            ecs_header("Location:./user.php?act=register\n");
//        }
//
//        /* 检查验证码 */
//        include_once('includes/cls_captcha.php');
//
//        $validator = new captcha();
//        if (!$validator->check_word($_POST['captcha']))
//        {
//            $_SESSION['errormsg'] = $_LANG['invalid_captcha'];
//            ecs_header("Location:./user.php?act=register\n");
//        }
//    }

    $user_info = $user->get_profile_by_id($user_id); //论坛记录


    if ($user->edit_user(array('username'=> $user_info['user_name'], 'old_password'=>'', 'password'=>$password), empty($code) ? 0 : 1))
    {
        $sql="UPDATE ".$ecs->table('users'). "SET `ec_salt`='0' WHERE user_id= '".$user_id."'";
        $db->query($sql);
        $user->logout();
        show_regmessage($_LANG['edit_password_success'], $_LANG['relogin_lnk'], 'user.php?act=login');
    }
    else
    {
        show_regmessage($_LANG['edit_password_failure'], $_LANG['sign_up'], 'user.php?act=register', 'error');
    }

}

/* 忘记密码获取短信验证码 */
elseif ($act == 'get_forgetvcode')
{
    include_once(ROOT_PATH . 'includes/cls_msgsend.php');
    include_once(ROOT_PATH .'includes/cls_json.php');

    $msgsend = new msgsend();
    $json = new JSON;
    $phone = $_REQUEST['phone'];

    $r['errorcode']   = 0;
    $r['msg'] = '校验码发送成功！';

    if($_SERVER['HTTP_REFERER'] != 'http://'.$_SERVER['HTTP_HOST'].'/mobile/user.php?act=forget_password'){
        $r['errorcode']   = 3;
        $r['msg'] = '短信接口错误';
        die($json->encode($r));
    }

    //检查短信ip记录表记录是否存在
    $sql = "SELECT COUNT(*) FROM " . $ecs->table('message_iprecord') .
        " WHERE user_ip = '" . real_ip() . "'";
    if ($db->getOne($sql) > 0)
    {
        $r['errorcode']   = 4;
        $r['msg'] =  '请勿频繁发送(两分钟内只能发一次)';
        die($json->encode($r));
    }

    //检查用户名是否存在
    $sql = "SELECT COUNT(*) FROM " . $ecs->table('users') .
        " WHERE mobile_phone = '" . $phone . "' ";
    if ($db->getOne($sql) <= 0)
    {
        $r['errorcode']   = 2;
        $r['msg'] = '该手机号未绑定';
        die($json->encode($r));
    }

    $vcode = rand(100000,999999);
    $_SESSION['vcode_str'] = $phone.$vcode;
    $msg = '尊敬的用户您好，您本次的短信验证码为：'. $vcode .'。请妥善保管，不要告诉他人';
    $result = $msgsend->send($phone,$msg,"mobilefp");

    //插入短信发送ip记录
    $db->query("INSERT INTO ".$ecs->table('message_iprecord')." (user_ip) VALUES('".real_ip()."')");

    if(!$result['status']){
        $r['errorcode']   = 1;
        $r['msg'] = $result['code'];
    }
    die($json->encode($r));
}

elseif ($act == 'chkname')
{
    include_once(ROOT_PATH .'includes/cls_json.php');
    $json = new JSON();

    $result = array('error' => 0, 'message' => '', 'content' => '');
    $name = $_POST['name'];
    $sql ="SELECT COUNT(*) FROM " . "ecs_users" . "  WHERE user_name = '$name'";
    $id = $db->getOne($sql);
    $result['content']=$id;
    if($id>0)
    {
        $result['message']   = '<span style="background-color: #ffa200;padding: 0px 6px;border-radius: 8px;color: #fff;margin-right: 4px;">!</span>该手机号已被注册！';
    }
    else
    {
        $result['error']=1;
        $result['message']   = "";
    }
    die($json->encode($result));
    exit;
}
/* 添加收藏商品(ajax) */
elseif ($act == 'collect')
{
	include_once(ROOT_PATH .'includes/cls_json.php');
	$json = new JSON();
	$result = array('error' => 0, 'message' => '');
	$goods_id = $_GET['id'];

	if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] == 0)
	{
		$result['error'] = 1;
		$result['message'] = "由于您还没有登录，因此您还不能使用该功能。";
		die($json->encode($result));
	}
	else
	{
		/* 检查是否已经存在于用户的收藏夹 */
		$sql = "SELECT COUNT(*) FROM " .$GLOBALS['ecs']->table('collect_goods') .
			" WHERE user_id='$_SESSION[user_id]' AND goods_id = '$goods_id'";
		if ($GLOBALS['db']->GetOne($sql) > 0)
		{
			$result['error'] = 1;
			$result['message'] = "该商品已经存在于您的收藏夹中。";
			die($json->encode($result));
		}
		else
		{
			$time = gmtime();
			$sql = "INSERT INTO " .$GLOBALS['ecs']->table('collect_goods'). " (user_id, goods_id, add_time)" .
					"VALUES ('$_SESSION[user_id]', '$goods_id', '$time')";

			if ($GLOBALS['db']->query($sql) === false)
			{
				$result['error'] = 1;
				$result['message'] = $GLOBALS['db']->errorMsg();
				die($json->encode($result));
			}
			else
			{
				$result['error'] = 0;
				$result['message'] = "该商品已经成功地加入了您的收藏夹。";
				die($json->encode($result));
			}
		}
	}
}
/*  我的关注 */
elseif ($act == 'follow')
{
    include_once(ROOT_PATH . 'includes/lib_clips.php');

    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

    $record_count = $db->getOne("SELECT COUNT(*) FROM " .$ecs->table('collect_goods').
        " WHERE user_id='$user_id' ORDER BY add_time DESC");

    $pager = get_pager('user.php', array('act' => $action), $record_count, $page, 8);
    $smarty->assign('pager', $pager);
    $smarty->assign('goods_list', get_collection_goods($user_id, $pager['size'], $pager['start']));
    $smarty->assign('shopname', $_CFG['shop_name']);
    $smarty->assign('show_name', "我的关注");
    $smarty->display('follow.html');
}
elseif ($act == 'act_edit_payment')
{
    if(!$_SESSION['user_id']){
//        $smarty->assign('footer', get_footer());
//        $smarty->display('login.html');
//        exit;
        ecs_header("Location: user.php?act=login\n");
        exit;
    }

    /* 检查支付方式 */
    $pay_id = intval($_REQUEST['pay_id']);
    if ($pay_id <= 0)
    {
        ecs_header("Location: ./\n");
        exit;
    }

    include_once(dirname(__FILE__) . '/../includes/lib_order.php');
    $payment_info = payment_info($pay_id);
    if (empty($payment_info))
    {
        ecs_header("Location: ./\n");
        exit;
    }

    /* 检查订单号 */
    $order_id = intval($_REQUEST['order_id']);
    if ($order_id <= 0)
    {
        ecs_header("Location: ./\n");
        exit;
    }

    /* 取得订单 */
    $order = order_info($order_id);
    $sql = 'SELECT log_id FROM ' . $ecs->table('pay_log') . " WHERE order_id='$order_id' AND order_amount='".$order['order_amount']."'";
    $logId = $db->getOne($sql);
    $order['log_id'] = $logId;
    if (empty($logId))
    {
        ecs_header("Location: ./\n");
        exit;
    }

    if (empty($order))
    {
        ecs_header("Location: ./\n");
        exit;
    }
    /* 检查订单用户跟当前用户是否一致 */
    if ($_SESSION['user_id'] != $order['user_id'])
    {
        ecs_header("Location: ./\n");
        exit;
    }

    /* 检查订单是否未付款和未发货 以及订单金额是否为0 和支付id是否为改变*/
    if ($order['pay_status'] != PS_UNPAYED || $order['shipping_status'] != SS_UNSHIPPED || $order['goods_amount'] <= 0)
    {
        ecs_header("Location: user.php?act=order_info&id=$order_id\n");
        exit;
    }

    $order_amount = $order['order_amount'] - $order['pay_fee'];
    $pay_fee = pay_fee($pay_id, $order_amount);
    $order_amount += $pay_fee;

    $sql = "UPDATE " . $ecs->table('order_info') .
        " SET pay_id='$pay_id', pay_name='$payment_info[pay_name]', pay_fee='$pay_fee', order_amount='$order_amount'".
        " WHERE order_id = '$order_id'";
    $db->query($sql);
    //取得支付信息，生成支付代码
    $payment = unserialize_config($payment_info['pay_config']);
    /* 调用相应的支付方式文件 */
    include_once(ROOT_PATH . 'includes/modules/payment/' . $payment_info['pay_code'] . '.php');
    /* 取得在线支付方式的支付按钮 */
    $pay_obj = new $payment_info['pay_code'];

    if ($payment_info['pay_code'] == 'weixinpay')
    {
        $openid = $pay_obj->get_Openid();
        $jspara = $pay_obj->get_jspara($order, $payment,$openid);

        echo "<script type='text/javascript'>
        function jsApiCall()
        {
            WeixinJSBridge.invoke(
                    'getBrandWCPayRequest',
                    {$jspara},
                    function(res){
                        if(res.err_msg == 'get_brand_wcpay_request:ok' ) {
                            alert('支付成功');
                            window.location.href = '/mobile/user.php?act=order_list';
                        }
                        else if(res.err_msg == 'get_brand_wcpay_request:cancel' ) {
                            alert('取消支付');
                        }
                        else{
                            alert('支付失败');
                        }
                    }
            );
        }

        function callpay()
        {
            if (typeof WeixinJSBridge == 'undefined'){
                if( document.addEventListener ){
                    document.addEventListener('WeixinJSBridgeReady', jsApiCall, false);
                }else if (document.attachEvent){
                    document.attachEvent('WeixinJSBridgeReady', jsApiCall);
                    document.attachEvent('onWeixinJSBridgeReady', jsApiCall);
                }
            }else{
                jsApiCall();
            }
        }
        callpay()</script>";
        exit;
    }
    else{
        $form = $pay_obj->redirect_pay($order, $payment,'mobile');
        echo $form;
        exit;
    }
}

//去支付
elseif ( $act == "order_pay" )
{
    include_once(ROOT_PATH . 'includes/lib_transaction.php');
    include_once(ROOT_PATH . 'includes/lib_clips.php');
    $order_id = $_REQUEST['id'];
    /* 订单详情 */
    $order = get_order_detail($order_id, $_SESSION['user_id'],'mobile');
    //取得订单金额
    $total['amount_formated'] = $order['formated_order_amount'];
    $tips = '您的订单已经成功提交，为了能尽快发货，请您立即支付订单';
    $goods_list = order_goods($order_id);
    $goods_ids = array();
    foreach ($goods_list as $goods)
    {
        $goods_ids[] = $goods['goods_id'];
    }
    $goods_ids = array_unique(array_filter($goods_ids));
    //获取可用的支付方式（如果有多个物品，要取多个物品公共的）- add by qihua on 20130816
    $payment_list = available_payment_list_by_goods($goods_ids, false, 0, true);
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
    }
    $pay_online=false;
    if($order['order_amount']>0)
    {
        $pay_online=true;
    }
    $smarty->assign('payment_list', $payment_list);
    $smarty->assign('pay_online', $pay_online);
    $smarty->assign('order', $order);
    $smarty->assign('total', $total);
    $smarty->assign('tips', $tips);
    $smarty->display('order_done.html');
}

/* 免费领取会员 */
elseif ($act == 'ajax_free_members')
{
    include_once(dirname(__FILE__) . '/../includes/cls_json.php');

    $json = new JSON;
    $r['errorcode']   = 0;
    $r['msg'] = '领取成功！';
    $user_id = $_SESSION['user_id'];
    $time = gmtime();

    /*
     * 检查用户是否已经登录
     * 如果用户已经登录了则检查是否有默认的收货地址
     * 如果没有登录则跳转到登录和注册页面
     */
    if ($user_id <= 0)
    {
        $r['errorcode']   = 1;
        $r['msg'] = '用户未登录！';
        die($json->encode($r));
    }
    /*
     * 检查用户是否为会员身份
     */
    $is_members = check_user_members($user_id);
    if ($is_members)
    {
        $r['errorcode']   = 2;
        $r['msg'] = '您已经是会员，不能重复领取！';
        die($json->encode($r));
    }

    $has_get = check_free_members($user_id);
    if ($has_get)
    {
        $r['errorcode']   = 3;
        $r['msg'] = '您已经领取过会员，不能重复领取！';
        die($json->encode($r));
    }

    $members_deadline = gmtime() + (90 * 24 * 3600);

    $msql  = "UPDATE " .$GLOBALS['ecs']->table('users'). " SET is_members='1', members_deadline='$members_deadline'".
        " WHERE user_id='" . $user_id . "'";
    $result = $GLOBALS['db']->query($msql);
    if(!$result){
        $result['error'] = 4;
        $result['msg'] = '会员领取失败';
        die($json->encode($result));
    }
    else{
        $sql = "INSERT INTO " .$GLOBALS['ecs']->table('freemembers_log'). " (user_id, has_get, add_time)" .
            "VALUES ('$user_id', '1', '$time')";

        $GLOBALS['db']->query($sql);
    }

    die($json->encode($r));
}
/* 跟踪订单 */
elseif ($act == 'order_traking')
{
    if(!$_SESSION['user_id']){
//        $smarty->assign('footer', get_footer());
//        $smarty->display('login.html');
//        exit;
        ecs_header("Location: user.php?act=login\n");
        exit;
    }
    $order_id = empty($_REQUEST['order_id']) ? 0 : intval($_REQUEST['order_id']);
    $order = order_info($order_id);
    include_once(ROOT_PATH . 'includes/lib_transaction.php');
    //获取物流信息
    $postcom = get_shipping_code($order["shipping_name"]);
    $getNu = $order["invoice_no"];
//    $getNu = 229488305424;
    $url = "http://www.kuaidi100.com/query?type=$postcom&postid=$getNu";
    $json_info = array(json_decode(file_get_contents($url)));
    $shipping_info['status'] = 0;
    $shipping_info['data'] = '';
    $data = array();
    if($json_info[0]->status == 200){
        $shipping_info['status'] = 1;
        foreach($json_info[0]->data as $val){
            $arr['time'] = $val->time;
            $arr['context'] = $val->context;
            array_push($data,$arr);
        }
        $shipping_info['data'] = $data;
    }
    switch($json_info[0]->state)
    {
        case '0':
            $shipping_state="在途";
        break;
        case 1:
            $shipping_state="揽件";
        break;
        case 2:
            $shipping_state="疑难";
        break;
        case 3:
            $shipping_state="签收";
        break;
        case 4:
            $shipping_state="退签";
        break;
        case 5:
            $shipping_state="派件";
        break;
        case 6:
            $shipping_state="退回";
        break;
        default:
            $shipping_state="暂无物流信息";
    }
    $shipping['shipping_state'] = $shipping_state;
    $shipping['consignee'] = $order["consignee"];
    $shipping['shipping_name'] = $order["shipping_name"];
    $shipping['shipping_no'] = $order["invoice_no"];

    $smarty->assign('shipping',      $shipping);
    $smarty->assign('shipping_info',      $shipping_info);
    $smarty->assign('shipping_state',      $shipping_state);
    $smarty->display('order_tracking.html');
    exit;
}
/* 添加一张优惠券 */
elseif ($act == 'act_add_coupons')
{
    include_once(ROOT_PATH . 'includes/lib_transaction.php');

    $bonus_sn = $_POST['bonus_sn'];
    if (add_bonus($user_id, $bonus_sn))
    {
        show_message($_LANG['bonus_add_success'], $_LANG['back_up_page'], 'user.php?act=coupons', 'info');
    }
    else
    {
        $smarty->assign('err_or', 1);
        $error=$GLOBALS['err']->_message[0];
    }
    $smarty->assign('user_type', 6);
    $smarty->assign('error', $error);
    $smarty->display('gift_cart.html');
    exit;
}
/* 添加一个礼品卡 */
elseif ($act == 'act_add_gift')
{
    include_once(ROOT_PATH . 'includes/lib_transaction.php');

    $gift_sn = $_POST['gift_sn'];
    $gift_password = $_POST['gift_password'];
    if (add_gift($user_id, $gift_sn,$gift_password))
    {
        show_message($_LANG['add_gift_sucess'], $_LANG['back_up_page'], 'user.php?act=gift_card', 'info');
    }
    else
    {
        $smarty->assign('err_or', 1);
        $error=$GLOBALS['err']->_message[0];
    }
    $smarty->assign('user_type', 4);
    $smarty->assign('error', $error);
    $smarty->display('gift_cart.html');
    exit;
}
/* 微信绑定 */
elseif ($act == 'wx_binding')
{
    include_once(ROOT_PATH . 'includes/cls_json.php');

    $json = new JSON;
    $is_user_binding = is_user_binding($_SESSION['user_name']);
    if($is_user_binding){
        $r['errorcode']   = 4;
        $r['msg'] =  '该万集客账号已被绑定';
        die($json->encode($r));
    }

    if(empty($_SESSION['openid'])){
        $r['errorcode']   = 1;
        $r['msg'] =  '微信授权错误，绑定失败';
        die($json->encode($r));
    }

    $is_binding = is_binding($_SESSION['openid']);
    if ($is_binding)
    {
        $r['errorcode']   = 2;
        $r['msg'] =  '该微信号已绑定';
        die($json->encode($r));
    }

    $result = binding_weixin($_SESSION['openid'],$_SESSION['user_id'],$_SESSION['user_name']);
    if(!$result){
        $r['errorcode']   = 3;
        $r['msg'] = '微信绑定失败';
        die($json->encode($r));
    }
    $r['errorcode']   = 0;
    $r['msg'] = '微信绑定成功';
    die($json->encode($r));
}
/* 微信解除绑定 */
elseif ($act == 'wx_delete_binding')
{
    include_once(ROOT_PATH . 'includes/cls_json.php');

    $json = new JSON;

    if(empty($_SESSION['openid'])){
        $r['errorcode']   = 1;
        $r['msg'] =  '微信授权错误，解绑失败';
        die($json->encode($r));
    }

    $is_binding = is_binding($_SESSION['openid']);
    if (!$is_binding)
    {
        $r['errorcode']   = 2;
        $r['msg'] =  '该微信号未绑定';
        die($json->encode($r));
    }

    $result = delete_binding($_SESSION['openid']);
    if(!$result){
        $r['errorcode']   = 3;
        $r['msg'] = '微信解绑失败';
        die($json->encode($r));
    }
    $r['errorcode']   = 0;
    $r['msg'] = '微信解绑成功';
    die($json->encode($r));
}
/* 用户中心 */
else
{
	if ($_SESSION['user_id'] > 0)
	{
        include_once(ROOT_PATH . 'includes/lib_clips.php');

        $day = getdate();
        $cur_date = local_mktime(23, 59, 59, $day['mon'], $day['mday'], $day['year']);
        // 取得用户可用优惠券数
        $avl_count = $db->getOne("SELECT COUNT(*) FROM " .$ecs->table('user_bonus'). " AS u ,".
            $ecs->table('bonus_type'). " AS b".
            " WHERE u.bonus_type_id = b.type_id AND u.use_end_datetime >= $cur_date AND order_id = 0 AND u.user_id = '" .$user_id. "'");

        //取得用户会员信息
        $is_members = check_user_members($_SESSION['user_id']);
        if($is_members){
            $members_info = get_members_byuser($user_id);
            $time = local_date('Y年m月d日', $members_info['members_deadline']);
            $smarty->assign('levelname', $members_info['goods_name']);
            $smarty->assign('time',      $time);
        }
        $smarty->assign('is_members', $is_members);

        $unpay_count = $db->getOne("SELECT COUNT(*) FROM " .$ecs->table('order_info'). " WHERE user_id = {$_SESSION['user_id']} and pay_status=0 and order_status<>2 and order_status<>4");
        $deliveryd_count = $db->getOne("SELECT COUNT(*) FROM " .$ecs->table('order_info'). " WHERE user_id = {$_SESSION['user_id']} and pay_status=2 and  shipping_status<>2");

        $smarty->assign('unpay_count', $unpay_count > 99 ? 99 : $unpay_count);
        $smarty->assign('deliveryd_count', $deliveryd_count > 99 ? 99 : $deliveryd_count);
        // 取得用户可用礼品卡数
        $user_gift = user_gift($_SESSION['user_id']);
        $smarty->assign('coupons_count', $avl_count);
        $smarty->assign('gift_count', count($user_gift));

        $is_binding = is_binding($_SESSION['openid']);
        $smarty->assign('is_binding', $is_binding);
        $is_show = false;
        if(is_weixin() && !empty($_SESSION['openid'])){
            if(get_binding_user($_SESSION['openid']) == $_SESSION['user_name']){
                $is_show = true;
            }
            if(!$is_binding && !is_user_binding($_SESSION['user_name'])){
                $is_show = true;
            }
        }
        $smarty->assign('is_show', $is_show);
		show_user_center();
	}
	else
	{
//		$smarty->assign('footer', get_footer());
//        $smarty->assign('back_act', $back_act);
//		$smarty->display('login.html');
//		exit;
        ecs_header("Location: user.php?act=login\n");
        exit;
	}
}

/**
 * 用户中心显示
 */
function show_user_center()
{
//	$best_goods = get_recommend_goods('best');
//	if (count($best_goods) > 0)
//	{
//		foreach  ($best_goods as $key => $best_data)
//		{
//			$best_goods[$key]['shop_price'] = encode_output($best_data['shop_price']);
//			$best_goods[$key]['name'] = encode_output($best_data['name']);
//		}
//	}
//	//22:18 2013-7-16
	$rank_name = $GLOBALS['db']->getOne('SELECT rank_name FROM ' . $GLOBALS['ecs']->table('user_rank') . ' WHERE rank_id = '.$_SESSION['user_rank']);
	$GLOBALS['smarty']->assign('rank_name', $rank_name);
	$GLOBALS['smarty']->assign('user_info', get_user_info());
//	$GLOBALS['smarty']->assign('best_goods' , $best_goods);
	$GLOBALS['smarty']->assign('footer', get_footer());
	$GLOBALS['smarty']->assign('is_user', 1);
	$GLOBALS['smarty']->display('user.html');
}

/**
 * 手机注册
 */
function m_register($username, $password, $email, $other = array())
{
	/* 检查username */
	if (empty($username))
	{
        show_regmessage('用户名不能为空！', '', 'user.php?act=register', 'error');
		return false;
	}
	if (preg_match('/\'\/^\\s*$|^c:\\\\con\\\\con$|[%,\\*\\"\\s\\t\\<\\>\\&\'\\\\]/', $username))
	{
        show_regmessage('用户名错误！', '', 'user.php?act=register', 'error');
		return false;
	}

	/* 检查email */
//	if (empty($email))
//	{
//        show_regmessage('email不能为空！', '', 'user.php?act=register', 'error');
//		return false;
//	}
//	if(!is_email($email))
//	{
//        show_regmessage('email错误！', '', 'user.php?act=register', 'error');
//		return false;
//	}

	/* 检查是否和管理员重名 */
	if (admin_registered($username))
	{
        show_regmessage('此用户已存在！', '', 'user.php?act=register', 'error');
		return false;
	}

	if (!$GLOBALS['user']->add_user($username, $password, $email))
	{
        if ($GLOBALS['user']->error == ERR_INVALID_USERNAME)
        {
            $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['username_invalid'], $username));
        }
        elseif ($GLOBALS['user']->error == ERR_USERNAME_NOT_ALLOW)
        {
            $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['username_not_allow'], $username));
        }
        elseif ($GLOBALS['user']->error == ERR_USERNAME_EXISTS)
        {
            $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['username_exist'], $username));
        }
        elseif ($GLOBALS['user']->error == ERR_INVALID_EMAIL)
        {
            $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['email_invalid'], $email));
        }
        elseif ($GLOBALS['user']->error == ERR_EMAIL_NOT_ALLOW)
        {
            $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['email_not_allow'], $email));
        }
        elseif ($GLOBALS['user']->error == ERR_EMAIL_EXISTS)
        {
            $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['email_exist'], $email));
        }
        else
        {
            $GLOBALS['err']->add('UNKNOWN ERROR!');
        }

        show_regmessage($GLOBALS['err']->_message[0], '', 'user.php?act=register', 'error');
		//注册失败
		return false;
	}
	else
	{
		//注册成功

		/* 设置成登录状态 */
		$GLOBALS['user']->set_session($username);
		$GLOBALS['user']->set_cookie($username);

	}

		//定义other合法的变量数组
		$other_key_array = array('msn', 'qq', 'office_phone', 'home_phone', 'mobile_phone');
		$update_data['reg_time'] = local_strtotime(local_date('Y-m-d H:i:s'));
		if ($other)
		{
			foreach ($other as $key=>$val)
			{
				//删除非法key值
				if (!in_array($key, $other_key_array))
				{
					unset($other[$key]);
				}
				else
				{
					$other[$key] =  htmlspecialchars(trim($val)); //防止用户输入javascript代码
				}
			}
			$update_data = array_merge($update_data, $other);
		}
		$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('users'), $update_data, 'UPDATE', 'user_id = ' . $_SESSION['user_id']);

		update_user_info();	  // 更新用户信息

		return true;

}


function show_regmessage($content, $links = '', $hrefs = '', $type = 'info', $auto_redirect = true)
{
    assign_template();

    $msg['content'] = $content;
    if (is_array($links) && is_array($hrefs))
    {
        if (!empty($links) && count($links) == count($hrefs))
        {
            foreach($links as $key =>$val)
            {
                $msg['url_info'][$val] = $hrefs[$key];
            }
            $msg['back_url'] = $hrefs['0'];
        }
    }
    else
    {
        $link   = empty($links) ? $GLOBALS['_LANG']['back_up_page'] : $links;
        $href    = empty($hrefs) ? 'javascript:history.back()'       : $hrefs;
        $msg['url_info'][$link] = $href;
        $msg['back_url'] = $href;
    }

    $msg['type']    = $type;
    $position = assign_ur_here(0, $GLOBALS['_LANG']['sys_msg']);
    $GLOBALS['smarty']->assign('page_title', $position['title']);   // 页面标题

    $GLOBALS['smarty']->assign('auto_redirect', $auto_redirect);
    $GLOBALS['smarty']->assign('message', $msg);
    $GLOBALS['smarty']->assign('user_id', $_SESSION['user_id']);
    $GLOBALS['smarty']->assign('user_name', $_SESSION['user_name']);
    $GLOBALS['smarty']->assign('source', get_register_source());
    $GLOBALS['smarty']->display('showmsg.html');

    exit;
}
//隐藏电话号码
function hidtel($phone){
    $IsWhat = preg_match('/(0[0-9]{2,3}[-]?[2-9][0-9]{6,7}[-]?[0-9]?)/i',$phone); //固定电话
    if($IsWhat == 1){
        return preg_replace('/(0[0-9]{2,3}[-]?[2-9])[0-9]{3,4}([0-9]{3}[-]?[0-9]?)/i','$1****$2',$phone);
    }else{
        return  preg_replace('/(1[358]{1}[0-9])[0-9]{4}([0-9]{4})/i','$1****$2',$phone);
    }
}
//购物车商品数量
function cart_count()
{
    if($_SESSION['user_id'] > 0)
    {
        $sql = 'SELECT SUM(goods_number) FROM ' . $GLOBALS['ecs']->table('cart') . ' WHERE user_id='.$_SESSION['user_id'].' AND is_immediately=0';
    }
    else
    {
        $sql = 'SELECT SUM(goods_number) FROM ' . $GLOBALS['ecs']->table('cart') . ' WHERE session_id="'.SESS_ID.'" AND is_immediately=0';
    }
    $count = $GLOBALS['db']->getOne($sql);
    $GLOBALS['smarty']->assign('count', $count);
}
?>