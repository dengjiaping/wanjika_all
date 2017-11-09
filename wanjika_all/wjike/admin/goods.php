<?php

/**
 * ECSHOP 商品管理程序
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liubo $
 * $Id: goods.php 17217 2011-01-19 06:29:08Z liubo $
*/

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . '/' . ADMIN_PATH . '/includes/lib_goods.php');
include_once(ROOT_PATH . '/includes/cls_image.php');
require_once(ROOT_PATH . 'includes/lib_order.php');
$image = new cls_image($_CFG['bgcolor']);
$exc = new exchange($ecs->table('goods'), $db, 'goods_id', 'goods_name');

/*------------------------------------------------------ */
//-- 商品列表，商品回收站
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list' || $_REQUEST['act'] == 'trash')
{
    admin_priv('goods_manage');

    $cat_id = empty($_REQUEST['cat_id']) ? 0 : intval($_REQUEST['cat_id']);
    $code   = empty($_REQUEST['extension_code']) ? '' : trim($_REQUEST['extension_code']);
    $suppliers_id = isset($_REQUEST['suppliers_id']) ? (empty($_REQUEST['suppliers_id']) ? '' : trim($_REQUEST['suppliers_id'])) : '';
    $is_on_sale = isset($_REQUEST['is_on_sale']) ? ((empty($_REQUEST['is_on_sale']) && $_REQUEST['is_on_sale'] === 0) ? '' : trim($_REQUEST['is_on_sale'])) : '';

    $handler_list = array();
    $handler_list['virtual_card'][] = array('url'=>'virtual_card.php?act=card', 'title'=>$_LANG['card'], 'img'=>'icon_send_bonus.gif');
    $handler_list['virtual_card'][] = array('url'=>'virtual_card.php?act=replenish', 'title'=>$_LANG['replenish'], 'img'=>'icon_add.gif');
    $handler_list['virtual_card'][] = array('url'=>'virtual_card.php?act=batch_card_add', 'title'=>$_LANG['batch_card_add'], 'img'=>'icon_output.gif');

    $handler_list['gift_card'][] = array('url'=>'gift_card.php?act=card', 'title'=>$_LANG['gift_card'], 'img'=>'icon_send_bonus.gif');
    $handler_list['gift_card'][] = array('url'=>'gift_card.php?act=replenish', 'title'=>$_LANG['replenish'], 'img'=>'icon_add.gif');
    $handler_list['gift_card'][] = array('url'=>'gift_card.php?act=batch_card_add', 'title'=>$_LANG['batch_card_add'], 'img'=>'icon_output.gif');

    if ($_REQUEST['act'] == 'list' && isset($handler_list[$code]))
    {
        $smarty->assign('add_handler',      $handler_list[$code]);
    }

    /* 供货商名 */
    $suppliers_list_name = suppliers_list_name();
    $suppliers_exists = 1;
    if (empty($suppliers_list_name))
    {
        $suppliers_exists = 0;
    }
    $smarty->assign('is_on_sale', $is_on_sale);
    $smarty->assign('suppliers_id', $suppliers_id);
    $smarty->assign('suppliers_exists', $suppliers_exists);
    $smarty->assign('suppliers_list_name', $suppliers_list_name);
    unset($suppliers_list_name, $suppliers_exists);

    /* 模板赋值 */
    $goods_ur = array('' => $_LANG['01_goods_list'], 'virtual_card'=>$_LANG['50_virtual_card_list']);
    $ur_here = ($_REQUEST['act'] == 'list') ? $goods_ur[$code] : $_LANG['11_goods_trash'];
    $smarty->assign('ur_here', $ur_here);

    $action_link = ($_REQUEST['act'] == 'list') ? add_link($code) : array('href' => 'goods.php?act=list', 'text' => $_LANG['01_goods_list']);
    $smarty->assign('action_link',  $action_link);
    $smarty->assign('code',     $code);
    $smarty->assign('cat_list',     cat_list(0, $cat_id));
    $smarty->assign('brand_list',   get_brand_list());
    $smarty->assign('intro_list',   get_intro_list());
    $smarty->assign('supplier_list',   get_supplier_list());
    $smarty->assign('lang',         $_LANG);
    $smarty->assign('list_type',    $_REQUEST['act'] == 'list' ? 'goods' : 'trash');
    $smarty->assign('use_storage',  empty($_CFG['use_storage']) ? 0 : 1);

    $suppliers_list = suppliers_list_info(' is_check = 1 ');
    $suppliers_list_count = count($suppliers_list);
    $smarty->assign('suppliers_list', ($suppliers_list_count == 0 ? 0 : $suppliers_list)); // 取供货商列表

    $goods_list = goods_list($_REQUEST['act'] == 'list' ? 0 : 1, ($_REQUEST['act'] == 'list') ? (($code == '') ? 1 : 0) : -1);
    $smarty->assign('goods_list',   $goods_list['goods']);
    $smarty->assign('filter',       $goods_list['filter']);
    $smarty->assign('record_count', $goods_list['record_count']);
    $smarty->assign('page_count',   $goods_list['page_count']);
    $smarty->assign('full_page',    1);

    $smarty->assign('admin_name',$_SESSION ["admin_name"]);

    /* 排序标记 */
    $sort_flag  = sort_flag($goods_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    /* 获取商品类型存在规格的类型 */
    $specifications = get_goods_type_specifications();
    $smarty->assign('specifications', $specifications);

    /* 显示商品列表页面 */
    assign_query_info();
    $htm_file = ($_REQUEST['act'] == 'list') ?
        'goods_list.htm' : (($_REQUEST['act'] == 'trash') ? 'goods_trash.htm' : 'group_list.htm');
    $smarty->display($htm_file);
}

/*------------------------------------------------------ */
//-- 添加新商品 编辑商品
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'add' || $_REQUEST['act'] == 'edit' || $_REQUEST['act'] == 'copy')
{
    include_once(ROOT_PATH . 'includes/fckeditor/fckeditor.php'); // 包含 html editor 类文件

    $is_add = $_REQUEST['act'] == 'add'; // 添加还是编辑的标识
    $is_copy = $_REQUEST['act'] == 'copy'; //是否复制
    $code = empty($_REQUEST['extension_code']) ? '' : trim($_REQUEST['extension_code']);
    $code=$code=='virual_card' ? 'virual_card': '';
    if ($code == 'virual_card')
    {
        admin_priv('virualcard'); // 检查权限
    }
    else
    {
        admin_priv('goods_manage'); // 检查权限
    }

    /* 供货商名 */
    $suppliers_list_name = suppliers_list_name();
    $suppliers_exists = 1;
    if (empty($suppliers_list_name))
    {
        $suppliers_exists = 0;
    }
    $smarty->assign('suppliers_exists', $suppliers_exists);
    $smarty->assign('suppliers_list_name', $suppliers_list_name);
    unset($suppliers_list_name, $suppliers_exists);

    /* 如果是安全模式，检查目录是否存在 */
    if (ini_get('safe_mode') == 1 && (!file_exists('../' . IMAGE_DIR . '/'.date('Ym')) || !is_dir('../' . IMAGE_DIR . '/'.date('Ym'))))
    {
        if (@!mkdir('../' . IMAGE_DIR . '/'.date('Ym'), 0777))
        {
            $warning = sprintf($_LANG['safe_mode_warning'], '../' . IMAGE_DIR . '/'.date('Ym'));
            $smarty->assign('warning', $warning);
        }
    }

    /* 如果目录存在但不可写，提示用户 */
    elseif (file_exists('../' . IMAGE_DIR . '/'.date('Ym')) && file_mode_info('../' . IMAGE_DIR . '/'.date('Ym')) < 2)
    {
        $warning = sprintf($_LANG['not_writable_warning'], '../' . IMAGE_DIR . '/'.date('Ym'));
        $smarty->assign('warning', $warning);
    }

    /* 取得商品信息 */
    if ($is_add)
    {
        /* 默认值 */
        $last_choose = array(0, 0);
        if (!empty($_COOKIE['ECSCP']['last_choose']))
        {
            $last_choose = explode('|', $_COOKIE['ECSCP']['last_choose']);
        }
        $goods = array(
            'goods_id'      => 0,
            'goods_desc'    => '',
            'cat_id'        => $last_choose[0],
            'brand_id'      => $last_choose[1],
            'is_on_sale'    => '1',
            'is_alone_sale' => '1',
            'is_shipping' => '0',
            'other_cat'     => array(), // 扩展分类
            'goods_type'    => 0,       // 商品类型
            'shop_price'    => 0,
            'promote_price' => 0,
            'market_price'  => 0,
            'integral'      => 0,
            'goods_number'  => $_CFG['default_storage'],
            'warn_number'   => 1,
            'promote_start_date' => local_date('Y-m-d'),
            'promote_end_date'   => local_date('Y-m-d', local_strtotime('+1 month')),
            'goods_weight'  => 0,
            'give_integral' => -1,
            'supplier_id' => 1,
            'rank_integral' => -1
        );

        if ($code != '')
        {
            $goods['goods_number'] = 0;
        }

        /* 关联商品 */
        $link_goods_list = array();
        $sql = "DELETE FROM " . $ecs->table('link_goods') .
                " WHERE (goods_id = 0 OR link_goods_id = 0)" .
                " AND admin_id = '$_SESSION[admin_id]'";
        $db->query($sql);

        /* 组合商品 */
        $group_goods_list = array();
        $sql = "DELETE FROM " . $ecs->table('group_goods') .
                " WHERE parent_id = 0 AND admin_id = '$_SESSION[admin_id]'";
        $db->query($sql);

        /* 关联文章 */
        $goods_article_list = array();
        $sql = "DELETE FROM " . $ecs->table('goods_article') .
                " WHERE goods_id = 0 AND admin_id = '$_SESSION[admin_id]'";
        $db->query($sql);

        /* 属性 */
        $sql = "DELETE FROM " . $ecs->table('goods_attr') . " WHERE goods_id = 0";
        $db->query($sql);

        /* 图片列表 */
        $img_list = array();
    }
    else
    {
        /* 商品信息 */
        $sql = "SELECT * FROM " . $ecs->table('goods') . " WHERE goods_id = '$_REQUEST[goods_id]'";
        $goods = $db->getRow($sql);

        /* 虚拟卡商品复制时, 将其库存置为0*/
        if ($is_copy && $code != '')
        {
            $goods['goods_number'] = 0;
        }

        if (empty($goods) === true)
        {
            /* 默认值 */
            $goods = array(
                'goods_id'      => 0,
                'goods_desc'    => '',
                'cat_id'        => 0,
                'is_on_sale'    => '1',
                'is_alone_sale' => '1',
                'is_shipping' => '0',
                'other_cat'     => array(), // 扩展分类
                'goods_type'    => 0,       // 商品类型
                'shop_price'    => 0,
                'promote_price' => 0,
                'market_price'  => 0,
                'integral'      => 0,
                'goods_number'  => 1,
                'warn_number'   => 1,
                'promote_start_date' => local_date('Y-m-d'),
                'promote_end_date'   => local_date('Y-m-d', gmstr2tome('+1 month')),
                'goods_weight'  => 0,
                'give_integral' => -1,
                'supplier_id' => 1,
                'rank_integral' => -1
            );
        }

        /* 获取商品类型存在规格的类型 */
        $specifications = get_goods_type_specifications();
        $goods['specifications_id'] = $specifications[$goods['goods_type']];
        $_attribute = get_goods_specifications_list($goods['goods_id']);
        $goods['_attribute'] = empty($_attribute) ? '' : 1;

        /* 根据商品重量的单位重新计算 */
        if ($goods['goods_weight'] > 0)
        {
            $goods['goods_weight_by_unit'] = ($goods['goods_weight'] >= 1) ? $goods['goods_weight'] : ($goods['goods_weight'] / 0.001);
        }

        if (!empty($goods['goods_brief']))
        {
            //$goods['goods_brief'] = trim_right($goods['goods_brief']);
            $goods['goods_brief'] = $goods['goods_brief'];
        }
        if (!empty($goods['keywords']))
        {
            //$goods['keywords']    = trim_right($goods['keywords']);
            $goods['keywords']    = $goods['keywords'];
        }

        /* 如果不是促销，处理促销日期 */
        if (isset($goods['is_promote']) && $goods['is_promote'] == '0')
        {
            unset($goods['promote_start_date']);
            unset($goods['promote_end_date']);
        }
        else
        {
            $goods['promote_start_date'] = local_date('Y-m-d H:i:s', $goods['promote_start_date']);
            $goods['promote_end_date'] = local_date('Y-m-d H:i:s', $goods['promote_end_date']);
        }

        /* 如果是复制商品，处理 */
        if ($_REQUEST['act'] == 'copy')
        {
            // 商品信息
            $goods['goods_id'] = 0;
            $goods['goods_sn'] = '';
            $goods['goods_name'] = '';
            $goods['goods_img'] = '';
            $goods['goods_thumb'] = '';
            $goods['original_img'] = '';

            // 扩展分类不变

            // 关联商品
            $sql = "DELETE FROM " . $ecs->table('link_goods') .
                    " WHERE (goods_id = 0 OR link_goods_id = 0)" .
                    " AND admin_id = '$_SESSION[admin_id]'";
            $db->query($sql);

            $sql = "SELECT '0' AS goods_id, link_goods_id, is_double, '$_SESSION[admin_id]' AS admin_id" .
                    " FROM " . $ecs->table('link_goods') .
                    " WHERE goods_id = '$_REQUEST[goods_id]' ";
            $res = $db->query($sql);
            while ($row = $db->fetchRow($res))
            {
                $db->autoExecute($ecs->table('link_goods'), $row, 'INSERT');
            }

            $sql = "SELECT goods_id, '0' AS link_goods_id, is_double, '$_SESSION[admin_id]' AS admin_id" .
                    " FROM " . $ecs->table('link_goods') .
                    " WHERE link_goods_id = '$_REQUEST[goods_id]' ";
            $res = $db->query($sql);
            while ($row = $db->fetchRow($res))
            {
                $db->autoExecute($ecs->table('link_goods'), $row, 'INSERT');
            }

            // 配件
            $sql = "DELETE FROM " . $ecs->table('group_goods') .
                    " WHERE parent_id = 0 AND admin_id = '$_SESSION[admin_id]'";
            $db->query($sql);

            $sql = "SELECT 0 AS parent_id, goods_id, goods_price, '$_SESSION[admin_id]' AS admin_id " .
                    "FROM " . $ecs->table('group_goods') .
                    " WHERE parent_id = '$_REQUEST[goods_id]' ";
            $res = $db->query($sql);
            while ($row = $db->fetchRow($res))
            {
                $db->autoExecute($ecs->table('group_goods'), $row, 'INSERT');
            }

            // 关联文章
            $sql = "DELETE FROM " . $ecs->table('goods_article') .
                    " WHERE goods_id = 0 AND admin_id = '$_SESSION[admin_id]'";
            $db->query($sql);

            $sql = "SELECT 0 AS goods_id, article_id, '$_SESSION[admin_id]' AS admin_id " .
                    "FROM " . $ecs->table('goods_article') .
                    " WHERE goods_id = '$_REQUEST[goods_id]' ";
            $res = $db->query($sql);
            while ($row = $db->fetchRow($res))
            {
                $db->autoExecute($ecs->table('goods_article'), $row, 'INSERT');
            }

            // 图片不变

            // 商品属性
            $sql = "DELETE FROM " . $ecs->table('goods_attr') . " WHERE goods_id = 0";
            $db->query($sql);

            $sql = "SELECT 0 AS goods_id, attr_id, attr_value, attr_price " .
                    "FROM " . $ecs->table('goods_attr') .
                    " WHERE goods_id = '$_REQUEST[goods_id]' ";
            $res = $db->query($sql);
            while ($row = $db->fetchRow($res))
            {
                $db->autoExecute($ecs->table('goods_attr'), addslashes_deep($row), 'INSERT');
            }
        }

        // 扩展分类
        $other_cat_list = array();
        $sql = "SELECT cat_id FROM " . $ecs->table('goods_cat') . " WHERE goods_id = '$_REQUEST[goods_id]'";
        $goods['other_cat'] = $db->getCol($sql);
        foreach ($goods['other_cat'] AS $cat_id)
        {
            $other_cat_list[$cat_id] = cat_list(0, $cat_id);
        }
        $smarty->assign('other_cat_list', $other_cat_list);

        $link_goods_list    = get_linked_goods($goods['goods_id']); // 关联商品
        $group_goods_list   = get_group_goods($goods['goods_id']); // 配件
        $goods_article_list = get_goods_articles($goods['goods_id']);   // 关联文章

        /* 商品图片路径 */
        if (isset($GLOBALS['shop_id']) && ($GLOBALS['shop_id'] > 10) && !empty($goods['original_img']))
        {
            $goods['goods_img'] = get_image_path($_REQUEST['goods_id'], $goods['goods_img']);
            $goods['goods_thumb'] = get_image_path($_REQUEST['goods_id'], $goods['goods_thumb'], true);
        }

        /* 图片列表 */
        $sql = "SELECT * FROM " . $ecs->table('goods_gallery') . " WHERE goods_id = '$goods[goods_id]'";
        $img_list = $db->getAll($sql);

        /* 格式化相册图片路径 */
        if (isset($GLOBALS['shop_id']) && ($GLOBALS['shop_id'] > 0))
        {
            foreach ($img_list as $key => $gallery_img)
            {
                $gallery_img[$key]['img_url'] = get_image_path($gallery_img['goods_id'], $gallery_img['img_original'], false, 'gallery');
                $gallery_img[$key]['thumb_url'] = get_image_path($gallery_img['goods_id'], $gallery_img['img_original'], true, 'gallery');
            }
        }
        else
        {
            foreach ($img_list as $key => $gallery_img)
            {
                $gallery_img[$key]['thumb_url'] = '../' . (empty($gallery_img['thumb_url']) ? $gallery_img['img_url'] : $gallery_img['thumb_url']);
            }
        }
    }

    /* 拆分商品名称样式 */
    $goods_name_style = explode('+', empty($goods['goods_name_style']) ? '+' : $goods['goods_name_style']);
    /*获取可用的支付方式 - add by qihua on 2013-08-13*/
    $payments_list = array();
    $payments = available_payment_list(1);
    $payments_ids = array();
    if (!empty($payments))
    {
        foreach ($payments as $info)
        {
            $payments_list[] = array('pay_id' => $info['pay_id'], 'pay_name' => $info['pay_name']);
            $payments_ids[] = $info['pay_id'];
        }
    }

    /*如果当前商品没有定义支付方式，那么则默认所有支付方式都可用。 - add by qihua on 2013-08-13*/
    if (empty($goods['payment_ids']))
    {
        $goods['payment_ids'] = implode(',', $payments_ids);
    }

    /*选中的支付方式 - add by qihua on 2013-08-14*/
    $payments_ids_arr = explode(',', $goods['payment_ids']);
    foreach ($payments_list as $key => $val)
    {
        //添加新商品货到付款默认不选中
        if($is_add){
            if (in_array($val['pay_id'], $payments_ids_arr) && $val['pay_name'] !='货到付款（需要支付一定的手续费）')
            {
                $payments_list[$key]['selected'] = 1;
            }
        }
        else{
            if (in_array($val['pay_id'], $payments_ids_arr))
            {
                $payments_list[$key]['selected'] = 1;
            }
        }
    }
    /* 创建 html editor */
    create_html_editor('goods_desc', $goods['goods_desc']);
    $country_array = country_list();
    foreach($country_array AS $key=>$value)
    {
        $overseas_logo_list[$value['country_code']]=$value['country_name'];
    }    /* 获取ERP分类列表 */
    $erpcat_list = erpcat_list();
    foreach($erpcat_list AS $value)
    {
        $erplist[$value['erpcat_id']]=$value['erpcat_name'];
    }
    /* 模板赋值 */
    $smarty->assign('code',    $code);
    $smarty->assign('ur_here', $is_add ? (empty($code) ? $_LANG['02_goods_add'] : $_LANG['51_virtual_card_add']) : ($_REQUEST['act'] == 'edit' ? $_LANG['edit_goods'] : $_LANG['copy_goods']));
    $smarty->assign('action_link', list_link($is_add, $code));
    $smarty->assign('goods', $goods);
    $smarty->assign('goods_name_color', $goods_name_style[0]);
    $smarty->assign('goods_name_style', $goods_name_style[1]);
    $smarty->assign('cat_list', cat_list(0, $goods['cat_id'],true,0,false));
    $smarty->assign('erpcat_list', $erplist);
    $smarty->assign('brand_list', get_brand_list());
    $smarty->assign('supplier_list', get_supplier_list());
    $smarty->assign('overseas_logo_list',   $overseas_logo_list);
    $smarty->assign('unit_list', get_unit_list());
    $smarty->assign('user_rank_list', get_user_rank_list());
//    $smarty->assign('weight_unit', $is_add ? '1' : ($goods['goods_weight'] >= 1 ? '1' : '0.001'));
    //添加商品默认重量单位改为克
    $smarty->assign('weight_unit', $is_add ? '0.001' : ($goods['goods_weight'] >= 1 ? '1' : '0.001'));
    $smarty->assign('cfg', $_CFG);
    $smarty->assign('form_act', $is_add ? 'insert' : ($_REQUEST['act'] == 'edit' ? 'update' : 'insert'));
    if ($_REQUEST['act'] == 'add' || $_REQUEST['act'] == 'edit')
    {
        $smarty->assign('is_add', true);
    }
    if(!$is_add)
    {
        $smarty->assign('member_price_list', get_member_price_list($_REQUEST['goods_id']));
    }
    $smarty->assign('link_goods_list', $link_goods_list);
    $smarty->assign('group_goods_list', $group_goods_list);
    $smarty->assign('goods_article_list', $goods_article_list);
    $smarty->assign('img_list', $img_list);
    $smarty->assign('goods_type_list', goods_type_list($goods['goods_type']));
    $smarty->assign('gd', gd_version());
    $smarty->assign('thumb_width', $_CFG['thumb_width']);
    $smarty->assign('thumb_height', $_CFG['thumb_height']);
    $smarty->assign('goods_attr_html', build_attr_html($goods['goods_type'], $goods['goods_id']));
    $volume_price_list = '';
    if(isset($_REQUEST['goods_id']))
    {
    $volume_price_list = get_volume_price_list($_REQUEST['goods_id']);
    }
    if (empty($volume_price_list))
    {
        $volume_price_list = array('0'=>array('number'=>'','price'=>''));
    }
    $smarty->assign('volume_price_list', $volume_price_list);
    $smarty->assign('payment_list', $payments_list);
    if(trim($_REQUEST['extension_code']) == 'charge_calls'){
        $smarty->assign('charge_calls', true);
    }
    /* 显示商品信息页面 */
    assign_query_info();

    include_once(ROOT_PATH . '/includes/lib_goods.php');
    /* 显示一品多类信息页面 */
    assign_collect_attr_info($_REQUEST['goods_id']);
    $smarty->display('goods_info.htm');
}

