<?php

/**
 * ECSHOP 商品分类页
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: testyang $
 * $Id: category.php 15013 2008-10-23 09:31:42Z testyang $
*/

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
cart_count();
$c_id = !empty($_GET['c_id']) ? intval($_GET['c_id']) : 0;
$cat_id = !empty($_GET['cat_id']) ? intval($_GET['cat_id']) : 0;
if($_REQUEST['act'] =='page')
{
    include('includes/cls_json.php');
    $c_id=$_REQUEST['cid'];
    $result = array('error' => 0, 'message' => '', 'content' => '', 'goods_id' => '');
    $json = new JSON;
    $cat_array = get_categories_tree($c_id);
//    $smarty->assign('c_id', $c_id);
    $cat_name = $db->getOne('SELECT cat_name FROM ' . $ecs->table('category') . ' WHERE cat_id=' . $c_id);
    $smarty->assign('cat_name', encode_output($cat_name));
    if (!empty($cat_array[$c_id]['cat_id']))
    {
        foreach ($cat_array[$c_id]['cat_id'] as $key => $child_data)
        {
            $cat_array[$c_id]['cat_id'][$key]['name'] = encode_output($child_data['name']);
        }
        $smarty->assign('cat_children', $cat_array[$c_id]['cat_id']);
    }

    if (empty($_POST['order_price']))
    {
        $order_rule = 'ORDER BY g.shop_price ASC, g.sort_order';
    }
    else
    {
        $order_rule = 'ORDER BY g.shop_price DESC, g.sort_order';
    }

    $cat_goods = assign_cat_goods($c_id, 0, 'wap', $order_rule);
    $num = count($cat_goods['goods']);
    if ($num > 0)
    {
        $page_num = '10';
        $page = !empty($_POST['page']) ? intval($_POST['page']) : 1;
        $pages = ceil($num / $page_num);
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
        $i = 1;
        foreach ($cat_goods['goods'] as $goods_data)
        {
            if (($i > ($page_num * ($page - 1 ))) && ($i <= ($page_num * $page)))
            {
                $price = empty($goods_data['promote_price_org']) ? $goods_data['shop_price'] : $goods_data['promote_price'];
                //$wml_data .= "<a href='goods.php?id={$goods_data['id']}'>".encode_output($goods_data['name'])."</a>[".encode_output($price)."]<br/>";
                $data[] = array('i' => $i , 'price' => encode_output($price) , 'goods_id' => $goods_data['id'], 'id' => $goods_data['id'] , 'name' => encode_output($goods_data['name']), 'thumb' => $goods_data['thumb'],'market_price'=>$goods_data['market_price'],'goods_discount'=>$goods_data['goods_discount'],'is_overseas'=>$goods_data['is_overseas'],'overseas_logo'=>$goods_data['overseas_logo'],'overseas_logo_name'=>$goods_data['overseas_logo_name'],'promote_pri'=>$goods_data['promote_price_org']);//16:41 2013-07-16
            }
            $i++;
        }
//        $smarty->assign('goods_data', $data);
        $pagebar = get_wap_pager($num, $page_num, $page, 'category.php?c_id='.$c_id.'&order_price='.(empty($order_price)?0:$order_price), 'page');
        $pages1 = ceil($num / $page_num);
        if($page > $pages1)
        {
            $result['error']=1;
        }
//        $smarty->assign('pagebar', $pagebar);
    }

    $result['content'] = $data;
    die($json->encode($result));exit;
}
if ($c_id <= 0 || $cat_id>0)
{
	$pcat_array = get_categories_tree();
	foreach ($pcat_array as $key => $pcat_data)
	{
		$pcat_array[$key]['name'] = encode_output($pcat_data['name']);
		if ($pcat_data['cat_id'])
		{
			foreach ($pcat_data['cat_id'] as $k => $v)
			{
				$pcat_array[$key]['cat_id'][$k]['name'] = encode_output($v['name']);
			}
		}
	}
    if($cat_id > 0)
    {
        $f_id = $cat_id;
    }
    else
    {
        reset($pcat_array);
        $first = current($pcat_array);
        $f_id = $first['id'];
    }
	$smarty->assign('cat_array' , $pcat_array);
	$smarty->assign('cat_arr' , $pcat_array[$f_id]['cat_id']);
	$smarty->assign('f_id' , $f_id);
	$smarty->assign('all_cat' , 1);
}
else
{
	$cat_array = get_categories_tree($c_id);
	$smarty->assign('c_id', $c_id);
	$cat_name = $db->getOne('SELECT cat_name FROM ' . $ecs->table('category') . ' WHERE cat_id=' . $c_id);
	$smarty->assign('cat_name', encode_output($cat_name));
	if (!empty($cat_array[$c_id]['cat_id']))
	{
		foreach ($cat_array[$c_id]['cat_id'] as $key => $child_data)
		{
			$cat_array[$c_id]['cat_id'][$key]['name'] = encode_output($child_data['name']);
		}
		$smarty->assign('cat_children', $cat_array[$c_id]['cat_id']);
	}

	if (empty($_GET['order_price']))
	{
        $order_rule = ' ORDER BY g.last_update DESC';
	}
	else
	{
        if($_GET['order_price'] == 2)
        {
            $order_rule = 'ORDER BY g.shop_price ASC, g.sort_order';
        }
        else
        {
            $order_rule = 'ORDER BY g.shop_price DESC, g.sort_order';
        }
	}
    if (!empty($_GET['new_goods']))
    {
        $order_rule = ' ORDER BY g.add_time DESC';
    }
    if (!empty($_GET['last']))
    {
        $order_rule = ' ORDER BY g.last_update DESC';
    }
    if (!empty($_GET['salesnum']))
    {
        $order_rule = ' ORDER BY g.salesnum DESC';
    }
    if($order_rule == ' ORDER BY g.last_update DESC')
    {
        $smarty->assign('last_update', 1);
    }
	$cat_goods = assign_cat_goods($c_id, 0, 'wap', $order_rule);
	$num = count($cat_goods['goods']);
	if ($num > 0)
	{
		$page_num = '10';
		$page = !empty($_GET['page']) ? intval($_GET['page']) : 1;
		$pages = ceil($num / $page_num);
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
		$i = 1;
		foreach ($cat_goods['goods'] as $goods_data)
		{
			if (($i > ($page_num * ($page - 1 ))) && ($i <= ($page_num * $page)))
			{
				$price = empty($goods_data['promote_price_org']) ? $goods_data['shop_price'] : $goods_data['promote_price'];
				//$wml_data .= "<a href='goods.php?id={$goods_data['id']}'>".encode_output($goods_data['name'])."</a>[".encode_output($price)."]<br/>";
				$data[] = array('i' => $i , 'price' => encode_output($price) , 'id' => $goods_data['id'] , 'name' => encode_output($goods_data['name']), 'thumb' => $goods_data['thumb'],'market_price'=>$goods_data['market_price'],'goods_discount'=>$goods_data['goods_discount'],'is_overseas'=>$goods_data['is_overseas'],'overseas_logo'=>$goods_data['overseas_logo'],'overseas_logo_name'=>$goods_data['overseas_logo_name'],'promote_pri'=>$goods_data['promote_price_org']);//16:41 2013-07-16
			}
			$i++;
		}
		$smarty->assign('goods_data', $data);
		$pagebar = get_wap_pager($num, $page_num, $page, 'category.php?c_id='.$c_id.'&order_price='.(empty($order_price)?0:$order_price), 'page');
		$smarty->assign('pagebar', $pagebar);
        $pages1 = ceil($num / $page_num);
        if(($page++) >= $pages1)
        {
            $smarty->assign('slidedown',1);
        }
		$smarty->assign('c_id', $c_id);
		$smarty->assign('pages1', $pages1);
	}

	$pcat_array = get_parent_cats($c_id);
	if (!empty($pcat_array[1]['cat_name']))
	{
		$pcat_array[1]['cat_name'] = encode_output($pcat_array[1]['cat_name']);
		$smarty->assign('pcat_array', $pcat_array[1]);
	}

	$smarty->assign('cat_array', $cat_array);
}

if ($_SESSION['user_id'] > 0)
{
    $smarty->assign('user_name', $_SESSION['user_name']);
    $smarty->assign('user_id', $_SESSION['user_id']);
}
$smarty->assign('footer', get_footer());
$smarty->display('category.html');
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