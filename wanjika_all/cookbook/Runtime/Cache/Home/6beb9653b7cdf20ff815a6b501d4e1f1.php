<?php if (!defined('THINK_PATH')) exit();?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Error_404_页面没找到</title>
	<style type="text/css">
	body{margin:8% auto 0;max-width: 550px; min-height: 200px;padding:10px;font-family: Verdana,Arial,Helvetica,sans-serif;font-size:14px;}p{color:#555;margin:10px 10px;}img {border:0px;}.d{color:#404040;}
	</style>
</head>
<body>
<a href="<?php echo ($domain); ?>"><img src="<?php echo ($logo); ?>" alt="<?php echo ($webname); ?>"/></a>
<p><b>404.</b> 抱歉! 您访问的页面不存在!</p>
<p class="d">请确认您输入的网址是否正确，如果问题持续存在，请与我们联系。</p>
<p><a href="<?php echo ($domain); ?>">返回网站首页</a></p>
</body>
</html>