/*------------------------------------------------------ */
//-- 插入商品 更新商品
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'insert' || $_REQUEST['act'] == 'update')
{
    $code = empty($_REQUEST['extension_code']) ? '' : trim($_REQUEST['extension_code']);

    /* 是否处理缩略图 */
    $proc_thumb = (isset($GLOBALS['shop_id']) && $GLOBALS['shop_id'] > 0)? false : true;
    if ($code == 'virtual_card')
    {
        admin_priv('virualcard'); // 检查权限
    }
    else
    {
        admin_priv('goods_manage'); // 检查权限
    }

    /* 检查货号是否重复 */
    if ($_POST['goods_sn'])
    {
        $sql = "SELECT COUNT(*) FROM " . $ecs->table('goods') .
                " WHERE goods_sn = '$_POST[goods_sn]' AND is_delete = 0 AND goods_id <> '$_POST[goods_id]'";
        if ($db->getOne($sql) > 0)
        {
            sys_msg($_LANG['goods_sn_exists'], 1, array(), false);
        }
    }

    /* 检查图片：如果有错误，检查尺寸是否超过最大值；否则，检查文件类型 */
    if (isset($_FILES['goods_img']['error'])) // php 4.2 版本才支持 error
    {
        // 最大上传文件大小
        $php_maxsize = ini_get('upload_max_filesize');
        $htm_maxsize = '2M';

        // 商品图片
        if ($_FILES['goods_img']['error'] == 0)
        {
            if (!$image->check_img_type($_FILES['goods_img']['type']))
            {
                sys_msg($_LANG['invalid_goods_img'], 1, array(), false);
            }
        }
        elseif ($_FILES['goods_img']['error'] == 1)
        {
            sys_msg(sprintf($_LANG['goods_img_too_big'], $php_maxsize), 1, array(), false);
        }
        elseif ($_FILES['goods_img']['error'] == 2)
        {
            sys_msg(sprintf($_LANG['goods_img_too_big'], $htm_maxsize), 1, array(), false);
        }

        // 商品缩略图
        if (isset($_FILES['goods_thumb']))
        {
            if ($_FILES['goods_thumb']['error'] == 0)
            {
                if (!$image->check_img_type($_FILES['goods_thumb']['type']))
                {
                    sys_msg($_LANG['invalid_goods_thumb'], 1, array(), false);
                }
            }
            elseif ($_FILES['goods_thumb']['error'] == 1)
            {
                sys_msg(sprintf($_LANG['goods_thumb_too_big'], $php_maxsize), 1, array(), false);
            }
            elseif ($_FILES['goods_thumb']['error'] == 2)
            {
                sys_msg(sprintf($_LANG['goods_thumb_too_big'], $htm_maxsize), 1, array(), false);
            }
        }

        // 相册图片
        foreach ($_FILES['img_url']['error'] AS $key => $value)
        {
            if ($value == 0)
            {
                if (!$image->check_img_type($_FILES['img_url']['type'][$key]))
                {
                    sys_msg(sprintf($_LANG['invalid_img_url'], $key + 1), 1, array(), false);
                }
            }
            elseif ($value == 1)
            {
                sys_msg(sprintf($_LANG['img_url_too_big'], $key + 1, $php_maxsize), 1, array(), false);
            }
            elseif ($_FILES['img_url']['error'] == 2)
            {
                sys_msg(sprintf($_LANG['img_url_too_big'], $key + 1, $htm_maxsize), 1, array(), false);
            }
        }
    }
    /* 4.1版本 */
    else
    {
        // 商品图片
        if ($_FILES['goods_img']['tmp_name'] != 'none')
        {
            if (!$image->check_img_type($_FILES['goods_img']['type']))
            {

                sys_msg($_LANG['invalid_goods_img'], 1, array(), false);
            }
        }

        // 商品缩略图
        if (isset($_FILES['goods_thumb']))
        {
            if ($_FILES['goods_thumb']['tmp_name'] != 'none')
            {
                if (!$image->check_img_type($_FILES['goods_thumb']['type']))
                {
                    sys_msg($_LANG['invalid_goods_thumb'], 1, array(), false);
                }
            }
        }

        // 相册图片
        foreach ($_FILES['img_url']['tmp_name'] AS $key => $value)
        {
            if ($value != 'none')
            {
                if (!$image->check_img_type($_FILES['img_url']['type'][$key]))
                {
                    sys_msg(sprintf($_LANG['invalid_img_url'], $key + 1), 1, array(), false);
                }
            }
        }
    }

    /* 插入还是更新的标识 */
    $is_insert = $_REQUEST['act'] == 'insert';

    /* 处理商品图片 */
    $goods_img        = '';  // 初始化商品图片
    $goods_thumb      = '';  // 初始化商品缩略图
    $original_img     = '';  // 初始化原始图片
    $old_original_img = '';  // 初始化原始图片旧图

    // 如果上传了商品图片，相应处理
    if (($_FILES['goods_img']['tmp_name'] != '' && $_FILES['goods_img']['tmp_name'] != 'none') or (($_POST['goods_img_url'] != $_LANG['lab_picture_url'] && $_POST['goods_img_url'] != 'http://') && $is_url_goods_img = 1))
    {
        if ($_REQUEST['goods_id'] > 0)
        {
            /* 删除原来的图片文件 */
            $sql = "SELECT goods_thumb, goods_img, original_img " .
                    " FROM " . $ecs->table('goods') .
                    " WHERE goods_id = '$_REQUEST[goods_id]'";
            $row = $db->getRow($sql);
            if ($row['goods_thumb'] != '' && is_file('../' . $row['goods_thumb']))
            {
                @unlink('../' . $row['goods_thumb']);
            }
            if ($row['goods_img'] != '' && is_file('../' . $row['goods_img']))
            {
                @unlink('../' . $row['goods_img']);
            }
            if ($row['original_img'] != '' && is_file('../' . $row['original_img']))
            {
                /* 先不处理，以防止程序中途出错停止 */
                //$old_original_img = $row['original_img']; //记录旧图路径
            }
            /* 清除原来商品图片 */
            if ($proc_thumb === false)
            {
                get_image_path($_REQUEST[goods_id], $row['goods_img'], false, 'goods', true);
                get_image_path($_REQUEST[goods_id], $row['goods_thumb'], true, 'goods', true);
            }
        }

        if (empty($is_url_goods_img))
        {
            $original_img   = $image->upload_image($_FILES['goods_img']); // 原始图片
        }
        elseif ($_POST['goods_img_url'])
        {
            
            if(preg_match('/(.jpg|.png|.gif|.jpeg)$/',$_POST['goods_img_url']) && copy(trim($_POST['goods_img_url']), ROOT_PATH . 'temp/' . basename($_POST['goods_img_url'])))
            {
                  $original_img = 'temp/' . basename($_POST['goods_img_url']);
            }
            
        }

        if ($original_img === false)
        {
            sys_msg($image->error_msg(), 1, array(), false);
        }
        $goods_img      = $original_img;   // 商品图片

        /* 复制一份相册图片 */
        /* 添加判断是否自动生成相册图片 */
        if ($_CFG['auto_generate_gallery'])
        {
            $img        = $original_img;   // 相册图片
            $pos        = strpos(basename($img), '.');
            $newname    = dirname($img) . '/' . $image->random_filename() . substr(basename($img), $pos);
            if (!copy('../' . $img, '../' . $newname))
            {
                sys_msg('fail to copy file: ' . realpath('../' . $img), 1, array(), false);
            }
            $img        = $newname;

            $gallery_img    = $img;
            $gallery_thumb  = $img;
        }

        // 如果系统支持GD，缩放商品图片，且给商品图片和相册图片加水印
        if ($proc_thumb && $image->gd_version() > 0 && $image->check_img_function($_FILES['goods_img']['type']) || $is_url_goods_img)
        {

            if (empty($is_url_goods_img))
            {
                // 如果设置大小不为0，缩放图片
                if ($_CFG['image_width'] != 0 || $_CFG['image_height'] != 0)
                {
                    $goods_img = $image->make_thumb('../'. $goods_img , $GLOBALS['_CFG']['image_width'],  $GLOBALS['_CFG']['image_height']);
                    if ($goods_img === false)
                    {
                        sys_msg($image->error_msg(), 1, array(), false);
                    }
                }

                /* 添加判断是否自动生成相册图片 */
                if ($_CFG['auto_generate_gallery'])
                {
                    $newname    = dirname($img) . '/' . $image->random_filename() . substr(basename($img), $pos);
                    if (!copy('../' . $img, '../' . $newname))
                    {
                        sys_msg('fail to copy file: ' . realpath('../' . $img), 1, array(), false);
                    }
                    $gallery_img        = $newname;
                }

                // 加水印
                if (intval($_CFG['watermark_place']) > 0 && !empty($GLOBALS['_CFG']['watermark']))
                {
                    if ($image->add_watermark('../'.$goods_img,'',$GLOBALS['_CFG']['watermark'], $GLOBALS['_CFG']['watermark_place'], $GLOBALS['_CFG']['watermark_alpha']) === false)
                    {
                        sys_msg($image->error_msg(), 1, array(), false);
                    }
                    /* 添加判断是否自动生成相册图片 */
                    if ($_CFG['auto_generate_gallery'])
                    {
                        if ($image->add_watermark('../'. $gallery_img,'',$GLOBALS['_CFG']['watermark'], $GLOBALS['_CFG']['watermark_place'], $GLOBALS['_CFG']['watermark_alpha']) === false)
                        {
                            sys_msg($image->error_msg(), 1, array(), false);
                        }
                    }
                }
            }

            // 相册缩略图
            /* 添加判断是否自动生成相册图片 */
            if ($_CFG['auto_generate_gallery'])
            {
                if ($_CFG['thumb_width'] != 0 || $_CFG['thumb_height'] != 0)
                {
                    $gallery_thumb = $image->make_thumb('../' . $img, $GLOBALS['_CFG']['thumb_width'],  $GLOBALS['_CFG']['thumb_height']);
                    if ($gallery_thumb === false)
                    {
                        sys_msg($image->error_msg(), 1, array(), false);
                    }
                }
            }
        }
        /* 取消该原图复制流程 */
        // else
        // {
        //     /* 复制一份原图 */
        //     $pos        = strpos(basename($img), '.');
        //     $gallery_img = dirname($img) . '/' . $image->random_filename() . // substr(basename($img), $pos);
        //     if (!copy('../' . $img, '../' . $gallery_img))
        //     {
        //         sys_msg('fail to copy file: ' . realpath('../' . $img), 1, array(), false);
        //     }
        //     $gallery_thumb = '';
        // }
    }


    // 是否上传商品缩略图
    if (isset($_FILES['goods_thumb']) && $_FILES['goods_thumb']['tmp_name'] != '' &&
        isset($_FILES['goods_thumb']['tmp_name']) &&$_FILES['goods_thumb']['tmp_name'] != 'none')
    {
        // 上传了，直接使用，原始大小
        $goods_thumb = $image->upload_image($_FILES['goods_thumb']);
        if ($goods_thumb === false)
        {
            sys_msg($image->error_msg(), 1, array(), false);
        }
    }
    else
    {
        // 未上传，如果自动选择生成，且上传了商品图片，生成所略图
        if ($proc_thumb && isset($_POST['auto_thumb']) && !empty($original_img))
        {
            // 如果设置缩略图大小不为0，生成缩略图
            if ($_CFG['thumb_width'] != 0 || $_CFG['thumb_height'] != 0)
            {
                $goods_thumb = $image->make_thumb('../' . $original_img, $GLOBALS['_CFG']['thumb_width'],  $GLOBALS['_CFG']['thumb_height']);
                if ($goods_thumb === false)
                {
                    sys_msg($image->error_msg(), 1, array(), false);
                }
            }
            else
            {
                $goods_thumb = $original_img;
            }
        }
    }


    /* 删除下载的外链原图 */
    if (!empty($is_url_goods_img))
    {
        unlink(ROOT_PATH . $original_img);
        empty($newname) || unlink(ROOT_PATH . $newname);
        $url_goods_img = $goods_img = $original_img = htmlspecialchars(trim($_POST['goods_img_url']));
    }


    /* 如果没有输入商品货号则自动生成一个商品货号 */
    if (empty($_POST['goods_sn']))
    {
        $max_id     = $is_insert ? $db->getOne("SELECT MAX(goods_id) + 1 FROM ".$ecs->table('goods')) : $_REQUEST['goods_id'];
        $goods_sn   = generate_goods_sn($max_id);
    }
    else
    {
        $goods_sn   = $_POST['goods_sn'];
    }

    /* 处理商品数据 */
    $kjt_goods_id = !empty($_POST['kjt_goods_id']) ? $_POST['kjt_goods_id'] : NULL;
    $goods_code = !empty($_POST['goods_code']) ? $_POST['goods_code'] : NULL;
    $goods_barcode = !empty($_POST['goods_barcode']) ? $_POST['goods_barcode'] : NULL;
    $kjt_price = !empty($_POST['kjt_price']) ? $_POST['kjt_price'] : NULL;
    $kjt_tariffrate = !empty($_POST['kjt_tariffrate']) ? $_POST['kjt_tariffrate'] : NULL;
    $warehouse_id = !empty($_POST['warehouse_id']) ? $_POST['warehouse_id'] : 0;
    $erpcat_id = !empty($_POST['erpcat_id']) ? $_POST['erpcat_id'] : 0;
    $shop_price = !empty($_POST['shop_price']) ? $_POST['shop_price'] : 0;
    $min_price = !empty($_POST['min_price']) ? $_POST['min_price'] : 0;
    $max_number = !empty($_POST['max_number']) ? $_POST['max_number'] : 0;
    $market_price = !empty($_POST['market_price']) ? $_POST['market_price'] : 0;
    $promote_price = !empty($_POST['promote_price']) ? floatval($_POST['promote_price'] ) : 0;
    $is_promote = empty($promote_price) ? 0 : 1;
    $promote_start_date = ($is_promote && !empty($_POST['promote_start_date'])) ? local_strtotime($_POST['promote_start_date']) : 0;
    $promote_end_date = ($is_promote && !empty($_POST['promote_end_date'])) ? local_strtotime($_POST['promote_end_date']) : 0;
    $goods_weight = !empty($_POST['goods_weight']) ? $_POST['goods_weight'] * $_POST['weight_unit'] : 0;
    $is_best = isset($_POST['is_best']) ? 1 : 0;
    $is_new = isset($_POST['is_new']) ? 1 : 0;
    $is_hot = isset($_POST['is_hot']) ? 1 : 0;
    $is_on_sale = isset($_POST['is_on_sale']) ? 1 : 0;
    $overseas_logo = !empty($_POST['overseas_logo']) ? $_POST['overseas_logo'] : NULL;
    $is_immediately = isset($_POST['is_immediately']) ? 1 : 0;
    if(trim($_REQUEST['extension_code']) == 'goods_members'){
        $is_on_sale = 2;
    }
    $is_alone_sale = isset($_POST['is_alone_sale']) ? 1 : 0;
    $is_shipping = isset($_POST['is_shipping']) ? 1 : 0;
    $goods_number = isset($_POST['goods_number']) ? $_POST['goods_number'] : 0;
    $warn_number = isset($_POST['warn_number']) ? $_POST['warn_number'] : 0;
    $goods_type = isset($_POST['goods_type']) ? $_POST['goods_type'] : 0;
    $give_integral = isset($_POST['give_integral']) ? intval($_POST['give_integral']) : '-1';
    $rank_integral = isset($_POST['rank_integral']) ? intval($_POST['rank_integral']) : '-1';
    $suppliers_id = isset($_POST['suppliers_id']) ? intval($_POST['suppliers_id']) : '0';

    $goods_name_style = $_POST['goods_name_color'] . '+' . $_POST['goods_name_style'];

    $catgory_id = empty($_POST['cat_id']) ? '' : intval($_POST['cat_id']);
    $brand_id = empty($_POST['brand_id']) ? '' : intval($_POST['brand_id']);

    $goods_thumb = (empty($goods_thumb) && !empty($_POST['goods_thumb_url']) && goods_parse_url($_POST['goods_thumb_url'])) ? htmlspecialchars(trim($_POST['goods_thumb_url'])) : $goods_thumb;
    $goods_thumb = (empty($goods_thumb) && isset($_POST['auto_thumb']))? $goods_img : $goods_thumb;

    /*支付方式 - add by qihua*/
    $payments_ids = empty($_POST['payments_list']) ? '' : implode(',', $_POST['payments_list']);
    /* 入库 */
    if ($is_insert)
    {
        if ($code == '')
        {
            $sql = "INSERT INTO " . $ecs->table('goods') . " (goods_name, goods_name_style, goods_sn, " .
                    "cat_id, brand_id, shop_price, min_price, market_price, is_promote, promote_price, " .
                    "promote_start_date, promote_end_date, goods_img, goods_thumb, original_img, keywords, goods_brief, " .
                    "seller_note, goods_weight, goods_number, warn_number, integral, give_integral, is_best, is_new, is_hot, " .
                    "is_on_sale, is_alone_sale, is_shipping, goods_desc, add_time, last_update, goods_type, rank_integral, suppliers_id, payment_ids,supplier_id,kjt_goods_id,kjt_price,kjt_tariffrate,is_immediately,max_number,goods_code,goods_barcode,overseas_logo,erpcat_id)" .
                "VALUES ('$_POST[goods_name]', '$goods_name_style', '$goods_sn', '$catgory_id', " .
                    "'$brand_id', '$shop_price', '$min_price', '$market_price', '$is_promote','$promote_price', ".
                    "'$promote_start_date', '$promote_end_date', '$goods_img', '$goods_thumb', '$original_img', ".
                    "'$_POST[keywords]', '$_POST[goods_brief]', '$_POST[seller_note]', '$goods_weight', '$goods_number',".
                    " '$warn_number', '$_POST[integral]', '$give_integral', '$is_best', '$is_new', '$is_hot', '$is_on_sale', '$is_alone_sale', $is_shipping, ".
                    " '$_POST[goods_desc]', '" . gmtime() . "', '". gmtime() ."', '$goods_type', '$rank_integral', '$suppliers_id', '$payments_ids','$warehouse_id','$kjt_goods_id','$kjt_price','$kjt_tariffrate','$is_immediately','$max_number','$goods_code','$goods_barcode','$overseas_logo','$erpcat_id')";
        }
        else
        {
            $sql = "INSERT INTO " . $ecs->table('goods') . " (goods_name, goods_name_style, goods_sn, " .
                    "cat_id, brand_id, shop_price, min_price, market_price, is_promote, promote_price, " .
                    "promote_start_date, promote_end_date, goods_img, goods_thumb, original_img, keywords, goods_brief, " .
                    "seller_note, goods_weight, goods_number, warn_number, integral, give_integral, is_best, is_new, is_hot, is_real, " .
                    "is_on_sale, is_alone_sale, is_shipping, goods_desc, add_time, last_update, goods_type, extension_code, rank_integral, payment_ids,supplier_id,kjt_goods_id,kjt_price,kjt_tariffrate,is_immediately,max_number,goods_code,goods_barcode,overseas_logo,erpcat_id)" .
                "VALUES ('$_POST[goods_name]', '$goods_name_style', '$goods_sn', '$catgory_id', " .
                    "'$brand_id', '$shop_price', '$min_price', '$market_price', '$is_promote','$promote_price', ".
                    "'$promote_start_date', '$promote_end_date', '$goods_img', '$goods_thumb', '$original_img', ".
                    "'$_POST[keywords]', '$_POST[goods_brief]', '$_POST[seller_note]', '$goods_weight', '$goods_number',".
                    " '$warn_number', '$_POST[integral]', '$give_integral', '$is_best', '$is_new', '$is_hot', 0, '$is_on_sale', '$is_alone_sale', $is_shipping, ".
                    " '$_POST[goods_desc]', '" . gmtime() . "', '". gmtime() ."', '$goods_type', '$code', '$rank_integral', '$payments_ids','$warehouse_id','$kjt_goods_id','$kjt_price','$kjt_tariffrate','$is_immediately','$max_number','$goods_code','$goods_barcode','$overseas_logo','$erpcat_id')";
        }
    }
    else
    {
        /* 如果有上传图片，删除原来的商品图 */
        $sql = "SELECT goods_thumb, goods_img, original_img " .
                    " FROM " . $ecs->table('goods') .
                    " WHERE goods_id = '$_REQUEST[goods_id]'";
        $row = $db->getRow($sql);
        if ($proc_thumb && $goods_img && $row['goods_img'] && !goods_parse_url($row['goods_img']))
        {
            @unlink(ROOT_PATH . $row['goods_img']);
            @unlink(ROOT_PATH . $row['original_img']);
        }

        if ($proc_thumb && $goods_thumb && $row['goods_thumb'] && !goods_parse_url($row['goods_thumb']))
        {
            @unlink(ROOT_PATH . $row['goods_thumb']);
        }

        $sql = "UPDATE " . $ecs->table('goods') . " SET " .
                "goods_name = '$_POST[goods_name]', " .
                "goods_name_style = '$goods_name_style', " .
                "goods_sn = '$goods_sn', " .
                "cat_id = '$catgory_id', " .
                "brand_id = '$brand_id', " .
                "shop_price = '$shop_price', " .
                "min_price = '$min_price', " .
                "market_price = '$market_price', " .
                "is_promote = '$is_promote', " .
                "promote_price = '$promote_price', " .
                "promote_start_date = '$promote_start_date', " .
                "suppliers_id = '$suppliers_id', " .
                "promote_end_date = '$promote_end_date', ";

        /* 如果有上传图片，需要更新数据库 */
        if ($goods_img)
        {
            $sql .= "goods_img = '$goods_img', original_img = '$original_img', ";
        }
        if ($goods_thumb)
        {
            $sql .= "goods_thumb = '$goods_thumb', ";
        }
        if ($code != '')
        {
            $sql .= "is_real=0, extension_code='$code', ";
        }
        $sql .= "keywords = '$_POST[keywords]', " .
                "goods_brief = '$_POST[goods_brief]', " .
                "seller_note = '$_POST[seller_note]', " .
                "goods_weight = '$goods_weight'," .
                "goods_number = '$goods_number', " .
                "warn_number = '$warn_number', " .
                "integral = '$_POST[integral]', " .
                "give_integral = '$give_integral', " .
                "rank_integral = '$rank_integral', " .
                "is_best = '$is_best', " .
                "is_new = '$is_new', " .
                "is_hot = '$is_hot', " .
                "is_on_sale = '$is_on_sale', " .
                "is_alone_sale = '$is_alone_sale', " .
                "is_shipping = '$is_shipping', " .
                "goods_desc = '$_POST[goods_desc]', " .
                "last_update = '". gmtime() ."', ".
                "goods_type = '$goods_type', " .
                "payment_ids = '$payments_ids', " .
                "supplier_id = '$warehouse_id', " .
                "erpcat_id = '$erpcat_id', " .
                "kjt_goods_id = '$kjt_goods_id', " .
                "kjt_price = '$kjt_price', " .
                "kjt_tariffrate = '$kjt_tariffrate', " .
                "max_number = '$max_number', " .
                "goods_code = '$goods_code', " .
                "goods_barcode = '$goods_barcode', " .
                "overseas_logo = '$overseas_logo', " .
                "is_immediately = '$is_immediately' " .
                "WHERE goods_id = '$_REQUEST[goods_id]' LIMIT 1";
    }
    $db->query($sql);

    /* 商品编号 */
    $goods_id = $is_insert ? $db->insert_id() : $_REQUEST['goods_id'];

    /* 记录日志 */
    if ($is_insert)
    {
        admin_log($_POST['goods_name'], 'add', 'goods');
    }
    else
    {
        admin_log($_POST['goods_name'], 'edit', 'goods');
    }

    /* 处理属性 */
    if ((isset($_POST['attr_id_list']) && isset($_POST['attr_value_list'])) || (empty($_POST['attr_id_list']) && empty($_POST['attr_value_list'])))
    {
        // 取得原有的属性值
        $goods_attr_list = array();

        $keywords_arr = explode(" ", $_POST['keywords']);

        $keywords_arr = array_flip($keywords_arr);
        if (isset($keywords_arr['']))
        {
            unset($keywords_arr['']);
        }

        $sql = "SELECT attr_id, attr_index FROM " . $ecs->table('attribute') . " WHERE cat_id = '$goods_type'";

        $attr_res = $db->query($sql);

        $attr_list = array();

        while ($row = $db->fetchRow($attr_res))
        {
            $attr_list[$row['attr_id']] = $row['attr_index'];
        }

        $sql = "SELECT g.*, a.attr_type
                FROM " . $ecs->table('goods_attr') . " AS g
                    LEFT JOIN " . $ecs->table('attribute') . " AS a
                        ON a.attr_id = g.attr_id
                WHERE g.goods_id = '$goods_id'";

        $res = $db->query($sql);

        while ($row = $db->fetchRow($res))
        {
            $goods_attr_list[$row['attr_id']][$row['attr_value']] = array('sign' => 'delete', 'goods_attr_id' => $row['goods_attr_id']);
        }
        // 循环现有的，根据原有的做相应处理
        if(isset($_POST['attr_id_list']))
        {
            foreach ($_POST['attr_id_list'] AS $key => $attr_id)
            {
                $attr_value = $_POST['attr_value_list'][$key];
                $attr_price = $_POST['attr_price_list'][$key];
                if (!empty($attr_value))
                {
                    if (isset($goods_attr_list[$attr_id][$attr_value]))
                    {
                        // 如果原来有，标记为更新
                        $goods_attr_list[$attr_id][$attr_value]['sign'] = 'update';
                        $goods_attr_list[$attr_id][$attr_value]['attr_price'] = $attr_price;
                    }
                    else
                    {
                        // 如果原来没有，标记为新增
                        $goods_attr_list[$attr_id][$attr_value]['sign'] = 'insert';
                        $goods_attr_list[$attr_id][$attr_value]['attr_price'] = $attr_price;
                    }
                    $val_arr = explode(' ', $attr_value);
                    foreach ($val_arr AS $k => $v)
                    {
                        if (!isset($keywords_arr[$v]) && $attr_list[$attr_id] == "1")
                        {
                            $keywords_arr[$v] = $v;
                        }
                    }
                }
            }
        }
        $keywords = join(' ', array_flip($keywords_arr));

        $sql = "UPDATE " .$ecs->table('goods'). " SET keywords = '$keywords' WHERE goods_id = '$goods_id' LIMIT 1";

        $db->query($sql);

        /* 插入、更新、删除数据 */
        foreach ($goods_attr_list as $attr_id => $attr_value_list)
        {
            foreach ($attr_value_list as $attr_value => $info)
            {
                if ($info['sign'] == 'insert')
                {
                    $sql = "INSERT INTO " .$ecs->table('goods_attr'). " (attr_id, goods_id, attr_value, attr_price)".
                            "VALUES ('$attr_id', '$goods_id', '$attr_value', '$info[attr_price]')";
                }
                elseif ($info['sign'] == 'update')
                {
                    $sql = "UPDATE " .$ecs->table('goods_attr'). " SET attr_price = '$info[attr_price]' WHERE goods_attr_id = '$info[goods_attr_id]' LIMIT 1";
                }
                else
                {
                    $sql = "DELETE FROM " .$ecs->table('goods_attr'). " WHERE goods_attr_id = '$info[goods_attr_id]' LIMIT 1";
                }
                $db->query($sql);
            }
        }
    }

    /* 处理会员价格 */
    if (isset($_POST['user_rank']) && isset($_POST['user_price']))
    {
        handle_member_price($goods_id, $_POST['user_rank'], $_POST['user_price']);
    }

    /* 处理优惠价格 */
    if (isset($_POST['volume_number']) && isset($_POST['volume_price']))
    {
        $temp_num = array_count_values($_POST['volume_number']);
        foreach($temp_num as $v)
        {
            if ($v > 1)
            {
                sys_msg($_LANG['volume_number_continuous'], 1, array(), false);
                break;
            }
        }
        handle_volume_price($goods_id, $_POST['volume_number'], $_POST['volume_price']);
    }

    /* 处理扩展分类 */
    if (isset($_POST['other_cat']))
    {
        handle_other_cat($goods_id, array_unique($_POST['other_cat']));
    }

    if ($is_insert)
    {
        /* 处理关联商品 */
        handle_link_goods($goods_id);

        /* 处理组合商品 */
        handle_group_goods($goods_id);

        /* 处理关联文章 */
        handle_goods_article($goods_id);
    }

    /* 重新格式化图片名称 */
    $original_img = reformat_image_name('goods', $goods_id, $original_img, 'source');
    $goods_img = reformat_image_name('goods', $goods_id, $goods_img, 'goods');
    $goods_thumb = reformat_image_name('goods_thumb', $goods_id, $goods_thumb, 'thumb');
    if ($goods_img !== false)
    {
        $db->query("UPDATE " . $ecs->table('goods') . " SET goods_img = '$goods_img' WHERE goods_id='$goods_id'");
    }

    if ($original_img !== false)
    {
        $db->query("UPDATE " . $ecs->table('goods') . " SET original_img = '$original_img' WHERE goods_id='$goods_id'");
    }

    if ($goods_thumb !== false)
    {
        $db->query("UPDATE " . $ecs->table('goods') . " SET goods_thumb = '$goods_thumb' WHERE goods_id='$goods_id'");
    }

    /* 如果有图片，把商品图片加入图片相册 */
    if (isset($img))
    {
        /* 重新格式化图片名称 */
        if (empty($is_url_goods_img))
        {
            $img = reformat_image_name('gallery', $goods_id, $img, 'source');
            $gallery_img = reformat_image_name('gallery', $goods_id, $gallery_img, 'goods');
        }
        else
        {
            $img = $url_goods_img;
            $gallery_img = $url_goods_img;
        }

        $gallery_thumb = reformat_image_name('gallery_thumb', $goods_id, $gallery_thumb, 'thumb');
        $sql = "INSERT INTO " . $ecs->table('goods_gallery') . " (goods_id, img_url, img_desc, thumb_url, img_original) " .
                "VALUES ('$goods_id', '$gallery_img', '', '$gallery_thumb', '$img')";
        $db->query($sql);
    }

    /* 处理相册图片 */
    handle_gallery_image($goods_id, $_FILES['img_url'], $_POST['img_desc'], $_POST['img_file']);

    /* 编辑时处理相册图片描述 */
    if (!$is_insert && isset($_POST['old_img_desc']))
    {
        foreach ($_POST['old_img_desc'] AS $img_id => $img_desc)
        {
            $sql = "UPDATE " . $ecs->table('goods_gallery') . " SET img_desc = '$img_desc' WHERE img_id = '$img_id' LIMIT 1";
            $db->query($sql);
        }
    }

    /* 不保留商品原图的时候删除原图 */
    if ($proc_thumb && !$_CFG['retain_original_img'] && !empty($original_img))
    {
        $db->query("UPDATE " . $ecs->table('goods') . " SET original_img='' WHERE `goods_id`='{$goods_id}'");
        $db->query("UPDATE " . $ecs->table('goods_gallery') . " SET img_original='' WHERE `goods_id`='{$goods_id}'");
        @unlink('../' . $original_img);
        @unlink('../' . $img);
    }

    /* 记录上一次选择的分类和品牌 */
    setcookie('ECSCP[last_choose]', $catgory_id . '|' . $brand_id, gmtime() + 86400);
    /* 清空缓存 */
    clear_cache_files();

    /* 提示页面 */
    $link = array();
    if (check_goods_specifications_exist($goods_id))
    {
        $link[0] = array('href' => 'goods.php?act=product_list&goods_id=' . $goods_id, 'text' => $_LANG['product']);
    }
    if ($code == 'virtual_card')
    {
        $link[1] = array('href' => 'virtual_card.php?act=replenish&goods_id=' . $goods_id, 'text' => $_LANG['add_replenish']);
    }
    if ($is_insert)
    {
        $link[2] = add_link($code);
    }
    $link[3] = list_link($is_insert, $code);


    //$key_array = array_keys($link);
    for($i=0;$i<count($link);$i++)
    {
       $key_array[]=$i;
    }
    krsort($link);
    $link = array_combine($key_array, $link);


    sys_msg($is_insert ? $_LANG['add_goods_ok'] : $_LANG['edit_goods_ok'], 0, $link);
}

