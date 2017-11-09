<?php

/**
 * 随行支付返回页面
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');

$msg = $_REQUEST['paySts'] == 'S' ? $_LANG['pay_success'] : $_LANG['pay_fail'];

assign_template();
$position = assign_ur_here();
$smarty->assign('page_title', $position['title']);   // 页面标题
$smarty->assign('ur_here',    $position['ur_here']); // 当前位置
$smarty->assign('page_title', $position['title']);   // 页面标题
$smarty->assign('ur_here',    $position['ur_here']); // 当前位置
$smarty->assign('helps',      get_shop_help());      // 网店帮助

$smarty->assign('message',    $msg);
$smarty->assign('shop_url',   $ecs->url());

$smarty->display('respond.dwt');

?>
