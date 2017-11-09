<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

	<title><?php echo ($info["name"]); ?></title>
	<meta name="keywords" content="<?php echo ($info["name"]); ?>">
	<meta name="description" content="<?php echo ($info["name"]); ?>">
	<link rel="stylesheet" type="text/css" href="__PUBLIC__/wap/css/css.css" />
	<script src="__PUBLIC__/wap/js/jquery.js" type="text/javascript" charset="utf-8"></script>
	<script src="__PUBLIC__/wap/js/slide.js" type="text/javascript" charset="utf-8"></script>
	<script src="__PUBLIC__/wap/js/pcwap.js" type="text/javascript" charset="utf-8"></script>
	 <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <style>
        .list{padding:0;margin: 0;background: #fff;}
        .list ul li p{position: static;}
        .list ul li img{border-radius: 0;border: 0;}
        .div_bg{background-color: #f5f5f5 !important;height: 18px !important;width: 100% !important;padding: 0;margin: 0 !important;border-radius: 0 !important;}
        .tl{text-align: left !important;}
        .f8{background-color: #f8f8f8;}
        .ff{background-color: #ffffff;}
        .div_li{float: left;width: 96%;padding: 2%;}
        .fs_bg{background-image: url(/Tpl/Home/wap/images/fs.png);background-repeat: no-repeat;background-size: 50%;margin-bottom: 10px !important;}
    </style>
</head>
<body style="background: #fff;">
<div class="list">
    <ul>
        <li style="margin: 0;width: 100%;">
            <a href="<?php echo ($info["coverpic_url"]); ?>"><img style="height: 100%;border-radius: 0;" src="<?php echo ($info["coverpic_url"]); ?>"></a>
        </li>
        <li style="height: inherit !important;background-image: url(__PUBLIC__/wap/images/introduction.png);background-size: 100% 100%;background-repeat: no-repeat;border-radius: 0;"><p style="height: inherit;padding: 10px 14px;"><?php echo ($info[introduction]); ?></p></li>
        <li class="div_bg"></li>
        <li style="height: inherit !important;width: 100%;margin:0;border-radius: 0;">
            <div>
                <p class="fs-18 fs_bg lh" style="padding: 9px !important;background-position: 50% 50%;margin-top: 10px;">所需食材</p>
                <?php if(is_array($info[materials])): $i = 0; $__LIST__ = $info[materials];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$materials): $mod = ($i % 2 );++$i;?><div class="div_li <?php if(($key % 2) == 0): ?>f8<?php else: ?>ff<?php endif; ?>" >
                <span style="padding: 9px 0 !important;width:50%;float: left;text-align: left;margin-right: -3px;"><?php echo ($materials["name"]); ?></span>
                <span style="padding: 9px 0 !important;width:50%;float: left;text-align: right;"><?php echo ($materials["num"]); ?></span>
                </div><?php endforeach; endif; else: echo "" ;endif; ?>
            </div>
        </li>
        <li class="div_bg"></li>
        <li class="fs_bg lh" style="background-position: 50% 50%;width: 100%;margin:0;border-radius: 0;padding: 20px 0;"><p class="fs-18 lh">烹饪步骤</p></li>
        <?php if(is_array($info[cook_step])): $i = 0; $__LIST__ = $info[cook_step];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$cook_step): $mod = ($i % 2 );++$i;?><li>
            <div>
                <p class="tl"><?php echo ($key+1); ?>、<?php echo ($cook_step["step"]); ?></p>
                <?php if($cook_step["src"] != ''): ?><a href="<?php echo ($cook_step["src"]); ?>">
                <img style="width: 100%;height: 100%;" id="preview1" src="<?php echo ($cook_step["src"]); ?>"/>
                </a><?php endif; ?>
            </div>
        </li><?php endforeach; endif; else: echo "" ;endif; ?>
        <li>
            <p class="tl">Tips</p>
            <p class="tl"><?php echo ($info["tips"]); ?></p>
        </li>
<?php if($is_true): ?><li class="div_bg"></li>
    <li style="background: #f5f5f5;margin: 0;border-radius: 0;padding: 0px 2% 10px;">
        <a style="background: #00c40c;border-radius: 5px;width: 100%;display: block;text-align: center;padding: 10px 0;" href="index.php?s=/Home/Info/share&id=<?php echo ($info["id"]); ?>&src=<?php echo ($info["coverpic_url"]); ?>&name=<?php echo ($info["name"]); ?>">生成美图</a>
    </li>
    <?php else: endif; ?>

    </ul>
</div>
<div style="background: #f5f5f5;">
    <?php $adlists=M("ads")->where('isshow=1')->order("id desc")->select();foreach ($adlists as $adlist):?><a style="display: inline-block;height: 100%;background: #f5f5f5;" href="<?php echo ($adlist['adurl']); ?>"><img style="width: 100%;height: 100%;" src="<?php echo ($adlist['adpic']); ?>" alt="<?php echo ($adlist['adremak']); ?>" /></a><?php endforeach ?>
</div>