/*------------------------------------------------------ */
//-- 批量操作
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'batch')
{
    $code = empty($_REQUEST['extension_code'])? '' : trim($_REQUEST['extension_code']);

    /* 取得要操作的商品编号 */
    $goods_id = !empty($_POST['checkboxes']) ? join(',', $_POST['checkboxes']) : 0;

    if (isset($_POST['type']))
    {
        /* 放入回收站 */
        if ($_POST['type'] == 'trash')
        {
            /* 检查权限 */
            admin_priv('remove_back');

            update_goods($goods_id, 'is_delete', '1');

            /* 记录日志 */
            admin_log('', 'batch_trash', 'goods');
        }
        /* 上架 */
        elseif ($_POST['type'] == 'on_sale')
        {
            /* 检查权限 */
            admin_priv('goods_manage');
            $flag = '1';
            if($code == 'goods_members'){
                $flag = '2';
            }
            update_goods($goods_id, 'is_on_sale', $flag);
        }

        /* 下架 */
        elseif ($_POST['type'] == 'not_on_sale')
        {
            /* 检查权限 */
            admin_priv('goods_manage');
            update_goods($goods_id, 'is_on_sale', '0');
        }

        /* 设为精品 */
        elseif ($_POST['type'] == 'best')
        {
            /* 检查权限 */
            admin_priv('goods_manage');
            update_goods($goods_id, 'is_best', '1');
        }

        /* 取消精品 */
        elseif ($_POST['type'] == 'not_best')
        {
            /* 检查权限 */
            admin_priv('goods_manage');
            update_goods($goods_id, 'is_best', '0');
        }

        /* 设为新品 */
        elseif ($_POST['type'] == 'new')
        {
            /* 检查权限 */
            admin_priv('goods_manage');
            update_goods($goods_id, 'is_new', '1');
        }

        /* 取消新品 */
        elseif ($_POST['type'] == 'not_new')
        {
            /* 检查权限 */
            admin_priv('goods_manage');
            update_goods($goods_id, 'is_new', '0');
        }

        /* 设为热销 */
        elseif ($_POST['type'] == 'hot')
        {
            /* 检查权限 */
            admin_priv('goods_manage');
            update_goods($goods_id, 'is_hot', '1');
        }

        /* 取消热销 */
        elseif ($_POST['type'] == 'not_hot')
        {
            /* 检查权限 */
            admin_priv('goods_manage');
            update_goods($goods_id, 'is_hot', '0');
        }

        /* 转移到分类 */
        elseif ($_POST['type'] == 'move_to')
        {
            /* 检查权限 */
            admin_priv('goods_manage');
            update_goods($goods_id, 'cat_id', $_POST['target_cat']);
        }

        /* 转移到供货商 */
        elseif ($_POST['type'] == 'suppliers_move_to')
        {
            /* 检查权限 */
            admin_priv('goods_manage');
            update_goods($goods_id, 'suppliers_id', $_POST['suppliers_id']);
        }

        /* 还原 */
        elseif ($_POST['type'] == 'restore')
        {
            /* 检查权限 */
            admin_priv('remove_back');

            update_goods($goods_id, 'is_delete', '0');

            /* 记录日志 */
            admin_log('', 'batch_restore', 'goods');
        }
        /* 删除 */
        elseif ($_POST['type'] == 'drop')
        {
            /* 检查权限 */
            admin_priv('remove_back');

            delete_goods($goods_id);

            /* 记录日志 */
            admin_log('', 'batch_remove', 'goods');
        }
    }

    /* 清除缓存 */
    clear_cache_files();

    if ($_POST['type'] == 'drop' || $_POST['type'] == 'restore')
    {
        $link[] = array('href' => 'goods.php?act=trash', 'text' => $_LANG['11_goods_trash']);
    }
    else
    {
        $link[] = list_link(true, $code);
    }
    sys_msg($_LANG['batch_handle_ok'], 0, $link);
}

