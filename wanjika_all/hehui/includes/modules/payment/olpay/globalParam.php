<?php
$GLOBALS['localAddr'] = "http://www.wanjika.com/";
$GLOBALS['characterSet'] = "02"; //00--GBK;01--GB2312;02--UTF-8
$GLOBALS['callbackUrl'] = $GLOBALS['localAddr']."/respond.php"; 
$GLOBALS['notifyUrl'] = $GLOBALS['localAddr']."/pay/mobRcvPay.php";
$GLOBALS['requestId'] = strtotime("now");                      
$GLOBALS['signType'] = "MD5";                                                            
$GLOBALS['version'] = "2.0.0"; 
$GLOBALS['merchantId'] = "888009915140006";
$GLOBALS['signKey'] = "hXe4pEtRjNWGbr0QjvXhZ09h1jYqezQ2GgTvqDk6SWCGi1kUOtp2Z9ePspV8azhY";
$GLOBALS['reqUrl'] ="https://ipos.10086.cn/ips/cmpayService";

?>
