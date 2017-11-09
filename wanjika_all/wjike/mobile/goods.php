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
 * $Id: goods.php 15013 2008-10-23 09:31:42Z testyang $
*/

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
/* 载入语言文件 */
require(ROOT_PATH . 'languages/' . $_CFG['lang'] . '/common.php');
$smarty->assign('lang', $_LANG);
cart_count();
$goods_id = !empty($_GET['id']) ? intval($_GET['id']) : '';
$act = !empty($_GET['act']) ? $_GET['act'] : '';
/*------------------------------------------------------ */
//-- 改变属性、数量时重新计算商品价格
/*------------------------------------------------------ */


if ($_SESSION['user_id'] > 0)
{
    $smarty->assign('user_name', $_SESSION['user_name']);
    $smarty->assign('user_id', $_SESSION['user_id']);
}
if (!empty($_REQUEST['act']) && $_REQUEST['act'] == 'price')
{
    include('includes/cls_json.php');

    $json   = new JSON;
    $res    = array('err_msg' => '', 'result' => '', 'qty' => 1);

    $attr_id    = isset($_REQUEST['attr']) ? explode(',', $_REQUEST['attr']) : array();
    $number     = (isset($_REQUEST['number'])) ? intval($_REQUEST['number']) : 1;

    if ($goods_id == 0)
    {
        $res['err_msg'] = "没有找到指定的商品或者没有找到指定的商品属性。";
        $res['err_no']  = 1;
    }
    else
    {
        if ($number == 0)
        {
            $res['qty'] = $number = 1;
        }
        else
        {
            $res['qty'] = $number;
        }

        $shop_price  = get_final_price($goods_id, $number, true, $attr_id);
        $res['result'] = price_format($shop_price * $number);
    }

    die($json->encode($res));
}

$_LANG['kilogram'] = '千克';
$_LANG['gram'] = '克';
$_LANG['home'] = '首页';
$_LANG['goods_attr'] = '';
$smarty->assign('goods_id', $goods_id);
$goods_info = get_goods_info($goods_id);
if ($goods_info === false)
{
   /* 如果没有找到任何记录则跳回到首页 */
   ecs_header("Location: ./\n");
   exit;
}
/* 取得用户等级 */
$user_rank_list = array();
$user_rank_list[0] = $_LANG['not_user'];
$sql = "SELECT rank_id, rank_name FROM " . $ecs->table('user_rank');
$res = $db->query($sql);
while ($row = $db->fetchRow($res))
{
    $user_rank_list[$row['rank_id']] = $row['rank_name'];
}
// 开始工作
$user_rank = ',' . $user_rank . ',';
$now = gmtime();
$sql = "SELECT * FROM " . $ecs->table('favourable_activity'). " where start_time <= '$now' AND end_time >= '$now' ORDER BY `sort_order` ASC,`end_time` DESC";
$res = $db->query($sql);
$list = array();
while ($row = $db->fetchRow($res))
{
    $row['start_time']  = local_date('Y-m-d H:i', $row['start_time']);
    $row['end_time']    = local_date('Y-m-d H:i', $row['end_time']);
    //享受优惠会员等级
    $user_rank = explode(',', $row['user_rank']);
    $row['user_rank'] = array();
    $rw['title1']="";
    $rw['title2']="";
    foreach($user_rank as $val)
    {
        if (isset($user_rank_list[$val]))
        {
            $row['user_rank'][] = $user_rank_list[$val];
        }
    }
    //优惠方式
    switch($row['act_type'])
    {
        case 0://赠品
            $row['act_type'] = $_LANG['fat_goods'];
            $row['gift'] = unserialize($row['gift']);
            $row['mobile_act_link'] = $row['mobile_act_link'];
            $row['title1']="满".$row['min_amount']."送以下赠品";
            if(is_array($row['gift']))
            {
                foreach($row['gift'] as $k=>$v)
                {
                    $row['gift'][$k]['thumb'] = get_image_path($v['id'], $db->getOne("SELECT goods_thumb FROM " . $ecs->table('goods') . " WHERE goods_id = '" . $v['id'] . "'"), true);
                }
            }
            break;
        case 1://减现金
            $row['act_type'] = $_LANG['fat_price'];
            // $row['act_type_ext'] .= $_LANG['unit_yuan'];
            $row['act_type_ext'] = floor($row['act_type_ext']);
            $row['min_amount'] = floor($row['min_amount']);
            $row['mobile_act_link'] = $row['mobile_act_link'];
            $row['gift'] = array();
            $row['title1']="满".$row['min_amount']."减".$row['act_type_ext'];
            break;
        case 2://折扣
            $row['act_type'] = $_LANG['fat_discount'];
            $row['act_type_ext'] = floor($row['act_type_ext'])."%";
            $row['min_amount'] = floor($row['min_amount']);
            $row['mobile_act_link'] = $row['mobile_act_link'];
            $row['gift'] = array();
            $row['title1']="满".$row['min_amount']."享折扣".$row['act_type_ext'];
            break;
    }


    if($row['act_range']==0)//全部
    {
        $row['title1']="全场".$row['title1'];
        $list[] = $row;
    }
    elseif($row['act_range']==1)//以下分类
    {
        $total_amount = 0;
        $id_list = array();
        $act_range_ext=$row["act_range_ext"];
        $str1=explode(',',$act_range_ext);
        foreach($str1 as $k)
        {
            $id_list = array_merge($id_list, array_keys(cat_list($k, 0, false)));
        }

        $ids = join(',', array_unique($id_list));
        if (strpos(',' . $ids . ',', ',' . $goods_info['cat_id'] . ',') !== false)
        {
            $list[] = $row;
        }

    }
    elseif($row['act_range']==2)//以下品牌
    {
        $act_range_ext=$row["act_range_ext"];
        $str1=explode(',',$act_range_ext);
        foreach($str1 as $k)
        {
            if($k==$goods_info['brand_id'])
            {
                $list[] = $row;
            }
        }
    }
    else{//商品ID
        $act_range_ext=$row["act_range_ext"];
        $str1=explode(',',$act_range_ext);
        foreach($str1 as $k)
        {
            if($k==$goods_id)
            {
                $list[] = $row;
            }
        }
    }
}

