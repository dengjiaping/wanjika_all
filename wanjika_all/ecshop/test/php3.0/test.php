<?php
 include("umpayHead.php");
 $url="0000|20080306|5609|200|0205582144";
 $sign = ssl_sign($url,"5609_XinYuXiongDi.key.pem");
 echo $sign;
 $result=ssl_verify($url,$sign,"5609_XinYuXiongDi.cert.pem");
 if(!$result){
	echo("shibaile");
	//exit;
}else echo("chenggong");
?>