/*------------------------------------------------------ */
//-- 显示图片
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'show_image')
{

    if (isset($GLOBALS['shop_id']) && $GLOBALS['shop_id'] > 0)
    {
        $img_url = $_GET['img_url'];
    }
    else
    {
        if (strpos($_GET['img_url'], 'http://') === 0)
        {
            $img_url = $_GET['img_url'];
        }
        else
        {
            $img_url = '../' . $_GET['img_url'];
        }
    }
    $smarty->assign('img_url', $img_url);
    $smarty->display('goods_show_image.htm');
}

/*------------------------------------------------------ */
//-- 修改商品名称
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'edit_goods_name')
{
    check_authz_json('goods_manage');

    $goods_id   = intval($_POST['id']);
    $goods_name = json_str_iconv(trim($_POST['val']));

    if ($exc->edit("goods_name = '$goods_name', last_update=" .gmtime(), $goods_id))
    {
        clear_cache_files();
        make_json_result(stripslashes($goods_name));
    }
}

/*------------------------------------------------------ */
//-- 修改商品货号
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'edit_goods_sn')
{
    check_authz_json('goods_manage');

    $goods_id = intval($_POST['id']);
    $goods_sn = json_str_iconv(trim($_POST['val']));

    /* 检查是否重复 */
    if (!$exc->is_only('goods_sn', $goods_sn, $goods_id))
    {
        make_json_error($_LANG['goods_sn_exists']);
    }
    $sql="SELECT goods_id FROM ". $ecs->table('products')."WHERE product_sn='$goods_sn'";
    if($db->getOne($sql))
    {
        make_json_error($_LANG['goods_sn_exists']);
    }
    if ($exc->edit("goods_sn = '$goods_sn', last_update=" .gmtime(), $goods_id))
    {
        clear_cache_files();
        make_json_result(stripslashes($goods_sn));
    }
}

elseif ($_REQUEST['act'] == 'check_goods_sn')
{
    check_authz_json('goods_manage');

    $goods_id = intval($_REQUEST['goods_id']);
    $goods_sn = htmlspecialchars(json_str_iconv(trim($_REQUEST['goods_sn'])));

    /* 检查是否重复 */
    if (!$exc->is_only('goods_sn', $goods_sn, $goods_id))
    {
        make_json_error($_LANG['goods_sn_exists']);
    }
    if(!empty($goods_sn))
    {
        $sql="SELECT goods_id FROM ". $ecs->table('products')."WHERE product_sn='$goods_sn'";
        if($db->getOne($sql))
        {
            make_json_error($_LANG['goods_sn_exists']);
        }
    }
    make_json_result('');
}
elseif ($_REQUEST['act'] == 'check_products_goods_sn')
{
    check_authz_json('goods_manage');

    $goods_id = intval($_REQUEST['goods_id']);
    $goods_sn = json_str_iconv(trim($_REQUEST['goods_sn']));
    $products_sn=explode('||',$goods_sn);
    if(!is_array($products_sn))
    {
        make_json_result('');
    }
    else
    {
        foreach ($products_sn as $val)
        {
            if(empty($val))
            {
                 continue;
            }
            if(is_array($int_arry))
            {
                if(in_array($val,$int_arry))
                {
                     make_json_error($val.$_LANG['goods_sn_exists']);
                }
            }
            $int_arry[]=$val;
            if (!$exc->is_only('goods_sn', $val, '0'))
            {
                make_json_error($val.$_LANG['goods_sn_exists']);
            }
            $sql="SELECT goods_id FROM ". $ecs->table('products')."WHERE product_sn='$val'";
            if($db->getOne($sql))
            {
                make_json_error($val.$_LANG['goods_sn_exists']);
            }
        }
    }
    /* 检查是否重复 */
    make_json_result('');
}

/*------------------------------------------------------ */
//-- 修改商品价格
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'edit_goods_price')
{
    check_authz_json('goods_manage');

    $goods_id       = intval($_POST['id']);
    $goods_price    = floatval($_POST['val']);
    $price_rate     = floatval($_CFG['market_price_rate'] * $goods_price);

    $sql = "SELECT min_price FROM " . $GLOBALS['ecs']->table('goods') . " WHERE goods_id=$goods_id";
    $min_price = $GLOBALS['db']->getOne($sql);
    if($goods_price < $min_price)
    {
        make_json_error("商品售价必须不低于最低价格");
    }
    if ($goods_price < 0 || $goods_price == 0 && $_POST['val'] != "$goods_price")
    {
        make_json_error($_LANG['shop_price_invalid']);
    }
    else
    {
        if ($exc->edit("shop_price = '$goods_price', market_price = '$price_rate', last_update=" .gmtime(), $goods_id))
        {
            clear_cache_files();
            make_json_result(number_format($goods_price, 2, '.', ''));
        }
    }
}

/*------------------------------------------------------ */
//-- 修改商品库存警告数量
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'edit_warn_number')
{
    check_authz_json('goods_manage');

    $goods_id   = intval($_POST['id']);
    $warn_num  = intval($_POST['val']);

    if($warn_num < 0 || $warn_num == 0 && $_POST['val'] != "$warn_num")
    {
        make_json_error('商品库存警告数量错误');
    }

    if ($exc->edit("warn_number = '$warn_num', last_update=" .gmtime(), $goods_id))
    {
        clear_cache_files();
        make_json_result($warn_num);
    }
}

/*------------------------------------------------------ */
//-- 修改商品库存数量
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'edit_goods_number')
{
    check_authz_json('goods_manage');

    $goods_id   = intval($_POST['id']);
    $goods_num  = intval($_POST['val']);

    if($goods_num < 0 || $goods_num == 0 && $_POST['val'] != "$goods_num")
    {
        make_json_error($_LANG['goods_number_error']);
    }

    if(check_goods_product_exist($goods_id) == 1)
    {
        make_json_error($_LANG['sys']['wrong'] . $_LANG['cannot_goods_number']);
    }

    if ($exc->edit("goods_number = '$goods_num', last_update=" .gmtime(), $goods_id))
    {
        clear_cache_files();
        make_json_result($goods_num);
    }
}

/*------------------------------------------------------ */
//-- 修改上架状态
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'toggle_on_sale')
{
    check_authz_json('goods_manage');

    $goods_id       = intval($_POST['id']);
    $on_sale        = intval($_POST['val']);
    //如果是会员充值商品is_on_sale值设为2
    $is_membersgoods = is_membersgoods($goods_id);
    if($is_membersgoods && $on_sale == 1){
        $on_sale = 2;
    }

    if ($exc->edit("is_on_sale = '$on_sale', last_update=" .gmtime(), $goods_id))
    {
        clear_cache_files();
        make_json_result($on_sale);
    }
}

/*------------------------------------------------------ */
//-- 修改精品推荐状态
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'toggle_best')
{
    check_authz_json('goods_manage');

    $goods_id       = intval($_POST['id']);
    $is_best        = intval($_POST['val']);

    if ($exc->edit("is_best = '$is_best', last_update=" .gmtime(), $goods_id))
    {
        clear_cache_files();
        make_json_result($is_best);
    }
}

/*------------------------------------------------------ */
//-- 修改新品推荐状态
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'toggle_new')
{
    check_authz_json('goods_manage');

    $goods_id       = intval($_POST['id']);
    $is_new         = intval($_POST['val']);

    if ($exc->edit("is_new = '$is_new', last_update=" .gmtime(), $goods_id))
    {
        clear_cache_files();
        make_json_result($is_new);
    }
}

/*------------------------------------------------------ */
//-- 修改热销推荐状态
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'toggle_hot')
{
    check_authz_json('goods_manage');

    $goods_id       = intval($_POST['id']);
    $is_hot         = intval($_POST['val']);

    if ($exc->edit("is_hot = '$is_hot', last_update=" .gmtime(), $goods_id))
    {
        clear_cache_files();
        make_json_result($is_hot);
    }
}

/*------------------------------------------------------ */
//-- 修改商品排序
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'edit_sort_order')
{
    check_authz_json('goods_manage');

    $goods_id       = intval($_POST['id']);
    $sort_order     = intval($_POST['val']);

    if ($exc->edit("sort_order = '$sort_order', last_update=" .gmtime(), $goods_id))
    {
        clear_cache_files();
        make_json_result($sort_order);
    }
}

/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    $is_delete = empty($_REQUEST['is_delete']) ? 0 : intval($_REQUEST['is_delete']);
    $code = empty($_REQUEST['extension_code']) ? '' : trim($_REQUEST['extension_code']);
    $goods_list = goods_list($is_delete, ($code=='') ? 1 : 0);

    $handler_list = array();
    $handler_list['virtual_card'][] = array('url'=>'virtual_card.php?act=card', 'title'=>$_LANG['card'], 'img'=>'icon_send_bonus.gif');
    $handler_list['virtual_card'][] = array('url'=>'virtual_card.php?act=replenish', 'title'=>$_LANG['replenish'], 'img'=>'icon_add.gif');
    $handler_list['virtual_card'][] = array('url'=>'virtual_card.php?act=batch_card_add', 'title'=>$_LANG['batch_card_add'], 'img'=>'icon_output.gif');

    if (isset($handler_list[$code]))
    {
        $smarty->assign('add_handler',      $handler_list[$code]);
    }
    $smarty->assign('code',         $code);
    $smarty->assign('goods_list',   $goods_list['goods']);
    $smarty->assign('filter',       $goods_list['filter']);
    $smarty->assign('record_count', $goods_list['record_count']);
    $smarty->assign('page_count',   $goods_list['page_count']);
    $smarty->assign('list_type',    $is_delete ? 'trash' : 'goods');
    $smarty->assign('use_storage',  empty($_CFG['use_storage']) ? 0 : 1);

    /* 排序标记 */
    $sort_flag  = sort_flag($goods_list['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    /* 获取商品类型存在规格的类型 */
    $specifications = get_goods_type_specifications();
    $smarty->assign('specifications', $specifications);

    $tpl = $is_delete ? 'goods_trash.htm' : 'goods_list.htm';

    make_json_result($smarty->fetch($tpl), '',
        array('filter' => $goods_list['filter'], 'page_count' => $goods_list['page_count']));
}

/*------------------------------------------------------ */
//-- 放入回收站
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'remove')
{
    $goods_id = intval($_REQUEST['id']);

    /* 检查权限 */
    check_authz_json('remove_back');

    if ($exc->edit("is_delete = 1", $goods_id))
    {
        clear_cache_files();
        $goods_name = $exc->get_name($goods_id);

        admin_log(addslashes($goods_name), 'trash', 'goods'); // 记录日志

        $url = 'goods.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);

        ecs_header("Location: $url\n");
        exit;
    }
}

/*------------------------------------------------------ */
//-- 还原回收站中的商品
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'restore_goods')
{
    $goods_id = intval($_REQUEST['id']);

    check_authz_json('remove_back'); // 检查权限

    $exc->edit("is_delete = 0, add_time = '" . gmtime() . "'", $goods_id);
    clear_cache_files();

    $goods_name = $exc->get_name($goods_id);

    admin_log(addslashes($goods_name), 'restore', 'goods'); // 记录日志

    $url = 'goods.php?act=query&' . str_replace('act=restore_goods', '', $_SERVER['QUERY_STRING']);

    ecs_header("Location: $url\n");
    exit;
}

