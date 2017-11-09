<?php

/**
 * ECSHOP 场馆管理管理程序
**/

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
$uri = $ecs->url();
$allow_suffix = array('gif', 'jpg', 'png', 'jpeg', 'bmp');
$exc = new exchange($ecs->table("venue_manage"), $db, 'venue_id', 'venue_name');

/* act操作项的初始化 */
if (empty($_REQUEST['act']))
{
    $_REQUEST['act'] = 'list';
}
else
{
    $_REQUEST['act'] = trim($_REQUEST['act']);
}

/*------------------------------------------------------ */
//-- 场馆列表
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'venue_list')
{
    /* 获取场馆列表 */
    $venue_list = venue_list();

    /* 模板赋值 */
    $smarty->assign('ur_here',      $_LANG['venue_manage']);
    $smarty->assign('action_link',  array('href' => 'venue.php?act=add', 'text' => $_LANG['add_venue']));
    $smarty->assign('full_page',    1);

    $smarty->assign('venue_info',     $venue_list);

    /* 列表页面 */
    assign_query_info();
    $smarty->display('venue_list.htm');
}
/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    $cat_list = venue_list();
    $smarty->assign('venue_info',     $cat_list);

    make_json_result($smarty->fetch('venue_list.htm'));
}
/*------------------------------------------------------ */
//-- 添加场馆
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'add')
{
    /* 模板赋值 */
    $smarty->assign('ur_here',      $_LANG['add_venue']);
    $smarty->assign('action_link',  array('href' => 'venue.php?act=venue_list', 'text' => $_LANG['venue_manage']));
    $smarty->assign('form_act',     'insert');
    $smarty->assign('venue_info',     array('is_show' => 1));

    /* 显示页面 */
    assign_query_info();
    $smarty->display('venue_info.htm');
}

