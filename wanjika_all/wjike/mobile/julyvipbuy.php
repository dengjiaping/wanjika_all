<?php

/**
 * 领取红包页面
 *
 * @author qihua
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
if ((DEBUG_MODE & 2) != 2)
{
    $smarty->caching = true;
}
if ($_SESSION['user_id'] > 0)
{
    $smarty->assign('user_name', $_SESSION['user_name']);
}
$smarty->display('julyvipbuy.html');

?>