/*------------------------------------------------------ */
//-- 彻底删除商品
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'drop_goods')
{
    // 检查权限
    check_authz_json('remove_back');

    // 取得参数
    $goods_id = intval($_REQUEST['id']);
    if ($goods_id <= 0)
    {
        make_json_error('invalid params');
    }

    /* 取得商品信息 */
    $sql = "SELECT goods_id, goods_name, is_delete, is_real, goods_thumb, " .
                "goods_img, original_img " .
            "FROM " . $ecs->table('goods') .
            " WHERE goods_id = '$goods_id'";
    $goods = $db->getRow($sql);
    if (empty($goods))
    {
        make_json_error($_LANG['goods_not_exist']);
    }

    if ($goods['is_delete'] != 1)
    {
        make_json_error($_LANG['goods_not_in_recycle_bin']);
    }

    /* 删除商品图片和轮播图片 */
    if (!empty($goods['goods_thumb']))
    {
        @unlink('../' . $goods['goods_thumb']);
    }
    if (!empty($goods['goods_img']))
    {
        @unlink('../' . $goods['goods_img']);
    }
    if (!empty($goods['original_img']))
    {
        @unlink('../' . $goods['original_img']);
    }
    /* 删除商品 */
    $exc->drop($goods_id);

    /* 删除商品的货品记录 */
    $sql = "DELETE FROM " . $ecs->table('products') .
            " WHERE goods_id = '$goods_id'";
    $db->query($sql);

    /* 记录日志 */
    admin_log(addslashes($goods['goods_name']), 'remove', 'goods');

    /* 删除商品相册 */
    $sql = "SELECT img_url, thumb_url, img_original " .
            "FROM " . $ecs->table('goods_gallery') .
            " WHERE goods_id = '$goods_id'";
    $res = $db->query($sql);
    while ($row = $db->fetchRow($res))
    {
        if (!empty($row['img_url']))
        {
            @unlink('../' . $row['img_url']);
        }
        if (!empty($row['thumb_url']))
        {
            @unlink('../' . $row['thumb_url']);
        }
        if (!empty($row['img_original']))
        {
            @unlink('../' . $row['img_original']);
        }
    }

    $sql = "DELETE FROM " . $ecs->table('goods_gallery') . " WHERE goods_id = '$goods_id'";
    $db->query($sql);

    /* 删除相关表记录 */
    $sql = "DELETE FROM " . $ecs->table('collect_goods') . " WHERE goods_id = '$goods_id'";
    $db->query($sql);
    $sql = "DELETE FROM " . $ecs->table('goods_article') . " WHERE goods_id = '$goods_id'";
    $db->query($sql);
    $sql = "DELETE FROM " . $ecs->table('goods_attr') . " WHERE goods_id = '$goods_id'";
    $db->query($sql);
    $sql = "DELETE FROM " . $ecs->table('goods_cat') . " WHERE goods_id = '$goods_id'";
    $db->query($sql);
    $sql = "DELETE FROM " . $ecs->table('member_price') . " WHERE goods_id = '$goods_id'";
    $db->query($sql);
    $sql = "DELETE FROM " . $ecs->table('group_goods') . " WHERE parent_id = '$goods_id'";
    $db->query($sql);
    $sql = "DELETE FROM " . $ecs->table('group_goods') . " WHERE goods_id = '$goods_id'";
    $db->query($sql);
    $sql = "DELETE FROM " . $ecs->table('link_goods') . " WHERE goods_id = '$goods_id'";
    $db->query($sql);
    $sql = "DELETE FROM " . $ecs->table('link_goods') . " WHERE link_goods_id = '$goods_id'";
    $db->query($sql);
    $sql = "DELETE FROM " . $ecs->table('tag') . " WHERE goods_id = '$goods_id'";
    $db->query($sql);
    $sql = "DELETE FROM " . $ecs->table('comment') . " WHERE comment_type = 0 AND id_value = '$goods_id'";
    $db->query($sql);
    $sql = "DELETE FROM " . $ecs->table('collect_goods') . " WHERE goods_id = '$goods_id'";
    $db->query($sql);
    $sql = "DELETE FROM " . $ecs->table('booking_goods') . " WHERE goods_id = '$goods_id'";
    $db->query($sql);
    $sql = "DELETE FROM " . $ecs->table('goods_activity') . " WHERE goods_id = '$goods_id'";
    $db->query($sql);

    /* 如果不是实体商品，删除相应虚拟商品记录 */
    if ($goods['is_real'] != 1)
    {
        $sql = "DELETE FROM " . $ecs->table('virtual_card') . " WHERE goods_id = '$goods_id'";
        if (!$db->query($sql, 'SILENT') && $db->errno() != 1146)
        {
            die($db->error());
        }
    }

    clear_cache_files();
    $url = 'goods.php?act=query&' . str_replace('act=drop_goods', '', $_SERVER['QUERY_STRING']);

    ecs_header("Location: $url\n");

    exit;
}

/*------------------------------------------------------ */
//-- 切换商品类型
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'get_attr')
{
    check_authz_json('goods_manage');

    $goods_id   = empty($_GET['goods_id']) ? 0 : intval($_GET['goods_id']);
    $goods_type = empty($_GET['goods_type']) ? 0 : intval($_GET['goods_type']);

    $content    = build_attr_html($goods_type, $goods_id);

    make_json_result($content);
}

/*------------------------------------------------------ */
//-- 删除图片
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'drop_image')
{
    check_authz_json('goods_manage');

    $img_id = empty($_REQUEST['img_id']) ? 0 : intval($_REQUEST['img_id']);

    /* 删除图片文件 */
    $sql = "SELECT img_url, thumb_url, img_original " .
            " FROM " . $GLOBALS['ecs']->table('goods_gallery') .
            " WHERE img_id = '$img_id'";
    $row = $GLOBALS['db']->getRow($sql);

    if ($row['img_url'] != '' && is_file('../' . $row['img_url']))
    {
        @unlink('../' . $row['img_url']);
    }
    if ($row['thumb_url'] != '' && is_file('../' . $row['thumb_url']))
    {
        @unlink('../' . $row['thumb_url']);
    }
    if ($row['img_original'] != '' && is_file('../' . $row['img_original']))
    {
        @unlink('../' . $row['img_original']);
    }

    /* 删除数据 */
    $sql = "DELETE FROM " . $GLOBALS['ecs']->table('goods_gallery') . " WHERE img_id = '$img_id' LIMIT 1";
    $GLOBALS['db']->query($sql);

    clear_cache_files();
    make_json_result($img_id);
}

/*------------------------------------------------------ */
//-- 搜索商品，仅返回名称及ID
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'get_goods_list')
{
    include_once(ROOT_PATH . 'includes/cls_json.php');
    $json = new JSON;

    $filters = $json->decode($_GET['JSON']);
    if($_GET['gcid'] > 0)
    {
        $gcid = $_GET['gcid'];
        $sql = "SELECT goods_id FROM " . $GLOBALS['ecs']->table('goods') .
            " WHERE gcid = '$gcid'";
        $r = $GLOBALS['db']->getAll($sql);
        foreach($r AS $value)
        {
            $goods[] = $value['goods_id'];
        }

        $filters->gcid=0;
    }
    $arr = get_goods_list($filters);
    $opt = array();
    if (empty($arr))
    {
        $opt[] = array('value' => 0,
            'text' => "没有找到相应记录，请重新搜索",
            'data' => 0);
    }
    foreach ($arr AS $key => $val)
    {
        if(in_array($val['goods_id'],$goods))
        {
            continue;
        }
        $opt[] = array('value' => $val['goods_id'],
                        'text' => $val['goods_name'],
                        'data' => $val['shop_price']);
    }

    make_json_result($opt);
}

/*------------------------------------------------------ */
//-- 把商品加入关联
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'add_link_goods')
{
    include_once(ROOT_PATH . 'includes/cls_json.php');
    $json = new JSON;

    check_authz_json('goods_manage');

    $linked_array   = $json->decode($_GET['add_ids']);
    $linked_goods   = $json->decode($_GET['JSON']);
    $goods_id       = $linked_goods[0];
    $is_double      = $linked_goods[1] == true ? 0 : 1;

    foreach ($linked_array AS $val)
    {
        if ($is_double)
        {
            /* 双向关联 */
            $sql = "INSERT INTO " . $ecs->table('link_goods') . " (goods_id, link_goods_id, is_double, admin_id) " .
                    "VALUES ('$val', '$goods_id', '$is_double', '$_SESSION[admin_id]')";
            $db->query($sql, 'SILENT');
        }

        $sql = "INSERT INTO " . $ecs->table('link_goods') . " (goods_id, link_goods_id, is_double, admin_id) " .
                "VALUES ('$goods_id', '$val', '$is_double', '$_SESSION[admin_id]')";
        $db->query($sql, 'SILENT');
    }

    $linked_goods   = get_linked_goods($goods_id);
    $options        = array();

    foreach ($linked_goods AS $val)
    {
        $options[] = array('value'  => $val['goods_id'],
                        'text'      => $val['goods_name'],
                        'data'      => '');
    }

    clear_cache_files();
    make_json_result($options);
}

/*------------------------------------------------------ */
//-- 删除关联商品
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'drop_link_goods')
{
    include_once(ROOT_PATH . 'includes/cls_json.php');
    $json = new JSON;

    check_authz_json('goods_manage');

    $drop_goods     = $json->decode($_GET['drop_ids']);
    $drop_goods_ids = db_create_in($drop_goods);
    $linked_goods   = $json->decode($_GET['JSON']);
    $goods_id       = $linked_goods[0];
    $is_signle      = $linked_goods[1];

    if (!$is_signle)
    {
        $sql = "DELETE FROM " .$ecs->table('link_goods') .
                " WHERE link_goods_id = '$goods_id' AND goods_id " . $drop_goods_ids;
    }
    else
    {
        $sql = "UPDATE " .$ecs->table('link_goods') . " SET is_double = 0 ".
                " WHERE link_goods_id = '$goods_id' AND goods_id " . $drop_goods_ids;
    }
    if ($goods_id == 0)
    {
        $sql .= " AND admin_id = '$_SESSION[admin_id]'";
    }
    $db->query($sql);

    $sql = "DELETE FROM " .$ecs->table('link_goods') .
            " WHERE goods_id = '$goods_id' AND link_goods_id " . $drop_goods_ids;
    if ($goods_id == 0)
    {
        $sql .= " AND admin_id = '$_SESSION[admin_id]'";
    }
    $db->query($sql);

    $linked_goods = get_linked_goods($goods_id);
    $options      = array();

    foreach ($linked_goods AS $val)
    {
        $options[] = array(
                        'value' => $val['goods_id'],
                        'text'  => $val['goods_name'],
                        'data'  => '');
    }

    clear_cache_files();
    make_json_result($options);
}

/*------------------------------------------------------ */
//-- 增加一个配件
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'add_group_goods')
{
    include_once(ROOT_PATH . 'includes/cls_json.php');
    $json = new JSON;

    check_authz_json('goods_manage');

    $fittings   = $json->decode($_GET['add_ids']);
    $arguments  = $json->decode($_GET['JSON']);
    $goods_id   = $arguments[0];
    $price      = $arguments[1];

    foreach ($fittings AS $val)
    {
        $sql = "INSERT INTO " . $ecs->table('group_goods') . " (parent_id, goods_id, goods_price, admin_id) " .
                "VALUES ('$goods_id', '$val', '$price', '$_SESSION[admin_id]')";
        $db->query($sql, 'SILENT');
    }

    $arr = get_group_goods($goods_id);
    $opt = array();

    foreach ($arr AS $val)
    {
        $opt[] = array('value'      => $val['goods_id'],
                        'text'      => $val['goods_name'],
                        'data'      => '');
    }

    clear_cache_files();
    make_json_result($opt);
}

/*------------------------------------------------------ */
//-- 删除一个配件
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'drop_group_goods')
{
    include_once(ROOT_PATH . 'includes/cls_json.php');
    $json = new JSON;

    check_authz_json('goods_manage');

    $fittings   = $json->decode($_GET['drop_ids']);
    $arguments  = $json->decode($_GET['JSON']);
    $goods_id   = $arguments[0];
    $price      = $arguments[1];

    $sql = "DELETE FROM " .$ecs->table('group_goods') .
            " WHERE parent_id='$goods_id' AND " .db_create_in($fittings, 'goods_id');
    if ($goods_id == 0)
    {
        $sql .= " AND admin_id = '$_SESSION[admin_id]'";
    }
    $db->query($sql);

    $arr = get_group_goods($goods_id);
    $opt = array();

    foreach ($arr AS $val)
    {
        $opt[] = array('value'      => $val['goods_id'],
                        'text'      => $val['goods_name'],
                        'data'      => '');
    }

    clear_cache_files();
    make_json_result($opt);
}

/*------------------------------------------------------ */
//-- 搜索文章
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'get_article_list')
{
    include_once(ROOT_PATH . 'includes/cls_json.php');
    $json = new JSON;

    $filters =(array) $json->decode(json_str_iconv($_GET['JSON']));

    $where = " WHERE cat_id > 0 ";
    if (!empty($filters['title']))
    {
        $keyword  = trim($filters['title']);
        $where   .=  " AND title LIKE '%" . mysql_like_quote($keyword) . "%' ";
    }

    $sql        = 'SELECT article_id, title FROM ' .$ecs->table('article'). $where.
                  'ORDER BY article_id DESC LIMIT 50';
    $res        = $db->query($sql);
    $arr        = array();

    while ($row = $db->fetchRow($res))
    {
        $arr[]  = array('value' => $row['article_id'], 'text' => $row['title'], 'data'=>'');
    }

    make_json_result($arr);
}

/*------------------------------------------------------ */
//-- 添加关联文章
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'add_goods_article')
{
    include_once(ROOT_PATH . 'includes/cls_json.php');
    $json = new JSON;

    check_authz_json('goods_manage');

    $articles   = $json->decode($_GET['add_ids']);
    $arguments  = $json->decode($_GET['JSON']);
    $goods_id   = $arguments[0];

    foreach ($articles AS $val)
    {
        $sql = "INSERT INTO " . $ecs->table('goods_article') . " (goods_id, article_id, admin_id) " .
                "VALUES ('$goods_id', '$val', '$_SESSION[admin_id]')";
        $db->query($sql);
    }

    $arr = get_goods_articles($goods_id);
    $opt = array();

    foreach ($arr AS $val)
    {
        $opt[] = array('value'      => $val['article_id'],
                        'text'      => $val['title'],
                        'data'      => '');
    }

    clear_cache_files();
    make_json_result($opt);
}

/*------------------------------------------------------ */
//-- 删除关联文章
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'drop_goods_article')
{
    include_once(ROOT_PATH . 'includes/cls_json.php');
    $json = new JSON;

    check_authz_json('goods_manage');

    $articles   = $json->decode($_GET['drop_ids']);
    $arguments  = $json->decode($_GET['JSON']);
    $goods_id   = $arguments[0];

    $sql = "DELETE FROM " .$ecs->table('goods_article') . " WHERE " . db_create_in($articles, "article_id") . " AND goods_id = '$goods_id'";
    $db->query($sql);

    $arr = get_goods_articles($goods_id);
    $opt = array();

    foreach ($arr AS $val)
    {
        $opt[] = array('value'      => $val['article_id'],
                        'text'      => $val['title'],
                        'data'      => '');
    }

    clear_cache_files();
    make_json_result($opt);
}

/*------------------------------------------------------ */
//-- 货品列表
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'product_list')
{
    admin_priv('goods_manage');

    /* 是否存在商品id */
    if (empty($_GET['goods_id']))
    {
        $link[] = array('href' => 'goods.php?act=list', 'text' => $_LANG['cannot_found_goods']);
        sys_msg($_LANG['cannot_found_goods'], 1, $link);
    }
    else
    {
        $goods_id = intval($_GET['goods_id']);
    }

    /* 取出商品信息 */
    $sql = "SELECT goods_sn, goods_name, goods_type, shop_price FROM " . $ecs->table('goods') . " WHERE goods_id = '$goods_id'";
    $goods = $db->getRow($sql);
    if (empty($goods))
    {
        $link[] = array('href' => 'goods.php?act=list', 'text' => $_LANG['01_goods_list']);
        sys_msg($_LANG['cannot_found_goods'], 1, $link);
    }
    $smarty->assign('sn', sprintf($_LANG['good_goods_sn'], $goods['goods_sn']));
    $smarty->assign('price', sprintf($_LANG['good_shop_price'], $goods['shop_price']));
    $smarty->assign('goods_name', sprintf($_LANG['products_title'], $goods['goods_name']));
    $smarty->assign('goods_sn', sprintf($_LANG['products_title_2'], $goods['goods_sn']));


    /* 获取商品规格列表 */
    $attribute = get_goods_specifications_list($goods_id);
    if (empty($attribute))
    {
        $link[] = array('href' => 'goods.php?act=edit&goods_id=' . $goods_id, 'text' => $_LANG['edit_goods']);
        sys_msg($_LANG['not_exist_goods_attr'], 1, $link);
    }
    foreach ($attribute as $attribute_value)
    {
        //转换成数组
        $_attribute[$attribute_value['attr_id']]['attr_values'][] = $attribute_value['attr_value'];
        $_attribute[$attribute_value['attr_id']]['attr_id'] = $attribute_value['attr_id'];
        $_attribute[$attribute_value['attr_id']]['attr_name'] = $attribute_value['attr_name'];
    }
    $attribute_count = count($_attribute);

    $smarty->assign('attribute_count',          $attribute_count);
    $smarty->assign('attribute_count_3',        ($attribute_count + 3));
    $smarty->assign('attribute',                $_attribute);
    $smarty->assign('product_sn',               $goods['goods_sn'] . '_');
    $smarty->assign('product_number',           $_CFG['default_storage']);

    /* 取商品的货品 */
    $product = product_list($goods_id, '');

    $smarty->assign('ur_here',      $_LANG['18_product_list']);
    $smarty->assign('action_link',  array('href' => 'goods.php?act=list', 'text' => $_LANG['01_goods_list']));
    $smarty->assign('product_list', $product['product']);
    $smarty->assign('product_null', empty($product['product']) ? 0 : 1);
    $smarty->assign('use_storage',  empty($_CFG['use_storage']) ? 0 : 1);
    $smarty->assign('goods_id',     $goods_id);
    $smarty->assign('filter',       $product['filter']);
    $smarty->assign('full_page',    1);

    /* 显示商品列表页面 */
    assign_query_info();

    $smarty->display('product_info.htm');
}

