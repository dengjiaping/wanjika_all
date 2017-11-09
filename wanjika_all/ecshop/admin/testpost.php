<?php

// $remote_server =
// "http://fanyi.youdao.com/translate?smartresult=dict&smartresult=rule&smartresult=ugc&sessionFrom=null";
//$remote_server = "http://fanyi.youdao.com/translate";
//$post_string = "type=AUTO&i=%C3%A5%C2%AE%C2%81%C3%A6%C2%B3%C2%A2%C3%A6%C2%B2%C2%AA%C3%A6%C2%B1%C2%9F%C3%A8%C2%BF%C2%9B%C3%A5%C2%87%C2%BA%C3%A5%C2%8F%C2%A3%C3%A6%C2%9C%C2%89%C3%A9%C2%99%C2%90%C3%A5%C2%85%C2%AC%C3%A5%C2%8F%C2%B8&doctype=json&xmlVersion=1.6&keyfrom=fanyi.web&ue=UTF-8&typoResult=true&flag=false";

//$res = request_by_other ( $remote_server, $post_string );
//echo $res;


$remote_server = "fanyi.youdao.com";
$remote_path = "translate?smartresult=dict&smartresult=rule&smartresult=ugc&sessionFrom=null";
$post_string = "type=AUTO&i=%C3%A5%C2%AE%C2%81%C3%A6%C2%B3%C2%A2%C3%A6%C2%B2%C2%AA%C3%A6%C2%B1%C2%9F%C3%A8%C2%BF%C2%9B%C3%A5%C2%87%C2%BA%C3%A5%C2%8F%C2%A3%C3%A6%C2%9C%C2%89%C3%A9%C2%99%C2%90%C3%A5%C2%85%C2%AC%C3%A5%C2%8F%C2%B8&doctype=json&xmlVersion=1.6&keyfrom=fanyi.web&ue=UTF-8&typoResult=true&flag=false";
$res = request_by_socket($remote_server, $remote_path,$post_string);
echo $res;
function request_by_socket($remote_server, $remote_path, $post_string, $port = 80, $timeout = 30) 
{
	$socket = fsockopen ( $remote_server, $port, $errno, $errstr, $timeout );
	if (! $socket)
		die ( "$errstr($errno)" );
	echo $post_string;
	fwrite ( $socket, "POST $remote_path HTTP/1.1\r\n" );
	fwrite ( $socket, "User-Agent:User-Agent:Mozilla/5.0 (Windows NT 6.1; rv:17.0) Gecko/20100101 Firefox/17.0\r\n" );
	fwrite ( $socket, "HOST: $remote_server\r\n" );
	fwrite ( $socket, "X-Requested-With:XMLHttpRequest\r\n" );
	fwrite ( $socket, "Content-length: " . (strlen ( $post_string ) + 8) . '\r\n' );
	fwrite ( $socket, "Accept:application/json, text/javascript, */*; q=0.01\r\n" );
	fwrite ( $socket, "Accept-Encoding:gzip, deflate\r\n" );
	fwrite ( $socket, "Accept-Language:zh-cn,zh;q=0.8,en-us;q=0.5,en;q=0.3\r\n" );
	fwrite ( $socket, "Cache-Control:no-cache\r\n" );
	fwrite ( $socket, "Connection:keep-alive\r\n" );
	fwrite ( $socket, "Content-Type:application/x-www-form-urlencoded; charset=UTF-8\r\n" );
	fwrite ( $socket, "Cookie:OUTFOX_SEARCH_USER_ID=1135942089@61.50.132.206; YODAO_MOBILE_ACCESS_TYPE=1; JSESSIONID=abcvzDq9zczXZ8U2b0kWt; SESSION_FROM_COOKIE=fanyiweb; __ntes__test__cookies=1357395576389; _ntes_nnid=59de60d8b380e5b1a953dde6bc6298f3,1357391069336; YOUDAO_MOBILE_ACCESS_TYPE=1; youdao_usr_info=dWlkPTQ4Nzg4MzQxMjM0MzE2NDIzMTg6ZnJwPWh0dHAlM0ElMkYlMkZmLnlvdWRhby5jb20lMkYl_M0ZwYXRoJTNEZmFueWk6c2NudD0xOmZ2dD0xMzU3MzkxMDkyMzA0OmZscD1odHRwJTNBJTJGJTJG_Zi55b3VkYW8uY29tJTJGJTNGdmVuZG9yJTNEZmFueWlpbnB1dA==; youdao_session_info=c2lkPTQ4Nzg4MzQxMjM0MzE2NDIzMTgtMTp0aW1lPTEzNTczOTU1Njg1ODg6cHY9MTI=\r\n" );
	fwrite ( $socket, "Pragma:no-cache\r\n" );
	fwrite ( $socket, "\r\n" );
	fwrite ( $socket, "$post_string\r\n" );
	fwrite ( $socket, "\r\n" );
	$header = "";
	while ( $str = trim ( fgets ( $socket, 4096 ) ) ) {
	echo	$header .= $str;
	}
	$data = "";
	while ( ! feof ( $socket ) ) {
		$data .= fgets ( $socket, 4096 );
	}
	return $data;
}
function request_by_other($remote_server, $post_string) {
	$context = array (
			'http' => array (
					'method' => 'POST',
					'header' => 'Accept:application/json, text/javascript, */*; q=0.01' . '\r\n' . 'Accept-Encoding:gzip, deflate' . '\r\n' . 'Accept-Language:zh-cn,zh;q=0.8,en-us;q=0.5,en;q=0.3' . '\r\n' . 'Cache-Control:no-cache' . '\r\n' . 'Connection:keep-alive' . '\r\n' . 'Content-Length:' . strlen ( $post_string ) . '\r\n' . 'Content-Type:application/x-www-form-urlencoded; charset=UTF-8' . '\r\n' . 'Cookie:OUTFOX_SEARCH_USER_ID=1135942089@61.50.132.206; YODAO_MOBILE_ACCESS_TYPE=1; JSESSIONID=abcvzDq9zczXZ8U2b0kWt; SESSION_FROM_COOKIE=fanyiweb; __ntes__test__cookies=1357391069334; _ntes_nnid=59de60d8b380e5b1a953dde6bc6298f3,1357391069336; YOUDAO_MOBILE_ACCESS_TYPE=1; youdao_usr_info=dWlkPTQ4Nzg4MzQxMjM0MzE2NDIzMTg6ZnJwPWh0dHAlM0ElMkYlMkZmLnlvdWRhby5jb20lMkYl_M0ZwYXRoJTNEZmFueWk6c2NudD0xOmZ2dD0xMzU3MzkxMDkyMzA0OmZscD1odHRwJTNBJTJGJTJG_Zi55b3VkYW8uY29tJTJGJTNGdmVuZG9yJTNEZmFueWlpbnB1dA==; youdao_session_info=c2lkPTQ4Nzg4MzQxMjM0MzE2NDIzMTgtMTp0aW1lPTEzNTczOTEwOTIzMDQ6cHY9MQ==' . '\r\n' . 'Host:fanyi.youdao.com' . '\r\n' . 'Pragma:no-cache' . '\r\n' . 'Referer:http://fanyi.youdao.com/' . '\r\n' . 'User-Agent:Mozilla/5.0 (Windows NT 6.1; rv:17.0) Gecko/20100101 Firefox/17.0' . '\r\n' . 'X-Requested-With:XMLHttpRequest',
					'content' => $post_string 
			) 
	);
	$stream_context = stream_context_create ( $context );
	$data = file_get_contents ( $remote_server, false, $stream_context );
	return $data;
}