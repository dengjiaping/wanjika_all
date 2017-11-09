<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

	<title><?php if($seotitle): echo ($seotitle); else: echo ($title); endif; ?>-<?php echo ($webname); ?></title>
	<meta name="keywords" content="<?php echo ($key); ?>">
	<meta name="description" content="<?php echo ($desc); ?>">
	<link rel="stylesheet" type="text/css" href="__PUBLIC__/wap/css/css.css" />
<link rel="stylesheet" type="text/css" href="__PUBLIC__/wap/css/swipe.css" />
	<script src="__PUBLIC__/wap/js/zepto.min.js" type="text/javascript" charset="utf-8"></script>
	<script src="__PUBLIC__/wap/js/swipe.js" type="text/javascript" charset="utf-8"></script>

	 <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
<style>
    .list ul li{height: 200px;}
    .list ul li p{background-image: linear-gradient(to bottom, rgba(255, 255, 255, 0.1) 0%, rgba(0, 0, 0, 0.5) 40%, rgba(0, 0, 0, 0.9) 98%, #FFFFFF 100%);width: 100%;padding: 10px 0;text-indent: 10px;text-align: left;border-radius: 8px;}
    .garbage{background-image: url(/Tpl/Home/wap/images/garbage.png);background-size: cover;position: absolute;width: 40px;height: 40px;left: 5px;top: 5px;}
    .dgarbage{background-image: url(/Tpl/Home/wap/images/garbage.png);background-size: cover;position: absolute;width: 40px;height: 40px;right: 5px;top: 5px;}
    .tb{background-image: url(/Tpl/Home/wap/images/tb.png);background-size: cover;position: absolute;width: 40px;height: 40px;right: 5px;top: 5px;}
</style>
</head>
<body>
	 <div class="list">
			<ul>
					<?php if(is_array($cookbook)): $i = 0; $__LIST__ = $cookbook;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$cook): $mod = ($i % 2 );++$i;?><li id="cook<?php echo ($cook["id"]); ?>">
                            <?php if($cook["is_draft"] != 1): ?><i class="garbage" onclick="clickdel(<?php echo ($cook["id"]); ?>)"></i>
                            <i class="tb" id="<?php echo ($cook["id"]); ?>" onclick="clickhref(this)"></i>
                                <p><a href="<?php echo ($cook["url"]); ?>"><?php echo (msubstr($cook[name],0,10,'utf-8',false)); ?></a></p>
                                <a href="<?php echo ($cook["url"]); ?>"><img src="<?php echo ($cook["coverpic_url"]); ?>"></a>
                            <?php else: ?>
                                <i class="dgarbage" onclick="clickdel(<?php echo ($cook["id"]); ?>)"></i>
                                <p><a href="/index.php?s=/<?php echo ($cook["id"]); ?>&url=update"><?php echo (msubstr($cook[name],0,10,'utf-8',false)); ?></a></p>
                                <a href="/index.php?s=/<?php echo ($cook["id"]); ?>&url=update"><img src="<?php echo ($cook["coverpic_url"]); ?>"></a><?php endif; ?>
						</li><?php endforeach; endif; else: echo "" ;endif; ?>
			</ul>
		 </div>
		<ul class="pagination">
			<?php echo ($page); ?>
		</ul>
     <script>
         function clickdel(cookid)
         {
             if(confirm( "确定要删除?" ))
             {
                 $.ajax({
                     type:'post',
                     url:'/index.php?s=/Home/Info/del',
                     data:{
                         id:cookid
                     },
                     cache: false,
                     success:function(msg){
                         if(msg.status == 1)
                         {
                             $("#cook"+cookid).remove();
                             alert(msg.info);
                         }
                         else
                         {
                             alert(msg.info);
                         }
                     }
                 });
             }
         }
         function clickhref(obj)
         {
             window.location.href = "/index.php?s=/"+obj.id+"&url=update";
         }
         window.alert = function(name){
             var iframe = document.createElement("IFRAME");
             iframe.style.display="none";
             iframe.setAttribute("src", 'data:text/plain,');
             document.documentElement.appendChild(iframe);
             window.frames[0].window.alert(name);
             iframe.parentNode.removeChild(iframe);
         }
         window.confirm = function(name){
             var iframe = document.createElement("IFRAME");
             iframe.style.display="none";
             iframe.setAttribute("src", 'data:text/plain,');
             document.documentElement.appendChild(iframe);
             var result = window.frames[0].window.confirm(name);
             iframe.parentNode.removeChild(iframe);
             return result;
         };
     </script>
</body>