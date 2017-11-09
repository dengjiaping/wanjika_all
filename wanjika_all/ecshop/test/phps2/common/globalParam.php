<?php
$GLOBALS['localAddr'] = "http://127.0.0.1/HiSimplePHP";                                
$GLOBALS['characterSet'] = "02"; //00--GBK;01--GB2312;02--UTF-8                          
$GLOBALS['callbackUrl'] = $GLOBALS['localAddr']."/back_url.php";                                                                                                                             
$GLOBALS['notifyUrl'] = $GLOBALS['localAddr']."/notify_url.php";                                   
$GLOBALS['requestId'] = strtotime("now");                      
$GLOBALS['signType'] = "MD5";                                                            
$GLOBALS['version'] = "2.0.0"; 
$GLOBALS['merchantId'] = "888073157340001";                                                       
$GLOBALS['signKey'] = "o5QLVefRcE2PseQpldj1gJgNsD4A1qDdzYNF1Sj91cz1Ng40KciSFzF1mS1Re7Mz";
$GLOBALS['reqUrl'] ="https://ipos.10086.cn/ips/cmpayService";

?>
