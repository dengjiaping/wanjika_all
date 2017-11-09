<?php

/**
 * ECSHOP 国家馆
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

setcookie("venue", "1", time()+3600);
include_once(dirname(__FILE__) . '/includes/init.php');
include_once(ROOT_PATH . 'includes/lib_order.php');
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/common.php');

$act = isset($_REQUEST['act']) ? $_REQUEST['act'] : '';

if ($_SESSION['user_id'] > 0)
{
    $smarty->assign('user_name', $_SESSION['user_name']);
    $smarty->assign('user_id', $_SESSION['user_id']);
}

$venue_id  = empty($_REQUEST['venue_id']) ? 0 : intval($_REQUEST['venue_id']);

$sql = "SELECT * FROM " . $ecs->table('venue_manage') .
    "WHERE venue_id = '$venue_id' ";

$venue_info = $db->getRow($sql);

if(empty($venue_info))
{
    /* 如果没有找到任何记录则跳回到首页 */
    ecs_header("Location: ./\n");
    exit;
}
$sql = "SELECT * FROM " . $ecs->table('venue_floor_manage') . " WHERE venue_id = '$venue_id' AND is_show=1 order by floor_sort asc";

$venue = $db->getALL($sql);

$goods_id = array();

foreach ($venue AS $key=>$value)
{
    $floor_ids = explode(',', $value['floor_ids']);
    $goods_ids = explode(',', $value['goods_ids']);
    $floor_href = explode(',', $value['floor_href']);
    $floor_desc = explode(',', $value['floor_desc']);
    $limit = $value['floor_style'] == 1 ? 5 : 10;
    $sql = 'SELECT g.goods_id, g.goods_name, g.goods_name_style, g.market_price, g.is_new, g.is_best, g.is_hot,g.overseas_logo, g.shop_price AS org_price, ' .
        "IFNULL(mp.user_price, g.shop_price * '$_SESSION[discount]') AS shop_price, g.promote_price, " .
        'g.promote_start_date, g.promote_end_date, g.goods_brief, g.goods_thumb ,g.original_img,gc.country_name ' .
        'FROM ' . $GLOBALS['ecs']->table('goods') . ' AS g ' .
        'LEFT JOIN ' . $GLOBALS['ecs']->table('member_price') . ' AS mp ' .
        "ON mp.goods_id = g.goods_id AND mp.user_rank = '$_SESSION[user_rank]' " .
        "LEFT JOIN " . $GLOBALS['ecs']->table('goods_country') . " AS gc ON g.overseas_logo=gc.country_code ".
        "WHERE " . db_create_in($goods_ids, 'g.goods_id') . " LIMIT $limit";

    $res = $GLOBALS['db']->getALL($sql);
    $venue[$key]['goods_ids'] = $res;
    $venue[$key]['floor_desc'] = $floor_desc;
    foreach($floor_ids AS $k=>$v)
    {
        $venue[$key]['cat'][] = array('name'=>$floor_ids[$k],'href'=>$floor_href[$k]);
    }
}

$sort_goods_arr = array();

while ($row = $GLOBALS['db']->fetchRow($res))
{
    if ($row['promote_price'] > 0)
    {
        $promote_price = bargain_price($row['promote_price'], $row['promote_start_date'], $row['promote_end_date']);
        $row['promote_price'] = $promote_price > 0 ? price_format($promote_price) : '';
    }
    else
    {
        $row['promote_price'] = '';
    }

    if ($row['shop_price'] > 0)
    {
        $row['shop_price'] =  price_format($row['shop_price']);
    }
    else
    {
        $row['shop_price'] = '';
    }

    $row['url']              = build_uri('goods', array('gid'=>$row['goods_id']), $row['goods_name']);
    $row['goods_style_name'] = add_style($row['goods_name'], $row['goods_name_style']);
    $row['short_name']       = $GLOBALS['_CFG']['goods_name_length'] > 0 ?
        sub_str($row['goods_name'], $GLOBALS['_CFG']['goods_name_length']) : $row['goods_name'];
    $row['goods_thumb']      = get_image_path($row['goods_id'], $row['goods_thumb'], true);
    $row['short_style_name'] = add_style($row['short_name'], $row['goods_name_style']);

    foreach ($arr AS $key => $value)
    {
        foreach ($value AS $val)
        {
            if ($val == $row['goods_id'])
            {
                $key = $key == 'default' ? $_LANG['all_goods'] : $key;
                $sort_goods_arr[$key][] = $row;
            }
        }
    }
}
/* 模板赋值 */
assign_template();
$position = assign_ur_here();
switch ($venue_info['venue_name'])
{
    case '亚洲馆':
        $page_title = $GLOBALS['_LANG']['title_venue_asia'];
        $keywords = $GLOBALS['_LANG']['keywords_venue_asia'];
        $description = $GLOBALS['_LANG']['description_venue_asia'];
        break;

    case '欧洲馆' :
        $page_title = $GLOBALS['_LANG']['title_venue_euro'];
        $keywords = $GLOBALS['_LANG']['keywords_venue_euro'];
        $description = $GLOBALS['_LANG']['description_venue_euro'];
        break;

    case '澳新馆' :
        $page_title = $GLOBALS['_LANG']['title_venue_aus'];
        $keywords = $GLOBALS['_LANG']['keywords_venue_aus'];
        $description = $GLOBALS['_LANG']['description_venue_aus'];
        break;

    case '北美馆' :
        $page_title = $GLOBALS['_LANG']['title_venue_na'];
        $keywords = $GLOBALS['_LANG']['keywords_venue_na'];
        $description = $GLOBALS['_LANG']['description_venue_na'];
        break;
    default :
        $page_title = $position['title'];
        $keywords = $venue_info['venue_name'];
        $description = $venue_info['venue_name'];
        break;
}
$smarty->assign('venue_name',       $venue_info['venue_name']);
$smarty->assign('data',             $venue_id.'_data');          // js文件名
$smarty->assign('flash_theme',      $_CFG['flash_theme']);  // Flash轮播图片模板
$smarty->assign('venue',            $venue);                   // 专题信息
$smarty->assign('page_title',       $page_title);       // 页面标题
$smarty->assign('keywords',         $keywords);       // 专题信息
$smarty->assign('description',      $description);    // 专题信息
$smarty->assign('footer', get_footer());
$smarty->display('venue.html');
?>