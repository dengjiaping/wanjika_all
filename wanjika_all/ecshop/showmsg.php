<?php

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');

if(isset($_REQUEST['paytype']) && $_REQUEST['paytype'] == 'yizhifu'){
    $order_sn = empty($_REQUEST['ORDERSEQ']) ? '' : trim($_REQUEST['ORDERSEQ']);
    $order_req = empty($_REQUEST['ORDERREQTRANSEQ']) ? '' : trim($_REQUEST['ORDERREQTRANSEQ']);
    $amount = empty($_REQUEST['ORDERAMOUNT']) ? '' : trim($_REQUEST['ORDERAMOUNT']);
    $addtime = empty($_REQUEST['ORDERREQTIME']) ? '' : trim($_REQUEST['ORDERREQTIME']);
    $goods_name = empty($_REQUEST['GOODSNAME']) ? '' : trim($_REQUEST['GOODSNAME']);
    $goods_number = empty($_REQUEST['GOODSNUM']) ? '' : trim($_REQUEST['GOODSNUM']);

    assign_template();
    $position = assign_ur_here(0, '电信翼支付');

    $smarty->assign('page_title', $position['title']);    // 页面标题
    $smarty->assign('ur_here',    $position['ur_here']);  // 当前位置
    $smarty->assign('categories', get_categories_tree()); // 分类树
    $smarty->assign('helps',      get_shop_help());       // 网店帮助
    $smarty->assign('order_sn', $order_sn);
    $smarty->assign('order_req', $order_req);
    $smarty->assign('amount', $amount);
    $smarty->assign('addtime', $addtime);
    $smarty->assign('goods_name', $goods_name);
    $smarty->assign('goods_number', $goods_number);
    $smarty->display('telecom.dwt');
}
else{
    $content = '您目前的支付环境存在较高风险，如有疑问请致电：4000851115。';
    show_message($content);
}