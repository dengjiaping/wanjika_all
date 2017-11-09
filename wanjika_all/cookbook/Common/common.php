<?php 

function browser()
{
	switch(TRUE)
	{
	// Apple/iPhone browser renders as mobile
	case (preg_match('/(apple|iphone|ipod)/i', $_SERVER['HTTP_USER_AGENT']) && preg_match('/mobile/i', $_SERVER['HTTP_USER_AGENT'])):
	$browser = "mobile";
	break; 
	// Other mobile browsers render as mobile
	case (preg_match('/(blackberry|configuration\/cldc|hp |hp-|htc |htc_|htc-|iemobile|kindle|midp|mmp|motorola|mobile|nokia|opera mini|opera mobi|palm|palmos|pocket|portalmmm|ppc;|smartphone|sonyericsson|sqh|spv|symbian|
	treo|up.browser|up.link|vodafone|windows ce|xda |xda_)/i', $_SERVER['HTTP_USER_AGENT'])):
	$browser = "mobile";
	break; 
	// Wap browser
	case (((strpos(strtolower($_SERVER['HTTP_ACCEPT']),'text/vnd.wap.wml') > 0) || (strpos(strtolower($_SERVER['HTTP_ACCEPT']),'application/vnd.wap.xhtml+xml')>0)) || ((isset($_SERVER['HTTP_X_WAP_PROFILE']) || isset($_SERVER['HTTP_PROFILE'])))):
	$browser = "mobile";
	break; 
	// Shortend user agents
	case (in_array(strtolower(substr($_SERVER['HTTP_USER_AGENT'],0,3)),array('lg '=>'lg ','lg-'=>'lg-','lg_'=>'lg_','lge'=>'lge'))); 
	$browser = "mobile";
	break; 
	// More shortend user agents
	case (in_array(strtolower(substr($_SERVER['HTTP_USER_AGENT'],0,4)),array('acs-'=>'acs-','amoi'=>'amoi','doco'=>'doco','eric'=>'eric','huaw'=>'huaw','lct_'=>'lct_','leno'=>'leno','mobi'=>'mobi','mot-'=>'mot-','moto'=>'moto','nec-'=>'nec-','phil'=>'phil','sams'=>'sams','sch-'=>'sch-','shar'=>'shar','sie-'=>'sie-','wap_'=>'wap_','zte-'=>'zte-')));
	$browser = "mobile";
	break; 
	// Render mobile site for mobile search engines
	case (preg_match('/Googlebot-Mobile/i', $_SERVER['HTTP_USER_AGENT']) || preg_match('/YahooSeeker\/M1A1-R2D2/i', $_SERVER['HTTP_USER_AGENT'])):
	$browser = "mobile";
	break;
	}
	return $browser;
}
	function create_unique() {   
		$data = $_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR'];   
		return sha1($data); 

	}
 function checkEmail($inAddress)   
			{   
			return (ereg("^([a-zA-Z0-9_-])+@([a-zA-Z0-9_-])+(.[a-zA-Z0-9_-])+",$inAddress));   
			} 
function msubstr($str, $start=0, $length, $charset="utf-8", $suffix=false){

 if(function_exists("mb_substr")){

  if($suffix)

   return mb_substr($str, $start, $length, $charset)."...";

  else

   return mb_substr($str, $start, $length, $charset);

 }elseif(function_exists('iconv_substr')) {

  if($suffix)

   return iconv_substr($str,$start,$length,$charset)."...";

  else

   return iconv_substr($str,$start,$length,$charset);

 }


 $re['utf-8'] = "/[x01-x7f]|[xc2-xdf][x80-xbf]|[xe0-xef][x80-xbf]{2}|[xf0-xff][x80-xbf]{3}/";

 $re['gb2312'] = "/[x01-x7f]|[xb0-xf7][xa0-xfe]/";

 $re['gbk'] = "/[x01-x7f]|[x81-xfe][x40-xfe]/";

 $re['big5'] = "/[x01-x7f]|[x81-xfe]([x40-x7e]|xa1-xfe])/";

 preg_match_all($re[$charset], $str, $match);

 $slice = join("",array_slice($match[0], $start, $length));

 if($suffix) return $slice."…";

 return $slice;

}
function inject_check($sql_str) {
	return eregi ( 'select|inert|update|delete|\'|\/\*|\*|\.\.\/|\.\/|UNION|into|load_file|outfile', $sql_str );
}

function get_keywords($keywords){

	$keywords=str_replace("-",",",$keywords);

	$keywords=str_replace("，",",",$keywords);

	//$keywords=str_replace(" ",",",$keywords);

	$keywords=str_replace("|",",",$keywords);

	$keywords=str_replace("、",",",$keywords);

	$keywords=str_replace(",,",",",$keywords);

	$keywords=str_replace("<","",$keywords);

	$keywords=str_replace(">","",$keywords);

	return explode(",",trim($keywords));

}
function send_mail($to, $name, $subject = '', $body = '', $attachment = null, $config = '') {
   // $config = is_array($config) ? $config : C('SYSTEM_EMAIL');
    import('Class.PHPMailer.phpmailer',LIB_PATH);         //从PHPMailer目录导class.phpmailer.php类文件
    $mail = new PHPMailer();                           //PHPMailer对象
    $mail->CharSet = 'UTF-8';                         //设定邮件编码，默认ISO-8859-1，如果发中文此项必须设置，否则乱码
    $mail->IsSMTP();                                   // 设定使用SMTP服务
//    $mail->IsHTML(true);
    $mail->SMTPDebug = 0;                             // 关闭SMTP调试功能 1 = errors and messages2 = messages only
    $mail->SMTPAuth = true;                           // 启用 SMTP 验证功能
    if (C('smtp_port') == 465)
        $mail->SMTPSecure = 'ssl';                    // 使用安全协议
    $mail->Host = C('smtp_host');                // SMTP 服务器
    $mail->Port = C('smtp_port');                // SMTP服务器的端口号
    $mail->Username = C('smtp_user');           // SMTP服务器用户名
    $mail->Password = C('smtp_pass'); // SMTP服务器密码
	$from_name = C('from_name');
	if($from_name==""){
		$from_name = C('webname');
	}
    $mail->SetFrom(C('from_email'),$from_name);
    $replyEmail = C('reply_email') ? C('reply_email') : C('reply_email');
    $replyName = C('reply_name') ? C('reply_name') : C('reply_name');
    $mail->AddReplyTo($replyEmail, $replyName);
    $mail->Subject = $subject;
    $mail->MsgHTML($body);
    $mail->AddAddress($to, $name);
    if (is_array($attachment)) { // 添加附件
        foreach ($attachment as $file) {
            if (is_array($file)) {
                is_file($file['path']) && $mail->AddAttachment($file['path'], $file['name']);
            } else {
                is_file($file) && $mail->AddAttachment($file);
            }
        }
    } else {
        is_file($attachment) && $mail->AddAttachment($attachment);
    }
    return $mail->Send() ? true : $mail->ErrorInfo;
}

	function openmulu($mulu){
				@$dir = opendir($mulu);
				while (($file = readdir($dir)) != false){
					 if($file!="." && $file!=".."){		
						$mul[]=array(
							'file'=>$file,
						);
					 }
				 }				
				return $mul;
				closedir($dir);				
		}
	function openmulu1($mulu){
				@$dir = opendir($mulu);
				
				while (($file = readdir($dir)) != false){
					 if($file!="." && $file!=".."){		
						$mo[]=$file;					
					 }
				 }		
					return $mo;				 
				//return $file;
				closedir($dir);				
		}
	function get_pinyin($str,$fenge='', $ishead = 1) {
    global $pinyins;
    $restr = array("", "");

    $str = trim($str);
    $slen = strlen($str);
    if ($slen < 2) {
        return $str;
    }
    if (count($pinyins) == 0) {
        $fp = fopen(CONF_PATH.'pinyin.dat', 'r');
        while (!feof($fp)) {
            $line = trim(fgets($fp));
            if (empty($line))
                continue;
            $pinyins[$line[0] . $line[1] . $line[2]] = substr($line, 4, strlen($line) - 4);
        }
        fclose($fp);
    }
    for ($i = 0; $i < $slen; $i++) {
        $l = 0;
        if (ord($str[$i]) > 0x80) {
            if (!isset($str[$i]) || !isset($str[$i+1]) || !isset($str[$i+2])) {
                continue;
            }
            @$c = $str[$i] . $str[$i + 1] . $str[$i + 2];
            $i+=2;
            if (isset($pinyins[$c])) {
                $restr[0] .= $pinyins[$c].$fenge;
                $restr[1] .= $pinyins[$c][0];
            } else {
//                     $restr[0] .= "_";
//                     $restr[1] .= "_";
            }
            if ($i < ($slen - 1)) {
//                     $restr[0] .= " ";
//                     $restr[1] .= " ";
            }
            $l++;
        } else if (preg_match("/[a-z0-9]/i", $str[$i])) {
            $restr[0] .= $str[$i];
            $restr[1] .= $str[$i];
        } else {
//                 $restr[0] .= "_";
//                 $restr[1] .= "_";
        }
    }
    $restr[0] = strtolower(str_replace("  ", " ", $restr[0]));
    $restr[1] = strtolower($restr[1]);
    return $restr;
}
function getFile($url,$save_dir='',$filename='',$type=0){
  if(trim($url)==''){
   return false;
  }
  if(trim($save_dir)==''){
   $save_dir='./';
  }
  if(0!==strrpos($save_dir,'/')){
   $save_dir.='/';
  }
  //创建保存目录
  if(!file_exists($save_dir)&&!mkdir($save_dir,0777,true)){
   return false;
  }
 //获取远程文件所采用的方法
 if($type){
  $ch=curl_init();
  $timeout=5;
  curl_setopt($ch,CURLOPT_URL,$url);
  curl_setopt($ch,CURLOPT_RETURNTRANSFER,1);
  curl_setopt($ch,CURLOPT_CONNECTTIMEOUT,$timeout);
  $content=curl_exec($ch);
  curl_close($ch);
 }else{
  ob_start();
  readfile($url);
  $content=ob_get_contents();
  ob_end_clean();
}
 $size=strlen($content);
 //文件大小
 $fp2=@fopen($save_dir.$filename,'a');
 fwrite($fp2,$content);
 fclose($fp2);
 unset($content,$url);
 return array('file_name'=>$filename,'save_path'=>$save_dir.$filename);
}
		
?>