/*------------------------------------------------------ */
//-- 场馆添加时的处理
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'insert')
{
    /* 初始化变量 */
    $venue['venue_name']     = !empty($_POST['venue_name'])     ? trim($_POST['venue_name'])     : '';
    $floor = $_POST['floor'];
    $arr = array();
    for($i=0;$i<count($floor['desc']);$i++)
    {
        $arr[$i]['floor_desc'] = $floor['desc'][$i];
        $arr[$i]['floor_href'] = $floor['file'][$i];
        $arr[$i]['goods_ids'] = $floor['goods'][$i];
        $arr[$i]['floor_ids'] = $floor['floors'][$i];
        $arr[$i]['floor_style'] = $floor['floor_style'][$i];
        $arr[$i]['is_show'] = $floor['is_show'][$i];
        $arr[$i]['floor_sort'] = $floor['floor_sort'][$i];
    }
    if (venue_exists($venue['venue_name']))
    {
        /* 同级别下不能有重复的分类名称 */
       $link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
       sys_msg($_LANG['venue_exist'], 0, $link);
    }
    include_once(ROOT_PATH . '/includes/cls_image.php');
    $image = new cls_image($_CFG['bgcolor']);
    /* 检查图片：如果有错误，检查尺寸是否超过最大值；否则，检查文件类型 */
    if (isset($_FILES['floor']['error']['url'])) // php 4.2 版本才支持 error
    {
        // 最大上传文件大小
        $php_maxsize = ini_get('upload_max_filesize');
        $htm_maxsize = '2M';
        foreach ($_FILES['floor']['error']['url'] AS $key => $value)
        {
            if ($value == 0)
            {
                if (!$image->check_img_type($_FILES['floor']['type']['url'][$key]))
                {
                    sys_msg(sprintf('第%s个图片格式不正确!', $key + 1), 1, array(), false);
                }
            }
            elseif ($value == 1)
            {
                sys_msg(sprintf('第%s个图片文件太大了（最大值：%s），无法上传。', $key + 1, $php_maxsize), 1, array(), false);
            }
            elseif ($_FILES['floor']['img_url']['error'] == 2)
            {
                sys_msg(sprintf('第%s个图片文件太大了（最大值：%s），无法上传。', $key + 1, $htm_maxsize), 1, array(), false);
            }
            elseif ($value == 4)
            {
//                unset($_FILES['floor']['name']['url'][$key]);
//                sys_msg('请选择要上传的图片', 1, array(), false);
            }
        }
    }
    /* 4.1版本 */
    else
    {
        foreach ($_FILES['floor']['img_url']['tmp_name'] AS $key => $value)
        {
            if ($value != 'none')
            {
                if (!$image->check_img_type($_FILES['floor']['img_url']['type'][$key]))
                {
                    sys_msg(sprintf($_LANG['invalid_img_url'], $key + 1), 1, array(), false);
                }
            }
        }
    }
    $image_files = $_FILES['floor'];
    /* 创建目录 */
    $dir = ROOT_PATH . 'data' . '/afficheimg/'.$venue_id.'_images/';
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
    foreach($image_files['name']['url'] AS $key=>$value)
    {
        if(!empty($value))
        {
            $name = date('Ymd');
            for ($i = 0; $i < 6; $i++)
            {
                $name .= chr(mt_rand(97, 122));
            }
            $end = explode('.', $_FILES['floor']['name']['url'][$key]);
            $name .= '.' . end($end);
            if (move_upload_file($image_files['tmp_name']['url'][$key], $dir.$name))
            {
                $arr[$key]['floor_img'] =  'data' . '/afficheimg/'.$venue_id.'_images/'.$name;
                $images_name='data/afficheimg/'.$name;
                $sql = "SELECT COUNT(*) FROM ecs_venue_floor_manage WHERE floor_img='$images_name' AND venue_id='$venue_id'";
                $r = $db->getOne($sql);
                if($r == 0)
                {
                    $image_array[] = $images_name;
                }
            }
            else
            {
                $image_err.=$image_files['name']['url'][$key].'/';
            }
        }
    }
    if ($image_err!=NULL)
    {
//        sys_msg(sprintf($GLOBALS['_LANG']['upload_failure'], $image_err));
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
//        $sql = "INSERT INTO " . $ecs->table('index_gallery')." (img_url) VALUES $str";
//        $res = $db->query($sql);
    }
    /* 入库的操作 */
    if ($db->autoExecute($ecs->table('venue_manage'), $venue) !== false)
    {
        $venue_id = $db->insert_id();
        foreach($arr AS $key=>$value)
        {
            $arr[$key]['venue_id'] = $venue_id;
            $db->autoExecute($ecs->table('venue_floor_manage'), $arr[$key]);
        }
        admin_log($_POST['venue_name'], 'add', 'venue');   // 记录管理员操作
        clear_cache_files();    // 清除缓存

        /*添加链接*/
        $link[0]['text'] = '继续添加新场馆';
        $link[0]['href'] = 'venue.php?act=add';

        $link[1]['text'] = '返回场馆列表';
        $link[1]['href'] = 'venue.php?act=venue_list';

        sys_msg('新场馆添加成功!', 0, $link);
    }
 }

