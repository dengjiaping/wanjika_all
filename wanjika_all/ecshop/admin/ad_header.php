<?php
define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
$uri = $ecs->url();
$allow_suffix = array('gif', 'jpg', 'png', 'jpeg', 'bmp');
if (empty($_POST['step']))
{
//如果step为空，则插入当前广告数据
//获取is_ad为1的，没有
    $sql = "select * FROM  ".
        $GLOBALS['ecs']->table('ad_header') .
        " WHERE is_ad=1";
    $rt = $db->getRow($sql);
    if(!empty($rt))
    {
        $rt['end_time'] =date('Y-m-d H:i',$rt['end_time']);
        $rt['start_time'] =date('Y-m-d H:i',$rt['start_time']);
    }else
    {
        $rt = 1;
    }


//模版赋值
    $smarty->assign('ur_here', $_LANG['ad_header']);
    $smarty->assign('rt', $rt);
    $smarty->display('ad_header.htm');
}
elseif($_POST['Submit'] == '添加')
{
    if (!empty($_FILES['img_file_src']['name']))
    {
        if(!get_file_suffix($_FILES['img_file_src']['name'], $allow_suffix))
        {
            sys_msg("您上传的图片格式不正确");
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
        $src = $_POST['img_src'];

        if(strstr($src, 'http') && !strstr($src, $_SERVER['SERVER_NAME']))
        {
            $src = get_url_image($src);
        }
    }
    else
    {
        $links[] = array('text' => '添加图片', 'href' => 'ad_header.php');
        sys_msg('请填写图片地址', 0, $links);
    }

    if (empty($_POST['img_url']))
    {
        $links[] = array('text' => '添加图片', 'href' => 'ad_header.php');
        sys_msg('请填写图片链接', 0, $links);
    }
    $sql = "UPDATE " . $ecs->table('ad_header') .
        " SET is_ad = 0 where 1 ";
    $db->query($sql);
    $rt = array('ad_set' => 1,'src' => $src,'alt' => $_POST['img_text'],'href' => $_POST['img_url'],'start_time' => strtotime($_POST['start_time']),'end_time' => strtotime($_POST['end_time']),'is_ad' => 1 );
//入库

    $GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('ad_header'), $rt, 'INSERT');
    sys_msg('操作成功', 0, $links);
}
elseif($_POST['Submit'] == '修改')
{
    $sql = "select * FROM  ".
        $GLOBALS['ecs']->table('ad_header') .
        " WHERE is_ad=1";
    $rt = $db->getRow($sql);
    if(empty($rt))
    {
        $links[] = array('text' => '添加广告', 'href' => 'ad_header.php');
        sys_msg('请添加之后再修改', 0, $links);
    }
    if (!empty($_FILES['img_file_src']['name']))
    {
        if(!get_file_suffix($_FILES['img_file_src']['name'], $allow_suffix))
        {
            sys_msg("您上传的图片格式不正确");
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
        $src = $_POST['img_src'];

        if(strstr($src, 'http') && !strstr($src, $_SERVER['SERVER_NAME']))
        {
            $src = get_url_image($src);
        }
    }
    else
    {
        $links[] = array('text' => '添加图片', 'href' => 'ad_header.php');
        sys_msg('请填写图片地址', 0, $links);
    }

    if (empty($_POST['img_url']))
    {
        $links[] = array('text' => '添加图片', 'href' => 'ad_header.php');
        sys_msg('请填写图片链接', 0, $links);
    }
    //入库
    $ad = array('src' => $src,'alt' => $_POST['img_text'],'href' => $_POST['img_url'],'start_time' => strtotime($_POST['start_time']),'end_time' => strtotime($_POST['end_time']) );
    if($rt['alt'] !== $ad['alt'])
    {
        $alt = $ad['alt'];
        $sql = 'UPDATE ' . $GLOBALS['ecs']->table('ad_header') . " SET alt='$alt' WHERE is_ad = '1'";
        $db->query($sql);
    }
    if($rt['href'] !== $ad['href'])
    {
        $href = $ad['href'];
        $sql = 'UPDATE ' . $GLOBALS['ecs']->table('ad_header') . " SET href='$href' WHERE is_ad = '1'";
        $db->query($sql);
    }
    if($rt['start_time'] !== $ad['start_time'])
    {
        $start_time = $ad['start_time'];
        $sql = 'UPDATE ' . $GLOBALS['ecs']->table('ad_header') . " SET start_time='$start_time' WHERE is_ad = '1'";
        $db->query($sql);
    }
    if($rt['end_time'] !== $ad['end_time'])
    {
        $end_time = $ad['end_time'];
        $sql = 'UPDATE ' . $GLOBALS['ecs']->table('ad_header') . " SET end_time='$end_time' WHERE is_ad = '1'";
        $db->query($sql);
    }
    $add = 1;
    $sql = 'UPDATE ' . $GLOBALS['ecs']->table('ad_header') . " SET ad_set=$add WHERE is_ad = '1'";
    $db->query($sql);
    sys_msg('修改成功', 0, $links);
}
elseif($_POST['Submit'] == '关闭广告')
{
    $sql = "UPDATE " . $ecs->table('ad_header') .
        " SET is_ad = 0 where 1 ";
    $db->query($sql);
    sys_msg('广告已关闭', 0, $links);
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
?>
