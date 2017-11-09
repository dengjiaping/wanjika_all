<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html>
  <head>
    <title>小C美图管理系统</title>
	<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
      <link rel="stylesheet" href="__PUBLIC__/css/bootcss.css">
    <link rel="stylesheet" href="__PUBLIC__/css/style.css">
	<script src="__PUBLIC__/js/jquery.min.js"></script>
    <script src="__PUBLIC__/js/bootstrap.min.js"></script>
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
        <script src="__PUBLIC__/js/html5shiv.min.js"></script>
        <script src="__PUBLIC__/js/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>
  <div class="panel panel-info">
   
	  <div class="panel-heading">程序版本信息</div>
	
	    <div class="panel-body">
		    <div class="alert alert-warning alert-dismissable hidden" id="tishi">
		
			</div>
	<!-- Nav tabs -->
		<ul class="nav nav-tabs">
		  <li class="active"><a href="#home" data-toggle="tab">网站基本信息</a></li>
		
		  <li><a href="#messages" data-toggle="tab">图片上传设置</a></li>
		  <li><a href="#settings" data-toggle="tab">URL模式设置</a></li>
		  <li><a href="#mail" data-toggle="tab">邮件发送配置</a></li>
		
		</ul>

<!-- Tab panes -->
		<div class="tab-content">
		  <div class="tab-pane active" id="home">
		  <div class="panel-body">
			 <form class="form-horizontal" action="__GROUP__/Index/webset" method="post" role="form">
				  <div class="form-group">
						<label for="webname" class="col-sm-3 control-label">静态缓存时间:</label>
						<div class="col-sm-2">
						  <input type="text" class="form-control" id="webname" name="HUNCUNTIME" value="<?php echo (C("HUNCUNTIME")); ?>" >
						</div>
						<div class="col-sm-2">
						  <p>单位秒0为永久</p>
						  <p><a href="__GROUP__/Delcahe/del_cache">更新缓存</a></p>
						</div>
					  </div>
					  <div class="form-group">
						<label for="webname" class="col-sm-3 control-label">网站名称(webname):</label>
						<div class="col-sm-4">
						  <input type="webname" class="form-control" id="webname" name="webname" value="<?php echo (C("webname")); ?>" placeholder="网站名称">
						</div>
					  </div>
					  <div class="form-group">
						<label for="domain" class="col-sm-3 control-label">网站域名(domain):</label>
						<div class="col-sm-4">
						  <input type="domain" class="form-control" id="domain" name="domain" value="<?php echo (C("domain")); ?>" placeholder="网站域名">
						</div>
					  </div>
					    
					  <div class="form-group">
						<label for="webtitle" class="col-sm-3 control-label">网站标题(SEO)(webtitle):</label>
						<div class="col-sm-4">
						  <input type="webtitle" class="form-control" id="webtitle" name="webtitle"  value="<?php echo (C("webtitle")); ?>" placeholder="网站标题(SEO)">
						</div>
					  </div>
					  <div class="form-group">
						<label for="webkey" class="col-sm-3 control-label">网站关键词(SEO)(webkey):</label>
						<div class="col-sm-4">
						  <input type="webkey" class="form-control" id="webkey" name="webkey" value="<?php echo (C("webkey")); ?>"  placeholder="网站关键词(SEO)">
						</div>
					  </div>
					   <div class="form-group">
						<label for="webdesc" class="col-sm-3 control-label">网站描述(SEO)(webdesc):</label>
						<div class="col-sm-4">
						 <textarea class="form-control" id="webdesc" name="webdesc" rows="3" placeholder="网站描述(SEO)"><?php echo (C("webdesc")); ?></textarea>
						</div>
					  </div>
					  <div class="form-group">
						<label for="icp" class="col-sm-3 control-label">备案号(icp):</label>
						<div class="col-sm-4">
						  <input type="icp" class="form-control" id="icp" name="icp" value="<?php echo (C("icp")); ?>" placeholder="备案号">
						</div>
					  </div>
					 <div class="form-group">
						<label for="tel" class="col-sm-3 control-label">联系电话(tel):</label>
						<div class="col-sm-4">
						  <input type="tel" class="form-control" id="tel" name="tel" value="<?php echo (C("tel")); ?>" placeholder="联系电话">
						</div>
					  </div>
					 <div class="form-group">
						<label for="fax" class="col-sm-3 control-label">传真(fax):</label>
						<div class="col-sm-4">
						  <input type="fax" class="form-control" id="fax" name="fax" value="<?php echo (C("fax")); ?>" placeholder="传真">
						</div>
					  </div>
					   <div class="form-group">
						<label for="email" class="col-sm-3 control-label">邮箱(email):</label>
						<div class="col-sm-4">
						  <input type="email" class="form-control" id="email" name="email" value="<?php echo (C("email")); ?>" placeholder="邮箱">
						</div>
					  </div>
					  <div class="form-group">
						<label for="email" class="col-sm-3 control-label">联系人(lianxi):</label>
						<div class="col-sm-4">
						  <input type="lianxi" class="form-control" id="lianxi" name="lianxi" value="<?php echo (C("lianxi")); ?>" placeholder="谢先生">
						</div>
					  </div>
					   <div class="form-group">
						<label for="qq" class="col-sm-3 control-label">在线QQ(qq):</label>
						<div class="col-sm-4">
						  <input type="qq" class="form-control" id="qq" name="qq" value="<?php echo (C("qq")); ?>" placeholder="在线QQ">如有多个用,号分开
						</div>
					  </div>
					 <div class="form-group">
						<label for="address" class="col-sm-3 control-label">公司地址(address):</label>
						<div class="col-sm-4">
						  <input type="address" class="form-control" id="address" name="address" value="<?php echo (C("address")); ?>" placeholder="公司地址">
						</div>
					  </div>
					  <div class="form-group">
						<label for="copyright" class="col-sm-3 control-label">版权信息(copyright):</label>
						<div class="col-sm-4">
						  <input type="copyright" class="form-control" id="copyright" name="copyright" value="<?php echo (C("copyright")); ?>" placeholder="版权信息">
						</div>
					  </div>
					
					   
					    <div class="form-group">
						<label for="logo" class="col-sm-3 control-label">网站LOGO(logo):</label>
						<div class="col-sm-3">
						  <input type="logo" class="form-control" id="logo" name="logo" value="<?php echo (C("logo")); ?>" placeholder="网站LOGO">
						  
						</div>
							<div class="col-sm-3">
								 	<iframe src="__GROUP__/Upload" height="40"  frameborder="0" scrolling="no" ></iframe>
							</div>
					    </div>
					 
					 
				</div>
		  
		  </div>
		
		  <div class="tab-pane" id="messages">
		   <div class="panel-body form-horizontal">
					<div class="form-group">
						<label for="MAX_SIZE" class="col-sm-2 control-label">上传大小设置(MAX_SIZE):</label>
						<div class="col-sm-4">
						  <input type="MAX_SIZE" class="form-control" id="MAX_SIZE" name="MAX_SIZE" value="<?php echo (C("MAX_SIZE")); ?>" placeholder="上传大小设置">*单位字节
						</div>
					</div>
					<div class="form-group">
						<label for="MAX_SIZE" class="col-sm-2 control-label">允许上传的后缀名(FIEL_NAME):</label>
						<div class="col-sm-4">
						  <input type="MAX_SIZE" class="form-control" id="FIEL_NAME" name="FIEL_NAME" value="<?php echo (C("FIEL_NAME")); ?>"  placeholder="允许上传的后缀名">
						</div>
					</div>
					<div class="form-group">
					  <label class="col-sm-2 col-sm-offset-2">
						<input type="radio" name="IS_thumb" id="IS_thumb"  value="1" <?php if(C("IS_thumb")== 1): ?>checked<?php endif; ?> >
						开启缩略图
					  </label>
					  <label class="col-sm-2">
						<input type="radio" name="IS_thumb" id="IS_thumb" value="0"  <?php if(C("IS_thumb")== 0): ?>checked<?php endif; ?>>
						关闭缩略图
					  </label>
					</div>
					<div class="form-group">
						<label for="thumbMaxWidth" class="col-sm-2 control-label">缩略图宽(thumbMaxWidth):</label>
						<div class="col-sm-4">
						  <input type="thumbMaxWidth" class="form-control" id="thumbMaxWidth" name="thumbMaxWidth" value="<?php echo (C("thumbMaxWidth")); ?>" placeholder="400">
						</div>
					</div>
					<div class="form-group">
						<label for="thumbMaxHeight" class="col-sm-2 control-label">缩略图高(thumbMaxHeight):</label>
						<div class="col-sm-4">
						  <input type="thumbMaxHeight" class="form-control" id="thumbMaxHeight" name="thumbMaxHeight" value="<?php echo (C("thumbMaxHeight")); ?>" placeholder="400">
						</div>
					</div>
					
					
					
			 </div>
		  
		  
		  </div>
		  <div class="tab-pane" id="settings">
		  
		   <div class="panel-body form-horizontal">
				
					
					<div class="form-group">
					<label class="col-sm-2 col-sm-offset-2">
						<input type="radio" name="URL_MODEL" id="URL_MODEL"  value="1" <?php if(C("URL_MODEL")== 1): ?>checked<?php endif; ?>>
						PATHINFO模式
					 </label>
					</div>
					<div class="form-group">
					<label class="col-sm-2 col-sm-offset-2">
						<input type="radio" name="URL_MODEL" id="URL_MODEL"  value="2" <?php if(C("URL_MODEL")== 2): ?>checked<?php endif; ?>>
						REWRITE模式
					</label>
					</div>
					<div class="form-group">
					<label class="col-sm-2 col-sm-offset-2">
						<input type="radio" name="URL_MODEL" id="URL_MODEL"  value="3" <?php if(C("URL_MODEL")== 3): ?>checked<?php endif; ?>>
						兼容模式
					</label>
					</div>
					
					<div class="form-group">
						<label for="URL_HTML_SUFFIX" class="col-sm-2 control-label">URL后缀(URL_HTML_SUFFIX):</label>
						<div class="col-sm-4">
						  <input type="URL_HTML_SUFFIX" class="form-control" id="URL_HTML_SUFFIX" name="URL_HTML_SUFFIX" value="<?php echo (C("URL_HTML_SUFFIX")); ?>" placeholder="html">
						</div>
					</div>
		  
		  </div>
		 </div>
		   <div class="tab-pane" id="mail">
		   
				 <div class="panel-body form-horizontal">
					<div class="form-group">
						<label for="smtp_host" class="col-sm-3 control-label">SMTP 服务器(smtp_host):</label>
						<div class="col-sm-4">
						  <input type="smtp_host" class="form-control" id="smtp_host" name="smtp_host" value="<?php echo (C("smtp_host")); ?>"  placeholder="smtp.163.com">
						</div>
					</div>
					<div class="form-group">
						<label for="smtp_port" class="col-sm-3 control-label">SMTP服务器的端口号(smtp_port):</label>
						<div class="col-sm-4">
						  <input type="smtp_port" class="form-control" id="smtp_port" name="smtp_port" value="<?php echo (C("smtp_port")); ?>" placeholder="25">
						</div>
					</div>
						<div class="form-group">
						<label for="smtp_user" class="col-sm-3 control-label">SMTP服务器用户名(smtp_user):</label>
						<div class="col-sm-4">
						  <input type="smtp_user" class="form-control" id="smtp_user" name="smtp_user" value="<?php echo (C("smtp_user")); ?>" placeholder="letvav@163.com">
						</div>
					</div>
						<div class="form-group">
						<label for="smtp_pass" class="col-sm-3 control-label">邮箱登陆密码(smtp_pass):</label>
						<div class="col-sm-4">
						  <input type="password" class="form-control" id="smtp_pass" name="smtp_pass" value="<?php echo (C("smtp_pass")); ?>" placeholder="476905668">
						</div>
					</div>
						<div class="form-group">
						<label for="from_email" class="col-sm-3 control-label">发送邮箱(from_email):</label>
						<div class="col-sm-4">
						  <input type="from_email" class="form-control" id="from_email" name="from_email" value="<?php echo (C("from_email")); ?>" placeholder="letvav@163.com">
						</div>
					</div>
						<div class="form-group">
						<label for="from_name" class="col-sm-3 control-label">邮件属名(from_name):</label>
						<div class="col-sm-4">
						  <input type="from_name" class="form-control" id="from_name" name="from_name" value="<?php echo (C("from_name")); ?>" placeholder="留空默认是网站名">
						 
						</div>
					</div>
				  <div class="form-group">
						<div class="col-sm-offset-3 col-sm-4">
						  <a  href="__GROUP__/Index/sendmeail" class="btn btn-default" >测试一下</a>
						</div>
					  </div>
					
			 </div>
		   
		   </div>
		  
		   <div class="form-group">
						<div class="col-sm-offset-3 col-sm-4">
						  <button type="submit" class="btn btn-default" id="post">提交</button>
						</div>
					  </div>
					</form>
		</div>
			
		</div>
 </div>
 


  </body>
</html>