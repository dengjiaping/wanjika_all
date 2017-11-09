<?php
define('IN_ECS', 1);
require(dirname(__FILE__) . '/../includes/init.php');
require_once (dirname ( __FILE__ ) .'/config/Config.php');

$sql = "select * from ecs_data_config where keywords<>'耳机/耳麦'";
$results = $GLOBALS['db']->getAll($sql);
if(count($results) == 0){
    exit;
}
//ob_start();//开启缓冲区
//echo "这是第一次输出内容!\n";
//$ff[1] = ob_get_contents() ; //获取当前缓冲区内容
//ob_flush();//缓冲器清除
//echo "这是第二次输出内容!\n";
//$ff[2] = ob_get_contents() ; //获取当前缓冲区内容
//echo "这是第三次输出内容!\n";
//$ff[3] = ob_get_contents() ; //获取当前缓冲区内容
//
//echo "<pre>";
//print_r($ff);
//exit;
set_time_limit(0);
ob_start();
$searchurl = '';
foreach($results as $result){
    $jd = array();
    $keyWords = rawurlencode($result['keywords']);
    if($result['sorttype'] == 2){
        $searchurl = 'http://search.jd.com/Search?keyword='.$keyWords.'&enc=utf-8&psort=3&page=';
    }
    else{
        $searchurl = 'http://search.jd.com/Search?keyword='.$keyWords.'&enc=utf-8&page=';
    }
    for($i = 1;$i <= $result['page'];$i++){
        $str = '';

        $url = $searchurl.$i;
//初始化
        ob_flush();
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_COOKIEJAR, "E:/www/pachong/cookie.txt");
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/4.0 (compatible; MSIE 7.0; Windows NT 5.1; .NET CLR 2.0.50727; InfoPath.1; CIBA)");
        curl_setopt($ch, CURLOPT_URL, $url);
//echo header
        curl_setopt($ch, CURLOPT_HEADER, FALSE);
//mark this as a new cookie "session".
        curl_setopt($ch, CURLOPT_COOKIESESSION, TRUE);
//echo body html
        curl_setopt($ch, CURLOPT_NOBODY, FALSE);
        curl_exec($ch);
        curl_close($ch);

        $info = ob_get_contents();

        $ul_pattern = "/<ul class=\"list-h[\s\S]*>([\s\S]*)<\/ul>/isU";
        preg_match($ul_pattern,$info,$ulcontent);

        $li_pattern = "/<li[^>]+>([\s\S]*?)<\/li>/";
        preg_match_all($li_pattern,$ulcontent[1],$licontent);

        foreach ($licontent[0] as $value) {
            $skuu = array();
            $sku_pattern =  "/<li sku=\"(.*)\"[^>]*>/i";
            preg_match_all($sku_pattern,$value,$skuid);
            $skuu['skuid'] = $skuid[1][0];

            $pname_pattern =  "/<div class=\"p-name\"[^>]*>([\s\S]*)<div class=\"p-price\"[^>]*>/i";
            preg_match_all($pname_pattern,$value,$pname);
            $name_pattern =  "/<a[^>]*>([\s\S]*)<font[^>]*>/i";
            preg_match_all($name_pattern,$pname[1][0],$name);
            $skuu['name'] = addslashes(trim(strip_tags($name[1][0])));

            $href_pattern =  "/<a[^>]*href=\"([\s\S]*?)\"[^>]*>/i";
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
    VALUES ('" . $value['skuid'] . "', '" . $value['name'] . "', '" . $value['price'] . "', '" . $rank . "', '" . $value['href'] . "', '" . $mytime. "', '" . $result['keywords']. "', '" . $result['sorttype']. "')";
        $GLOBALS['db']->query($sql);
        $rank++;
    }
}
?>