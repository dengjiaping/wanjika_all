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

setcookie("mobileact", "1", time()+3600);
include_once(dirname(__FILE__) . '/includes/init.php');
include_once(ROOT_PATH . 'includes/lib_order.php');

$act = isset($_REQUEST['act']) ? $_REQUEST['act'] : '';
//$cp = isset($_REQUEST['cp']) ? $_REQUEST['cp'] : '';

$smarty->assign('footer', get_footer());
if($act==iphone)
{
    $smarty->display('iphone.html');
}
else
{
    $smarty->display('japan.html');
}
?>