/*------------------------------------------------------ */
//-- 货品排序、分页、查询
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'product_query')
{
    /* 是否存在商品id */
    if (empty($_REQUEST['goods_id']))
    {
        make_json_error($_LANG['sys']['wrong'] . $_LANG['cannot_found_goods']);
    }
    else
    {
        $goods_id = intval($_REQUEST['goods_id']);
    }

    /* 取出商品信息 */
    $sql = "SELECT goods_sn, goods_name, goods_type, shop_price FROM " . $ecs->table('goods') . " WHERE goods_id = '$goods_id'";
    $goods = $db->getRow($sql);
    if (empty($goods))
    {
        make_json_error($_LANG['sys']['wrong'] . $_LANG['cannot_found_goods']);
    }
    $smarty->assign('sn', sprintf($_LANG['good_goods_sn'], $goods['goods_sn']));
    $smarty->assign('price', sprintf($_LANG['good_shop_price'], $goods['shop_price']));
    $smarty->assign('goods_name', sprintf($_LANG['products_title'], $goods['goods_name']));
    $smarty->assign('goods_sn', sprintf($_LANG['products_title_2'], $goods['goods_sn']));


    /* 获取商品规格列表 */
    $attribute = get_goods_specifications_list($goods_id);
    if (empty($attribute))
    {
        make_json_error($_LANG['sys']['wrong'] . $_LANG['cannot_found_goods']);
    }
    foreach ($attribute as $attribute_value)
    {
        //转换成数组
        $_attribute[$attribute_value['attr_id']]['attr_values'][] = $attribute_value['attr_value'];
        $_attribute[$attribute_value['attr_id']]['attr_id'] = $attribute_value['attr_id'];
        $_attribute[$attribute_value['attr_id']]['attr_name'] = $attribute_value['attr_name'];
    }
    $attribute_count = count($_attribute);

    $smarty->assign('attribute_count',          $attribute_count);
    $smarty->assign('attribute',                $_attribute);
    $smarty->assign('attribute_count_3',        ($attribute_count + 3));
    $smarty->assign('product_sn',               $goods['goods_sn'] . '_');
    $smarty->assign('product_number',           $_CFG['default_storage']);

    /* 取商品的货品 */
    $product = product_list($goods_id, '');

    $smarty->assign('ur_here', $_LANG['18_product_list']);
    $smarty->assign('action_link', array('href' => 'goods.php?act=list', 'text' => $_LANG['01_goods_list']));
    $smarty->assign('product_list',  $product['product']);
    $smarty->assign('use_storage',  empty($_CFG['use_storage']) ? 0 : 1);
    $smarty->assign('goods_id',    $goods_id);
    $smarty->assign('filter',       $product['filter']);

    /* 排序标记 */
    $sort_flag  = sort_flag($product['filter']);
    $smarty->assign($sort_flag['tag'], $sort_flag['img']);

    make_json_result($smarty->fetch('product_info.htm'), '',
        array('filter' => $product['filter'], 'page_count' => $product['page_count']));
}

/*------------------------------------------------------ */
//-- 货品删除
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'product_remove')
{
    /* 检查权限 */
    check_authz_json('remove_back');

    /* 是否存在商品id */
    if (empty($_REQUEST['id']))
    {
        make_json_error($_LANG['product_id_null']);
    }
    else
    {
        $product_id = intval($_REQUEST['id']);
    }

    /* 货品库存 */
    $product = get_product_info($product_id, 'product_number, goods_id');

    /* 删除货品 */
    $sql = "DELETE FROM " . $ecs->table('products') . " WHERE product_id = '$product_id'";
    $result = $db->query($sql);
    if ($result)
    {
        /* 修改商品库存 */
        if (update_goods_stock($product['goods_id'], $product_number - $product['product_number']))
        {
            //记录日志
            admin_log('', 'update', 'goods');
        }

        //记录日志
        admin_log('', 'trash', 'products');

        $url = 'goods.php?act=product_query&' . str_replace('act=product_remove', '', $_SERVER['QUERY_STRING']);

        ecs_header("Location: $url\n");
        exit;
    }
}

/*------------------------------------------------------ */
//-- 修改货品价格
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'edit_product_sn')
{
    check_authz_json('goods_manage');

    $product_id       = intval($_POST['id']);
    $product_sn       = json_str_iconv(trim($_POST['val']));
    $product_sn       = ($_LANG['n_a'] == $product_sn) ? '' : $product_sn;

    if (check_product_sn_exist($product_sn, $product_id))
    {
        make_json_error($_LANG['sys']['wrong'] . $_LANG['exist_same_product_sn']);
    }

    /* 修改 */
    $sql = "UPDATE " . $ecs->table('products') . " SET product_sn = '$product_sn' WHERE product_id = '$product_id'";
    $result = $db->query($sql);
    if ($result)
    {
        clear_cache_files();
        make_json_result($product_sn);
    }
}

/*------------------------------------------------------ */
//-- 修改货品库存
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'edit_product_number')
{
    check_authz_json('goods_manage');

    $product_id       = intval($_POST['id']);
    $product_number       = intval($_POST['val']);

    /* 货品库存 */
    $product = get_product_info($product_id, 'product_number, goods_id');

    /* 修改货品库存 */
    $sql = "UPDATE " . $ecs->table('products') . " SET product_number = '$product_number' WHERE product_id = '$product_id'";
    $result = $db->query($sql);
    if ($result)
    {
        /* 修改商品库存 */
        if (update_goods_stock($product['goods_id'], $product_number - $product['product_number']))
        {
            clear_cache_files();
            make_json_result($product_number);
        }
    }
}

/*------------------------------------------------------ */
//-- 货品添加 执行
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'product_add_execute')
{
    admin_priv('goods_manage');

    $product['goods_id']        = intval($_POST['goods_id']);
    $product['attr']            = $_POST['attr'];
    $product['product_sn']      = $_POST['product_sn'];
    $product['product_number']  = $_POST['product_number'];

    /* 是否存在商品id */
    if (empty($product['goods_id']))
    {
        sys_msg($_LANG['sys']['wrong'] . $_LANG['cannot_found_goods'], 1, array(), false);
    }

    /* 判断是否为初次添加 */
    $insert = true;
    if (product_number_count($product['goods_id']) > 0)
    {
        $insert = false;
    }

    /* 取出商品信息 */
    $sql = "SELECT goods_sn, goods_name, goods_type, shop_price FROM " . $ecs->table('goods') . " WHERE goods_id = '" . $product['goods_id'] . "'";
    $goods = $db->getRow($sql);
    if (empty($goods))
    {
        sys_msg($_LANG['sys']['wrong'] . $_LANG['cannot_found_goods'], 1, array(), false);
    }

    /*  */
    foreach($product['product_sn'] as $key => $value)
    {
        //过滤
        $product['product_number'][$key] = empty($product['product_number'][$key]) ? (empty($_CFG['use_storage']) ? 0 : $_CFG['default_storage']) : trim($product['product_number'][$key]); //库存

        //获取规格在商品属性表中的id
        foreach($product['attr'] as $attr_key => $attr_value)
        {
            /* 检测：如果当前所添加的货品规格存在空值或0 */
            if (empty($attr_value[$key]))
            {
                continue 2;
            }

            $is_spec_list[$attr_key] = 'true';

            $value_price_list[$attr_key] = $attr_value[$key] . chr(9) . ''; //$key，当前

            $id_list[$attr_key] = $attr_key;
        }
        $goods_attr_id = handle_goods_attr($product['goods_id'], $id_list, $is_spec_list, $value_price_list);

        /* 是否为重复规格的货品 */
        $goods_attr = sort_goods_attr_id_array($goods_attr_id);
        $goods_attr = implode('|', $goods_attr['sort']);
        if (check_goods_attr_exist($goods_attr, $product['goods_id']))
        {
            continue;
            //sys_msg($_LANG['sys']['wrong'] . $_LANG['exist_same_goods_attr'], 1, array(), false);
        }
        //货品号不为空
        if (!empty($value))
        {
            /* 检测：货品货号是否在商品表和货品表中重复 */
            if (check_goods_sn_exist($value))
            {
                continue;
                //sys_msg($_LANG['sys']['wrong'] . $_LANG['exist_same_goods_sn'], 1, array(), false);
            }
            if (check_product_sn_exist($value))
            {
                continue;
                //sys_msg($_LANG['sys']['wrong'] . $_LANG['exist_same_product_sn'], 1, array(), false);
            }
        }

        /* 插入货品表 */
        $sql = "INSERT INTO " . $GLOBALS['ecs']->table('products') . " (goods_id, goods_attr, product_sn, product_number)  VALUES ('" . $product['goods_id'] . "', '$goods_attr', '$value', '" . $product['product_number'][$key] . "')";
        if (!$GLOBALS['db']->query($sql))
        {
            continue;
            //sys_msg($_LANG['sys']['wrong'] . $_LANG['cannot_add_products'], 1, array(), false);
        }

        //货品号为空 自动补货品号
        if (empty($value))
        {
            $sql = "UPDATE " . $GLOBALS['ecs']->table('products') . "
                    SET product_sn = '" . $goods['goods_sn'] . "g_p" . $GLOBALS['db']->insert_id() . "'
                    WHERE product_id = '" . $GLOBALS['db']->insert_id() . "'";
            $GLOBALS['db']->query($sql);
        }

        /* 修改商品表库存 */
        $product_count = product_number_count($product['goods_id']);
        if (update_goods($product['goods_id'], 'goods_number', $product_count))
        {
            //记录日志
            admin_log($product['goods_id'], 'update', 'goods');
        }
    }

    clear_cache_files();

    /* 返回 */
    if ($insert)
    {
         $link[] = array('href' => 'goods.php?act=add', 'text' => $_LANG['02_goods_add']);
         $link[] = array('href' => 'goods.php?act=list', 'text' => $_LANG['01_goods_list']);
         $link[] = array('href' => 'goods.php?act=product_list&goods_id=' . $product['goods_id'], 'text' => $_LANG['18_product_list']);
    }
    else
    {
         $link[] = array('href' => 'goods.php?act=list&uselastfilter=1', 'text' => $_LANG['01_goods_list']);
         $link[] = array('href' => 'goods.php?act=edit&goods_id=' . $product['goods_id'], 'text' => $_LANG['edit_goods']);
         $link[] = array('href' => 'goods.php?act=product_list&goods_id=' . $product['goods_id'], 'text' => $_LANG['18_product_list']);
    }
    sys_msg($_LANG['save_products'], 0, $link);
}

/*------------------------------------------------------ */
//-- 货品批量操作
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'batch_product')
{
    /* 定义返回 */
    $link[] = array('href' => 'goods.php?act=product_list&goods_id=' . $_POST['goods_id'], 'text' => $_LANG['item_list']);

    /* 批量操作 - 批量删除 */
    if ($_POST['type'] == 'drop')
    {
        //检查权限
        admin_priv('remove_back');

        //取得要操作的商品编号
        $product_id = !empty($_POST['checkboxes']) ? join(',', $_POST['checkboxes']) : 0;
        $product_bound = db_create_in($product_id);

        //取出货品库存总数
        $sum = 0;
        $goods_id = 0;
        $sql = "SELECT product_id, goods_id, product_number FROM  " . $GLOBALS['ecs']->table('products') . " WHERE product_id $product_bound";
        $product_array = $GLOBALS['db']->getAll($sql);
        if (!empty($product_array))
        {
            foreach ($product_array as $value)
            {
                $sum += $value['product_number'];
            }
            $goods_id = $product_array[0]['goods_id'];

            /* 删除货品 */
            $sql = "DELETE FROM " . $ecs->table('products') . " WHERE product_id $product_bound";
            if ($db->query($sql))
            {
                //记录日志
                admin_log('', 'delete', 'products');
            }

            /* 修改商品库存 */
            if (update_goods_stock($goods_id, -$sum))
            {
                //记录日志
                admin_log('', 'update', 'goods');
            }

            /* 返回 */
            sys_msg($_LANG['product_batch_del_success'], 0, $link);
        }
        else
        {
            /* 错误 */
            sys_msg($_LANG['cannot_found_products'], 1, $link);
        }
    }

    /* 返回 */
    sys_msg($_LANG['no_operation'], 1, $link);
}

/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query_warehouse')
{
    /* 获取商品仓库列表 */
    $warehouse_list = warehouse_list();
    $smarty->assign('warehouse_list',     $warehouse_list);

    make_json_result($smarty->fetch('warehouse_list.htm'));
}
/*------------------------------------------------------ */
//-- 商品仓库
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'warehouse_list')
{
    /* 获取商品仓库列表 */
    $warehouse_list = warehouse_list();

    /* 模板赋值 */
    $smarty->assign('ur_here',      $_LANG['60_warehouse_list']);
    $smarty->assign('action_link',  array('href' => 'goods.php?act=add_warehouse', 'text' => $_LANG['add_warehouse']));
    $smarty->assign('full_page',    1);

    $smarty->assign('warehouse_list',     $warehouse_list);

    /* 列表页面 */
    assign_query_info();
    $smarty->display('warehouse_list.htm');
}

/*------------------------------------------------------ */
//-- 添加商品仓库
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'add_warehouse')
{
    $overseas_list=array('0'=>'非海淘','1'=>'海淘');
    $shipping_list=array('1'=>'顺丰快递','2'=>'圆通快递','93'=>'申通快递','84'=>'如风达快递','264'=>'邮政小包','328'=>'中通快递','375'=>'韵达快递','393'=>'天天快递');
    $warehouse_info['is_overseas']=-1;
    $smarty->assign('overseas_list',    $overseas_list);
    $smarty->assign('shipping_list',    $shipping_list);
    $smarty->assign('warehouse_info',    $warehouse_info);
    /* 模板赋值 */
    $smarty->assign('ur_here',      $_LANG['add_warehouse']);
    $smarty->assign('action_link',  array('href' => 'goods.php?act=warehouse_list', 'text' => $_LANG['60_warehouse_list']));
    $smarty->assign('form_act',     'insert_warehouse');
    $smarty->assign('cat_info',     array('is_show' => 1));

    /* 显示页面 */
    assign_query_info();
    $smarty->display('warehouse_info.htm');
}

/*------------------------------------------------------ */
//-- 添加商品仓库（操作）
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'insert_warehouse')
{

    /* 初始化变量 */
    $warehouse['warehouse_type']     = !empty($_POST['warehouse_type'])     ? trim($_POST['warehouse_type'])     : '';
    $warehouse['person_charge']     = !empty($_POST['person_charge'])     ? trim($_POST['person_charge'])     : '';
    $warehouse['supplier_type']      = !empty($_POST['supplier_type'])     ? trim($_POST['supplier_type'])     : '';
    $warehouse['is_overseas']        = !empty($_POST['is_overseas'])       ? intval($_POST['is_overseas'])     : 0;
    $warehouse['shipping_id']        = !empty($_POST['shipping_id'])       ? intval($_POST['shipping_id'])     : 93;
    $warehouse['is_dds']        = !empty($_POST['is_dds'])       ? intval($_POST['is_dds'])     : 0;

    if (warehouse_type_exists($warehouse['warehouse_type']))
    {
        /* 同级别下不能有重复的分类名称 */
        $link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
        sys_msg($_LANG['catname_exist'], 0, $link);
    }

    /* 入库的操作 */
    if ($db->autoExecute($ecs->table('goods_supplier'), $warehouse) !== false)
    {
        $warehouse_id = $db->insert_id();

        admin_log($_POST['warehouse_type'], 'add', 'goods_supplier');   // 记录管理员操作
        clear_cache_files();    // 清除缓存

        /*添加链接*/
        $link[0]['text'] = '继续添加新商品仓库';
        $link[0]['href'] = 'goods.php?act=add_warehouse';

        $link[1]['text'] = '返回商品仓库';
        $link[1]['href'] = 'goods.php?act=warehouse_list';

        sys_msg('新商品仓库添加成功!', 0, $link);
    }
}
/*------------------------------------------------------ */
//-- 编辑商品仓库
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'edit_warehouse')
{
    $warehouse_id = intval($_REQUEST['warehouse_id']);
    $warehouse_info = get_warehouse_info($warehouse_id);  // 查询分类信息数据
    $overseas_list=array('0'=>'非海淘','1'=>'海淘');
    $shipping_list=array('1'=>'顺丰快递','2'=>'圆通快递','93'=>'申通快递','84'=>'如风达快递','264'=>'邮政小包','328'=>'中通快递','375'=>'韵达快递','393'=>'天天快递');
    /* 模板赋值 */
    $smarty->assign('ur_here',     '编辑商品仓库');
    $smarty->assign('action_link', array('text' => $_LANG['60_warehouse_list'], 'href' => 'goods.php?act=warehouse_list'));

    $smarty->assign('warehouse_info',    $warehouse_info);
    $smarty->assign('overseas_list',    $overseas_list);
    $smarty->assign('shipping_list',    $shipping_list);
    $smarty->assign('form_act',    'update_warehouse');

    /* 显示页面 */
    assign_query_info();
    $smarty->display('warehouse_info.htm');
}

