<?php

/**
 * ECSHOP 程序说明
 * ===========================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ==========================================================
 * $Author: liubo $
 * $Id: flashplay.php 17217 2011-01-19 06:29:08Z liubo $
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
$uri = $ecs->url();
$allow_suffix = array('gif', 'jpg', 'png', 'jpeg', 'bmp');

/*------------------------------------------------------ */
//-- 系统
/*------------------------------------------------------ */
if ($_REQUEST['act']== 'list')
{
    /* 判断系统当前设置 如果为用户自定义 则跳转到自定义 */
    if ($_CFG['index_ad'] == 'cus')
    {
        ecs_header("Location: flashplay.php?act=custom_list\n");
        exit;
    }

    $playerdb = get_flash_xml();
    foreach ($playerdb as $key => $val)
    {
        if (strpos($val['src'], 'http') === false)
        {
            $playerdb[$key]['src'] = $uri . $val['src'];
        }
    }

    /* 标签初始化 */
    $group_list = array(
        'sys' => array('text' => $_LANG['system_set'], 'url' => ''),
        'cus' => array('text' => $_LANG['custom_set'], 'url' => 'flashplay.php?act=custom_list')
                       );

    assign_query_info();
    $flash_dir = ROOT_PATH . 'data/flashdata/';

    $smarty->assign('current', 'sys');
    $smarty->assign('group_list', $group_list);
    $smarty->assign('group_selected', $_CFG['index_ad']);
    $smarty->assign('uri', $uri);
    $smarty->assign('ur_here', $_LANG['flashplay']);
    $smarty->assign('action_link_special', array('text' => $_LANG['add_new'], 'href' => 'flashplay.php?act=add'));
    $smarty->assign('flashtpls', get_flash_templates($flash_dir));
    $smarty->assign('current_flashtpl', $_CFG['flash_theme']);
    $smarty->assign('playerdb', $playerdb);
    $smarty->display('flashplay_list.htm');
}
elseif($_REQUEST['act']== 'del')
{
    admin_priv('flash_manage');

    $id = (int)$_GET['id'];
    $flashdb = get_flash_xml();
    if (isset($flashdb[$id]))
    {
        $rt = $flashdb[$id];
    }
    else
    {
        $links[] = array('text' => $_LANG['go_url'], 'href' => 'flashplay.php?act=list');
        sys_msg($_LANG['id_error'], 0, $links);
    }

    if (strpos($rt['src'], 'http') === false)
    {
        @unlink(ROOT_PATH . $rt['src']);
    }
    $temp = array();
    foreach ($flashdb as $key => $val)
    {
        if ($key != $id)
        {
            $temp[] = $val;
        }
    }
    put_flash_xml($temp);
    set_flash_data($_CFG['flash_theme'], $error_msg = '');
    ecs_header("Location: flashplay.php?act=list\n");
    exit;
}
elseif ($_REQUEST['act'] == 'add')
{
    admin_priv('flash_manage');

    if (empty($_POST['step']))
    {
        $url = isset($_GET['url']) ? $_GET['url'] : 'http://';
        $src = isset($_GET['src']) ? $_GET['src'] : '';
        $sort = 0;
        $rt = array('act'=>'add','img_url'=>$url,'img_src'=>$src, 'img_sort'=>$sort);
        $width_height = get_width_height();
        assign_query_info();
        if(isset($width_height['width'])|| isset($width_height['height']))
        {
            $smarty->assign('width_height', sprintf($_LANG['width_height'], $width_height['width'], $width_height['height']));
        }

        $smarty->assign('action_link', array('text' => $_LANG['go_url'], 'href' => 'flashplay.php?act=list'));
        $smarty->assign('rt', $rt);
        $smarty->assign('ur_here', $_LANG['add_picad']);
        $smarty->display('flashplay_add.htm');
    }
    elseif ($_POST['step'] == 2)
    {
        if (!empty($_FILES['img_file_src']['name']))
        {
            if(!get_file_suffix($_FILES['img_file_src']['name'], $allow_suffix))
            {
                sys_msg($_LANG['invalid_type']);
            }
            $name = date('Ymd');
            for ($i = 0; $i < 6; $i++)
            {
                $name .= chr(mt_rand(97, 122));
            }
            $name .= '.' . end(explode('.', $_FILES['img_file_src']['name']));
            $target = ROOT_PATH . DATA_DIR . '/afficheimg/' . $name;
            if (move_upload_file($_FILES['img_file_src']['tmp_name'], $target))
            {
                $src = DATA_DIR . '/afficheimg/' . $name;
            }
        }
        elseif (!empty($_POST['img_src']))
        {
            if(!get_file_suffix($_POST['img_src'], $allow_suffix))
            {
                sys_msg($_LANG['invalid_type']);
            }
            $src = $_POST['img_src'];
            if(strstr($src, 'http') && !strstr($src, $_SERVER['SERVER_NAME']))
            {
                $src = get_url_image($src);
            }
        }
        else
        {
            $links[] = array('text' => $_LANG['add_new'], 'href' => 'flashplay.php?act=add');
            sys_msg($_LANG['src_empty'], 0, $links);
        }

        if (empty($_POST['img_url']))
        {
            $links[] = array('text' => $_LANG['add_new'], 'href' => 'flashplay.php?act=add');
            sys_msg($_LANG['link_empty'], 0, $links);
        }

        // 获取flash播放器数据
        $flashdb = get_flash_xml();

        // 插入新数据
        array_unshift($flashdb, array('src'=>$src, 'url'=>$_POST['img_url'], 'text'=>$_POST['img_text'] ,'sort'=>$_POST['img_sort']));

        // 实现排序
        $flashdb_sort   = array();
        $_flashdb       = array();
        foreach ($flashdb as $key => $value)
        {
            $flashdb_sort[$key] = $value['sort'];
        }
        asort($flashdb_sort, SORT_NUMERIC);
        foreach ($flashdb_sort as $key => $value)
        {
            $_flashdb[] = $flashdb[$key];
        }
        unset($flashdb, $flashdb_sort);

        put_flash_xml($_flashdb);
        set_flash_data($_CFG['flash_theme'], $error_msg = '');
        $links[] = array('text' => $_LANG['go_url'], 'href' => 'flashplay.php?act=list');
        sys_msg($_LANG['edit_ok'], 0, $links);
    }
}
elseif ($_REQUEST['act'] == 'edit')
{
    admin_priv('flash_manage');

    $id = (int)$_REQUEST['id']; //取得id
    $flashdb = get_flash_xml(); //取得数据
    if (isset($flashdb[$id]))
    {
        $rt = $flashdb[$id];
    }
    else
    {
        $links[] = array('text' => $_LANG['go_url'], 'href' => 'flashplay.php?act=list');
        sys_msg($_LANG['id_error'], 0, $links);
    }
    if (empty($_POST['step']))
    {
        $rt['act'] = 'edit';
        $rt['img_url'] = $rt['url'];
        $rt['img_src'] = $rt['src'];
        $rt['img_txt'] = $rt['text'];
        $rt['img_sort'] = empty($rt['sort']) ? 0 : $rt['sort'];

        $rt['id'] = $id;
        $smarty->assign('action_link', array('text' => $_LANG['go_url'], 'href' => 'flashplay.php?act=list'));
        $smarty->assign('rt', $rt);
        $smarty->assign('ur_here', $_LANG['edit_picad']);
        $smarty->display('flashplay_add.htm');
    }
    elseif ($_POST['step'] == 2)
    {
        if (empty($_POST['img_url']))
        {
            //若链接地址为空
            $links[] = array('text' => $_LANG['return_edit'], 'href' => 'flashplay.php?act=edit&id=' . $id);
            sys_msg($_LANG['link_empty'], 0, $links);
        }

        if (!empty($_FILES['img_file_src']['name']))
        {
            if(!get_file_suffix($_FILES['img_file_src']['name'], $allow_suffix))
            {
                sys_msg($_LANG['invalid_type']);
            }
            //有上传
            $name = date('Ymd');
            for ($i = 0; $i < 6; $i++)
            {
                $name .= chr(mt_rand(97, 122));
            }
            $name .= '.' . end(explode('.', $_FILES['img_file_src']['name']));
            $target = ROOT_PATH . DATA_DIR . '/afficheimg/' . $name;

            if (move_upload_file($_FILES['img_file_src']['tmp_name'], $target))
            {
                $src = DATA_DIR . '/afficheimg/' . $name;
            }
        }
        else if (!empty($_POST['img_src']))
        {
            $src =$_POST['img_src'];
            if(!get_file_suffix($_POST['img_src'], $allow_suffix))
            {
                sys_msg($_LANG['invalid_type']);
            }
            if(strstr($src, 'http') && !strstr($src, $_SERVER['SERVER_NAME']))
            {
                $src = get_url_image($src);
            }
        }
        else
        {
            $links[] = array('text' => $_LANG['return_edit'], 'href' => 'flashplay.php?act=edit&id=' . $id);
            sys_msg($_LANG['src_empty'], 0, $links);
        }

        if (strpos($rt['src'], 'http') === false && $rt['src'] != $src)
        {
            @unlink(ROOT_PATH . $rt['src']);
        }
        $flashdb[$id] = array('src'=>$src,'url'=>$_POST['img_url'],'text'=>$_POST['img_text'],'sort'=>$_POST['img_sort']);

        // 实现排序
        $flashdb_sort   = array();
        $_flashdb       = array();
        foreach ($flashdb as $key => $value)
        {
            $flashdb_sort[$key] = $value['sort'];
        }
        asort($flashdb_sort, SORT_NUMERIC);
        foreach ($flashdb_sort as $key => $value)
        {
            $_flashdb[] = $flashdb[$key];
        }
        unset($flashdb, $flashdb_sort);

        put_flash_xml($_flashdb);
        set_flash_data($_CFG['flash_theme'], $error_msg = '');
        $links[] = array('text' => $_LANG['go_url'], 'href' => 'flashplay.php?act=list');
        sys_msg($_LANG['edit_ok'], 0, $links);
    }
}
elseif ($_REQUEST['act'] == 'install')
{
    check_authz_json('flash_manage');
    $flash_theme = trim($_GET['flashtpl']);
    if ($_CFG['flash_theme'] != $flash_theme)
    {
        $sql = "UPDATE " .$GLOBALS['ecs']->table('shop_config'). " SET value = '$flash_theme' WHERE code = 'flash_theme'";
        if ($db->query($sql, 'SILENT'))
        {
            clear_all_files(); //清除模板编译文件

            $error_msg = '';
            if (set_flash_data($flash_theme, $error_msg))
            {
                make_json_error($error_msg);
            }
            else
            {
                make_json_result($flash_theme, $_LANG['install_success']);
            }
        }
        else
        {
            make_json_error($db->error());
        }
    }
    else
    {
        make_json_result($flash_theme, $_LANG['install_success']);
    }
}

