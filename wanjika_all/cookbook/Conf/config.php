<?php
$config_arr1=  array(    	
	'OUTPUT_ENCODE'=>false,
	'APP_GROUP_LIST' => 'Home,Admin', //项目分组设定
	'DEFAULT_GROUP'  => 'Home', //默认分组
	'URL_ROUTER_ON'   => true, //开启路由
	'URL_ROUTE_RULES' => array( 
		'/^tag\/([a-z0-9]+)$/' => 'Home/Tags/index?url=:1',
		'/^tag\/([a-z0-9]+)\/(\d+)$/' => 'Home/Tags/index?url=:1&p=:2',
		'/^key\/([a-z0-9]+)$/' => 'Home/Search/index?url=:1',			
		'/^key\/([a-z0-9]+)\/(\d+)$/' => 'Home/Search/index?url=:1&p=:2',
		'/^(\d+)$/' => 'Home/Show/index?id=:1',		
		'/^([a-z0-9]+)$/' => 'Home/List/index?url=:1',		
		'/^([a-z0-9]+)\/(\d+)$/' => 'Home/List/index?url=:1&p=:2',				
		'search.php' => 'Home/Search/index',					
		'tags.php' => 'Home/Tags/index',
		'book.php' => 'Home/Book/index',			
		'code.php' => 'Home/Code/index',
		'info.php' => 'Home/Info/index',
		'like.php' => 'Home/Like/index',
		'login.php' => 'Admin/Login/index',
	
				
							
				),
	
	'TAGLIB_LOAD'  => true,//加载标签库打开
	'APP_AUTOLOAD_PATH'         =>'@.TagLib',
	'TAGLIB_BUILD_IN'           =>'cx,Lists',
	'LOG_RECORD'=>false,  // 进行日志记录
    'LOG_EXCEPTION_RECORD'  => false,    // 是否记录异常信息日志
    'DB_FIELDS_CACHE'=> true, // 字段缓存信息
    'TMPL_CACHE_ON'    => false,        // 是否开启模板编译缓存,设为false则每次都会重新编译
    'TMPL_STRIP_SPACE' => false,       // 是否去除模板文件里面的html空格与换行
    'SHOW_ERROR_MSG' => true,    // 显示错误信息
	'SHOW_PAGE_TRACE'=>0,
	
	
);
$config_arr = include_once(CONF_PATH.'pcwap.php');
$config_arr3 = include_once(CONF_PATH.'webset.php');
return array_merge($config_arr,$config_arr1,$config_arr3);

?>