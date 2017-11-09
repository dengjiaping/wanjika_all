<?php
//顶通广告赋值
$sql = "select * FROM  ".
    $GLOBALS['ecs']->table('ad_header') .
    " WHERE is_ad=1";
$rt = $db->getRow($sql);
$adtime = time();
$add = $rt['ad_set'];
if(!empty($rt['start_time']) && !empty($rt['end_time']))
{
    if($rt['start_time'] < $adtime && $add == 1)
    {
        $add = 2;
        $sql = 'UPDATE ' . $GLOBALS['ecs']->table('ad_header') . " SET ad_set=$add WHERE is_ad = '1'";
        $db->query($sql);
        clear_all_files();
    }
    if($rt['end_time'] < $adtime && $add == 2)
    {
        $add = 3;
        $sql = 'UPDATE ' . $GLOBALS['ecs']->table('ad_header') . " SET ad_set=$add WHERE is_ad = '1'";
        $db->query($sql);
        clear_all_files();
    }
}

$smarty->assign('add', $add);
$smarty->assign('rt', $rt);

?>