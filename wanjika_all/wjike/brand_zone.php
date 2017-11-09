<?php

/**
 * ECSHOP 品牌专区前台
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * @author:     webboy <laupeng@163.com>
 * @version:    v2.1
 * ---------------------------------------------
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');

if ((DEBUG_MODE & 2) != 2)
{
    $smarty->caching = true;
}
$sql = "SELECT * FROM " . $ecs->table('brand_zone') . " WHERE floor_level=0";

$zone_info = $db->getRow($sql);
if(empty($zone_info))
{
    /* 如果没有找到任何记录则跳回到首页 */
    ecs_header("Location: ./\n");
    exit;
}

$templates = 'brand_zone.dwt';

$cache_id = sprintf('%X', crc32($_SESSION['user_rank'] . '-' . $_CFG['lang'] . '-' . $zone_info['brand_id']));

if (!$smarty->is_cached($templates, $cache_id))
{
    $sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('brand_zone');
    $zone = $GLOBALS['db']->getAll($sql);

    $goods_id = array();

    $sql1 = 'SELECT floor_name FROM ' . $GLOBALS['ecs']->table('brand_zone') . " WHERE floor_level=3";
    $goods_ids = $GLOBALS['db']->getAll($sql1);
    foreach($goods_ids AS $value)
    {
        $goods_id[] = $value['floor_name'];
    }
    $sql = 'SELECT * ' .
        'FROM ' . $GLOBALS['ecs']->table('brand') . ' AS b ' .
        "WHERE " . db_create_in($goods_id, 'b.brand_id');
    $res = $GLOBALS['db']->getAll($sql);
    $arr = array();
    foreach($zone AS $key=>$value)
    {
        if($value['floor_level'] == 0)
        {
            $value['floor_name'] = explode(',', $value['floor_name']);
            $arr[0] = $value;
        }
        elseif($value['floor_level'] == 1)
        {
            $arr[1][] = $value;
        }
        elseif($value['floor_level'] == 2)
        {
            $arr[2][] = $value;
        }
        else
        {
            $arr[3][] = $value;
        }
    }
    $arr[3] = array_sort($arr[3],'floor_sort',"asc");
    foreach($arr[2] AS $key=>$value)
    {
        foreach($arr[3] AS $k=>$v)
        {
            if($v['zone_id'] == $value['brand_id'])
            {
                foreach($res AS $r)
                {
                    if($v['floor_name'] == $r['brand_id'])
                    {
                        $v['brand_name'] =$r['brand_name'];
                        $v['brand_logo'] =$r['brand_logo'];
                        $v['brand_desc_img'] =$r['brand_desc_img'];
                        $v['url']  = 'http://www.wjike.com/b'.$r['brand_id'].'.html';
                        if(is_local()){
                            $v['url']  = build_uri('brand', array('bid' => $r['brand_id']));
                        }
                    }
                }
                $arr[2][$key]["cat"][] = $v;
            }
        }
        $arr[2][$key]["count"] = count($arr[2][$key]["cat"]);
    }
    $arr[1] = array_sort($arr[1],'floor_sort',"asc");
    $arr[2] = array_sort($arr[2],'floor_sort',"asc");
    unset($arr[3]);
    /* 模板赋值 */
    assign_template();
    $position = assign_ur_here();
    $smarty->assign('page_title',       $GLOBALS['_LANG']['title_brand_zone']);       // 页面标题
    $smarty->assign('ur_here',          $position['ur_here'] . '> ' . $zone['title']);     // 当前位置
    $smarty->assign('show_marketprice', $_CFG['show_marketprice']);
    $smarty->assign('helps',            get_shop_help()); // 网店帮助
    $smarty->assign('arr',   $arr);          // 品牌列表
    $smarty->assign('venue',            $zone);                   // 专题信息
    $smarty->assign('categories',       get_categories_tree()); // 分类树
    $smarty->assign('keywords',         $GLOBALS['_LANG']['keywords_brand_zone']);       // 专题信息
    $smarty->assign('description',      $GLOBALS['_LANG']['description_brand_zone']);    // 专题信息

    $template_file = empty($zone['template']) ? 'brand_zone.dwt' : $zone['template'];
}
/* 显示模板 */
$smarty->display($templates, $cache_id);
function array_sort($arr, $keys, $type = 'asc') {

    $keysvalue = $new_array = array();

    foreach ($arr as $k => $v) {

        $keysvalue[$k] = $v[$keys];
    }

    if ($type == 'asc') {

        asort($keysvalue);
    } else {

        arsort($keysvalue);
    }

    reset($keysvalue);

    foreach ($keysvalue as $k => $v) {

        $new_array[] = $arr[$k];
    }

    return $new_array;
}
?>