/*------------------------------------------------------ */
//-- 编辑场馆信息
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'edit')
{
    $venue_id = intval($_REQUEST['venue_id']);
    $venue_info = get_venue_info($venue_id);  // 查询场馆信息数据
    $floor = get_venue_floor_info($venue_id);
    /* 轮播图列表 */
    $playerdb = get_venue_flash_xml($venue_id);
    foreach ($playerdb as $key => $val)
    {
        if (strpos($val['src'], 'http') === false)
        {
            $playerdb[$key]['src'] = $uri . $val['src'];
        }
    }

    /* 标签初始化 */
    $group_list = array(
        'sys' => array('text' => $_LANG['system_set'], 'url' => '')
    );

    $flash_dir = ROOT_PATH . 'data/flashdata/';
    $smarty->assign('flashtpls', get_flash_templates($flash_dir));
    $smarty->assign('playerdb', $playerdb);
    $smarty->assign('action_link_special', array('text' => "添加轮播图", 'href' => 'venue.php?act=venue_add&venue_id='.$venue_info['venue_id']));

    /* 模板赋值 */
    $smarty->assign('ur_here',     '编辑场馆');
    $smarty->assign('action_link', array('text' => $_LANG['venue_manage'], 'href' => 'venue.php?act=venue_list'));

    $smarty->assign('venue_info',    $venue_info);
    $smarty->assign('floor_info',    $floor);
    $smarty->assign('form_act',    'update');

    /* 显示页面 */
    assign_query_info();
    $smarty->display('venue_info.htm');
}
/*------------------------------------------------------ */
//-- 编辑场馆信息
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'update')
{
    /* 初始化变量 */
    $venue_id              = !empty($_POST['venue_id'])       ? intval($_POST['venue_id'])     : 0;
    $old_venue_name        = $_POST['old_venue_name'];
    $venue['venue_name']     = !empty($_POST['venue_name'])     ? trim($_POST['venue_name'])     : '';
    $floor = $_POST['floor'];
    $arr = array();
    for($i=0;$i<count($floor['desc']);$i++)
    {
        $arr[$i]['floor_desc'] = $floor['desc'][$i];
        $arr[$i]['floor_href'] = $floor['file'][$i];
        $arr[$i]['goods_ids']  = $floor['goods'][$i];
        $arr[$i]['floor_ids']  = $floor['floors'][$i];
        $arr[$i]['floor_id']   = $floor['floor_id'][$i];
        $arr[$i]['floor_img']   = $_POST['old_img'][$i];
        $arr[$i]['floor_style']   = $floor['floor_style'][$i];
        $arr[$i]['is_show']   = $floor['is_show'][$i];
        $arr[$i]['floor_sort']   = $floor['floor_sort'][$i];
    }
////////////////////////////////////////////
    include_once(ROOT_PATH . '/includes/cls_image.php');
    $image = new cls_image($_CFG['bgcolor']);
    /* 检查图片：如果有错误，检查尺寸是否超过最大值；否则，检查文件类型 */
    if (isset($_FILES['floor']['error']['url'])) // php 4.2 版本才支持 error
    {
        // 最大上传文件大小
        $php_maxsize = ini_get('upload_max_filesize');
        $htm_maxsize = '2M';
        foreach ($_FILES['floor']['error']['url'] AS $key => $value)
        {
            if ($value == 0)
            {
                if (!$image->check_img_type($_FILES['floor']['type']['url'][$key]))
                {
                    sys_msg(sprintf('第%s个图片格式不正确!', $key + 1), 1, array(), false);
                }
            }
            elseif ($value == 1)
            {
                sys_msg(sprintf('第%s个图片文件太大了（最大值：%s），无法上传。', $key + 1, $php_maxsize), 1, array(), false);
            }
            elseif ($_FILES['floor']['img_url']['error'] == 2)
            {
                sys_msg(sprintf('第%s个图片文件太大了（最大值：%s），无法上传。', $key + 1, $htm_maxsize), 1, array(), false);
            }
            elseif ($value == 4)
            {
//                unset($_FILES['floor']['name']['url'][$key]);
//                sys_msg('请选择要上传的图片', 1, array(), false);
            }
        }
    }
    /* 4.1版本 */
    else
    {
        foreach ($_FILES['floor']['img_url']['tmp_name'] AS $key => $value)
        {
            if ($value != 'none')
            {
                if (!$image->check_img_type($_FILES['floor']['img_url']['type'][$key]))
                {
                    sys_msg(sprintf($_LANG['invalid_img_url'], $key + 1), 1, array(), false);
                }
            }
        }
    }
    $image_files = $_FILES['floor'];
    /* 创建目录 */
    $dir = ROOT_PATH . 'data' . '/afficheimg/'.$venue_id.'_images/';
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
    foreach($image_files['name']['url'] AS $key=>$value)
    {
        if(!empty($value))
        {
            $name = date('Ymd');
            for ($i = 0; $i < 6; $i++)
            {
                $name .= chr(mt_rand(97, 122));
            }
            $end = explode('.', $_FILES['floor']['name']['url'][$key]);
            $name .= '.' . end($end);
            if (move_upload_file($image_files['tmp_name']['url'][$key], $dir.$name))
            {
                $arr[$key]['floor_img'] =  'data' . '/afficheimg/'.$venue_id.'_images/'.$name;
                $images_name='data/afficheimg/'.$name;
                $sql = "SELECT COUNT(*) FROM ecs_venue_floor_manage WHERE floor_img='$images_name' AND venue_id='$venue_id'";
                $r = $db->getOne($sql);
                if($r == 0)
                {
                    $image_array[] = $images_name;
                }
            }
            else
            {
                $image_err.=$image_files['name']['url'][$key].'/';
            }
        }
    }
    if ($image_err!=NULL)
    {
//        sys_msg(sprintf($GLOBALS['_LANG']['upload_failure'], $image_err));
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
//        $sql = "INSERT INTO " . $ecs->table('index_gallery')." (img_url) VALUES $str";
//        $res = $db->query($sql);
    }
    //////////////////////////////////////////////////////////////
    if($venue['venue_name']!=$old_venue_name)
    {
        if (venue_exists($venue['venue_name']))
        {
            /* 同级别下不能有重复的分类名称 */
            $link[] = array('text' => $_LANG['go_back'], 'href' => 'javascript:history.back(-1)');
            sys_msg($_LANG['venue_exist'], 0, $link);
        }
    }
    if ($db->autoExecute($ecs->table('venue_manage'), $venue, 'UPDATE', "venue_id='$venue_id'"))
    {
        foreach($arr AS $key=>$value)
        {
            $arr[$key]['venue_id'] = $venue_id;
            if($arr[$key]['floor_desc'] == "" && $arr[$key]['floor_href'] == "" && $arr[$key]['goods_ids'] == "" && $arr[$key]['floor_ids'] == "" && $arr[$key]['floor_id'] == "" && $arr[$key]['floor_img'] == "")
            {
                continue;
            }
            if($arr[$key]['floor_id']>0)
            {
                $floor_id= $arr[$key]['floor_id'];
                unset($arr[$key]['floor_id']);
                $db->autoExecute($ecs->table('venue_floor_manage'), $arr[$key], 'UPDATE', "venue_id='$venue_id' AND floor_id=$floor_id");
            }
            else
            {
                $db->autoExecute($ecs->table('venue_floor_manage'), $arr[$key]);
            }
        }
        /* 更新分类信息成功 */
        clear_cache_files(); // 清除缓存
        admin_log($_POST['venue_name'], 'edit', 'venue'); // 记录管理员操作

        /* 提示信息 */
        $link[] = array('text' => $_LANG['back_list'], 'href' => 'venue.php?act=venue_list');
        sys_msg('修改场馆成功', 0, $link);
    }
}

