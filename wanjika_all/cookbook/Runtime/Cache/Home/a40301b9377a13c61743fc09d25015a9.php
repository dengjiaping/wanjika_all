<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

	 <title><?php echo ($webtitle); ?>--<?php echo ($webname); ?></title> 
	  <meta name="Keywords" content="<?php echo ($webkey); ?>" /> 
	  <meta name="Description" content="<?php echo ($webdesc); ?>" />  
	<link rel="stylesheet" type="text/css" href="__PUBLIC__/wap/css/css.css" />
	<link rel="stylesheet" type="text/css" href="__PUBLIC__/wap/css/swipe.css" />
	<script src="__PUBLIC__/wap/js/zepto.min.js" type="text/javascript" charset="utf-8"></script>
	<script src="__PUBLIC__/wap/js/swipe.js" type="text/javascript" charset="utf-8"></script>
	<meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <style>
        .index_div{position:absolute;text-align: center;width: 100%;height: 100%;background-image: url(__PUBLIC__/wap/images/bg.jpg);background-size: cover;}
        .index_li{position: absolute;top:33%;width: 100%;}
        .index_lii{position: absolute;top:50%;width: 100%;}
        .index_image{width: 80%;}
    </style>
</head>
<body style="height: 100%;">
<div class="index_div">
    <div>
        <div class="index_li">
            <a href="/index.php?s=/wodecaipu.html"><img class="index_image" src="__PUBLIC__/wap/images/my1.png"/></a>
            <a href="/index.php?s=/chuangjiancaipu.html"><img class="index_image" style="margin-top: 6%;" src="__PUBLIC__/wap/images/my2.png"/></a>
            <a href="/index.php?s=/wodecaogao.html"><img class="index_image" style="margin-top: 6%;" src="__PUBLIC__/wap/images/bt.png"/></a>
        </div>
    </div>
</div>
</body>
</html>