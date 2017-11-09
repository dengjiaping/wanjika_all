<?php
define('IN_ECS', 1);
require(dirname(__FILE__) . '/../includes/init.php');
require_once (dirname ( __FILE__ ) .'/config/Config.php');

$sql = "select * from ecs_data_config where keywords='耳机/耳麦'";
$result = $GLOBALS['db']->getAll($sql);
if(count($result) == 0){
    exit;
}

$jd = array();
for($i = 1;$i <= $result[0]['page'];$i++){
    $url ='http://list.jd.com/list.html?cat=652,828,842&page='.$i;  //这儿填页面地址
    $str = '';
    $info=file_get_contents($url);

    $ul_pattern = "/<ul class=\"list-h\">(.*)<\/ul>/";
    preg_match($ul_pattern,$info,$ulcontent);

    $li_pattern = "/<li[^>]+>(.*?)<\/li>/";
    preg_match_all($li_pattern,$ulcontent[1],$licontent);

    foreach ($licontent[0] as $value) {
        $skuu = array();
        $sku_pattern =  "/<li index=\".*\" sku=\"(.*)\" selfservice=\".*\">/i";
        preg_match_all($sku_pattern,$value,$skuid);
        $skuu['skuid'] = $skuid[1][0];

        $name_pattern =  "/<img width=\"220\" height=\"220\" alt=\"(.*?)\"/i";
        preg_match_all($name_pattern,$value,$name);
        $skuu['name'] = addslashes(trim(strip_tags($name[1][0])));

        $href_pattern =  "/<a [^>]+href=\"(.*?)\"[^>]*>/i";
        preg_match_all($href_pattern,$value,$href);
        $skuu['href'] = $href[1][0];

        $jd[$skuu['skuid']] = $skuu;

        $str .= 'J_'.$skuu['skuid'].',';
    }

    $url1 = 'http://p.3.cn/prices/mgets?skuIds='.$str;
    $pricejd = file_get_contents($url1);

    $priceobj = json_decode($pricejd);
    foreach($priceobj as $obj) {
        $strr = $obj->id;
        $skui = str_replace('J_','',$strr);

        $jd[$skui]['price'] = $obj->p;
    }
}

$rank = 1;
$mytime = time() - date('Z');
foreach ($jd as $value) {
    $sql = "INSERT INTO " . $GLOBALS['ecs']->table('data_jdgrasp') . "(goods_id,goods_name,goods_price,rank,goods_link,addtime,grasp_keywords,sorttype)
    VALUES ('" . $value['skuid'] . "', '" . $value['name'] . "', '" . $value['price'] . "', '" . $rank . "', '" . $value['href'] . "', '" . $mytime. "', '" . $result[0]['keywords']. "', '" . $result[0]['sorttype']. "')";
    $GLOBALS['db']->query($sql);
    $rank++;
}
?>