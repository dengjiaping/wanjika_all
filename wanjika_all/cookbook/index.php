<?php
function is_mobile_request()  
{  
	$_SERVER['ALL_HTTP'] = isset($_SERVER['ALL_HTTP']) ? $_SERVER['ALL_HTTP'] : '';  
	$mobile_browser = '0';  
	if(preg_match('/(up.browser|up.link|mmp|symbian|smartphone|midp|wap|phone|iphone|ipad|ipod|android|xoom)/i', strtolower($_SERVER['HTTP_USER_AGENT'])))  
		$mobile_browser++;  
	if((isset($_SERVER['HTTP_ACCEPT'])) and (strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml') !== false))  
		$mobile_browser++;  
	if(isset($_SERVER['HTTP_X_WAP_PROFILE']))  
		$mobile_browser++;  
	if(isset($_SERVER['HTTP_PROFILE']))  
		$mobile_browser++;  
	$mobile_ua = strtolower(substr($_SERVER['HTTP_USER_AGENT'],0,4));  
	$mobile_agents = array(  
		'w3c ','acs-','alav','alca','amoi','audi','avan','benq','bird','blac',  
		'blaz','brew','cell','cldc','cmd-','dang','doco','eric','hipt','inno',  
		'ipaq','java','jigs','kddi','keji','leno','lg-c','lg-d','lg-g','lge-',  
		'maui','maxo','midp','mits','mmef','mobi','mot-','moto','mwbp','nec-',  
		'newt','noki','oper','palm','pana','pant','phil','play','port','prox',  
		'qwap','sage','sams','sany','sch-','sec-','send','seri','sgh-','shar',  
		'sie-','siem','smal','smar','sony','sph-','symb','t-mo','teli','tim-',  
		'tosh','tsm-','upg1','upsi','vk-v','voda','wap-','wapa','wapi','wapp',  
		'wapr','webc','winw','winw','xda','xda-'
		);  
	if(in_array($mobile_ua, $mobile_agents))  
		$mobile_browser++;  
	if(strpos(strtolower($_SERVER['ALL_HTTP']), 'operamini') !== false)  
		$mobile_browser++;  
 // Pre-final check to reset everything if the user is on Windows  
	if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows') !== false)  
		$mobile_browser=0;  
 // But WP7 is also Windows, with a slightly different characteristic  
	if(strpos(strtolower($_SERVER['HTTP_USER_AGENT']), 'windows phone') !== false)  
		$mobile_browser++;  
	if($mobile_browser>0)  
		return true;  
	else
		return false;
}


//如果是手机设备 定义常量
if(is_mobile_request()==true){
	define('PCWAP_TPL','wap');
}else{
	define('PCWAP_TPL','wap');
}

define('VERSION','小C美图_V1.0.0');
define('CXNAME','小C美图');
define('AUTHOR','万集客');
define('AUTHOR_QQ','');
define('PCWAP','http://cookbook.wjike.com');
define("APP_NAME","");
define("APP_PATH","./");
define('APP_DEBUG',1);//调试模式
if (!file_exists(APP_PATH.'Conf/pcwap.php')) {
header("Content-type: text/html; charset=utf-8");
    echo "<a href=\"Install/install.php\" >点击安装</a>";
    exit();
}
require("./Inc/index.php");
?>