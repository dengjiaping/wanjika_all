<?php
$m_arr = array(
//应用ID
'aid' => '2c2894954d0368c0014d99972e730794',//'402881e74522467c0145254f6825000a',
//端口ID
'tid'=>'',
//密钥
'key' => 'slt|m2|150528',//'m2_www_m2_12345',
//接口ID
'api_id' => array('T288'=>'bonuspay_general@T288','T604'=>'bonuspay_general@T604'),//'2c288be445886dc701458870c5890001',
//钱包商户号
'bonuse_merchno'=>'611100000310430',//611100000301195
//钱包密钥
'bonuse_key'=>'ebcc1b3d',//868dd528
//服务编码+@+"接口编码"
'api_server_code' => '',
//随机数
'nonce' =>rand(000000,999999),
//是否调试模式
'debug' => 'true',
//10步模式接口地址
'url' => 'http://api.ebcm2.com/CoreServlet',
//mode:模式，可选项（默认：0，0=10步模式；1=4步模式），使用4步模式时打开注释。
'mode'=>'1',
//url_ac：4步模式的M2服务地址，4步模式时必填项。
'url_ac' => 'http://api.ebcm2.com/CoreServlet',
//url_ac_token：4步模式的得到访问令牌的M2服务地址，4步模式时必填项。
'url_ac_token' => 'http://api.ebcm2.com/access_token',
//获取令牌最大次数
'max_token' => 2,
//是否对数据包执行签名 1是  0否
'is_data_sign'=>'1',
//是否使用memcache
//'memcache_open' => false,
//memcache地址
//'memcached_server'=>'127.0.0.1:11211',
);





