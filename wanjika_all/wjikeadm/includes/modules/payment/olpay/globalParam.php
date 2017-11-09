<?php
$GLOBALS['localAddr'] = "http://www.wjike.com";
$GLOBALS['characterSet'] = "02"; //00--GBK;01--GB2312;02--UTF-8
$GLOBALS['callbackUrl'] = $GLOBALS['localAddr']."/respond.php"; 
$GLOBALS['notifyUrl'] = $GLOBALS['localAddr']."/pay/mobRcvPay.php";
$GLOBALS['requestId'] = strtotime("now");                      
$GLOBALS['signType'] = "MD5";                                                            
$GLOBALS['version'] = "2.0.0"; 
$GLOBALS['merchantId'] = "888009953110759";                                                       
$GLOBALS['signKey'] = "qZkBkBnnZATNUOvsEbFo3gpgmoVjYTeD8S0E0AsCYzyWQY09nCVc8o3HyHmX1AE0";
$GLOBALS['reqUrl'] ="https://ipos.10086.cn/ips/cmpayService";

?>
