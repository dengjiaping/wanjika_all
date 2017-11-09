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
   
	  <div class="panel-heading">菜谱管理</div>
	
	    <div class="panel-body">
		    <div class="alert alert-warning alert-dismissable hidden" id="tishi">
		
			</div>
	<!-- Nav tabs -->
	
	<table class="table table-bordered table-hover table-condensed">
	  <tr>
		<th>ID</th>
		<th width="30%">菜名</th>
		<th>类型</th>
		<th>状态</th>
        <th>微信昵称</th>
		<!--<th>修改</th>-->
	  </tr>
	  <form action="__GROUP__/Info/index" method="post" >
	  <?php if(is_array($info)): $i = 0; $__LIST__ = $info;if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$vo): $mod = ($i % 2 );++$i;?><tr>
			<td class="font12"><input type="checkbox" name="id[]" value="<?php echo ($vo["id"]); ?>"><?php echo ($vo["id"]); ?></td>
			<td class="font12"><?php echo ($vo["name"]); ?></td>
			<td class="font12"><?php if($vo['catetype'] == 1): ?>文字<?php else: ?>图片<?php endif; ?></td>
			<td class="font12">
			<?php if($vo['istop'] == 1): ?>置顶 |<?php endif; ?>
			<?php if($vo['isrec'] == 1): ?>推荐 |<?php endif; ?> 
			<?php if($vo['isshow'] == 1): ?>显示  <?php else: ?>隐藏<?php endif; ?>
			<!--<?php if($vo['isrev'] == 1): ?>可评<?php else: ?>禁评<?php endif; ?> -->
			</td>
            <td class="font12"><?php echo ($vo["author"]); ?></td>
			<!--<td class="font12"><a href="__GROUP__/Info/edit/id/<?php echo ($vo["id"]); ?>">修改</a></td>-->
	  </tr><?php endforeach; endif; else: echo "" ;endif; ?>
	 
	  
	 
	</table>
	<div class="form-group">
						<div class="col-sm-offset col-sm-12">
						  <button type="button" id="btn1" class="btn btn-default">全选</button> 
							<button type="button" id="btn2" class="btn btn-default">取消全选</button> 
							<button type="button" id="btn4" class="btn btn-default">返选</button> 
							<!--<label><input type="radio" value="1" name="del" />删除</label>-->
							<label><input type="radio" value="2" name="del" />显示</label>					 
							<!--<label><input type="radio" value="3" name="del" />置顶</label>					 -->
							<!--<label><input type="radio" value="4" name="del" />推荐</label>					 -->
							<!--<label><input type="radio" value="5" name="del" />开启评论</label>					 -->
							<!--<label><input type="radio" value="6" name="del" />关闭评论</label>					 -->
							<!--<label><input type="radio" value="7" name="del" />取消置顶</label>					 -->
							<!--<label><input type="radio" value="8" name="del" />取消推荐</label>-->
							<label><input type="radio" value="9" name="del" />隐藏</label>					 
												 
						   <button type="subimt" class="btn btn-default">提交</button>
                            <input type="hidden" name="post" value="1" />
						  
						</div>
					  </div>
	 </form>
	
		<ul class="pagination">
			<?php echo ($page); ?>
		</ul>
		   </div>
		  
				
</div>
			



<script type="text/javascript"> 
jQuery(function($){ 
//全选 
$("#btn1").click(function(){ 
$("input[type='checkbox']").attr("checked","true"); 
}) 
//取消全选 
$("#btn2").click(function(){ 
$("input[type='checkbox']").removeAttr("checked"); 
}) 


//反选 
$("#btn4").click(function(){ 
$("input[type='checkbox']").each(function(){ 
if($(this).attr("checked")) 
{ 
$(this).removeAttr("checked"); 
} 
else 
{ 
$(this).attr("checked","true"); 
} 
}) 
})
});

//删除


</script> 
  </body>
</html>