/*------------------------------------------------------ */
//-- 用户自定义
/*------------------------------------------------------ */

elseif ($_REQUEST['act']== 'custom_list')
{
    /* 标签初始化 */
    $group_list = array(
        'sys' => array('text' => $_LANG['system_set'], 'url' => ($_CFG['index_ad'] == 'cus') ? 'javascript:system_set();void(0);' : 'flashplay.php?act=list'),
        'cus' => array('text' => $_LANG['custom_set'], 'url' => '')
                       );

    /* 列表 */
    $ad_list = ad_list();
    $smarty->assign('ad_list', $ad_list['ad']);

    assign_query_info();
        $width_height = get_width_height();
//        if(isset($width_height['width'])|| isset($width_height['height']))
//        {
            $smarty->assign('width_height', sprintf($_LANG['width_height'], $width_height['width'], $width_height['height']));
//        }
    $smarty->assign('full_page', 1);
    $smarty->assign('current', 'cus');
    $smarty->assign('group_list', $group_list);
    $smarty->assign('group_selected', $_CFG['index_ad']);
    $smarty->assign('uri', $uri);
    $smarty->assign('ur_here', $_LANG['flashplay']);
    $smarty->assign('action_link_special', array('text' => $_LANG['add_flash'], 'href' => 'flashplay.php?act=custom_add'));

    /* 添加 */
    $ad = array('ad_name' => '', 'ad_type' => 0, 'ad_url' => 'http://', 'htmls' => '',
                'ad_status' =>'1', 'ad_id' => '0', 'url' => 'http://');
    $smarty->assign('ad', $ad);
    $smarty->assign('form_act', 'custom_insert');

    $smarty->display('flashplay_custom.htm');
}

/*------------------------------------------------------ */
//-- 用户自定义添加
/*------------------------------------------------------ */