$smarty->assign('list',             $list);
$goods_info['goods_name'] = encode_output($goods_info['goods_name']);
$goods_info['goods_brief'] = encode_output($goods_info['goods_brief']);
$goods_info['promote_price'] = encode_output($goods_info['promote_price']);
$goods_info['market_price'] = encode_output($goods_info['market_price']);
$goods_info['shop_price'] = encode_output($goods_info['shop_price']);
$goods_info['shop_price_formated'] = encode_output($goods_info['shop_price_formated']);
$goods_info['goods_number'] = encode_output($goods_info['goods_number']);
//广告投放参数
$goods_info['cate_name'] = get_cat_name($goods_info['cat_id']);
$goods_info['ad_goods_img'] = 'http://www.wjike.com/'.$goods_info['original_img'];

$country_array = country_list();
foreach($country_array AS $key=>$value)
{
    if($goods_info['overseas_logo'] == $value['country_code'])
    {
        $goods_info['overseas_logo_name'] = $value['country_name'];
    }
}
$smarty->assign('goods_info', $goods_info);

$pack = get_package_goods_list($goods_info['goods_id']);
if(count($pack) > 0)
{
    $smarty->assign('is_pack', true);
}
$shop_price   = $goods_info['shop_price'];
$smarty->assign('rank_prices',		 get_user_rank_prices($goods_id, $shop_price));	// 会员等级价格
$smarty->assign('promote_end_time',   $goods_info['gmt_end_time']);
$smarty->assign('footer', get_footer());

/* 查看商品图片操作 */
if ($act == 'view_img')
{
    if($_REQUEST['package_goods'] == 1)
    {
        //获取关联礼包
        $package_goods_list = get_package_goods_list($goods_info['goods_id']);
        $smarty->assign('package_goods_list',$package_goods_list);    // 获取关联礼包
    }
    if($_REQUEST['package_detail'] > 0)
    {
        $package_goods_list = get_package_goods($_REQUEST['package_detail']);
        $smarty->assign('act_name',$package_goods_list[0]['act_name']);
        $smarty->assign('pack_price',$package_goods_list[0]['price']);
        $smarty->assign('package_goods_list',$package_goods_list);    // 获得指定礼包的商品
        $smarty->assign('package_detail' , true);
        $smarty->assign('package_id' , $_REQUEST['package_detail']);
    }
	$smarty->assign('goods_desc' , $goods_info['goods_desc']);
	$smarty->display('goods_img.html');
	exit();
}

/* 检查是否有商品品牌 */
if (!empty($goods_info['brand_id']))
{
	$brand_name = $db->getOne("SELECT brand_name FROM " . $ecs->table('brand') . " WHERE brand_id={$goods_info['brand_id']}");
	$smarty->assign('brand_name', encode_output($brand_name));
}
/* 显示分类名称 */
$cat_array = get_parent_cats($goods_info['cat_id']);
krsort($cat_array);
$cat_str = '';
foreach ($cat_array as $key => $cat_data)
{
	$cat_array[$key]['cat_name'] = encode_output($cat_data['cat_name']);
	$cat_str .= "<a href='category.php?c_id={$cat_data['cat_id']}'>" . encode_output($cat_data['cat_name']) . "</a>-&gt;";
}
$smarty->assign('cat_array', $cat_array);


$properties = get_goods_properties($goods_id);  // 获得商品的规格和属性
$smarty->assign('specification',	   $properties['spe']);  // 商品规格


$comment = assign_comment($goods_id, 0);
$smarty->assign('comment', $comment);

$goods_gallery = get_goods_gallery($goods_id);
$smarty->assign('picturesnum', count($goods_gallery));// 相册数
$smarty->assign('pictures', $goods_gallery);// 商品相册
$smarty->assign('now_time',  gmtime()); // 当前系统时间
$smarty->display('goods.html');

/**
 * 获得指定商品的各会员等级对应的价格
 *
 * @access  public
 * @param   integer	 $goods_id
 * @return  array
 */