/*------------------------------------------------------ */
//-- 删除场馆
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'remove')
{
//    check_authz_json('erpcat_drop');
    $venue_id   = intval($_GET['id']);
    $venue_name = $db->getOne('SELECT venue_name FROM ' .$ecs->table('venue_manage'). " WHERE venue_id='$venue_id'");
    /* 删除场馆 */
    $sql = 'DELETE FROM ' .$ecs->table('venue_manage'). " WHERE venue_id = '$venue_id'";
    if ($db->query($sql))
    {
        //删除xml文件、js以及所有图片
        @unlink(ROOT_PATH . DATA_DIR . '/'.$venue_id.'venue_flash_data.xml');
        @unlink(ROOT_PATH . DATA_DIR . '/flashdata/test/'.$venue_id.'_data.js');
        @deldir(ROOT_PATH . DATA_DIR . '/afficheimg/'.$venue_id.'_images');
        clear_cache_files();
        admin_log($venue_name, 'remove', 'venue');
    }
    else
    {
        make_json_error($venue_name .' '. '删除失败!');
    }
    $url = 'venue.php?act=query&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);

    ecs_header("Location: $url\n");
    exit;
}
/*------------------------------------------------------ */
//-- 添加轮播图
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'venue_add')
{
//    admin_priv('flash_manage');

    $venue_id = (int)$_REQUEST['venue_id']; //取得venue_id
    if($venue_id<1)
    {
        sys_msg("请先添加场馆");
    }
    if (empty($_POST['step']))
    {
        $url = isset($_GET['url']) ? $_GET['url'] : 'http://';
        $src = isset($_GET['src']) ? $_GET['src'] : '';
        $sort = 0;
        $rt = array('act'=>'venue_add','img_url'=>$url,'img_src'=>$src, 'img_sort'=>$sort);
        $width_height = get_width_height();
        assign_query_info();
        if(isset($width_height['width'])|| isset($width_height['height']))
        {
            $smarty->assign('width_height', sprintf($_LANG['width_height'], $width_height['width'], $width_height['height']));
        }

        $smarty->assign('action_link', array('text' => "编辑场馆", 'href' => 'venue.php?act=edit&venue_id='.$venue_id));
        $smarty->assign('rt', $rt);
        $smarty->assign('venue_id', $venue_id);
        $smarty->assign('ur_here', "添加场馆轮播广告");
        $smarty->display('venue_flashplay_add.htm');
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
            $end = explode('.', $_FILES['img_file_src']['name']);
            $name .= '.' . end($end);
            $target = ROOT_PATH . DATA_DIR . '/afficheimg/'.$venue_id.'_images/';
            /* 如果目标目录不存在，则创建它 */
            if (!file_exists($target))
            {
                if (!make_dir($target))
                {
                    /* 创建目录失败 */
                    sys_msg(sprintf($GLOBALS['_LANG']['directory_readonly'], $target));
                }
            }
            $target .= $name;
            if (move_upload_file($_FILES['img_file_src']['tmp_name'], $target))
            {
                $src = DATA_DIR . '/afficheimg/'.$venue_id.'_images/' . $name;
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
                $src = get_venue_url_image($src);
            }
        }
        else
        {
            $links[] = array('text' => $_LANG['add_new'], 'href' => 'venue.php?act=venue_add&venue_id='.$venue_id);
            sys_msg($_LANG['src_empty'], 0, $links);
        }

        if (empty($_POST['img_url']))
        {
            $links[] = array('text' => $_LANG['add_new'], 'href' => 'venue.php?act=venue_add&venue_id='.$venue_id);
            sys_msg($_LANG['link_empty'], 0, $links);
        }

        // 获取flash播放器数据
        $flashdb = get_venue_flash_xml($venue_id);

        // 插入新数据
        array_unshift($flashdb, array('src'=>$src, 'url'=>$_POST['img_url'], 'text'=>$_POST['img_text'] ,'sort'=>$_POST['img_sort'],'platform'=>$_POST['platform']));

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

        put_venue_flash_xml($_flashdb,$venue_id);
        $error_msg = '';
        set_venue_flash_data($_CFG['flash_theme'], $error_msg,$venue_id);
        $links[] = array('text' => $_LANG['go_url'], 'href' => 'venue.php?act=venue_list');
        sys_msg($_LANG['edit_ok'], 0, $links);
    }
}
/*------------------------------------------------------ */
//-- 删除楼层
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == "del_floor")
{
    $floor_id = (int)$_REQUEST['id']; //取得floor_id

    $sql = 'DELETE FROM ' .$ecs->table('venue_floor_manage'). " WHERE floor_id = '$floor_id'";

    if ($db->query($sql))
    {
        clear_cache_files();
        admin_log($floor_id, 'remove', 'venue_floor');
        make_json_result($floor_id);
    }
    else
    {
        make_json_error('删除失败!');
    }
}
/*------------------------------------------------------ */
//-- 编辑轮播图
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'venue_edit')
{
//    admin_priv('flash_manage');

    $venue_id = (int)$_REQUEST['venue_id']; //取得venue_id
    $id = (int)$_REQUEST['id']; //取得id
    $flashdb = get_venue_flash_xml($venue_id); //取得数据
    if (isset($flashdb[$id]))
    {
        $rt = $flashdb[$id];
    }
    else
    {
        $links[] = array('text' => $_LANG['go_url'], 'href' => 'venue.php?act=venue_list');
        sys_msg($_LANG['id_error'], 0, $links);
    }
    if (empty($_POST['step']))
    {
        $rt['act'] = 'venue_edit';
        $rt['img_url'] = $rt['url'];
        $rt['img_src'] = $rt['src'];
        $rt['img_txt'] = $rt['text'];
        $rt['img_sort'] = empty($rt['sort']) ? 0 : $rt['sort'];

        $rt['id'] = $id;
        $smarty->assign('action_link', array('text' => "编辑场馆", 'href' => 'venue.php?act=edit&venue_id='.$venue_id));
        $smarty->assign('rt', $rt);
        $smarty->assign('venue_id', $venue_id);
        $smarty->assign('ur_here', $_LANG['edit_picad']);
        $smarty->display('venue_flashplay_add.htm');
    }
    elseif ($_POST['step'] == 2)
    {
        if (empty($_POST['img_url']))
        {
            //若链接地址为空
            $links[] = array('text' => $_LANG['return_edit'], 'href' => 'venue.php?act=venue_edit&id=' . $id.'&venue_id'.$venue_id);
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
            $end = explode('.', $_FILES['img_file_src']['name']);
            $name .= '.' . end($end);
            $target = ROOT_PATH . DATA_DIR . '/afficheimg/'.$venue_id.'_images/';
            /* 如果目标目录不存在，则创建它 */
            if (!file_exists($target))
            {
                if (!make_dir($target))
                {
                    /* 创建目录失败 */
                    sys_msg(sprintf($GLOBALS['_LANG']['directory_readonly'], $target));
                }
            }
            $target .= $name;
            if (move_upload_file($_FILES['img_file_src']['tmp_name'], $target))
            {
                $src = DATA_DIR . '/afficheimg/'.$venue_id.'_images/' . $name;
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
                $src = get_venue_url_image($src);
            }
        }
        else
        {
            $links[] = array('text' => $_LANG['return_edit'], 'href' => 'venue.php?act=venue_edit&id=' . $id.'&venue_id'.$venue_id);
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

        put_venue_flash_xml($_flashdb,$venue_id);
        $error_msg = '';
        set_venue_flash_data($_CFG['flash_theme'], $error_msg,$venue_id);
        $links[] = array('text' => $_LANG['go_url'], 'href' => 'venue.php?act=edit&venue_id=' . $venue_id);
        sys_msg($_LANG['edit_ok'], 0, $links);
    }
}
/*------------------------------------------------------ */
//-- 删除轮播图
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] = "venue_del")
{
    $venue_id = (int)$_REQUEST['venue_id']; //取得venue_id
    $id = (int)$_GET['id'];
    $flashdb = get_venue_flash_xml($venue_id);
    if (isset($flashdb[$id]))
    {
        $rt = $flashdb[$id];
    }
    else
    {
        $links[] = array('text' => $_LANG['go_url'], 'href' => 'venue.php?act=edit&venue_id='.$venue_id);
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
    put_venue_flash_xml($temp,$venue_id);
    $error_msg = '';
    set_venue_flash_data($_CFG['flash_theme'], $error_msg,$venue_id);
    ecs_header("Location: venue.php?act=edit&venue_id=".$venue_id."\n");
    exit;
}
/**
 * 获得场馆的所有信息
 *
 * @param   integer     $venue_id     指定的场馆ID
 *
 * @return  mix
 */
function get_venue_info($venue_id)
{
    $sql = "SELECT * FROM " .$GLOBALS['ecs']->table('venue_manage'). " WHERE venue_id='$venue_id' LIMIT 1";
    return $GLOBALS['db']->getRow($sql);
}
function get_venue_floor_info($venue_id)
{
    $sql = "SELECT * FROM " .$GLOBALS['ecs']->table('venue_floor_manage'). " WHERE venue_id='$venue_id'";
    return $GLOBALS['db']->getAll($sql);
}
function get_venue_flash_xml($id)
{
    $flashdb = array();
    if (file_exists(ROOT_PATH . DATA_DIR . '/'.$id.'venue_flash_data.xml'))
    {

        // 兼容v2.7.0及以前版本
        if (!preg_match_all('/item_url="([^"]+)"\slink="([^"]+)"\stext="([^"]*)"\ssort="([^"]*)"\splatform="([^"]*)"/', file_get_contents(ROOT_PATH . DATA_DIR . '/'.$id.'venue_flash_data.xml'), $t, PREG_SET_ORDER))
        {
            preg_match_all('/item_url="([^"]+)"\slink="([^"]+)"\stext="([^"]*)"\splatform="([^"]*)"/', file_get_contents(ROOT_PATH . DATA_DIR . '/'.$id.'venue_flash_data.xml'), $t, PREG_SET_ORDER);
        }

        if (!empty($t))
        {
            foreach ($t as $key => $val)
            {
                $val[4] = isset($val[4]) ? $val[4] : 0;
                $flashdb[] = array('src'=>$val[1],'url'=>$val[2],'text'=>$val[3],'sort'=>$val[4],'platform'=>$val[5]);
            }
        }
    }
    return $flashdb;
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
function get_venue_url_image($url)
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
function put_venue_flash_xml($flashdb,$id)
{
    if (!empty($flashdb))
    {
        $xml = '<?xml version="1.0" encoding="' . EC_CHARSET . '"?><bcaster>';
        foreach ($flashdb as $key => $val)
        {
            $xml .= '<item item_url="' . $val['src'] . '" link="' . $val['url'] . '" text="' . $val['text'] . '" sort="' . $val['sort'] . '" platform="' . $val['platform'] . '"/>';
        }
        $xml .= '</bcaster>';
        file_put_contents(ROOT_PATH . DATA_DIR . '/'.$id.'venue_flash_data.xml', $xml);
    }
    else
    {
        @unlink(ROOT_PATH . DATA_DIR . '/'.$id.'venue_flash_data.xml');
        @unlink(ROOT_PATH . DATA_DIR . '/flashdata/test/'.$id.'_data.js');
    }
}
function set_venue_flash_data($tplname, &$msg,$id)
{
    $flashdata = get_venue_flash_xml($id);
    if (empty($flashdata))
    {
        $flashdata[] = array(
            'src' => 'data/afficheimg/20081027angsif.jpg',
            'text' => 'wjike',
            'url' =>'http://www.ecshop.com',
            'platform' => '0'
        );
        $flashdata[] = array(
            'src' => 'data/afficheimg/20081027wdwd.jpg',
            'text' => 'wjike',
            'url' =>'http://www.wjike.com',
            'platform' => '0'
        );
        $flashdata[] = array(
            'src' => 'data/afficheimg/20081027xuorxj.jpg',
            'text' => 'wjike',
            'url' =>'http://www.wjike.com',
            'platform' => '0'
        );
    }
    switch($tplname)
    {
        case 'uproll':
            $msg = set_venue_flash_uproll($tplname, $flashdata,$id);
            break;
        case 'redfocus':
        case 'pinkfocus':
        case 'dynfocus':
            $msg = set_venue_flash_focus($tplname, $flashdata,$id);
            break;
        case 'test':
            $msg = set_venue_flash_focus($tplname, $flashdata,$id);
            break;
        case 'default':
        default:
            $msg = set_venue_flash_focus($tplname, $flashdata,$id);
            break;
    }
    return $msg !== true;
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
function set_venue_flash_focus($tplname, $flashdata,$id)
{
    if($tplname=="test")
    {
        $data_file = ROOT_PATH . DATA_DIR . '/flashdata/' . $tplname . '/'.$id.'_data.js';
        $jsdata = '';
        $jsdata2 = array('test' => 'var list=');
        $count = 1;
        $join = '';
        foreach ($flashdata as $data)
        {
            $jsdata .= 'var test'.$count.'={'. "\n".'imgUrl:"' . $data['src'] . '",' . "\n";
            $jsdata .= 'imgtext' . ':"' . $data['text'] . '",' . "\n";
            $jsdata .= 'imgLink' . ':escape("' . $data['url'] . '"),' . "\n";
            $jsdata .= 'platform' . ':"' . $data['platform'] . '"' . "\n" .'}'. "\n";
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
    $data_file = ROOT_PATH . DATA_DIR . '/flashdata/' . $tplname . '/'.$id.'_data.js';
    $jsdata = '';
    $jsdata2 = array('url' => 'var pics=', 'txt' => 'var texts=', 'link' => 'var links=');
    $count = 1;
    $join = '';
    foreach ($flashdata as $data)
    {
        $jsdata .= 'imgUrl' . $count . '="' . $data['src'] . '";' . "\n";
        $jsdata .= 'imgtext' . $count . '="' . $data['text'] . '";' . "\n";
        $jsdata .= 'imgLink' . $count . '=escape("' . $data['url'] . '");' . "\n";
        $jsdata .= 'platform' . $count . '="' . $data['platform'] . '";' . "\n";
        if ($count != 1)
        {
            $join = '+"|"+';
        }
        $jsdata2['url'] .= $join . 'imgUrl' . $count;
        $jsdata2['txt'] .= $join . 'imgtext' . $count;
        $jsdata2['link'] .= $join . 'imgLink' . $count;
        $jsdata2['platform'] .= $join . 'platform' . $count;
        ++$count;
    }
    file_put_contents($data_file, $jsdata . "\n" . $jsdata2['url'] . ";\n" . $jsdata2['link'] . ";\n" . $jsdata2['txt'] . ";\n" . $jsdata2['platform'] . ";");
    return true;
}
function deldir($dir) {
    //先删除目录下的文件：
    $dh=opendir($dir);
    while ($file=readdir($dh)) {
        if($file!="." && $file!="..") {
            $fullpath=$dir."/".$file;
            if(!is_dir($fullpath)) {
                unlink($fullpath);
            } else {
                deldir($fullpath);
            }
        }
    }

    closedir($dh);
    //删除当前文件夹：
    if(rmdir($dir)) {
        return true;
    } else {
        return false;
    }
}

?>