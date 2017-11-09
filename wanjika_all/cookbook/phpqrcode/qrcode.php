<?php
include "./phpqrcode.php";
$value="http://cookbook.wjike.com/index.php?s=/".$_REQUEST["cookid"].".html";
$errorCorrectionLevel = "L"; // 纠错级别：L、M、Q、H
$matrixPointSize = "4"; // 点的大小：1到10
$color='ff0000';
QRcode::png($value, false, $errorCorrectionLevel, $matrixPointSize,0,false,$color);

    