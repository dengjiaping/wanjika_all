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

</style>
  <body>
	
  


  <div class="panel">   
	  <div class="panel-heading">
	  <img src="http://www.pcwap.cn/Uploads/mid/5425923cc0d72.png" />
  </div>
	   <div class="panel-body">
		  <div class="alert">
				
				<?php  
						if ( !(phpversion() >= '5.2') ){
							$dsb='disabled="disabled"';
							echo '<p>PHP版必须大于5.2</p><br />';
						}
						if ( !is_writable('../Conf/') ){
							$dsb='disabled="disabled"';
							echo '<p>Conf目录不可写</p><br />';
						}
						?>						
						
			</div>
		  	 <form class="form-horizontal" action="installed.php" method="post" role="form">
					<div class="form-group">
						<label for="DB_TYPE" class="col-sm-3 control-label">数据库类型:</label>
						<div class="col-sm-4">
						  <input type="text" class="form-control" id="DB_TYPE" name="DB_TYPE" value="mysql"  placeholder="mysql">
						</div>
					</div>
					<div class="form-group">
						<label for="DB_HOST" class="col-sm-3 control-label">主机名:</label>
						<div class="col-sm-4">
						  <input type="text" class="form-control" id="DB_HOST" name="DB_HOST" value="localhost" placeholder="127.0.0.1">
						</div>
					</div>
						<div class="form-group">
						<label for="DB_PORT" class="col-sm-3 control-label">端口号:</label>
						<div class="col-sm-4">
						  <input type="text" class="form-control" id="DB_PORT" name="DB_PORT" value="3306" placeholder="3306">
						</div>
					</div>
						<div class="form-group">
						<label for="DB_NAME" class="col-sm-3 control-label">数据库名:</label>
						<div class="col-sm-4">
						  <input type="text" class="form-control" id="DB_NAME" name="DB_NAME" placeholder="数据库名">
						</div>
					</div>
						<div class="form-group">
						<label for="DB_USER" class="col-sm-3 control-label">数据库用户名:</label>
						<div class="col-sm-4">
						  <input type="text" class="form-control" id="DB_USER" name="DB_USER"  placeholder="数据库用户名">
						</div>
					</div>
					<div class="form-group">
						<label for="DB_PWD" class="col-sm-3 control-label">数据库密码:</label>
						<div class="col-sm-4">
						  <input type="text" class="form-control" id="DB_PWD" name="DB_PWD"  placeholder="数据库密码">
						  <input type="hidden"  name="DB_PREFIX" id="DB_PREFIX" value="az_" />
						</div>
					</div>
					<div class="form-group">
						<label for="DB_PWD" class="col-sm-3 control-label">表前缀:</label>
						<div class="col-sm-4">
						
						  <input type="text"  name="DB_PREFIX" id="DB_PREFIX" value="pcwap_" />
						</div>
					</div>
					
					<div class="form-group">
						<label for="webuser" class="col-sm-3 control-label">后台管理员账号:</label>
						<div class="col-sm-4">
						  <input type="text" class="form-control" id="webuser" name="webuser"   placeholder="admin">
						</div>
					</div>
					<div class="form-group">
						<label for="webuserpass" class="col-sm-3 control-label">后台管理员密码:</label>
						<div class="col-sm-4">
						  <input type="text" class="form-control" id="webuserpass" name="webuserpass" placeholder="admin">
						</div>
					</div>
			 
		   
		  
						  <button type="submit" class="btn"  id="post">下一步</button>						
					
		</form>
			 <div class="panel-footer">声明：请使用IE8以上浏览 PCWAP.CN保留所有版权 盗版必究 <a href="http://wpa.qq.com/msgrd?v=3&uin=442648313&site=qq&menu=yes" >帮助</a></div>
		  </div>
	</div>
		  
	
		
		
				
		
<script>
$(document).ready(function(){
  $("form").submit(function(e){

			if($("input[name='DB_TYPE']").val()==false){
					$("input[name='DB_TYPE']").focus();
							return false
				}
				if($("input[name='DB_HOST']").val()==false){
							$("input[name='DB_HOST']").focus();
							return false
				}
				if($("input[name='DB_PORT']").val()==false){
							$("input[name='DB_PORT']").focus();
							return false
				}
				if($("input[name='DB_NAME']").val()==false){
							$("input[name='DB_NAME']").focus();
							return false
				}
				if($("input[name='DB_USER']").val()==false){
							$("input[name='DB_USER']").focus();
							return false
				}
				
				if($("input[name='webuser']").val()==false){
							$("input[name='webuser']").focus();
							return false
				}
				if($("input[name='webuserpass']").val()==false){
							$("input[name='webuserpass']").focus();
							return false
				}
			
  });
});
	
</script>

  </body>
</html>