elseif ($_REQUEST['act']== 'custom_add')
{
    /* 标签初始化 */
    $group_list = array(
        'sys' => array('text' => $_LANG['system_set'], 'url' => ($_CFG['index_ad'] == 'cus') ? 'javascript:system_set();void(0);' : 'flashplay.php?act=list'),
        'cus' => array('text' => $_LANG['custom_set'], 'url' => '')
                       );

    /* 列表 */
    $ad_list = ad_list();
    $smarty->assign('ad_list', $ad_list['ad']);

    assign_query_info();
        $width_height = get_width_height();
//        if(isset($width_height['width'])|| isset($width_height['height']))
//        {
            $smarty->assign('width_height', sprintf($_LANG['width_height'], $width_height['width'], $width_height['height']));
//        }
    $smarty->assign('full_page', 1);
    $smarty->assign('current', 'cus');
    $smarty->assign('group_list', $group_list);
    $smarty->assign('group_selected', $_CFG['index_ad']);
    $smarty->assign('uri', $uri);
    $smarty->assign('ur_here', $_LANG['add_ad']);
    $smarty->assign('action_link_special', array('text' => $_LANG['add_flash'], 'href' => 'flashplay.php?act=custom_add'));
    $smarty->assign('action_link', array('text' => $_LANG['ad_play_url'], 'href' => 'flashplay.php?act=custom_list'));
    /* 添加 */
    $ad = array('ad_name' => '', 'ad_type' => 0, 'ad_url' => 'http://', 'htmls' => '',
                'ad_status' =>'1', 'ad_id' => '0', 'url' => 'http://');
    $smarty->assign('ad', $ad);
    $smarty->assign('form_act', 'custom_insert');

    $smarty->display('flashplay_custom_add.htm');
}



/*------------------------------------------------------ */
//-- 用户自定义 添加广告入库
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'custom_insert')
{
    admin_priv('flash_manage');

    /* 定义当前时间 */
    define('GMTIME_UTC', gmtime()); // 获取 UTC 时间戳

    if (empty($_POST['ad']) || empty($_POST['content']) || empty($_POST['ad']['ad_name']))
    {
        $links[] = array('text' => $_LANG['back'], 'href' => 'flashplay.php?act=custom_list');
        sys_msg($_LANG['form_none'], 0, $links);
    }

    $filter = array();
    $filter['ad'] = $_POST['ad'];
    $filter['content'] = $_POST['content'];
    $ad_img = $_FILES;

    /* 配置接收文件类型 */
    switch ($filter['ad']['ad_type'])
    {
        case '0' :
        break;

        case '1' :
            $allow_suffix[] = 'swf';
        break;
    }

    /* 接收文件 */
    if ($ad_img['ad_img']['name'] && $ad_img['ad_img']['size'] > 0)
    {
        /* 检查文件合法性 */
        if(!get_file_suffix($ad_img['ad_img']['name'], $allow_suffix))
        {
            sys_msg($_LANG['invalid_type']);
        }

        /* 处理 */
        $name = date('Ymd');
        for ($i = 0; $i < 6; $i++)
        {
            $name .= chr(mt_rand(97, 122));
        }
        $name .= '.' . end(explode('.', $ad_img['ad_img']['name']));
        $target = ROOT_PATH . DATA_DIR . '/afficheimg/' . $name;

        if (move_upload_file($ad_img['ad_img']['tmp_name'], $target))
        {
            $src = DATA_DIR . '/afficheimg/' . $name;
        }
    }
    else if (!empty($filter['content']['url']))
    {
        /* 来自互联网图片 不可以是服务器地址 */
        if(strstr($filter['content']['url'], 'http') && !strstr($filter['content']['url'], $_SERVER['SERVER_NAME']))
        {
            /* 取互联网图片至本地 */
            $src = get_url_image($filter['content']['url']);
        }
        else{
            sys_msg($_LANG['web_url_no']);
        }
    }

    /* 入库 */
    switch ($filter['ad']['ad_type'])
    {
        case '0' :

        case '1' :
            $filter['content'] = $src;
        break;

        case '2' :

        case '3' :
            $filter['content'] = $filter['content']['htmls'];
        break;
    }
    $ad = array('ad_type' => $filter['ad']['ad_type'],
                'ad_name' => $filter['ad']['ad_name'],
                'add_time' => GMTIME_UTC,
                'content' => $filter['content'],
                'url' => $filter['ad']['url'],
                'ad_status' => $filter['ad']['ad_status']
               );
    $db->autoExecute($ecs->table('ad_custom'), $ad, 'INSERT', '', 'SILENT');
    $ad_id = $db->insert_id();

    /* 修改状态 */
    modfiy_ad_status($ad_id, $filter['ad']['ad_status']);

    /* 状态为启用 清除模板编译文件 */
    if ($filter['ad']['ad_status'] == 1)
    {
        clear_all_files();
    }

    $links[] = array('text' => $_LANG['back_custom_set'], 'href' => 'flashplay.php?act=custom_list');
    sys_msg($_LANG['edit_ok'], 0, $links);
}

/*------------------------------------------------------ */
//-- 用户自定义 删除广告
/*------------------------------------------------------ */

elseif($_REQUEST['act']== 'custom_del')
{
    admin_priv('flash_manage');

    $id = empty($_GET['id']) ? 0 : intval(trim($_GET['id']));
    if (!$id)
    {
        $links[] = array('text' => $_LANG['back_custom_set'], 'href' => 'flashplay.php?act=custom_list');
        sys_msg($_LANG['form_none'], 0, $links);
    }

    /* 修改状态 */
    modfiy_ad_status($id, 0);

    /* 清除模板编译文件 */
    clear_all_files();

    $query = $db->query("DELETE FROM " . $ecs->table('ad_custom') . " WHERE ad_id = $id");

    $links[] = array('text' => $_LANG['back_custom_set'], 'href' => 'flashplay.php?act=custom_list');
    if ($query)
    {
        sys_msg($_LANG['edit_ok'], 0, $links);
    }
    else
    {
        sys_msg($_LANG['edit_no'], 0, $links);
    }
}