/*------------------------------------------------------ */
//-- 编辑商品仓库（操作）
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'update_warehouse')
{
    /* 初始化变量 */
    $type_id              = !empty($_POST['type_id'])       ? intval($_POST['type_id'])     : 0;
    $old_warehouse_type        = $_POST['old_warehouse_type'];
    $old_supplier_type        = $_POST['old_supplier_type'];
    $old_is_overseas        = $_POST['old_is_overseas'];
    $warehouse['warehouse_type']     = !empty($_POST['warehouse_type'])     ? trim($_POST['warehouse_type'])     : '';
    $warehouse['person_charge']     = !empty($_POST['person_charge'])     ? trim($_POST['person_charge'])     : '';
    $warehouse['supplier_type']       = !empty($_POST['supplier_type'])     ? trim($_POST['supplier_type'])     : '';
    $warehouse['is_overseas']       = !empty($_POST['is_overseas'])     ? trim($_POST['is_overseas'])     : '';
    $warehouse['shipping_id']        = !empty($_POST['shipping_id'])       ? intval($_POST['shipping_id'])     : 93;
    $warehouse['is_dds']       = !empty($_POST['is_dds'])     ? trim($_POST['is_dds'])     : '';

    if($warehouse['warehouse_type']!=$old_warehouse_type)
    {
        if (warehouse_type_exists($warehouse['warehouse_type']))
        {
            /* 不能有重复的仓库名称 */
            $link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
            sys_msg('已存在相同仓库', 1, $link);
        }
    }

    if ($db->autoExecute($ecs->table('goods_supplier'), $warehouse, 'UPDATE', "type_id='$type_id'"))
    {
        /* 更新分类信息成功 */
        clear_cache_files(); // 清除缓存
        admin_log($_POST['warehouse_type'], 'edit', 'goods_supplier'); // 记录管理员操作

        /* 提示信息 */
        $link[] = array('text' => '返回商品仓库', 'href' => 'goods.php?act=warehouse_list');
        sys_msg('修改商品仓库成功', 0, $link);
    }
}

/*------------------------------------------------------ */
//-- 删除商品仓库（操作）
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'remove_warehouse')
{
    $warehouse_id   = intval($_GET['id']);
    $warehouse_name = $db->getOne('SELECT warehouse_type FROM ' .$ecs->table('goods_supplier'). " WHERE type_id='$warehouse_id'");
    $count = $db->getOne('SELECT COUNT(*) FROM ' .$ecs->table('goods'). " WHERE supplier_id='$warehouse_id'");
    if($count>0)
    {
        make_json_error($warehouse_name .' '. '下还存在商品.您不能删除!');
    }
    else
    {
        /* 删除分类 */
        $sql = 'DELETE FROM ' .$ecs->table('goods_supplier'). " WHERE type_id = '$warehouse_id'";
        if ($db->query($sql))
        {
            clear_cache_files();
            admin_log($warehouse_name, 'remove', 'goods_supplier');
        }
        else
        {
            make_json_error($warehouse_name .' '. '删除失败!');
        }
    }
    $url = 'goods.php?act=query_warehouse&' . str_replace('act=remove_warehouse', '', $_SERVER['QUERY_STRING']);

    ecs_header("Location: $url\n");
    exit;
}

/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query_country')
{
    /* 获取商品仓库列表 */
    $country_list = country_list();
    $smarty->assign('country_list',     $country_list);

    make_json_result($smarty->fetch('country_list.htm'));
}
/*------------------------------------------------------ */
//-- 商品国家
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'country_list')
{
    /* 获取商品仓库列表 */
    $country_list = country_list();

    /* 模板赋值 */
    $smarty->assign('ur_here',      $_LANG['61_country_list']);
    $smarty->assign('action_link',  array('href' => 'goods.php?act=add_country', 'text' => $_LANG['add_country']));
    $smarty->assign('full_page',    1);

    $smarty->assign('country_list',     $country_list);

    /* 列表页面 */
    assign_query_info();
    $smarty->display('country_list.htm');
}

/*------------------------------------------------------ */
//-- 添加商品国家
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'add_country')
{
    /* 模板赋值 */
    $smarty->assign('ur_here',      $_LANG['add_country']);
    $smarty->assign('action_link',  array('href' => 'goods.php?act=country_list', 'text' => $_LANG['61_country_list']));
    $smarty->assign('form_act',     'insert_country');
    $smarty->assign('cat_info',     array('is_show' => 1));

    /* 显示页面 */
    assign_query_info();
    $smarty->display('country_info.htm');
}

/*------------------------------------------------------ */
//-- 添加商品国家（操作）
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'insert_country')
{
    /* 初始化变量 */
    $country['country_name']     = !empty($_POST['country_name'])     ? trim($_POST['country_name'])     : '';
    $country['country_code']      = !empty($_POST['country_code'])     ? trim($_POST['country_code'])     : '';

    if (country_type_exists($country['country_name'],$country['country_code']))
    {
        /* 同级别下不能有重复的分类名称 */
        $link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
        sys_msg('已存在相同商品国家或国家编码', 0, $link);
    }

    if (!empty($_FILES['img_file_src']['name']))
    {
        if(!get_file_suffix($_FILES['img_file_src']['name'], $allow_suffix))
        {
            sys_msg("您上传的图片格式不正确");
        }
        $name = $country['country_code'];
        $exp = explode('.', $_FILES['img_file_src']['name']);
        $name .= '.' . end($exp);
        $target = ROOT_PATH . '/themes/default/images/' . $name;
        if (move_upload_file($_FILES['img_file_src']['tmp_name'], $target))
        {
            $country['country_img'] = '/themes/default/images/' . $name;
        }
    }
    else
    {
        $links[] = array('text' => '上传商品国家图', 'href' => 'goods.php?act=add_country');
        sys_msg('请上传商品国家图标', 0, $links);
    }
    /* 入库的操作 */
    if ($db->autoExecute($ecs->table('goods_country'), $country) !== false)
    {
        $country_id = $db->insert_id();

        admin_log($_POST['country_name'], 'add', 'goods_country');   // 记录管理员操作
        clear_cache_files();    // 清除缓存

        /*添加链接*/
        $link[0]['text'] = '继续添加新商品国家';
        $link[0]['href'] = 'goods.php?act=add_country';

        $link[1]['text'] = '返回商品国家';
        $link[1]['href'] = 'goods.php?act=country_list';

        sys_msg('新商品国家添加成功!', 0, $link);
    }
}
/*------------------------------------------------------ */
//-- 编辑商品国家
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'edit_country')
{
    $country_id = intval($_REQUEST['country_id']);
    $country_info = get_country_info($country_id);  // 查询分类信息数据
    /* 模板赋值 */
    $smarty->assign('ur_here',     '编辑商品仓库');
    $smarty->assign('action_link', array('text' => $_LANG['61_country_list'], 'href' => 'goods.php?act=country_list'));

    $smarty->assign('country_info',    $country_info);
    $smarty->assign('form_act',    'update_country');

    /* 显示页面 */
    assign_query_info();
    $smarty->display('country_info.htm');
}

/*------------------------------------------------------ */
//-- 编辑商品国家（操作）
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'update_country')
{
    /* 初始化变量 */
    $country_id              = !empty($_POST['country_id'])       ? intval($_POST['country_id'])     : 0;
    $old_country_name        = $_POST['old_country_name'];
    $old_country_code        = $_POST['old_country_code'];

    $country['country_name']     = !empty($_POST['country_name'])     ? trim($_POST['country_name'])     : '';
    $country['country_code']       = !empty($_POST['country_code'])     ? trim($_POST['country_code'])     : '';

    if($country['country_name']!=$old_country_name)
    {
        if (country_type_exists($country['country_name'],$country['country_code']))
        {
            /* 不能有重复的国家名称 */
            $link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
            sys_msg('已存在相同商品国家或国家编码', 1, $link);
        }
    }
    if (!empty($_FILES['img_file_src']['name']))
    {
        if(!get_file_suffix($_FILES['img_file_src']['name'], $allow_suffix))
        {
            sys_msg("您上传的图片格式不正确");
        }
        $name = $country['country_code'];
        $exp = explode('.', $_FILES['img_file_src']['name']);
        $name .= '.' . end($exp);
        $target = ROOT_PATH . '/themes/default/images/' . $name;
        if (move_upload_file($_FILES['img_file_src']['tmp_name'], $target))
        {
            $country['country_img'] = '/themes/default/images/' . $name;
        }
    }
    elseif (!empty($_POST['img_src']))
    {
        $src = $_POST['img_src'];
        $name = $country['country_code'] . '.' . end(explode('.', $src));
        if(strstr($src, 'http') && !strstr($src, $_SERVER['SERVER_NAME']))
        {
            $src = get_url_image($src);
        }
        if(is_file('..'.$src))
        {
            if(rename('..'.$src,'../themes/default/images/'.$name))
            {
                $country['country_img'] = '/themes/default/images/'.$name;
            }
        }
        else
        {
            sys_msg("图片不存在或被删除，请重新上传图片");
        }
    }
    else
    {
        $links[] = array('text' => '上传商品国家图', 'href' => 'goods.php?act=add_country');
        sys_msg('请上传商品国家图标', 0, $links);
    }
    if ($db->autoExecute($ecs->table('goods_country'), $country, 'UPDATE', "country_id='$country_id'"))
    {
        /* 更新分类信息成功 */
        clear_cache_files(); // 清除缓存
        admin_log($_POST['country_name'], 'edit', 'goods_country'); // 记录管理员操作

        /* 提示信息 */
        $link[] = array('text' => '返回商品国家', 'href' => 'goods.php?act=country_list');
        sys_msg('修改商品国家成功', 0, $link);
    }
}

/*------------------------------------------------------ */
//-- 删除商品国家（操作）
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'remove_country')
{
    $country_id   = intval($_GET['id']);
    $country_info = get_country_info($country_id);  // 查询分类信息数据
    $country_name = $country_info['country_code'];
    $count = $db->getOne('SELECT COUNT(*) FROM ' .$ecs->table('goods'). " WHERE overseas_logo='$country_name'");
    if($count>0)
    {
        make_json_error($country_name .' '. '下还存在商品.您不能删除!');
    }
    else
    {
        if ($country_info['country_img'] != '' && is_file('../' . $country_info['country_img']))
        {
            @unlink('../' . $country_info['country_img']);
        }
        /* 删除分类 */
        $sql = 'DELETE FROM ' .$ecs->table('goods_country'). " WHERE country_id = '$country_id'";
        if ($db->query($sql))
        {
            clear_cache_files();
            admin_log($country_name, 'remove', 'goods_country');
        }
        else
        {
            make_json_error($country_name .' '. '删除失败!');
        }
    }
    $url = 'goods.php?act=query_country&' . str_replace('act=remove_country', '', $_SERVER['QUERY_STRING']);

    ecs_header("Location: $url\n");
    exit;
}

