<?php
define('IN_QQ1261352121', true);
//屏蔽错误
error_reporting(E_ERROR);
//设置超时时间
set_time_limit(0);
//读取关键字的文件
$file = 'key.txt'; 
//读取内容文件
$contentfile = 'content.txt'; 
//html文件夹
$dir = 'news/';
//检查文件夹
if(!is_dir($dir)) mkdir($dir, 0777,1);
//模板
$moban = file_get_contents('tpl.php');
//随机时间戳
function make_seed(){
	list($usec, $sec) = explode(' ', microtime());
    return (float) $sec + ((float) $usec * 100000);
}
//随机字符
function Spider_RAND($length){
	$possible = "ABCDEFGHIJKLM1234567890NOPQR";
	$str = '';
	while(strlen($str) < $length)
	$str .= substr($possible,(rand() % strlen($possible)),1);
	return $str;
}
//加载模板
$dirdata = explode('<!--QQ1261352121-->',file_get_contents('dirdata.php'));
//解析模板
function tpl_parse($str){
	$str = preg_replace('~\{(.*)\}~Uis','\'.$1.\'',$str);
	return $str;
}

//生成列表函数
function AutoWrite($filename,$filedata){
	global $dirdata;
	$dirdata2 .= $dirdata[1];
	$filecode = $dirdata[0].$filedata.$dirdata2;
	$handle = @fopen($filename,'w');
	$key = @fwrite($handle,$filecode);
	@fclose($handle);
	return $key ? true : false;
}
//生成内容页函数
function filew($filename,$filecode){
	$handle = @fopen($filename,'w');
	$key = @fwrite($handle,$filecode);
	@fclose($handle);
	return $key ? true : false;
}
//文件名
$infile1 = $dir.'index.html';
$infile2 = $dir.'page_2.html';
$infile3 = $dir.'page_3.html';
$infile4 = $dir.'page_4.html';
$infile5 = $dir.'page_5.html';
$infile6 = $dir.'page_6.html';
$infile7 = $dir.'page_7.html';
$infile8 = $dir.'page_8.html';
$infile9 = $dir.'page_9.html';
$infile10 = $dir.'page_10.html';
$infile11 = $dir.'page_11.html';
$infile12 = $dir.'page_12.html';
$infile13 = $dir.'page_13.html';
$infile14 = $dir.'page_14.html';
//关键词
$guanjianzi = file($file);
//统计key
$num = count($guanjianzi);
//正文
$content = file($contentfile);
//统计正文
$cnum = count($content);
//For循环
$row = 0;
for($k = 0;$k < $num;$k++){
	//news/ID.html
	$wfile = $dir.$k.'.html';
	//上一篇
	if($k!=0){
		$p = $k-1;
		$shang = '上一篇：<a href="'.($k-1).'.html">'.$guanjianzi[$p].'</a>';
	}else{
		$shang = '';
	}
	//下一篇
	if($num>$k+1){
		$p = $k+1;
		$xia = '下一篇：<a href="'.($k+1).'.html">'.$guanjianzi[$p].'</a>';
	}else{
		$xia = '';
	}
	if($num>$k+2){
		$p = $k+2;
		$xia2 = '<a href="'.($k+10).'.html">'.$guanjianzi[$p].'</a>';
	}else{
		$xia2 = '';
	}
	if($num>$k+3){
		$p = $k+3;
		$xia3 = '<a href="'.($k+100).'.html">'.$guanjianzi[$p].'</a>';
	}else{
		$xia3 = '';
	}
	if($num>$k+4){
		$p = $k+4;
		$xia4 = '<a href="'.($k+8).'.html">'.$guanjianzi[$p].'</a>';
	}else{
		$xia4 = '';
	}
	if($num>$k+5){
		$p = $k+5;
		$xia5 = '<a href="'.($k+5).'.html">'.$guanjianzi[$p].'</a>';
	}else{
		$xia5 = '';
	}
	if($num>$k+6){
		$p = $k+6;
		$xia6 = '<a href="'.($k+7).'.html">'.$guanjianzi[$p].'</a>';
	}else{
		$xia6 = '';
	}
	if($num>$k+7){
		$p = $k+7;
		$xia7 = '<a href="'.($k+9).'.html">'.$guanjianzi[$p].'</a>';
	}else{
		$xia7 = '';
	}
	if($num>$k+8){
		$p = $k+8;
		$xia8 = '<a href="'.($k+1000).'.html">'.$guanjianzi[$p].'</a>';
	}else{
		$xia8 = '';
	}
	if($num>$k+9){
		$p = $k+9;
		$xia9 = '<a href="'.($k+11).'.html">'.$guanjianzi[$p].'</a>';
	}else{
		$xia9 = '';
	}
	if($num>$k+10){
		$p = $k+10;
		$xia10 = '<a href="'.($k+18).'.html">'.$guanjianzi[$p].'</a>';
	}else{
		$xia10 = '';
	}
	if($num>$k+11){
		$p = $k+11;
		$xia11 = '<a href="'.($k+38).'.html">'.$guanjianzi[$p].'</a>';
	}else{
		$xia11 = '';
	}
	if($num>$k+12){
		$p = $k+12;
		$xia12 = '<a href="'.($k+25).'.html">'.$guanjianzi[$p].'</a>';
	}else{
		$xia12 = '';
	}
	if($num>$k+13){
		$p = $k+13;
		$xia13 = '<a href="'.($k+13).'.html">'.$guanjianzi[$p].'</a>';
	}else{
		$xia13 = '';
	}
	if($num>$k+14){
		$p = $k+14;
		$xia14 = '<a href="'.($k+19).'.html">'.$guanjianzi[$p].'</a>';
	}else{
		$xia14 = '';
	}
	if($num>$k+15){
		$p = $k+15;
		$xia15 = '<a href="'.($k+21).'.html">'.$guanjianzi[$p].'</a>';
	}else{
		$xia15 = '';
	}
	if($num>$k+16){
		$p = $k+16;
		$xia16 = '<a href="'.($k+108).'.html">'.$guanjianzi[$p].'</a>';
	}else{
		$xia16 = '';
	}
	if($num>$k+17){
		$p = $k+17;
		$xia17 = '<a href="'.($k+127).'.html">'.$guanjianzi[$p].'</a>';
	}else{
		$xia17 = '';
	}
	if($num>$k+18){
		$p = $k+18;
		$xia18 = '<a href="'.($k+118).'.html">'.$guanjianzi[$p].'</a>';
	}else{
		$xia18 = '';
	}
	if($num>$k+19){
		$p = $k+19;
		$xia19 = '<a href="'.($k+119).'.html">'.$guanjianzi[$p].'</a>';
	}else{
		$xia19 = '';
	}
	if($num>$k+20){
		$p = $k+20;
		$xia20 = '<a href="'.($k+120).'.html">'.$guanjianzi[$p].'</a>';
	}else{
		$xia20 = '';
	}
	if($num>$k+21){
		$p = $k+21;
		$xia21 = '<a href="'.($k+121).'.html">'.$guanjianzi[$p].'</a>';
	}else{
		$xia21 = '';
	}
	if($num>$k+22){
		$p = $k+22;
		$xia22 = '<a href="'.($k+122).'.html">'.$guanjianzi[$p].'</a>';
	}else{
		$xia22 = '';
	}
	if($num>$k+23){
		$p = $k+23;
		$xia23 = '<a href="'.($k+123).'.html">'.$guanjianzi[$p].'</a>';
	}else{
		$xia23 = '';
	}
	if($num>$k+24){
		$p = $k+24;
		$xia24 = '<a href="'.($k+124).'.html">'.$guanjianzi[$p].'</a>';
	}else{
		$xia24 = '';
	}
	if($num>$k+25){
		$p = $k+25;
		$xia25 = '<a href="'.($k+125).'.html">'.$guanjianzi[$p].'</a>';
	}else{
		$xia25 = '';
	}
	if($num>$k+26){
		$p = $k+26;
		$xia26 = '<a href="'.($k+126).'.html">'.$guanjianzi[$p].'</a>';
	}else{
		$xia26 = '';
	}
	if($num>$k+27){
		$p = $k+27;
		$xia27 = '<a href="'.($k+128).'.html">'.$guanjianzi[$p].'</a>';
	}else{
		$xia27 = '';
	}
	srand(make_seed());
	$startline = rand(0,$cnum);
	$getline = rand(5,20);
	reset($content);
	$c = '';
	$y = 0;
	while ($y<$getline){
		$yu = $startline+$y;
		$c = $c.$content[$yu]."<p>";
		$y++;
	}
	$newmoban = str_replace('{title}',$guanjianzi[$k],$moban);
	$newmoban = str_replace('{pass}',Spider_RAND(6),$newmoban);
	$newmoban = str_replace('{xia}',$xia,$newmoban);
	$newmoban = str_replace('{xia2}',$xia2,$newmoban);
	$newmoban = str_replace('{xia3}',$xia3,$newmoban);
	$newmoban = str_replace('{xia4}',$xia4,$newmoban);
	$newmoban = str_replace('{xia5}',$xia5,$newmoban);
	$newmoban = str_replace('{xia6}',$xia6,$newmoban);
	$newmoban = str_replace('{xia7}',$xia7,$newmoban);
	$newmoban = str_replace('{xia8}',$xia8,$newmoban);
	$newmoban = str_replace('{xia9}',$xia9,$newmoban);
	$newmoban = str_replace('{xia10}',$xia10,$newmoban);
	$newmoban = str_replace('{xia11}',$xia11,$newmoban);
	$newmoban = str_replace('{xia12}',$xia12,$newmoban);
	$newmoban = str_replace('{xia13}',$xia13,$newmoban);
	$newmoban = str_replace('{xia14}',$xia14,$newmoban);
	$newmoban = str_replace('{xia15}',$xia15,$newmoban);
	$newmoban = str_replace('{xia16}',$xia16,$newmoban);
	$newmoban = str_replace('{xia17}',$xia17,$newmoban);
	$newmoban = str_replace('{xia18}',$xia18,$newmoban);
	$newmoban = str_replace('{xia19}',$xia19,$newmoban);
	$newmoban = str_replace('{xia20}',$xia20,$newmoban);
	$newmoban = str_replace('{xia21}',$xia21,$newmoban);
	$newmoban = str_replace('{xia22}',$xia22,$newmoban);
	$newmoban = str_replace('{xia23}',$xia23,$newmoban);
	$newmoban = str_replace('{xia24}',$xia24,$newmoban);
	$newmoban = str_replace('{xia25}',$xia25,$newmoban);
	$newmoban = str_replace('{xia26}',$xia26,$newmoban);
	$newmoban = str_replace('{xia27}',$xia27,$newmoban);
	$newmoban = str_replace('{shang}',$shang,$newmoban);
	$newmoban = str_replace('{content}',$c,$newmoban);
	$newmoban = str_replace('{title}',$guanjianzi[$k],$newmoban);
	$newmoban = str_replace('{title1}',$guanjianzi[$k+1],$newmoban);
	$newmoban = str_replace('{title2}',$guanjianzi[$k-1],$newmoban);
	if($row==0){
		$ty = '<tr>';
	}else{
		$ty = '';
	}
	if($row==2){
		$te = '</tr>';
		$row = -1;
	}else{
		$te = '';
	}
	if($k < 800){
		$dirdata1 .= $ty."\r\n".'<td><a href="'.$k.'.html'.'">'.$guanjianzi[$k].'</a></td>'."\r\n".$te."\r\n";
		filew($wfile,$newmoban);
		echo "$k.html生成...........ok\r\n<br />";
	}
	elseif($k < 1600){
		$dirdata2 .= $ty."\r\n".'<td><a href="'.$k.'.html'.'">'.$guanjianzi[$k].'</a></td>'."\r\n".$te."\r\n";
		filew($wfile,$newmoban);
		echo "$k.html生成...........ok\r\n<br />";
	}
	elseif($k < 2400){
		$dirdata3 .= $ty."\r\n".'<td><a href="'.$k.'.html'.'">'.$guanjianzi[$k].'</a></td>'."\r\n".$te."\r\n";
		filew($wfile,$newmoban);
		echo "$k.html生成...........ok\r\n<br />";
	}
	elseif($k < 3200){
		$dirdata4 .= $ty."\r\n".'<td><a href="'.$k.'.html'.'">'.$guanjianzi[$k].'</a></td>'."\r\n".$te."\r\n";
		filew($wfile,$newmoban);
		echo "$k.html生成...........ok\r\n<br />";
	}
	elseif($k < 4000){
		$dirdata5 .= $ty."\r\n".'<td><a href="'.$k.'.html'.'">'.$guanjianzi[$k].'</a></td>'."\r\n".$te."\r\n";
		filew($wfile,$newmoban);
		echo "$k.html生成...........ok\r\n<br />";
	}
	elseif($k < 4800){
		$dirdata6 .= $ty."\r\n".'<td><a href="'.$k.'.html'.'">'.$guanjianzi[$k].'</a></td>'."\r\n".$te."\r\n";
		filew($wfile,$newmoban);
		echo "$k.html生成...........ok\r\n<br />";
	}
	elseif($k < 5600){
		$dirdata7 .= $ty."\r\n".'<td><a href="'.$k.'.html'.'">'.$guanjianzi[$k].'</a></td>'."\r\n".$te."\r\n";
		filew($wfile,$newmoban);
		echo "$k.html生成...........ok\r\n<br />";
	}
	elseif($k < 6400){
		$dirdata8 .= $ty."\r\n".'<td><a href="'.$k.'.html'.'">'.$guanjianzi[$k].'</a></td>'."\r\n".$te."\r\n";
		filew($wfile,$newmoban);
		echo "$k.html生成...........ok\r\n<br />";
	}
	elseif($k < 7200){
		$dirdata9 .= $ty."\r\n".'<td><a href="'.$k.'.html'.'">'.$guanjianzi[$k].'</a></td>'."\r\n".$te."\r\n";
		filew($wfile,$newmoban);
		echo "$k.html生成...........ok\r\n<br />";
	}
	elseif($k < 8000){
		$dirdata10 .= $ty."\r\n".'<td><a href="'.$k.'.html'.'">'.$guanjianzi[$k].'</a></td>'."\r\n".$te."\r\n";
		filew($wfile,$newmoban);
		echo "$k.html生成...........ok\r\n<br />";
	}
	elseif($k < 8800){
		$dirdata11 .= $ty."\r\n".'<td><a href="'.$k.'.html'.'">'.$guanjianzi[$k].'</a></td>'."\r\n".$te."\r\n";
		filew($wfile,$newmoban);
		echo "$k.html生成...........ok\r\n<br />";
	}
	elseif($k < 9600){
		$dirdata12 .= $ty."\r\n".'<td><a href="'.$k.'.html'.'">'.$guanjianzi[$k].'</a></td>'."\r\n".$te."\r\n";
		filew($wfile,$newmoban);
		echo "$k.html生成...........ok\r\n<br />";
	}
	elseif($k < 10400){
		$dirdata13 .= $ty."\r\n".'<td><a href="'.$k.'.html'.'">'.$guanjianzi[$k].'</a></td>'."\r\n".$te."\r\n";
		filew($wfile,$newmoban);
		echo "$k.html生成...........ok\r\n<br />";
	}
	elseif($k < 18000){
		$dirdata14 .= $ty."\r\n".'<td><a href="'.$k.'.html'.'">'.$guanjianzi[$k].'</a></td>'."\r\n".$te."\r\n";
		filew($wfile,$newmoban);
		echo "$k.html生成...........ok\r\n<br />";
	}
	$row++;
}
AutoWrite($infile1,$dirdata1);
AutoWrite($infile2,$dirdata2);
AutoWrite($infile3,$dirdata3);
AutoWrite($infile4,$dirdata4);
AutoWrite($infile5,$dirdata5);
AutoWrite($infile6,$dirdata6);
AutoWrite($infile7,$dirdata7);
AutoWrite($infile8,$dirdata8);
AutoWrite($infile9,$dirdata9);
AutoWrite($infile10,$dirdata10);
AutoWrite($infile11,$dirdata11);
AutoWrite($infile12,$dirdata12);
AutoWrite($infile13,$dirdata13);
AutoWrite($infile14,$dirdata14);
?>