/*------------------------------------------------------ */
//-- 用户自定义 启用与关闭广告
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'custom_status')
{
    check_authz_json('flash_manage');

    $ad_status = empty($_GET['ad_status']) ? 1 : 0;
    $id = empty($_GET['id']) ? 0 : intval(trim($_GET['id']));
    $is_ajax = $_GET['is_ajax'];
    if (!$id || $is_ajax != '1')
    {
        make_json_error($_LANG['edit_no']);
    }

    /* 修改状态 */
    $links[] = array('text' => $_LANG['back_custom_set'], 'href' => 'flashplay.php?act=custom_list');
    if (modfiy_ad_status($id, $ad_status))
    {
        /* 清除模板编译文件 */
        clear_all_files();

        /* 标签初始化 */
        $sql = "SELECT  value FROM " . $ecs->table("shop_config") . " WHERE id =337";
        $shop_config = $db->getRow($sql);
        $group_list = array(
            'sys' => array('text' => $_LANG['system_set'], 'url' => ($shop_config['value'] == 'cus') ? 'javascript:system_set();void(0);' : 'flashplay.php?act=list'),
            'cus' => array('text' => $_LANG['custom_set'], 'url' => '')
                           );

        /* 列表 */
        $ad_list = ad_list();
        $smarty->assign('ad_list', $ad_list['ad']);
        $smarty->assign('current', 'cus');
        $smarty->assign('group_list', $group_list);
        $smarty->assign('group_selected', $_CFG['index_ad']);
        $smarty->assign('uri', $uri);
        $smarty->assign('ur_here', $_LANG['flashplay']);
        $smarty->assign('action_link_special', array('text' => $_LANG['add_flash'], 'href' => 'flashplay.php?act=custom_add'));
        /* 添加 */
        $ad = array('ad_name' => '', 'ad_type' => 0, 'ad_url' => 'http://', 'htmls' => '',
                    'ad_status' =>'1', 'ad_id' => '0', 'url' => 'http://');
        $smarty->assign('ad', $ad);
        $smarty->assign('form_act', 'custom_insert');

        $smarty->fetch('flashplay_custom.htm');

        make_json_result($smarty->fetch('flashplay_custom.htm'));
    }
    else
    {
        make_json_error($_LANG['edit_no']);
    }
}

/*------------------------------------------------------ */
//-- 用户自定义 修改
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'custom_edit')
{
    $id = empty($_GET['id']) ? 0 : intval(trim($_GET['id']));

    /* 查询自定义广告信息 */
    $sql = "SELECT ad_id, ad_type, content, url, ad_status, ad_name FROM " . $GLOBALS['ecs']->table("ad_custom") . " WHERE ad_id = $id LIMIT 0, 1";
    $ad = $GLOBALS['db']->getRow($sql);

    assign_query_info();
    $width_height = get_width_height();
    $smarty->assign('width_height', sprintf($_LANG['width_height'], $width_height['width'], $width_height['height']));

    $smarty->assign('group_selected', $_CFG['index_ad']);
    $smarty->assign('uri', $uri);
    $smarty->assign('ur_here', $_LANG['flashplay']);
    $smarty->assign('action_link', array('text' => $_LANG['ad_play_url'], 'href' => 'flashplay.php?act=custom_list'));
    $smarty->assign('ur_here', $_LANG['edit_ad']);

    /* 添加 */
    $smarty->assign('ad', $ad);
    $smarty->display('flashplay_ccustom_edit.htm');


}

/*------------------------------------------------------ */
//-- 首页维护
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'index_maintain')
{
    admin_priv('index_maintain');
    /* 包含插件语言项 */
    $sql = "SELECT code FROM ".$ecs->table('plugins');
    $rs = $db->query($sql);
    while ($row = $db->FetchRow($rs))
    {
        /* 取得语言项 */
        if (file_exists(ROOT_PATH . 'plugins/'.$row['code'].'/languages/common_'.$_CFG['lang'].'.php'))
        {
            include_once(ROOT_PATH . 'plugins/'.$row['code'].'/languages/common_'.$_CFG['lang'].'.php');
        }
    }
    $curr_template = $_CFG['template'];
    $arr_library   = array();
    $library_path  = '../themes/' . $curr_template . '/library';
    $library_dir   = @opendir($library_path);
    $curr_library  = '';

    while ($file = @readdir($library_dir))
    {
        if ($file == "index_maintain.lbi" || $file == "index_maintain_css.lbi")
        {
            $filename               = substr($file, 0, -4);

            $arr_library[$filename] = $file. ' - ' . @$_LANG['template_libs'][$filename];

            if ($curr_library == '')
            {
                $curr_library = $filename;
            }
        }
    }



    @closedir($library_dir);

    $lib = load_library($curr_template, $curr_library);
    /* 图片列表 */
    $sql = "SELECT * FROM " . $ecs->table('index_gallery');
    $img_list = $db->getAll($sql);
    assign_query_info();

    $smarty->assign('thumb_width', $_CFG['thumb_width']);
    $smarty->assign('thumb_height', $_CFG['thumb_height']);
    $smarty->assign('libraries',    $arr_library);
    $smarty->assign('ur_here', $_LANG['index_maintain']);
    $smarty->assign('library_html', $lib['html']);
    $smarty->assign('img_list', $img_list);
    $smarty->display('index_maintain.htm');
}

/*------------------------------------------------------ */
//-- 更新首页内容
/*------------------------------------------------------ */

if ($_REQUEST['act'] == 'update_library')
{
    check_authz_json('index_maintain');
    $html = stripslashes(json_str_iconv($_POST['html']));
    $lib_file = '../themes/' . $_CFG['template'] . '/library/'. $_POST['lib'] .'.lbi';
    $lib_file = str_replace("0xa", '', $lib_file); // 过滤 0xa 非法字符

    $org_html = str_replace("\xEF\xBB\xBF", '', file_get_contents($lib_file));

    if (@file_exists($lib_file) === true && @file_put_contents($lib_file, $html))
    {
        @file_put_contents('../temp/backup/library/' . $_CFG['template'] . '-' . $_POST['lib'] . '.lbi', $org_html);

        make_json_result('', '更新首页内容成功');
    }
    else
    {
        make_json_error(sprintf('首页更新内容失败。请检查 %s 目录是否可以写入。', 'themes/' . $_CFG['template'] . '/library'));
    }
}

