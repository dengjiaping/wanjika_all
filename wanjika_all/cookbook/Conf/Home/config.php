<?php
$config_arr = include_once APP_PATH.'Conf/Home/tpl.php';
$config_arrs = include_once CONF_PATH.'tpllist.php';
$config_arr1= array(
		
		
		'TMPL_PARSE_STRING'=>array(
			'__PUBLIC__'=>__ROOT__.'/Tpl/'.GROUP_NAME,			
		),
	
	
	
);
return array_merge($config_arrs,$config_arr,$config_arr1);
?>