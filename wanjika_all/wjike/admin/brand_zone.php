<?php

/**
 * ECSHOP 品牌专区管理程序
**/

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
$uri = $ecs->url();
$allow_suffix = array('gif', 'jpg', 'png', 'jpeg', 'bmp');
$exc = new exchange($ecs->table("brand_zone"), $db, 'brand_id', 'floor_name');

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
//-- 专区列表
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'brand_list')
{
    /* 获取场馆列表 */
    $brand_zone_list = brand_zone_list();
    $arr = array();
    foreach($brand_zone_list AS $key=>$value)
    {
        if($value['floor_level'] == 0)
        {
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
    /* 模板赋值 */
    $smarty->assign('ur_here',      $_LANG['brand_zone']);
    $smarty->assign('form_act',     'update');

    $smarty->assign('brand_zone_info',     $arr);

    /* 列表页面 */
    assign_query_info();
    $smarty->display('brand_zone_list.htm');
}
/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{

    $brand_id = intval($_REQUEST['zone_id']);
    $brand_zone_info = get_brand_zone_info($brand_id);  // 查询分类楼层信息数据

    $smarty->assign('brand_zone_info',    $brand_zone_info);

    make_json_result($smarty->fetch('brand_zone_info.htm'));
}
/*------------------------------------------------------ */
//-- 编辑分类楼层信息
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'edit')
{
    $brand_id = intval($_REQUEST['brand_id']);
    $brand_zone_info = get_brand_zone_info($brand_id);  // 查询分类楼层信息数据
    /* 标签初始化 */
    $group_list = array(
        'sys' => array('text' => $_LANG['system_set'], 'url' => '')
    );

    /* 模板赋值 */
    $smarty->assign('ur_here',     '编辑品牌专区分类');
    $smarty->assign('action_link', array('text' => $_LANG['brand_zone'], 'href' => 'brand_zone.php?act=brand_list'));

    $smarty->assign('brand_zone_info',    $brand_zone_info);
    $smarty->assign('form_act',    'edit_cat');
    $smarty->assign('brand_id',    $brand_id);
    $smarty->assign('full_page',    1);

    /* 显示页面 */
    assign_query_info();
    $smarty->display('brand_zone_info.htm');
}
/*------------------------------------------------------ */
//-- 修改分类楼层信息
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'edit_cat')
{
    $brand_id = intval($_REQUEST['brand_id']);
    $cat = $_POST['cat'];
    for($i=0;$i<count($cat['brand_id']);$i++)
    {
        $arr[$i]['brand_id'] = $cat['brand_id'][$i];
        $arr[$i]['floor_name']  = $cat['floor_name'][$i];
        $arr[$i]['floor_sort']   = $cat['floor_sort'][$i];
    }
    foreach($arr AS $key=>$value)
    {
        if($arr[$key]['brand_id'] == "" && $arr[$key]['floor_name'] == "" && $arr[$key]['floor_sort'] == "" )
        {
            continue;
        }
        if($arr[$key]['brand_id']>0)
        {
            //修改分类楼层数据
            $id= $arr[$key]['brand_id'];
            unset($arr[$key]['brand_id']);
            $db->autoExecute($ecs->table('brand_zone'), $arr[$key], 'UPDATE', "brand_id=$id AND floor_level='3'");
        }
        else
        {
            $arr[$key]['zone_id'] = $brand_id;
            $arr[$key]['floor_level'] = 3;
            //添加分类楼层数据
            $db->autoExecute($ecs->table('brand_zone'), $arr[$key]);
        }
    }

    /* 更新分类信息成功 */
    clear_cache_files(); // 清除缓存
    admin_log("品牌专区分类管理", 'edit_cat', 'brand_zone'); // 记录管理员操作

    /* 提示信息 */
    $link[] = array('text' => $_LANG['back_list'], 'href' => 'brand_zone.php?act=brand_list');
    sys_msg('修改分类成功', 0, $link);
}
/*------------------------------------------------------ */
//-- 编辑品牌专区信息
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'update')
{
    /* 初始化变量 */
    $brand_id              = !empty($_POST['brand_id'])       ? intval($_POST['brand_id'])     : 0;
    $old_floor_name        = $_POST['old_floor_name'];
    $floor_name     = !empty($_POST['floor_name'])     ? trim($_POST['floor_name'])     : '';
    $floor = $_POST['floor'];
    $cat = $_POST['cat'];
    $arr = array();
    $result = array();
    for($i=0;$i<count($floor['url']);$i++)
    {
        $arr[$i]['floor_href'] = $floor['url'][$i];
        $arr[$i]['floor_img'] = $_POST['old_img'][$i];
        $arr[$i]['floor_sort']  = $floor['floor_sort'][$i];
        $arr[$i]['brand_id']   = $floor['brand_id'][$i];
    }
    for($i=0;$i<count($cat['floor_name']);$i++)
    {
        $result[$i]['floor_name'] = $cat['floor_name'][$i];
        $result[$i]['floor_sort']   = $cat['floor_sort'][$i];
        $result[$i]['brand_id']   = $cat['brand_id'][$i];
    }
    include_once(ROOT_PATH . '/includes/cls_image.php');
    $image = new cls_image($_CFG['bgcolor']);
    /* 检查图片：如果有错误，检查尺寸是否超过最大值；否则，检查文件类型 */
    if (isset($_FILES['floor']['error']['img'])) // php 4.2 版本才支持 error
    {
        // 最大上传文件大小
        $php_maxsize = ini_get('upload_max_filesize');
        $htm_maxsize = '2M';
        foreach ($_FILES['floor']['error']['img'] AS $key => $value)
        {
            if ($value == 0)
            {
                if (!$image->check_img_type($_FILES['floor']['type']['img'][$key]))
                {
                    sys_msg(sprintf('第%s个图片格式不正确!', $key + 1), 1, array(), false);
                }
            }
            elseif ($value == 1)
            {
                sys_msg(sprintf('第%s个图片文件太大了（最大值：%s），无法上传。', $key + 1, $php_maxsize), 1, array(), false);
            }
            elseif ($_FILES['floor']['img']['error'] == 2)
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
        foreach ($_FILES['floor']['img']['tmp_name'] AS $key => $value)
        {
            if ($value != 'none')
            {
                if (!$image->check_img_type($_FILES['floor']['img']['type'][$key]))
                {
                    sys_msg(sprintf($_LANG['invalid_img_url'], $key + 1), 1, array(), false);
                }
            }
        }
    }
    $image_files = $_FILES['floor'];
    /* 创建目录 */
    $dir = ROOT_PATH . 'data' . '/afficheimg/'.$brand_id.'_zone_images/';
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
    foreach($image_files['name']['img'] AS $key=>$value)
    {
        if(!empty($value))
        {
            $name = date('Ymd');
            for ($i = 0; $i < 6; $i++)
            {
                $name .= chr(mt_rand(97, 122));
            }
            $end = explode('.', $_FILES['floor']['name']['img'][$key]);
            $name .= '.' . end($end);
            if (move_upload_file($image_files['tmp_name']['img'][$key], $dir.$name))
            {
                $arr[$key]['floor_img'] =  'data' . '/afficheimg/'.$brand_id.'_zone_images/'.$name;
                $images_name='data/afficheimg/'.$name;
                $sql = "SELECT COUNT(*) FROM ecs_brand_zone WHERE floor_img='$images_name' AND floor_level='1'";
                $r = $db->getOne($sql);
                if($r == 0)
                {
                    $image_array[] = $images_name;
                }
            }
            else
            {
                $image_err.=$image_files['name']['img'][$key].'/';
            }
        }
    }
    if ($image_err!=NULL)
    {
//        sys_msg(sprintf($GLOBALS['_LANG']['upload_failure'], $image_err));
    }
    if (true)
    {
        if($old_floor_name != $floor_name)
        {
            if($old_floor_name == "")
            {
                $sql = "INSERT INTO " . $ecs->table('brand_zone')." (floor_name) VALUES ('$floor_name')";
                $res = $db->query($sql);
            }
            else
            {
                $sql = "UPDATE " . $ecs->table('brand_zone')." SET floor_name='$floor_name' WHERE brand_id='$brand_id'";
                $res = $db->query($sql);
            }
        }
        foreach($arr AS $key=>$value)
        {
            if($arr[$key]['floor_href'] == "" && $arr[$key]['floor_img'] == "" && $arr[$key]['floor_sort'] == "" && $arr[$key]['brand_id'] == "")
            {
                continue;
            }
            if($arr[$key]['brand_id']>0)
            {
                //修改顶部楼层数据
                $id= $arr[$key]['brand_id'];
                unset($arr[$key]['brand_id']);
                $db->autoExecute($ecs->table('brand_zone'), $arr[$key], 'UPDATE', "brand_id=$id AND floor_level='1'");
            }
            else
            {
                $arr[$key]['zone_id'] = $brand_id;
                $arr[$key]['floor_level'] = 1;
                //添加顶部楼层数据
                $db->autoExecute($ecs->table('brand_zone'), $arr[$key]);
            }
        }
        foreach($result AS $k=>$v)
        {
            if($result[$k]['floor_name'] == "" && $result[$k]['floor_sort'] == "" && $result[$k]['brand_id'] == "" )
            {
                continue;
            }
            $result[$k]['zone_id'] = $brand_id;
            if($result[$k]['brand_id']>0)
            {
                //修改分类楼层数据
                $id= $result[$k]['brand_id'];
                unset($result[$k]['brand_id']);
                $db->autoExecute($ecs->table('brand_zone'), $result[$k], 'UPDATE', "brand_id=$id AND floor_level='2'");
            }
            else
            {
                $result[$k]['floor_level'] = 2;
                //添加分类楼层数据
                $db->autoExecute($ecs->table('brand_zone'), $result[$k]);
            }
        }
        /* 更新分类信息成功 */
        clear_cache_files(); // 清除缓存
        admin_log($_POST['floor_name'], 'edit', 'brand_zone'); // 记录管理员操作

        /* 提示信息 */
        $link[] = array('text' => $_LANG['back_list'], 'href' => 'brand_zone.php?act=brand_list');
        sys_msg('修改品牌专区成功', 0, $link);
    }
}

/*------------------------------------------------------ */
//-- 删除分类楼层品牌ID
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'remove')
{
//    check_authz_json('erpcat_drop');
    $brand_id   = intval($_GET['id']);
    $sql1 = "SELECT zone_id FROM ecs_brand_zone WHERE brand_id=$brand_id";
    $zone_id = $db->getOne($sql1);
    /* 删除场馆 */
    $sql = 'DELETE FROM ' .$ecs->table('brand_zone'). " WHERE brand_id = '$brand_id' AND floor_level=3";
    if ($db->query($sql))
    {
        clear_cache_files();
        admin_log($brand_id, 'remove', 'brand_zone');
    }
    else
    {
        make_json_error($brand_id .' '. '删除失败!');
    }
    $url = 'brand_zone.php?act=query&zone_id='.$zone_id.'&' . str_replace('act=remove', '', $_SERVER['QUERY_STRING']);

    ecs_header("Location: $url\n");
    exit;
}
/*------------------------------------------------------ */
//-- 删除楼层
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == "del_floor")
{
    $floor_id = (int)$_REQUEST['id']; //取得floor_id

    $sql = 'DELETE FROM ' .$ecs->table('brand_zone'). " WHERE brand_id = '$floor_id'";

    if ($db->query($sql))
    {
        clear_cache_files();
        admin_log($floor_id, 'remove', 'del_floor');
        make_json_result($floor_id);
    }
    else
    {
        make_json_error('删除失败!');
    }
}
/*------------------------------------------------------ */
//-- 删除楼层
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == "zone_del")
{
    $floor_id = (int)$_REQUEST['id']; //取得floor_id

    $sql = 'DELETE FROM ' .$ecs->table('brand_zone'). " WHERE brand_id = '$floor_id'";

    if ($db->query($sql))
    {
        clear_cache_files();
        admin_log($floor_id, 'remove', 'del_floor');
        make_json_result($floor_id);
    }
    else
    {
        make_json_error('删除失败!');
    }
}
/**
 * 获得分类楼层的所有信息
 *
 * @param   integer     $brand_id     指定的楼层ID
 *
 * @return  mix
 */
function get_brand_zone_info($brand_id)
{
    $sql = "SELECT * FROM " .$GLOBALS['ecs']->table('brand_zone'). " WHERE zone_id='$brand_id' AND floor_level=3 ";
    return $GLOBALS['db']->getAll($sql);
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

/*
 * 取得品牌信息
 */
function brand_zone_list()
{
    $sql = 'SELECT * FROM ' . $GLOBALS['ecs']->table('brand_zone');
    $res = $GLOBALS['db']->getAll($sql);
    return $res;
}
?>