/*------------------------------------------------------ */
//-- 还原首页内容
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'restore_library')
{
    admin_priv('index_maintain');
    $lib_name   = trim($_GET['lib']);
    $lib_file   = '../themes/' . $_CFG['template'] . '/library/' . $lib_name . '.lbi';
    $lib_file   = str_replace("0xa", '', $lib_file); // 过滤 0xa 非法字符
    $lib_backup = '../temp/backup/library/' . $_CFG['template'] . '-' . $lib_name . '.lbi';
    $lib_backup = str_replace("0xa", '', $lib_backup); // 过滤 0xa 非法字符

    if (file_exists($lib_backup) && filemtime($lib_backup) >= filemtime($lib_file))
    {
        make_json_result(str_replace("\xEF\xBB\xBF", '',file_get_contents($lib_backup)));
    }
    else
    {
        make_json_result(str_replace("\xEF\xBB\xBF", '',file_get_contents($lib_file)));
    }
}

/*------------------------------------------------------ */
//-- 载入指定首页的内容
/*------------------------------------------------------ */

if ($_REQUEST['act'] == 'load_library')
{
    $library = load_library($_CFG['template'], trim($_GET['lib']));
    $message = ($library['mark'] & 7) ? '' : $_LANG['library_not_written'];

    make_json_result($library['html'], $message);
}
//上传图片
elseif ($_REQUEST['act'] == 'update')
{
    admin_priv('index_maintain');
    include_once(ROOT_PATH . '/includes/cls_image.php');
    $image = new cls_image($_CFG['bgcolor']);
    /* 检查图片：如果有错误，检查尺寸是否超过最大值；否则，检查文件类型 */
    if (isset($_FILES['img_url']['error'])) // php 4.2 版本才支持 error
    {
        // 最大上传文件大小
        $php_maxsize = ini_get('upload_max_filesize');
        $htm_maxsize = '2M';
        foreach ($_FILES['img_url']['error'] AS $key => $value)
        {
            if ($value == 0)
            {
                if (!$image->check_img_type($_FILES['img_url']['type'][$key]))
                {
                    sys_msg(sprintf('第%s个图片格式不正确!', $key + 1), 1, array(), false);
                }
            }
            elseif ($value == 1)
            {
                sys_msg(sprintf('第%s个图片文件太大了（最大值：%s），无法上传。', $key + 1, $php_maxsize), 1, array(), false);
            }
            elseif ($_FILES['img_url']['error'] == 2)
            {
                sys_msg(sprintf('第%s个图片文件太大了（最大值：%s），无法上传。', $key + 1, $htm_maxsize), 1, array(), false);
            }
            elseif ($value == 4)
            {
                sys_msg('请选择要上传的图片', 1, array(), false);
            }
        }
    }
    /* 4.1版本 */
    else
    {
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
    $image_files = $_FILES['img_url'];
    $dir='test';//文件名
    $img_name='test.jpg';//图片名
    /* 创建目录 */
//    $dir = ROOT_PATH . $image->data_dir . '/afficheimg' . '/';
    $dir = ROOT_PATH . 'data' . '/afficheimg' . '/';
    /* 如果目标目录不存在，则创建它 */
    $image_array=array();
    if (!file_exists($dir))
    {
        if (!make_dir($dir))
        {
            /* 创建目录失败 */
            sys_msg(sprintf($GLOBALS['_LANG']['directory_readonly'], $dir));
        }
    }
    foreach($image_files['name'] AS $key=>$value)
    {
        $ex_name = explode('.', $image_files['name'][$key]);
        $name .= '.' . end($ex_name);
        if (move_upload_file($image_files['tmp_name'][$key], $dir.$image_files['name'][$key]))
        {
            $images_name='data/afficheimg/'.$image_files['name'][$key];
            $sql = "SELECT COUNT(*) FROM ecs_index_gallery WHERE img_url='$images_name' ";
            $r = $db->getOne($sql);
            if($r == 0)
            {
                $image_array[] = $images_name;
            }
        }
        else
        {
            $image_err.=$image_files['name'][$key].'/';
        }
    }
    if ($image_err!=NULL)
    {
        sys_msg(sprintf($GLOBALS['_LANG']['upload_failure'], $image_err));
    }
    $str="";
    for($i=0;$i<count($image_array);$i++)
    {
        if($i==0)
        {
            $str='("'.(string)$image_array[$i].'")';
        }
        else
        {
            $str .=',("'.(string)$image_array[$i].'")';
        }
    }
    if(count($image_array)!=0)
    {
        $sql = "INSERT INTO " . $ecs->table('index_gallery')." (img_url) VALUES $str";
        $res = $db->query($sql);
    }
    $links[] = array('text' => '返回首页维护', 'href' => 'flashplay.php?act=index_maintain');
    sys_msg('操作成功', 0, $links);
}

/*------------------------------------------------------ */
//-- 删除图片
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'drop_image')
{
    admin_priv('index_maintain');
    $img_id = empty($_REQUEST['img_id']) ? 0 : intval($_REQUEST['img_id']);

    /* 删除图片文件 */
    $sql = "SELECT img_url " .
        " FROM " . $GLOBALS['ecs']->table('index_gallery') .
        " WHERE img_id = '$img_id'";
    $row = $GLOBALS['db']->getRow($sql);

    if ($row['img_url'] != '' && is_file('../' . $row['img_url']))
    {
        @unlink('../' . $row['img_url']);
    }
    /* 删除数据 */
    $sql = "DELETE FROM " . $GLOBALS['ecs']->table('index_gallery') . " WHERE img_id = '$img_id' LIMIT 1";
    $GLOBALS['db']->query($sql);

    clear_cache_files();
    make_json_result($img_id);
}
/*------------------------------------------------------ */
//-- 用户自定义 更新数据库
/*------------------------------------------------------ */

