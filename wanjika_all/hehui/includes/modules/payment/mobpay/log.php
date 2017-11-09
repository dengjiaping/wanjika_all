<?php
function RecordLog($uname, $text) {
   $date_time = date("Y��m��d H:i:s");
   $LOG_FILE = "/web/logs/mobpay_".date("Ymd").$uname.".txt";
//    $LOG_FILE = dirname(__FILE__)."/mobpay_".date("Ymd").$uname.".txt";
   $text = "$date_time  ".$text;

   if (!file_exists($LOG_FILE)) {
    touch($LOG_FILE);
    //chmod($LOG_FILE,"744");
   }

   $fd = @fopen($LOG_FILE, "a");
   @fwrite($fd, $text."\r\n");
   @fclose($fd);
}

function RecordMyLog($text) {
	RecordLog("YGM",$text);
}
?>