function get_user_rank_prices($goods_id, $shop_price)
{
	$sql = "SELECT rank_id, IFNULL(mp.user_price, r.discount * $shop_price / 100) AS price, r.rank_name, r.discount " .
			'FROM ' . $GLOBALS['ecs']->table('user_rank') . ' AS r ' .
			'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . " AS mp ".
				"ON mp.goods_id = '$goods_id' AND mp.user_rank = r.rank_id " .
			"WHERE r.show_price = 1 OR r.rank_id = '$_SESSION[user_rank]'";
	$res = $GLOBALS['db']->query($sql);

	$arr = array();
	while ($row = $GLOBALS['db']->fetchRow($res))
	{

		$arr[$row['rank_id']] = array(
						'rank_name' => htmlspecialchars($row['rank_name']),
						'price'	 => price_format($row['price']));
	}

	return $arr;
}

/**
 * 获得分类的名称
 *
 * @param   integer $cat_id
 *
 * @return  void
 */
function get_cat_name($cat_id)
{
    return $GLOBALS['db']->getOne('SELECT cat_name FROM ' . $GLOBALS['ecs']->table('category') .
        " WHERE cat_id = '$cat_id'");
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

/**
 * 取得跟商品关联的礼包列表
 *
 * @param   string  $goods_id    商品编号
 *
 * @return  礼包列表
 */
function get_package_goods_list($goods_id)
{
    $now = gmtime();
    $sql = "SELECT pg.goods_id, ga.act_id, ga.act_name, ga.act_desc, ga.goods_name, ga.start_time,
                   ga.end_time, ga.is_finished, ga.ext_info
            FROM " . $GLOBALS['ecs']->table('goods_activity') . " AS ga, " . $GLOBALS['ecs']->table('package_goods') . " AS pg
            WHERE pg.package_id = ga.act_id
            AND ga.start_time <= '" . $now . "'
            AND ga.end_time >= '" . $now . "'
            AND pg.goods_id = " . $goods_id . "
            GROUP BY ga.act_id
            ORDER BY ga.act_id ";
    $res = $GLOBALS['db']->getAll($sql);

    foreach ($res as $tempkey => $value)
    {
        $subtotal = 0;
        $row = unserialize($value['ext_info']);
        unset($value['ext_info']);
        if ($row)
        {
            foreach ($row as $key=>$val)
            {
                $res[$tempkey][$key] = $val;
            }
        }

        $sql = "SELECT pg.package_id, pg.goods_id, pg.goods_number, pg.admin_id, p.goods_attr, g.goods_sn, g.goods_name, g.market_price, g.goods_thumb, IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS rank_price
                FROM " . $GLOBALS['ecs']->table('package_goods') . " AS pg
                    LEFT JOIN ". $GLOBALS['ecs']->table('goods') . " AS g
                        ON g.goods_id = pg.goods_id
                    LEFT JOIN ". $GLOBALS['ecs']->table('products') . " AS p
                        ON p.product_id = pg.product_id
                    LEFT JOIN " . $GLOBALS['ecs']->table('member_price') . " AS mp
                        ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]'
                WHERE pg.package_id = " . $value['act_id']. "
                ORDER BY pg.package_id, pg.goods_id";

        $goods_res = $GLOBALS['db']->getAll($sql);

        foreach($goods_res as $key => $val)
        {
            $goods_id_array[] = $val['goods_id'];
            $goods_res[$key]['goods_thumb']  = get_image_path($val['goods_id'], $val['goods_thumb'], true);
            $goods_res[$key]['market_price'] = price_format($val['market_price']);
            $goods_res[$key]['rank_price']   = price_format($val['rank_price']);
            $subtotal += $val['rank_price'] * $val['goods_number'];
        }

        /* 取商品属性 */
        $sql = "SELECT ga.goods_attr_id, ga.attr_value
                FROM " .$GLOBALS['ecs']->table('goods_attr'). " AS ga, " .$GLOBALS['ecs']->table('attribute'). " AS a
                WHERE a.attr_id = ga.attr_id
                AND a.attr_type = 1
                AND " . db_create_in($goods_id_array, 'goods_id');
        $result_goods_attr = $GLOBALS['db']->getAll($sql);

        $_goods_attr = array();
        foreach ($result_goods_attr as $value)
        {
            $_goods_attr[$value['goods_attr_id']] = $value['attr_value'];
        }

        /* 处理货品 */
        $format = '[%s]';
        foreach($goods_res as $key => $val)
        {
            if ($val['goods_attr'] != '')
            {
                $goods_attr_array = explode('|', $val['goods_attr']);

                $goods_attr = array();
                foreach ($goods_attr_array as $_attr)
                {
                    $goods_attr[] = $_goods_attr[$_attr];
                }

                $goods_res[$key]['goods_attr_str'] = sprintf($format, implode('，', $goods_attr));
            }
        }

        $res[$tempkey]['goods_list']    = $goods_res;
        $res[$tempkey]['subtotal']      = price_format($subtotal);
        $res[$tempkey]['saving']        = price_format(($subtotal - $res[$tempkey]['package_price']));
        $res[$tempkey]['package_price'] = price_format($res[$tempkey]['package_price']);
    }

    return $res;
}
?>