elseif ($_REQUEST['act'] == 'custom_update')
{
    admin_priv('flash_manage');

    if (empty($_POST['ad']) || empty($_POST['content']) || empty($_POST['ad']['ad_name']) || empty($_POST['ad']['id']))
    {
        $links[] = array('text' => $_LANG['back'], 'href' => 'flashplay.php?act=custom_list');
        sys_msg($_LANG['form_none'], 0, $links);
    }

    $filter = array();
    $filter['ad'] = $_POST['ad'];
    $filter['content'] = $_POST['content'];
    $ad_img = $_FILES;

    /* 查询自定义广告信息 */
    $sql = "SELECT ad_id, ad_type, content, url, ad_status, ad_name FROM " . $GLOBALS['ecs']->table("ad_custom") . " WHERE ad_id = " . $filter['ad']['id'] ." LIMIT 0, 1";
    $ad_info = $GLOBALS['db']->getRow($sql);

    /* 配置接收文件类型 */
    switch ($filter['ad']['ad_type'])
    {
        case '0' :
        break;

        case '1' :
            $allow_suffix[] = 'swf';
        break;
    }

    /* 接收文件 */
    if ($ad_img['ad_img']['name'] && $ad_img['ad_img']['size'] > 0)
    {
        /* 检查文件合法性 */
        if(!get_file_suffix($ad_img['ad_img']['name'], $allow_suffix))
        {
            sys_msg($_LANG['invalid_type']);
        }

        /* 处理 */
        $name = date('Ymd');
        for ($i = 0; $i < 6; $i++)
        {
            $name .= chr(mt_rand(97, 122));
        }
        $name .= '.' . end(explode('.', $ad_img['ad_img']['name']));
        $target = ROOT_PATH . DATA_DIR . '/afficheimg/' . $name;

        if (move_upload_file($ad_img['ad_img']['tmp_name'], $target))
        {
            $src = DATA_DIR . '/afficheimg/' . $name;
        }
    }
    else if (!empty($filter['content']['url']))
    {
        /* 来自互联网图片 不可以是服务器地址 */
        if(strstr($filter['content']['url'], 'http') && !strstr($filter['content']['url'], $_SERVER['SERVER_NAME']))
        {
            /* 取互联网图片至本地 */
            $src = get_url_image($filter['content']['url']);
        }
        else{
            sys_msg($_LANG['web_url_no']);
        }
    }

    /* 入库 */
    switch ($filter['ad']['ad_type'])
    {
        case '0' :

        case '1' :
            $filter['content'] = !is_file(ROOT_PATH . $src) && (trim($src) == '') ? $ad_info['content'] : $src;
        break;

        case '2' :

        case '3' :
            $filter['content'] = $filter['content']['htmls'];
        break;
    }
    $ad = array('ad_type' => $filter['ad']['ad_type'],
                'ad_name' => $filter['ad']['ad_name'],
                'content' => $filter['content'],
                'url' => $filter['ad']['url'],
                'ad_status' => $filter['ad']['ad_status']
               );
    $db->autoExecute($ecs->table('ad_custom'), $ad, 'UPDATE', 'ad_id = ' . $ad_info['ad_id'], 'SILENT');

    /* 修改状态 */
    modfiy_ad_status($ad_info['ad_id'], $filter['ad']['ad_status']);

    /* 状态为启用 清除模板编译文件 */
    if ($filter['ad']['ad_status'] == 1)
    {
        clear_all_files();
    }

    $links[] = array('text' => $_LANG['back_custom_set'], 'href' => 'flashplay.php?act=custom_list');
    sys_msg($_LANG['edit_ok'], 0, $links);
}

function get_flash_xml()
{
    $flashdb = array();
    if (file_exists(ROOT_PATH . DATA_DIR . '/flash_data.xml'))
    {

        // 兼容v2.7.0及以前版本
        if (!preg_match_all('/item_url="([^"]+)"\slink="([^"]+)"\stext="([^"]*)"\ssort="([^"]*)"/', file_get_contents(ROOT_PATH . DATA_DIR . '/flash_data.xml'), $t, PREG_SET_ORDER))
        {
            preg_match_all('/item_url="([^"]+)"\slink="([^"]+)"\stext="([^"]*)"/', file_get_contents(ROOT_PATH . DATA_DIR . '/flash_data.xml'), $t, PREG_SET_ORDER);
        }

        if (!empty($t))
        {
            foreach ($t as $key => $val)
            {
                $val[4] = isset($val[4]) ? $val[4] : 0;
                $flashdb[] = array('src'=>$val[1],'url'=>$val[2],'text'=>$val[3],'sort'=>$val[4]);
            }
        }
    }
    return $flashdb;
}

function put_flash_xml($flashdb)
{
    if (!empty($flashdb))
    {
        $xml = '<?xml version="1.0" encoding="' . EC_CHARSET . '"?><bcaster>';
        foreach ($flashdb as $key => $val)
        {
            $xml .= '<item item_url="' . $val['src'] . '" link="' . $val['url'] . '" text="' . $val['text'] . '" sort="' . $val['sort'] . '"/>';
        }
        $xml .= '</bcaster>';
        file_put_contents(ROOT_PATH . DATA_DIR . '/flash_data.xml', $xml);
    }
    else
    {
        @unlink(ROOT_PATH . DATA_DIR . '/flash_data.xml');
    }
}

function get_url_image($url)
{
    $ext = strtolower(end(explode('.', $url)));
    if($ext != "gif" && $ext != "jpg" && $ext != "png" && $ext != "bmp" && $ext != "jpeg")
    {
        return $url;
    }

    $name = date('Ymd');
    for ($i = 0; $i < 6; $i++)
    {
        $name .= chr(mt_rand(97, 122));
    }
    $name .= '.' . $ext;
    $target = ROOT_PATH . DATA_DIR . '/afficheimg/' . $name;

    $tmp_file = DATA_DIR . '/afficheimg/' . $name;
    $filename = ROOT_PATH . $tmp_file;

    $img = file_get_contents($url);

    $fp = @fopen($filename, "a");
    fwrite($fp, $img);
    fclose($fp);

    return $tmp_file;
}

function get_width_height()
{
    $curr_template = $GLOBALS['_CFG']['template'];
    $path = ROOT_PATH . 'themes/' . $curr_template . '/library/';
    $template_dir = @opendir($path);

    $width_height = array();
    while($file = readdir($template_dir))
    {
        if($file == 'index_ad.lbi')
        {
            $string = file_get_contents($path . $file);
            $pattern_width = '/var\s*swf_width\s*=\s*(\d+);/';
            $pattern_height = '/var\s*swf_height\s*=\s*(\d+);/';
            preg_match($pattern_width, $string, $width);
            preg_match($pattern_height, $string, $height);
            if(isset($width[1]))
            {
                $width_height['width'] = $width[1];
            }
            if(isset($height[1]))
            {
                $width_height['height'] = $height[1];
            }
            break;
        }
    }

    return $width_height;
}

