<?php

/**
 * ECSHOP 会员
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

setcookie("vip", "1", time()+3600);
include_once(dirname(__FILE__) . '/includes/init.php');
include_once(ROOT_PATH . 'includes/lib_order.php');

$act = isset($_REQUEST['act']) ? $_REQUEST['act'] : '';
//$cp = isset($_REQUEST['cp']) ? $_REQUEST['cp'] : '';
if ($_SESSION['user_id'] > 0)
{
	$smarty->assign('user_name', $_SESSION['user_name']);
}
if($act == 'vip_info')
{
    $usermembers_39 = get_all_user_members(39);
    $members_flag_39 = false;
    if(!empty($usermembers_39)){
        $members_flag_39 = true;
        $smarty->assign('goods_id_three',$usermembers_39['goods_id']);
    }
    $usermembers_69 = get_all_user_members(69);
    $members_flag_69 = false;
    if(!empty($usermembers_69)){
        $members_flag_69 = true;
        $smarty->assign('goods_id_four',$usermembers_69['goods_id']);
    }
    $usermembers_99 = get_all_user_members(99);
    $members_flag_99 = false;
    if(!empty($usermembers_99)){
        $members_flag_99 = true;
        $smarty->assign('goods_id_one',$usermembers_99['goods_id']);
    }
    $usermembers_199 = get_all_user_members(199);
    $members_flag_199 = false;
    if(!empty($usermembers_199)){
        $members_flag_199 = true;
        $smarty->assign('goods_id_two',$usermembers_199['goods_id']);
    }
    $has_get = true;
    $is_login = false;
    $user_id = $_SESSION['user_id'];
    if($user_id > 0){
        $is_login = true;
        $has_get = check_free_members($user_id);
    }
    $smarty->assign('is_login',$is_login);
    $smarty->assign('has_get',$has_get);
    $smarty->assign('members_flag_39',$members_flag_39);
    $smarty->assign('members_flag_69',$members_flag_69);
    $smarty->assign('members_flag_99',$members_flag_99);
    $smarty->assign('members_flag_199',$members_flag_199);
    $smarty->assign('info',true);
}

$smarty->assign('footer', get_footer());
$smarty->display('vip.html');
?>