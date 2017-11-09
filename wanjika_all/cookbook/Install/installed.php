<?php 

if (file_exists('../Conf/pcwap.php')) {
header("Content-type: text/html; charset=utf-8");
	echo "你已经安装过了！要重装请删除Conf目录下的PCWAP.php";
    exit();
}
?>

<!DOCTYPE html>
<html>
  <head>
    <title>小C美图管理系统</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<script src="../Tpl/Admin/Public/js/jquery.min.js"></script>
<style>
body{
font-family:"Times New Roman",Georgia,Serif;
font-size: 100%;
background: url(../Tpl/Admin/Public/images/bg.jpg);

background-attachment: fixed;
background-position: center;
background-size: cover;
}
a {
text-decoration: none;
}
html, body, div, span, applet, object, iframe, h1, h2, h3, h4, h5, h6, p, blockquote, pre, a, abbr, acronym, address, big, cite, code, del, dfn, em, img, ins, kbd, q, s, samp, small, strike, strong, sub, sup, tt, var, b, u, i, dl, dt, dd, ol, nav ul, nav li, fieldset, form, label, legend, table, caption, tbody, tfoot, thead, tr, th, td, article, aside, canvas, details, embed, figure, figcaption, footer, header, hgroup, menu, nav, output, ruby, section, summary, time, mark, audio, video {
margin: 0;
padding: 0;
border: 0;
font-size: 100%;
font: inherit;
vertical-align: baseline;
}
div{display:block}
.panel{width:500px; margin:5% auto 0 auto;box-shadow: 0 0 0 3px rgba(56, 41, 32, 0.25);}
.panel-heading{ padding:25px;width:450px;background:#6D4A70;color:#fff;line-height:25px;float:left;font-weight:bold;}
.panel-body{padding:10px 25px;width:450px;float:left;background:#fff;}
.alert{color:red;text-align:center;line-height:40px;}
.form-horizontal{width:100%;}
.form-group{width:100%;line-height:40px;float:left;color:#888;}
.col-sm-3{width:140px;height:40px; line-height:40px;float:left;text-align:right;margin-right:8px;}
.col-sm-4{width:300px;height:40px; line-height:40px;float:left;}
input[type="text"]{padding: 0.5em 2em 0.5em 1em;width:200px;}
.btn{cursor: pointer;outline: none;width: 200px;line-height:40px; margin:20px 0 20px 150px;font-size: 18px;float:left;
background: #6C496F;
color: #fff;
border: 2px solid #6C496F;
border-radius:6px;
}
.btn:hover {
background: #fff;
color: #6C496F;
border: 2px solid #6C496F;}
.panel-footer{width:100%; float:left;line-height:25px; font-size:14px;margin:20px 0;}
p{line-height:25px;font-size:16px; color:#888;}
</style>
  <body>
	
  


  <div class="panel">   
	  <div class="panel-heading">
	  <img src="http://www.pcwap.cn/Uploads/mid/5425923cc0d72.png" />
  </div>
	   <div class="panel-body">
			<?php
			

				
					@header("content-Type: text/html; charset=utf-8");
				
					$host = trim($_POST['DB_HOST']).':'.trim($_POST['DB_PORT']);
					$user = trim($_POST['DB_USER']);
					$pwd = trim($_POST['DB_PWD']);					
					$data_base = trim($_POST['DB_NAME']);
					$conn = @mysql_connect($host,$user,$pwd);
					if (!$conn)
						  {
						  die('数据库链接失败,请返回');
						  }				
					$db_crac=@mysql_query("CREATE DATABASE IF NOT EXISTS $data_base default charset utf8 COLLATE utf8_general_ci;",$conn);
					
					if (!$db_crac){
							die('数据库创建失败.请手动创建数据库');						
					}
					$db_selected=@mysql_select_db($data_base,$conn);
					if(!$db_selected){
						die('数据库创建失败.请手动创建数据库');		
					}
									 
					$file_name ="pcwap.sql";					  
					$get_sql_data = file_get_contents($file_name);				
					$explode = explode(";",$get_sql_data);
				
					
					$cnt = count($explode);
					
					for($i=0;$i<$cnt ;$i++){						
						$sql = str_ireplace('pcwap_',trim($_POST['DB_PREFIX']),$explode[$i]);					
						mysql_query('SET NAMES UTF8');
						@$result =mysql_query($sql);
						
					}	
					mysql_close($conn);	
					
					$peizhi="<?php 
							 return array(
							'DB_TYPE'=>"."'".trim($_POST['DB_TYPE'])."'".",  
							'DB_HOST'=>"."'".trim($_POST['DB_HOST'])."'".", 
							'DB_PORT'=>"."'".trim($_POST['DB_PORT'])."'".", 
							'DB_NAME'=>"."'".trim($_POST['DB_NAME'])."'".", 
							'DB_USER'=>"."'".trim($_POST['DB_USER'])."'".", 
							'DB_PWD'=>"."'".trim($_POST['DB_PWD'])."'".", 
							'DB_PREFIX'=>"."'".trim($_POST['DB_PREFIX'])."'".", 
							'webuser'=>"."'".trim($_POST['webuser'])."'".", 
							'webuserpass'=>"."'".trim($_POST['webuserpass'])."'".",				
							);";
				$confpath ='../Conf/pcwap.php';
				$webconfig = @fopen($confpath,w);
				if($webconfig){
				$fwrite=fwrite($webconfig,$peizhi );			
				if($fwrite > 0){
						echo "<p>配置文件创建成功！</p>";			
						echo "<p>PCWAP程序已成功安装！</p>";				
						echo "<a href=\"../index.php?s=/Admin\">进入后台</a>";
						@file_get_contents('http://www.pcwap.cn/tongji?url='.$_SERVER["HTTP_HOST"]); 						
					}
				}
				else{
					echo "配置文件生成失败，请检查根目录是否可写！";
				}
			
			
			?>
		  </div>
		  
		
		
		
		</div>
			
	


  </body>
</html>

