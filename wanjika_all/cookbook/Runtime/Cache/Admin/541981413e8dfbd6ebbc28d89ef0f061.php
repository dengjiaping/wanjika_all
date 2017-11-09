<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
  <head>
    <title>小C美图管理系统</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
	<script src="__PUBLIC__js/jquery.min.js"></script>
<style>
body{
font-family:"Times New Roman",Georgia,Serif;
font-size: 100%;
background: url(__PUBLIC__images/bg.jpg);

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

.col-sm-4{width:250px;height:40px; line-height:40px;float:left;margin:5px 0;}
.col-sm-2{width:250px;height:40px; line-height:40px;float:left;}
input[type="text"]{padding: 0.5em 2em 0.5em 1em;}
input[type="password"]{padding: 0.5em 2em 0.5em 1em;}
#code{width:80px;float:left;}
#checkImg{width:50px;float:left;height:32px;margin-left:8px;}
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
  </div>
	   <div class="panel-body">		  
		  	 <form class="form-horizontal" action="" method="post" role="form">
					<div class="form-group">
						<label for="username" class="col-sm-3 control-label">用户名:</label>
						<div class="col-sm-4">
						  <input type="text" class="form-control" id="username" name="username" >
						</div>
					</div>
					<div class="form-group">
						<label for="pwd" class="col-sm-3">密　码:</label>
						<div class="col-sm-4">
						  <input type="password" class="form-control" id="pwd" name="pwd" >
						</div>
					</div>
					<div class="form-group">
						<label for="code" class="col-sm-3 control-label">验证码:</label>
						<div class="col-sm-2">
						  <input type="text" class="form-control" id="code"  name="code" >
						   <img class="checkbox-inlin" src='__APP__/code.php' id="checkImg" onclick="fGetCode()"  /> <a onclick="fGetCode()">刷新</a>  
						</div>
					</div>
						<button type="submit" class="btn" id="post">登录</button>						
					
		</form>
	</div>
		  
	
		
		
				
		
<script>
		function  fGetCode()
				{   
				document.getElementById('checkImg').src='__APP__/code.php'; 
				} 
$(document).ready(function(){

  $("form").submit(function(e){

			if($("input[name='username']").val()==false){
					$("input[name='username']").focus();
							return false
				}
				if($("input[name='pwd']").val()==false){
							$("input[name='pwd']").focus();
							return false
				}
				if($("input[name='code']").val()==false){
							$("input[name='code']").focus();
							return false
				}
				
			
  });
});
	
</script>

  </body>
</html>