function get_flash_templates($dir)
{
    $flashtpls = array();
    $template_dir        = @opendir($dir);
    while ($file = readdir($template_dir))
    {
        if ($file != '.' && $file != '..' && is_dir($dir . $file) && $file != '.svn' && $file != 'index.htm')
        {
            $flashtpls[] = get_flash_tpl_info($dir, $file);
        }
    }
    @closedir($template_dir);
    return $flashtpls;
}

function get_flash_tpl_info($dir, $file)
{
    $info = array();
    if (is_file($dir . $file . '/preview.jpg'))
    {
        $info['code'] = $file;
        $info['screenshot'] = '../data/flashdata/' . $file . '/preview.jpg';
        $arr = array_slice(file($dir . $file . '/cycle_image.js'), 1, 2);
        $info_name = explode(':', $arr[0]);
        $info_desc = explode(':', $arr[1]);
        $info['name'] = isset($info_name[1])?trim($info_name[1]):'';
        $info['desc'] = isset($info_desc[1])?trim($info_desc[1]):'';
    }
    return $info;
}

function set_flash_data($tplname, &$msg)
{
    $flashdata = get_flash_xml();
    if (empty($flashdata))
    {
        $flashdata[] = array(
                                'src' => 'data/afficheimg/20081027angsif.jpg',
                                'text' => 'wjike',
                                'url' =>'http://www.ecshop.com'
                            );
        $flashdata[] = array(
                                'src' => 'data/afficheimg/20081027wdwd.jpg',
                                'text' => 'wjike',
                                'url' =>'http://www.wjike.com'
                            );
        $flashdata[] = array(
                                'src' => 'data/afficheimg/20081027xuorxj.jpg',
                                'text' => 'wjike',
                                'url' =>'http://www.wjike.com'
                            );
    }
    switch($tplname)
    {
        case 'uproll':
            $msg = set_flash_uproll($tplname, $flashdata);
            break;
        case 'redfocus':
        case 'pinkfocus':
        case 'dynfocus':
            $msg = set_flash_focus($tplname, $flashdata);
            break;
        case 'test':
            $msg = set_flash_focus($tplname, $flashdata);
            break;
        case 'default':
        default:
            $msg = set_flash_default($tplname, $flashdata);
            break;
    }
    return $msg !== true;
}

function set_flash_uproll($tplname, $flashdata)
{
    $data_file = ROOT_PATH . DATA_DIR . '/flashdata/' . $tplname . '/data.xml';
    $xmldata = '<?xml version="1.0" encoding="' . EC_CHARSET . '"?><myMenu>';
    foreach ($flashdata as $data)
    {
        $xmldata .= '<myItem pic="' . $data['src'] . '" url="' . $data['url'] . '" />';
    }
    $xmldata .= '</myMenu>';
    file_put_contents($data_file, $xmldata);
    return true;
}

function set_flash_focus($tplname, $flashdata)
{
    if($tplname=="test")
    {
        $data_file = ROOT_PATH . DATA_DIR . '/flashdata/' . $tplname . '/data.js';
        $jsdata = '';
        $jsdata2 = array('test' => 'var list=');
        $count = 1;
        $join = '';
        foreach ($flashdata as $data)
        {
            $jsdata .= 'var test'.$count.'={'. "\n".'imgUrl:"' . $data['src'] . '",' . "\n";
            $jsdata .= 'imgtext' . ':"' . $data['text'] . '",' . "\n";
            $jsdata .= 'imgLink' . ':escape("' . $data['url'] . '")' . "\n" .'}'. "\n";
            if ($count == 1)
            {
                $jsdata2['test'] .= '[test' . $count;
            }
            else
            {
                $jsdata2['test'] .=',' .'test' . $count;
            }
            ++$count;
        }
        $jsdata2['test'] .=']';
        file_put_contents($data_file, $jsdata . "\n" . $jsdata2['test'] . ";");
        return true;
    }
    $data_file = ROOT_PATH . DATA_DIR . '/flashdata/' . $tplname . '/data.js';
    $jsdata = '';
    $jsdata2 = array('url' => 'var pics=', 'txt' => 'var texts=', 'link' => 'var links=');
    $count = 1;
    $join = '';
    foreach ($flashdata as $data)
    {
        $jsdata .= 'imgUrl' . $count . '="' . $data['src'] . '";' . "\n";
        $jsdata .= 'imgtext' . $count . '="' . $data['text'] . '";' . "\n";
        $jsdata .= 'imgLink' . $count . '=escape("' . $data['url'] . '");' . "\n";
        if ($count != 1)
        {
            $join = '+"|"+';
        }
        $jsdata2['url'] .= $join . 'imgUrl' . $count;
        $jsdata2['txt'] .= $join . 'imgtext' . $count;
        $jsdata2['link'] .= $join . 'imgLink' . $count;
        ++$count;
    }
    file_put_contents($data_file, $jsdata . "\n" . $jsdata2['url'] . ";\n" . $jsdata2['link'] . ";\n" . $jsdata2['txt'] . ";");
    return true;
}

function set_flash_default($tplname, $flashdata)
{
    $data_file = ROOT_PATH . DATA_DIR . '/flashdata/' . $tplname . '/data.xml';
    $xmldata = '<?xml version="1.0" encoding="' . EC_CHARSET . '"?><bcaster>';
    foreach ($flashdata as $data)
    {
        $xmldata .= '<item item_url="' . $data['src'] . '" link="' . $data['url'] . '" />';
    }
    $xmldata .= '</bcaster>';
    file_put_contents($data_file, $xmldata);
    return true;
}

/**
 *  获取用户自定义广告列表信息
 *
 * @access  public
 * @param
 *
 * @return void
 */
function ad_list()
{
    $result = get_filter();
    if ($result === false)
    {
        $aiax = isset($_GET['is_ajax']) ? $_GET['is_ajax'] : 0;
        $filter = array();
        $filter['sort_by'] = 'add_time';
        $filter['sort_order'] = 'DESC';

        /* 过滤信息 */
        $where = 'WHERE 1 ';

        /* 查询 */
        $sql = "SELECT ad_id, CASE WHEN ad_type = 0 THEN '图片'
                                   WHEN ad_type = 1 THEN 'Flash'
                                   WHEN ad_type = 2 THEN '代码'
                                   WHEN ad_type = 3 THEN '文字'
                                   ELSE '' END AS type_name, ad_name, add_time, CASE WHEN ad_status = 1 THEN '启用' ELSE '关闭' END AS status_name, ad_type, ad_status
                FROM " . $GLOBALS['ecs']->table("ad_custom") . "
                $where
                ORDER BY " . $filter['sort_by'] . " " . $filter['sort_order']. " ";

        set_filter($filter, $sql);
    }
    else
    {
        $sql    = $result['sql'];
        $filter = $result['filter'];
    }

    $row = $GLOBALS['db']->getAll($sql);

    /* 格式化数据 */
    foreach ($row AS $key => $value)
    {
        $row[$key]['add_time'] = local_date($GLOBALS['_CFG']['time_format'], $value['add_time']);
    }

    $arr = array('ad' => $row, 'filter' => $filter);

    return $arr;
}


