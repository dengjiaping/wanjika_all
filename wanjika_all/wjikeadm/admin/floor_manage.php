<?php
define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');

$allow_suffix = array('jpg');

for($i = 1; $i < 8; $i++)
{
$filename = ROOT_PATH . 'themes/68ecshopcom_yihaodian/images/' . $i . 'f.jpg';
$filemtime[$i] = filemtime($filename);
}

if($_POST['Submit'] == '添加')
{
    $count = 0;
    foreach($_FILES as $key=>$value)
    {
        if(!empty($_FILES[$key]['tmp_name']))
        {
            $count = $count + 1;
            if(!get_file_suffix($_FILES[$key]['name'], $allow_suffix))
            {
                sys_msg("您上传的图片格式不正确");
            }
            $name = $key;
            $somename = explode('.', $_FILES[$key]['name']);
            $name .= '.' . end($somename);
            $target = ROOT_PATH . 'themes/68ecshopcom_yihaodian/images/' . $name;
            move_upload_file($_FILES[$key]['tmp_name'], $target);
        }
    }
    if(!empty($count))
    {
        sys_msg('成功添加' . $count . '张楼层图', 0, $links);
    }

}
//模版赋值
    $smarty->assign('ur_here', $_LANG['floor_manage']);
    $smarty->assign('filemtime', $filemtime);
    $smarty->display('floor_manage.htm');
?>
