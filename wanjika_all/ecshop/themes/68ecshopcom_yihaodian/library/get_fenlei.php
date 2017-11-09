<?php
/* 
根据广告名称获取图片列表
广告名称格式为fenleiid_开头后面跟的是分类ID数字
*/
function get_fenlei($cat_id)
{
     $fenlei_list=array();
     $sql = "select ap.ad_width,ap.ad_height, ad.ad_id, ad.ad_name,ad.ad_code,ad.ad_link from ".$GLOBALS['ecs']->table('ad_position').
		" as ap left join ".$GLOBALS['ecs']->table('ad')." as ad on ad.position_id = ap.position_id ".
		" where ap.position_name='fenleiid_" . $cat_id . "' and ad.media_type=0 ".
		" and UNIX_TIMESTAMP()>ad.start_time and UNIX_TIMESTAMP()<ad.end_time and ad.enabled=1 order by ad.ad_name";
     $res = $GLOBALS['db']->query($sql);
     $fenlei_num=1;
     while ( $row = $GLOBALS['db']->fetchRow($res) )
     {
	$row['fenlei_num']=$fenlei_num;
	$row['href'] = "affiche.php?ad_id=" . $row[ad_id] . "&amp;uri=" . urlencode($row['ad_link']);
	$row['src'] = "data/afficheimg/". $row['ad_code'];
	$fenlei_list[] = $row;
	$fenlei_num++;
     }
     return $fenlei_list;
}
?>