/**
 * 载入库项目内容
 *
 * @access  public
 * @param   string  $curr_template  模版名称
 * @param   string  $lib_name       库项目名称
 * @return  array
 */
function load_library($curr_template, $lib_name)
{
    $lib_name = str_replace("0xa", '', $lib_name); // 过滤 0xa 非法字符

    $lib_file    = '../themes/' . $curr_template . '/library/' . $lib_name . '.lbi';
    $arr['mark'] = file_mode_info($lib_file);
    $arr['html'] = str_replace("\xEF\xBB\xBF", '', file_get_contents($lib_file));

    return $arr;
}
/**
 * 修改自定义相状态
 *
 * @param   int     $ad_id       自定义广告 id
 * @param   int     $ad_status   自定义广告 状态 0，关闭；1，开启。
 * @access  private
 * @return  Bool
 */
 function modfiy_ad_status($ad_id, $ad_status = 0)
 {
    $return = false;

    if (empty($ad_id))
    {
        return $return;
    }

    /* 查询自定义广告信息 */
    $sql = "SELECT ad_type, content, url, ad_status FROM " . $GLOBALS['ecs']->table("ad_custom") . " WHERE ad_id = $ad_id LIMIT 0, 1";
    $ad = $GLOBALS['db']->getRow($sql);

    if ($ad_status == 1)
    {
        /* 如果当前自定义广告是关闭状态 则修改其状态为启用 */
        if ($ad['ad_status'] == 0)
        {
            $sql = "UPDATE " . $GLOBALS['ecs']->table("ad_custom") . " SET ad_status = 1 WHERE ad_id = $ad_id";
            $GLOBALS['db']->query($sql);
        }

        /* 关闭 其它自定义广告 */
        $sql = "UPDATE " . $GLOBALS['ecs']->table("ad_custom") . " SET ad_status = 0 WHERE ad_id <> $ad_id";
        $GLOBALS['db']->query($sql);

        /* 用户自定义广告开启 */
        $sql = "UPDATE " . $GLOBALS['ecs']->table("shop_config") . " SET value = 'cus' WHERE id =337";
        $GLOBALS['db']->query($sql);
    }
    else
    {
        /* 如果当前自定义广告是关闭状态 则检查是否存在启用的自定义广告 */
        /* 如果无 则启用系统默认广告播放器 */
        if ($ad['ad_status'] == 0)
        {
            $sql = "SELECT COUNT(ad_id) FROM " . $GLOBALS['ecs']->table("ad_custom") . " WHERE ad_status = 1";
            $ad_status_1 = $GLOBALS['db']->getOne($sql);
            if (empty($ad_status_1))
            {
                $sql = "UPDATE " . $GLOBALS['ecs']->table("shop_config") . " SET value = 'sys' WHERE id =337";
                $GLOBALS['db']->query($sql);
            }
            else
            {
                $sql = "UPDATE " . $GLOBALS['ecs']->table("shop_config") . " SET value = 'cus' WHERE id =337";
                $GLOBALS['db']->query($sql);
            }
        }
        else
        {
            /* 当前自定义广告是开启状态 关闭之 */
            /* 如果无 则启用系统默认广告播放器 */
            $sql = "UPDATE " . $GLOBALS['ecs']->table("ad_custom") . " SET ad_status = 0 WHERE ad_id = $ad_id";
            $GLOBALS['db']->query($sql);

            $sql = "UPDATE " . $GLOBALS['ecs']->table("shop_config") . " SET value = 'sys' WHERE id =337";
            $GLOBALS['db']->query($sql);
        }
    }

    return $return = true;
 }
/**
 * 图片上传的处理函数
 *
 * @access      public
 * @param       array       upload       包含上传的图片文件信息的数组
 * @param       array       dir          文件要上传在$this->data_dir下的目录名。如果为空图片放在则在$this->images_dir下以当月命名的目录下
 * @param       array       img_name     上传图片名称，为空则随机生成
 * @return      mix         如果成功则返回文件名，否则返回false
 */
function upload_image($upload, $dir = '', $img_name = '')
{
    /* 创建目录 */
    $dir = ROOT_PATH . 'themes/default/images/adimages' . $dir . '/';
    if ($img_name)
    {
        $img_name = $dir . $img_name; // 将图片定位到正确地址
    }

    /* 如果目标目录不存在，则创建它 */
    if (!file_exists($dir))
    {
        if (!make_dir($dir))
        {
            /* 创建目录失败 */
            $this->error_msg = sprintf($GLOBALS['_LANG']['directory_readonly'], $dir);
            $this->error_no  = ERR_DIRECTORY_READONLY;

            return false;
        }
    }

    if (empty($img_name))
    {
        $img_name = $image->unique_name($dir);
        $img_name = $dir . $img_name . $image->get_filetype($upload['name']);
    }

    if (!$image->check_img_type($upload['type']))
    {
        $image->error_msg = $GLOBALS['_LANG']['invalid_upload_image_type'];
        $image->error_no  =  ERR_INVALID_IMAGE_TYPE;
        return false;
    }

    /* 允许上传的文件类型 */
    $allow_file_types = '|GIF|JPG|JEPG|PNG|BMP|SWF|';
    if (!check_file_type($upload['tmp_name'], $img_name, $allow_file_types))
    {
        $this->error_msg = $GLOBALS['_LANG']['invalid_upload_image_type'];
        $this->error_no  =  ERR_INVALID_IMAGE_TYPE;
        return false;
    }

    if ($image->move_file($upload, $img_name))
    {
        return str_replace(ROOT_PATH, '', $img_name);
    }
    else
    {
        $this->error_msg = sprintf($GLOBALS['_LANG']['upload_failure'], $upload['name']);
        $this->error_no  = ERR_UPLOAD_FAILURE;

        return false;
    }
}
?>