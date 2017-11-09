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

	<div class="panel-group" id="accordion">
		<div class="panel panel-info">
		  <!-- Default panel contents -->
		  <div class="panel-heading" data-toggle="collapse" data-toggle="collapse" data-parent="#accordion" href="#info">网站信息管理</div>
		  <div class="panel-body panel-collapse collapse " id="info">
		
			  <ul class="list-group">
				<li class="list-group-item"><a href="__GROUP__/Info/cookbookinfo" target="right">菜谱管理</a></li>

			  </ul>
		  </div>		 
		</div>
		<div class="panel panel-info">
		  <!-- Default panel contents -->
		  <div class="panel-heading" data-toggle="collapse" data-toggle="collapse" data-parent="#accordion" href="#fujia">扩展功能</div>
		  <div class="panel-body panel-collapse collapse " id="fujia">
			  <ul class="list-group">
				<li class="list-group-item"><a href="__GROUP__/Ads/index" target="right">广告管理</a></li>
			  </ul>
		  </div>		 
		</div>
	</div>
  </body>
  </html>