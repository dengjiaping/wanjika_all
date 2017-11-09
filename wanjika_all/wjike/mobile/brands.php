<?php

/**
 * ECSHOP 品牌专区
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: testyang $
 * $Id: brands.php 15013 2008-10-23 09:31:42Z testyang $
*/

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');

if ($_SESSION['user_id'] > 0)
{
    $smarty->assign('user_name', $_SESSION['user_name']);
    $smarty->assign('user_id', $_SESSION['user_id']);
}
$b_id = !empty($_GET['b_id']) ? intval($_GET['b_id']) : 0;
$sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('brand') . " WHERE brand_id = '$b_id'";

$brand_info = $GLOBALS['db']->getRow($sql);
if (empty($brand_info))
{
    ecs_header("Location: ./\n");
    exit;
}
$country_array = country_list();
foreach($country_array AS $key=>$value)
{
    if($brand_info['overseas_logo']  == $value['country_code'])
    {
        $brand_info['overseas_logo_name'] = $value['country_name'];
    }
}
if ($b_id > 0)
{
	if (empty($_GET['order_price']))
	{
		$order_rule = ' ORDER BY g.shop_price ASC ';
	}
    elseif($_GET['order_price'] == 2)
    {
        $order_rule = ' ORDER BY g.shop_price ASC ';
    }
	else
	{
		$order_rule = ' ORDER BY g.shop_price DESC ';
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
    if($order_rule == ' ORDER BY g.shop_price ASC ' && empty($_GET['order_price']))
    {
        $smarty->assign('last_update', 1);
    }
	$brands_array = assign_brand_goods($b_id, 0, 0, $order_rule);
	$brands_array['brand']['name'] = encode_output($brands_array['brand']['name']);
	$smarty->assign('brands_array' , $brands_array);
	$num = count($brands_array['goods']);
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
		foreach ($brands_array['goods'] as $goods_data)
		{
			if (($i > ($page_num * ($page - 1 ))) && ($i <= ($page_num * $page)))
			{
				$price = empty($goods_info['promote_price_org']) ? $goods_data['shop_price'] : $goods_data['promote_price'];
				//$wml_data .= "<a href='goods.php?id={$goods_data['id']}'>".encode_output($goods_data['name'])."</a>[".encode_output($price)."]<br/>";
				$data[] = array('i' => $i , 'price' => encode_output($price) , 'id' => $goods_data['id'] ,'goods_img' => $goods_data['goods_img'],'is_overseas' => $goods_data['is_overseas'],'overseas_logo' => $goods_data['overseas_logo'],'overseas_logo_name' => $goods_data['overseas_logo_name'] , 'name' => encode_output($goods_data['name']));
			}
			$i++;
		}
		$smarty->assign('goods_data', $data);
		$pagebar = get_wap_pager($num, $page_num, $page, 'brands.php?b_id=' . $b_id.'&order_price='.(empty($order_price)?0:$order_price), 'page');
		$smarty->assign('pagebar', $pagebar);
	}
}

$brands_array = get_brands();
if (count($brands_array) > 1)
{
	foreach ($brands_array as $key => $brands_data)
	{
		   $brands_array[$key]['brand_name'] =  encode_output($brands_data['brand_name']);
	}
	$smarty->assign('brand_id', $b_id);
	$smarty->assign('other_brands', $brands_array);
}

$smarty->assign('brand',           $brand_info);
$smarty->assign('footer', get_footer());
$smarty->display('brands.html');

?>