/*------------------------------------------------------ */
//-- 海淘商品明细下载
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'download_overseas')
{
    // 导出到文本;
    ini_set('memory_limit',-1);
    set_time_limit(0);
    $file_name = "overseas_onsale_". date ( "YmdHis" ) . ".csv";
    $str_down = "商品编号\t万集客ID\t供货商\t跨境通ID\t产品条形码\t品牌\t品名\t产地\t一级分类\t二级分类\t三级分类\t四级分类\t商品采购价\t万集客售价\t商品单重\t商品状态（是否上架等）\t库存\t预警\t是否海淘";
    $sql = "select t1.goods_id,t1.goods_sn,t2.warehouse_type,t2.is_overseas,t1.kjt_goods_id,t1.goods_barcode,t3.brand_name,t1.goods_name,t4.country_name,t1.goods_number,t1.warn_number,t1.shop_price,t1.kjt_price,t1.goods_weight,t1.cat_id,t1.is_on_sale from (ecs_goods t1 inner JOIN ecs_goods_supplier t2 on t1.supplier_id=t2.type_id) LEFT JOIN ecs_brand t3 on t1.brand_id=t3.brand_id  LEFT JOIN ecs_goods_country t4 on t1.overseas_logo=t4.country_code where t1.is_delete=0";
    $result = $GLOBALS['db']->getAll($sql);

    reset($result);

    $ordersn = '';
    while ( list ( $key, $val ) = each ( $result ) ) {
        $cats = get_parent_cats($val['cat_id']);
        $i = count($cats)-1;
        foreach($cats as $k=>$v)
        {
            $catlist[$i] = $v['cat_name'];
            $i--;
        }
        ksort($catlist);
        $val['is_on_sale'] = $val['is_on_sale'] ? "是" : "否";
        $val['is_overseas'] = $val['is_overseas'] ? "是" : "否";
        $str_down .= "\n" . $val['goods_id'] .  "\t". $val['goods_sn'].  "\t". $val['warehouse_type']. "\t" . $val['kjt_goods_id']. "\t" . $val['goods_barcode'] . "\t" . $val['brand_name'] ."\t". $val['goods_name'] ."\t". $val['country_name'] ."\t". $catlist[0] ."\t". $catlist[1] ."\t". $catlist[2] ."\t". $catlist[3] ."\t". $val['kjt_price'] ."\t". $val['shop_price'] ."\t". $val['goods_weight'] ."\t". $val['is_on_sale'] ."\t". $val['goods_number'] ."\t". $val['warn_number'] ."\t". $val['is_overseas'];
        unset($catlist);
    }
    header ( 'Content-Description: File Transfer' );
    header ( 'Content-Type: application/vnd.ms-excel ; charset=UTF-16LE' );
    header ( 'Content-Disposition: attachment; filename=' . $file_name );
    // header ( 'Content-Transfer-Encoding: binary' );
    header ( 'Expires: 0' );
    header ( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
    header ( 'Pragma: public' );
    //header ( 'Content-Length: ' . strlen ( $str_down ) );
    ob_clean ();
    flush ();

    //添加BOM，保证csv能够显示utf8的字符
    echo(chr(255).chr(254));
    echo(mb_convert_encoding($str_down,"UTF-16LE","UTF-8"));

    exit;

}

/*    添加组名或更新组名 */
elseif ($_REQUEST['act'] == 'add_collect_name')
{
    include_once(ROOT_PATH . 'includes/lib_goods.php');
    include_once(ROOT_PATH . 'includes/cls_json.php');
    $json = new JSON;
    $id = $_POST['id'];
    $array_name = $_POST['array_name'];
    $goods_id = $_POST['goods_id'];

    $r['error'] = 0;
    $r['errormsg'] = "添加成功！";
    //查询当前商品是否存在某个组名下，不在任何组名下。可以添加到当前组名下。
    $res = get_goods_collectvalues($goods_id);
    if($res != null)
    {
        $r['error'] = 1;
        $r['errormsg'] = "该商品已有组名！";
    }
    else
    {
        if($id != null)
        {
            $gcid = $id;
            //查找组名ID下的属性,存在就添加并且下架该商品
            $sql="SELECT collect_name FROM " . $GLOBALS['ecs']->table('goods_collect'). " WHERE gcid='$gcid'";
            $array_name =  $GLOBALS['db']->getOne($sql);
            if($array_name != null)
            {
                $sql = 'UPDATE ' . $GLOBALS['ecs']->table('goods') .
                    " SET gcid=$gcid WHERE goods_id=$goods_id";
                $result = $GLOBALS['db']->query($sql);
                if(!$result)
                {
                    $r['error'] = 1;
                    $r['errormsg'] = "添加组名失败！";
                }
            }
            else
            {
                $r['error'] = 1;
                $r['errormsg'] = "添加组名失败！";
            }
        }
        else
        {
            //查询是否有该组名，没有则新建，并且下架该商品
            $insert_name = insert_goods_collect($array_name);
            if(!$insert_name)
            {
                $r['error'] = 1;
                $r['errormsg'] = "添加组名失败！";
            }
            elseif($insert_name === 'existend')
            {
                $r['error'] = 1;
                $r['errormsg'] = "组名重复！";
            }
            else
            {
                $gcid = $db->insert_id();
                $sql = 'UPDATE ' . $GLOBALS['ecs']->table('goods') .
                    " SET gcid=$gcid WHERE goods_id=$goods_id";
                $result = $GLOBALS['db']->query($sql);
                if(!$result)
                {
                    $r['error'] = 1;
                    $r['errormsg'] = "添加组名失败！";
                }
            }
        }
    }
    if($r['error'] == 0)
    {
        //返回关联商品
        $collect_goods = get_collect_goods($gcid,$goods_id);
        $collect_attr = get_collect_attrs($gcid);
        $goods_collect = get_goods_collectvalues($goods_id);
        foreach($collect_attr as $k=>$v){
            $caid = $v["caid"];
            $collect_attr[$k]["attr_value"] = get_str_collect_attrvalues($caid);
        }
        $r['msg'] = $collect_attr;
        $r['collect_goods'] = $collect_goods;
        $r['goods_collect'] = $goods_collect;
        $r['gcid'] = $gcid;
        if($collect_name != null || $array_name != null)
        {
            $r['name'] = $array_name;
        }
    }
    die($json->encode($r));
}
/* 删除组名 */
elseif ($_REQUEST['act'] == 'delete_goods_collect')
{
    include_once(ROOT_PATH . 'includes/lib_goods.php');
    include_once(ROOT_PATH . 'includes/cls_json.php');

    $json = new JSON;
    $gcid = $_POST['gcid'];
    $r['error'] = 0;
    $r['errormsg'] = "删除成功！";

    $rel = delete_goods_collect($gcid);
    if(!$rel){
        $r['error'] = 1;
        $r['errormsg'] = "删除失败！";
    }
    elseif($rel === "error"){
        $r['error'] = 2;
        $r['errormsg'] = "该属性下有关联商品或关联属性，删除失败！";
    }
    else
    {
        //所有组下的空白商品gcid字段置为0
        $sql = 'UPDATE ' . $GLOBALS['ecs']->table('goods') .
            " SET gcid=0 WHERE gcid=$gcid";
        $GLOBALS['db']->query($sql);
    }
    die($json->encode($r));
}
/* 获取一品多色商品编辑内容 */
elseif ($_REQUEST['act'] == 'update_goods_value')
{
    include_once(ROOT_PATH . 'includes/lib_goods.php');
    include_once(ROOT_PATH . 'includes/cls_json.php');

    $json = new JSON;
    $goods_id = $_POST['goods_id'];

    $r = get_goods_collectvalues($goods_id);
    die($json->encode($r));
}

/* 编辑商品集合属性值 */
elseif ($_REQUEST['act'] == 'change_attrs')
{
    include_once(ROOT_PATH . 'includes/lib_goods.php');
    include_once(ROOT_PATH . 'includes/cls_json.php');

    $json = new JSON;
    $caid = $_POST['caid'];

    $ca_name = get_caname_byid($caid);
    $r['error'] = 0;
    $collect_attrvalues = get_collect_attrvalues($caid);
    foreach($collect_attrvalues as $k=>$v){
        $collect_attrvalues[$k]['ca_name'] = get_caname_byid($caid);
    }
    $r['msg'] = $collect_attrvalues;
    $r['caid'] = $caid;
    $r['caname'] = $ca_name;

    die($json->encode($r));
}

/* 添加商品集合属性 */
elseif ($_REQUEST['act'] == 'add_attr')
{
    include_once(ROOT_PATH . 'includes/lib_goods.php');
    include_once(ROOT_PATH . 'includes/cls_json.php');

    $json = new JSON;
    $gcid = $_POST['gcid'];
    $ca_name = $_POST['ca_name'];

    $result = 0;
    $errormsg = "";
    $rel = insert_collect_attr($gcid, $ca_name);
    $g_cid = $db->insert_id();
    if(!$rel){
        $errormsg = "添加失败";
        $result = 1;
    }
    elseif($rel === "existend"){
        $errormsg = $ca_name;
        $errormsg .= "类型重复，添加失败！";
        $result = 2;
    }

    $r['error'] = $result;
    $r['errormsg'] = $errormsg;
    $r['gcid'] = $g_cid;

    die($json->encode($r));
}

/* 删除商品集合属性值 */
elseif ($_REQUEST['act'] == 'delete_attr')
{
    include_once(ROOT_PATH . 'includes/lib_goods.php');
    include_once(ROOT_PATH . 'includes/cls_json.php');

    $json = new JSON;
    $caid = $_POST['caid'];

    $r['error'] = 0;
    $r['errormsg'] = "删除成功！";

    $rel = delete_collect_attr($caid);
    if(!$rel){
        $r['error'] = 1;
        $r['errormsg'] = "删除失败！";
    }
    elseif($rel === "error"){
        $r['error'] = 2;
        $r['errormsg'] = "该属性下有关联商品，删除失败！";
    }
    die($json->encode($r));
}

/* 添加商品集合属性值 */
elseif ($_REQUEST['act'] == 'add_attrvalue')
{
    include_once(ROOT_PATH . 'includes/lib_goods.php');
    include_once(ROOT_PATH . 'includes/cls_json.php');

    $json = new JSON;
    $caid = $_POST['caid'];
    $attrvalues = $_POST['name'];
    $attrvalues = explode(',',$attrvalues);
    $result = 0;
    $attrvalue = "";
    foreach($attrvalues as $v){
        if($v != "")
        {
            $rel = insert_collect_attrvalue($caid, $v);
            if(!$rel){
                $result = 1;
                $attrvalue = "添加失败";
            }
            elseif($rel === "existend"){
                $result = 2;
                $attrvalue .= ($v.',');
            }
        }
    }
    $attrvalue = substr($attrvalue, 0, -1);
    $attrvalue .= "属性值重复，添加失败！";
    if($result == 0)
    {
        $collect_attrvalues = get_str_collect_attrvalues($caid);
        $r['msg'] = $collect_attrvalues;
    }
    $r['error'] = $result;
    $r['errormsg'] = $attrvalue;

    die($json->encode($r));
}

/* 删除商品集合属性值 */
elseif ($_REQUEST['act'] == 'delete_attrvalue')
{
    include_once(ROOT_PATH . 'includes/lib_goods.php');
    include_once(ROOT_PATH . 'includes/cls_json.php');

    $json = new JSON;
    $attrv_id = $_POST['attrv_id'];

    $r['error'] = 0;
    $r['errormsg'] = "删除成功！";

    $rel = delete_collect_attrvalue($attrv_id);
    if(!$rel){
        $r['error'] = 1;
        $r['errormsg'] = "删除失败！";
    }
    elseif($rel === "error"){
        $r['error'] = 2;
        $r['errormsg'] = "该属性下有关联商品，删除失败！";
    }

    die($json->encode($r));
}

/* 更新商品集合明细 */
elseif ($_REQUEST['act'] == 'set_in_collect')
{
    include_once(ROOT_PATH . 'includes/lib_goods.php');
    include_once(ROOT_PATH . 'includes/cls_json.php');

    $json = new JSON;
    $gcid = $_POST['gcid'];
    $goods_id = $_POST['goods_id'];
    $is_add = $_POST['is_add']>0?1:0;
    $attr_array = $_POST['attr'];
    $attrs = explode(',',$attr_array);
    sort($attrs);
    $collect_attrids = implode(',',$attrs);

    $r['error'] = 0;
    $r['errormsg'] = "关联成功！";

    $rel = set_in_collect($goods_id, $gcid, $collect_attrids,$is_add);
    if(!$rel){
        $r['error'] = 1;
        $r['errormsg'] = "关联失败！";
    }
    elseif($rel === "existend"){
        $r['error'] = 2;
        $r['errormsg'] = "该属性已被其他商品关联，关联失败！";
    }
    $r['msg'] = get_goods_collectvalues($goods_id);

    die($json->encode($r));
}

/*------------------------------------------------------ */
//-- 删除一品多类关联商品
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'delete_relation_goods')
{
    include_once(ROOT_PATH . 'includes/lib_goods.php');
    include_once(ROOT_PATH . 'includes/cls_json.php');
    $r['error'] = 0;
    $r['errormsg'] = "删除成功！";
    $json = new JSON;
    $goods_id = $_POST['goods_id'];
    $rel = set_out_collect($goods_id);
    if(!$rel)
    {
        $r['error'] = 1;
        $r['errormsg'] = "删除失败！";
    }
    die($json->encode($r));
}


/*------------------------------------------------------ */
//-- Excel批量修改商品价格
/*------------------------------------------------------ */

if ($_REQUEST['act'] == 'update_goods_price')
{
    //上传订单文件
    if(! empty ( $_FILES ['upload_file'] ['name'] ))
    {
        $tmp_file = $_FILES ['upload_file'] ['tmp_name'];
        $file_types = explode( ".", $_FILES ['upload_file'] ['name'] );
        $file_type = $file_types[count( $file_types ) - 1];

        /*判别是不是.xls文件，判别是不是excel文件*/
        if (strtolower($file_type) != "xls")
        {
            /* 提示信息 */
            $link[] = array('text' => $_LANG['back_list'], 'href' => 'goods.php?act=list');
            sys_msg('不是.xls后缀的Excel文件，重新上传', 0, $link);
        }

        /*
          注意：这里调用执行了read函数，把Excel转化为数组并返回给$res,再进行数据库写入
        */
        require_once '../temptool/PHPExcel.php';
        $objPHPExcel = new PHPExcel();
        $objReader = PHPExcel_IOFactory::createReader('Excel5');//use excel2007 for 2007 format
        $objPHPExcel = $objReader->load($tmp_file); //$filename可以是上传的文件，或者是指定的文件
        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow(); // 取得总行数
        $highestColumn = $sheet->getHighestColumn(); // 取得总列数
        $arr=array();
        //循环读取excel文件,读取一条,插入一条
        for($j=1;$j<=$highestRow;$j++)
        {
            $suc = 0;
            $fal = 0;
            $a = $objPHPExcel->getActiveSheet()->getCell("A".$j)->getValue();
            $b = $objPHPExcel->getActiveSheet()->getCell("B".$j)->getValue();
            $goods_id = intval($a);
            $price = intval($b);
            if($j == 1 && $goods_id == 0){
                continue;
            }
            $sql = 'UPDATE ' . $GLOBALS['ecs']->table('goods') . " SET shop_price=$price WHERE goods_id = $goods_id";
            $r = $GLOBALS['db']->query($sql);
        }

        $link[] = array('text' => $_LANG['back_list'], 'href' => 'goods.php?act=list');
        sys_msg('操作完成', 0, $link);
    }
}
/*------------------------------------------------------ */
//-- Excel批量修改商品促销价格
/*------------------------------------------------------ */

if ($_REQUEST['act'] == 'update_promote_price')
{
    //上传订单文件
    if(! empty ( $_FILES ['upload_file'] ['name'] ))
    {
        $tmp_file = $_FILES ['upload_file'] ['tmp_name'];
        $file_types = explode( ".", $_FILES ['upload_file'] ['name'] );
        $file_type = $file_types[count( $file_types ) - 1];

        /*判别是不是.xls文件，判别是不是excel文件*/
        if (strtolower($file_type) != "xls")
        {
            /* 提示信息 */
            $link[] = array('text' => $_LANG['back_list'], 'href' => 'goods.php?act=list');
            sys_msg('不是.xls后缀的Excel文件，重新上传', 0, $link);
        }

        /*
          注意：这里调用执行了read函数，把Excel转化为数组并返回给$res,再进行数据库写入
        */
        require_once '../temptool/PHPExcel.php';
        $objPHPExcel = new PHPExcel();
        $objReader = PHPExcel_IOFactory::createReader('Excel5');//use excel2007 for 2007 format
        $objPHPExcel = $objReader->load($tmp_file); //$filename可以是上传的文件，或者是指定的文件
        $sheet = $objPHPExcel->getSheet(0);
        $highestRow = $sheet->getHighestRow(); // 取得总行数
        $highestColumn = $sheet->getHighestColumn(); // 取得总列数
        $arr=array();
        //循环读取excel文件,读取一条,插入一条
        for($j=1;$j<=$highestRow;$j++)
        {
            $suc = 0;
            $fal = 0;
            $a = $objPHPExcel->getActiveSheet()->getCell("A".$j)->getValue();
            $b = $objPHPExcel->getActiveSheet()->getCell("B".$j)->getValue();
            $starttime = PHPExcel_Shared_Date::ExcelToPHP($objPHPExcel->getActiveSheet()->getCell("C".$j)->getValue())-57600;
            $endtime = PHPExcel_Shared_Date::ExcelToPHP($objPHPExcel->getActiveSheet()->getCell("D".$j)->getValue())-57600;
            $e = $objPHPExcel->getActiveSheet()->getCell("E".$j)->getValue();
            $goods_id = intval($a);
            $price = intval($b);
            $is_promote = intval($e);
            if($j == 1 && $goods_id == 0){
                continue;
            }
            if($e){
                $sql = 'UPDATE ' . $GLOBALS['ecs']->table('goods') . " SET promote_price=$price,is_promote=1,promote_start_date=$starttime,promote_end_date=$endtime WHERE goods_id = $goods_id";
            }
            else{
                $sql = 'UPDATE ' . $GLOBALS['ecs']->table('goods') . " SET promote_price=0,is_promote=0,promote_start_date=0,promote_end_date=0 WHERE goods_id = $goods_id";
            }
            $r = $GLOBALS['db']->query($sql);
        }
        $link[] = array('text' => $_LANG['back_list'], 'href' => 'goods.php?act=list');
        sys_msg('操作完成', 0, $link);
    }
}
/**
 * 列表链接
 * @param   bool    $is_add         是否添加（插入）
 * @param   string  $extension_code 虚拟商品扩展代码，实体商品为空
 * @return  array('href' => $href, 'text' => $text)
 */
function list_link($is_add = true, $extension_code = '')
{
    $href = 'goods.php?act=list';
    if (!empty($extension_code))
    {
        $href .= '&extension_code=' . $extension_code;
    }
    if (!$is_add)
    {
        $href .= '&' . list_link_postfix();
    }

    if ($extension_code == 'virtual_card')
    {
        $text = $GLOBALS['_LANG']['50_virtual_card_list'];
    }
    else
    {
        $text = $GLOBALS['_LANG']['01_goods_list'];
    }

    return array('href' => $href, 'text' => $text);
}

/**
 * 添加链接
 * @param   string  $extension_code 虚拟商品扩展代码，实体商品为空
 * @return  array('href' => $href, 'text' => $text)
 */
function add_link($extension_code = '')
{
    $href = 'goods.php?act=add';
    if (!empty($extension_code))
    {
        $href .= '&extension_code=' . $extension_code;
    }

    if ($extension_code == 'virtual_card')
    {
        $text = $GLOBALS['_LANG']['51_virtual_card_add'];
    }
    else
    {
        $text = $GLOBALS['_LANG']['02_goods_add'];
    }

    return array('href' => $href, 'text' => $text);
}

/**
 * 检查图片网址是否合法
 *
 * @param string $url 网址
 *
 * @return boolean
 */
function goods_parse_url($url)
{
    $parse_url = @parse_url($url);
    return (!empty($parse_url['scheme']) && !empty($parse_url['host']));
}

/**
 * 保存某商品的优惠价格
 * @param   int     $goods_id    商品编号
 * @param   array   $number_list 优惠数量列表
 * @param   array   $price_list  价格列表
 * @return  void
 */
function handle_volume_price($goods_id, $number_list, $price_list)
{
    $sql = "DELETE FROM " . $GLOBALS['ecs']->table('volume_price') .
           " WHERE price_type = '1' AND goods_id = '$goods_id'";
    $GLOBALS['db']->query($sql);


    /* 循环处理每个优惠价格 */
    foreach ($price_list AS $key => $price)
    {
        /* 价格对应的数量上下限 */
        $volume_number = $number_list[$key];

        if (!empty($price))
        {
            $sql = "INSERT INTO " . $GLOBALS['ecs']->table('volume_price') .
                   " (price_type, goods_id, volume_number, volume_price) " .
                   "VALUES ('1', '$goods_id', '$volume_number', '$price')";
            $GLOBALS['db']->query($sql);
        }
    }
}

/**
 * 修改商品库存
 * @param   string  $goods_id   商品编号，可以为多个，用 ',' 隔开
 * @param   string  $value      字段值
 * @return  bool
 */
function update_goods_stock($goods_id, $value)
{
    if ($goods_id)
    {
        /* $res = $goods_number - $old_product_number + $product_number; */
        $sql = "UPDATE " . $GLOBALS['ecs']->table('goods') . "
                SET goods_number = goods_number + $value,
                    last_update = '". gmtime() ."'
                WHERE goods_id = '$goods_id'";
        $result = $GLOBALS['db']->query($sql);

        /* 清除缓存 */
        clear_cache_files();

        return $result;
    }
    else
    {
        return false;
    }
}

/**
 * 判断是否是会员充值商品
 * @param   string  $goods_id   商品编号
 * @return  bool
 */
function is_membersgoods($goods_id)
{
    if ($goods_id)
    {
        /* 取商品属性 */
        $sql = "SELECT extension_code FROM " .$GLOBALS['ecs']->table('goods'). " WHERE goods_id=$goods_id";
        $result = $GLOBALS['db']->getOne($sql);
        $r = false;
        if($result == "goods_members"){
            $r = true;
        }

        return $r;
    }
    else
    {
        return false;
    }
}

/**
 * 获得仓库所有信息
 *
 * @param   integer     $warehouse_id     指定的商品仓库ID
 *
 * @return  mix
 */
function get_warehouse_info($warehouse_id)
{
    $sql = "SELECT * FROM " .$GLOBALS['ecs']->table('goods_supplier'). " WHERE type_id='$warehouse_id' LIMIT 1";
    return $GLOBALS['db']->getRow($sql);
}
/**
 * 获得商品国家所有信息
 *
 * @param   integer     $country_id     指定的商品国家ID
 *
 * @return  mix
 */
function get_country_info($country_id)
{
    $sql = "SELECT * FROM " .$GLOBALS['ecs']->table('goods_country'). " WHERE country_id='$country_id' LIMIT 1";
    return $GLOBALS['db']->getRow($sql);
}

/**
 * 获得指定分类的所有上级分类
 *
 * @access  public
 * @param   integer $cat    分类编号
 * @return  array
 */
function get_parent_cats($cat)
{
    if ($cat == 0)
    {
        return array();
    }

    $arr = $GLOBALS['db']->GetAll('SELECT cat_id, cat_name, parent_id FROM ' . $GLOBALS['ecs']->table('category'));

    if (empty($arr))
    {
        return array();
    }

    $index = 0;
    $cats  = array();

    while (1)
    {
        foreach ($arr AS $row)
        {
            if ($cat == $row['cat_id'])
            {
                $cat = $row['parent_id'];

                $cats[$index]['cat_id']   = $row['cat_id'];
                $cats[$index]['cat_name'] = $row['cat_name'];

                $index++;
                break;
            }
        }

        if ($index == 0 || $cat == 0)
        {
            break;
        }
    }

    return $cats;
}
?>