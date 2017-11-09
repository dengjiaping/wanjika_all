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
    <script src="Tpl/Admin/Public/js/jquery.min.js"></script>
    <script src="Tpl/Admin/Public/js/ajaxfileupload.js"></script>
	 <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <style>
        p{margin: 0;padding: 0;}
        .w100{width: 100%;}
        .w50{width: 50%;}
        .ml-8{margin-left: -8px;}
        .ct{text-align: center;}
        /*a  upload */
        .a-upload {
            width: 100%;
            height: 200px;
            line-height: 200px;
            text-align: center;
            position: relative;
            cursor: pointer;
            color: #888;
            background: #f5f5f5;
            overflow: hidden;
            display: inline-block;
            *display: inline;
            *zoom: 1
        }

        .a-upload  input {
            position: absolute;
            height: 200px;
            font-size: 100px;
            right: 0;
            top: 0;
            opacity: 0;
            filter: alpha(opacity=0);
            cursor: pointer
        }

        .a-upload:hover {
            color: #444;
            background: #eee;
            border-color: #ccc;
            text-decoration: none
        }
        .opa{
            width: 100%;
            height: 200px;
            position: absolute;
            opacity: 0;
        }
        .bg{width:96% !important;background-color: #f5f5f5;border: 0;padding: 14px 2%;font-size: 14px;color: #999999;}
        .div_bg{background-color: #fff !important;height: 18px !important;width: 100% !important;padding: 0;margin: 0 !important;border-radius: 0 !important;}
        #textArea {
            height: 50px;
        }
        #textArea::-webkit-input-placeholder{
            height: 50px;line-height: 50px
        }    /* 使用webkit内核的浏览器 */
        #textArea:-moz-placeholder{
            height: 50px;line-height: 50px
        }                  /* Firefox版本4-18 */
        #textArea::-moz-placeholder{
            height: 50px;line-height: 50px
        }                  /* Firefox版本19+ */
        #textArea:-ms-input-placeholder{
            height: 50px;line-height: 50px
        }
        .fs_bg{margin-top: 10px;background-image: url(/Tpl/Home/wap/images/fs.png);background-repeat: no-repeat;background-size: 50%;margin-bottom: 10px !important;text-align: center;border-bottom: 1px solid #e9e9e9;}
        .mate_l{padding: 9px 0 !important;width:50%;float: left;text-align: left;margin-right: -3px;border: 0;}
        .mate_r{padding: 9px 0 !important;width:50%;float: right;text-align: right;border: 0;}
        .memb{margin-bottom: 0px;border-bottom: 1px solid #e9e9e9;display: inline-block;width: 100%;}
        #clony{margin-bottom: 10px;;background-color: #00c40c;padding: 10px 20px;border: 0;border-radius: 5px;color: #fff;}
        .bf5{display: block;margin-top:-4px;text-align: center;padding: 10px 0;border: 1px solid #f5f5f5;}
        .bf5::-webkit-input-placeholder{color: #000;font-weight: bold;}
        .bf5::-ms-input-placeholder{color: #000;font-weight: bold;}
        .bf5::-moz-placeholder{color: #000;font-weight: bold;}
        #clon{margin: 10px 0;padding: 10px 20px;background-color: #00c40c;border: 0;border-radius: 5px;color: #fff;}
        .share{
            width: 100%;
            height: 50px;
            line-height: 50px;
            text-align: center;
            position: relative;
            cursor: pointer;
            color: #888;
            background: #00c40c;
            border-radius: 5px;
            overflow: hidden;
            display: inline-block;}
        .share input{
            position: absolute;
            height: 50px;
            font-size: 100px;
            right: 0;
            top: 0;
            opacity: 0;
            filter: alpha(opacity=0);
            cursor: pointer;}
        .list_none{display: none !important;}
        .list_block{display: block !important;}
        .remove{width: 35px;height: 35px;margin-top: -17px;position: absolute;right: 0;z-index: 400;background-image: url(/Tpl/Home/wap/images/close.png);background-size: cover;}

        #clipArea {
            height: 100%;
        }
        #file,
        #clipBtn {
            margin: 20px;
        }
        #view {
            margin: 0 auto;
            width: 200px;
            height: 200px;
            background-color: #666;
        }
    </style>
</head>
<body style="background: #f5f5f5;">
<div id="clipbody" style="display: none;position: absolute;width: 100%;z-index: 999;height: 100%;background-color: #f5f5f5;">
<div id="clipArea" style="width: 100%;"></div>
<button id="clipBtn" style="position: absolute;top:0;">截取</button>
</div>
<input type="file" style="z-index: 500;position: absolute;margin:0;padding:0;width:100%;height: 214px;font-size: 100px;right: 0;top: 0;opacity: 0;filter: alpha(opacity=0);cursor: pointer;" id="file">
<script src="js/iscroll-zoom.js"></script>
<script src="js/hammer.min.js"></script>
<script src="js/lrz.all.bundle.js"></script>
<script src="js/PhotoClip.js"></script>
<script>
    var p_widht=$(window).width();
    var pc = new PhotoClip('#clipArea', {
        size: [p_widht,p_widht],
        outputSize: [0,0],
//        adaptive: ['100%', '50%'],
        file: '#file',
        view: '#view',
        ok: '#clipBtn',
        //img: 'img/mm.jpg',
        loadStart: function() {
//            console.log('开始读取照片');
        },
        loadComplete: function() {
//            console.log('照片读取完成');
            document.getElementById("clipbody").style.display="block";
//            $("#clipbody").on('touchmove',function(event) { event.preventDefault(); }, false);
        },
        done: function(dataURL) {
//            console.log(dataURL);
//            $("#clipbody").unbind('touchmove');
            document.getElementById("localImag").style.display="block";
            document.getElementById("preview").src = dataURL;
            document.getElementById("clipbody").style.display="none";
            document.getElementById("localImags").style.display="none";
            $.ajax({
                url:'index.php?s=/Home/Upload/uploadimgc',
                data:{str:dataURL},
                secureuri:false,
                type:'post',
                success: function (data)
                {
                    parent.document.getElementById('src0').value=data;
                }
            })
        },
        fail: function(msg) {
            alert(msg);
        }
    });

    // 加载的图片必须要与本程序同源，否则无法截图
    //pc.load('img/mm.jpg');

</script>
<form name="form" action="/book.php" method="POST" enctype="multipart/form-data">
    <div class="list">
        <div>
        <div id="localImags" class="col-sm-3 <?php if($info["coverpic_url"] != ''): ?>list_none<?php endif; ?>" >
                <div class="input-group">
                    <a href="javascript:;" class="a-upload">
                        <input type="file" name="img_file_src"  size="35" id="uplogo"  /><span style="background: #00c40c;padding: 4% 15%;border-radius: 5px;color: #fff;"><img class="index_img" src="__PUBLIC__/wap/images/creatimg.png"/>添加一张带有小C的美图</span>
                    </a>

                </div>
        </div>
        <div id="localImag" style=" <?php if($info["coverpic_url"] == ''): ?>display: none;<?php endif; ?>margin-bottom: 4px;" ><input class="opa" type="file" name="img_file_src" id="uplogo1"  size="35" /><img id="preview" src="<?php echo ($info["coverpic_url"]); ?>" style="width: 100%;height: 200px;"/></div>
        <input type="hidden" id="src0" name="coverpic_url" value="<?php echo ($info["coverpic_url"]); ?>" />
        </div>
        <div class="div_bg"></div>
        <div>
        <input class="w100 ct bg" name="name" value="<?php echo ($info[name]); ?>" type="text" placeholder="添加菜谱名称" />
        </div>
        <div class="div_bg"></div>
        <div>
        <textarea id="textArea" class="w100 ct bg" name="introduction" value="" type="text" placeholder="写一段美丽的文字（50字以内）" ><?php echo ($info["introduction"]); ?></textarea>
        </div>
        <div class="div_bg"></div>
        <div style="padding: 0 10px;background: #fff;">
            <p class="fs-18 fs_bg lh" style="padding: 20px 0;background-position: 50% 50%;">所需食材</p>
            <?php if($info["materials"] != ''): if(is_array($info[materials])): $i = 0; $__LIST__ = $info[materials];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$materials): $mod = ($i % 2 );++$i;?><div class="memb">
                    <input class="w50 mate_l" type="text" name="materials[1][name]" value="<?php echo ($materials["name"]); ?>" placeholder="食材：西红柿" /><input class="w50 ml-8 mate_r" name="materials[1][num]" value="<?php echo ($materials["num"]); ?>" type="text" placeholder="用量：1个" />
                </div><?php endforeach; endif; else: echo "" ;endif; ?>
            <?php else: ?>
            <div class="memb">
            <input class="w50 mate_l" type="text" name="materials[1][name]" value="" placeholder="食材：西红柿" /><input class="w50 ml-8 mate_r" name="materials[1][num]" value="" type="text" placeholder="用量：1个" />
            </div>
            <div class="memb">
            <input class="w50 mate_l" type="text" name="materials[2][name]" value="" placeholder="食材：鸡蛋" /><input class="w50 ml-8 mate_r" name="materials[2][num]" value="" type="text" placeholder="用量：1个" />
            </div><?php endif; ?>
        </div>
        <div id="yl" style="padding: 0 10px;background: #fff;"></div>
        <div style="padding: 0 10px;background: #fff;">
        <button type="button" id="clony">再添加一行</button>
        </div>
        <div class="fs-18 fs_bg lh" style=" margin-bottom: 0px !important;padding: 20px 0;background-position: 50% 50%;border: 0;background-color: #fff;"><p>烹饪步骤</p></div>
        <?php if($info["cook_step"] != ''): if(is_array($info[cook_step])): $i = 0; $__LIST__ = $info[cook_step];if( count($__LIST__)==0 ) : echo "" ;else: foreach($__LIST__ as $key=>$cook_step): $mod = ($i % 2 );++$i;?><div class="member" style="padding: 0 10px;background: #fff;font-size: 14px;" id="bz<?php echo ($key+1); ?>">
                    <p class="mb-10">步骤<?php echo ($key+1); ?></p><i class="remove" onclick="removeElement('bz<?php echo ($key+1); ?>')"></i>
                    <a id="localImags<?php echo ($key+1); ?>" style="padding-right: 2px;" href="javascript:;" class="a-upload <?php if($info.$cook_step["src"] != ''): ?>list_none<?php endif; ?>">
                        <input type="file" name="img_file" id="imag<?php echo ($key+1); ?>" onchange="javascript:setImagePreview(this,<?php echo ($key+1); ?>);" />+添加步骤图
                    </a>
                    <div id="localimg<?php echo ($key+1); ?>"><div id="localImag<?php echo ($key+1); ?>" style="<?php if($info.$cook_step["src"] == ''): ?>display: none;<?php endif; ?>margin-bottom: 4px;"><input class="opa" type="file" name="img_file" id="img<?php echo ($key+1); ?>" onchange="javascript:setImagePreview(this,<?php echo ($key+1); ?>);" ><img id="preview<?php echo ($key+1); ?>" src="<?php echo ($cook_step["src"]); ?>" style="width: 100%;height: 200px;"/></div></div>
                    <input type="text" class="w100 bf5" name="cook_step[<?php echo ($key+1); ?>][step]" value="<?php echo ($cook_step["step"]); ?>" placeholder="添加步骤说明" />
                    <input type="hidden" id="src<?php echo ($key+1); ?>" name="cook_step[<?php echo ($key+1); ?>][src]" value="<?php echo ($cook_step["src"]); ?>" />
                </div><?php endforeach; endif; else: echo "" ;endif; ?>
            <?php else: ?>
            <div class="member" style="padding: 0 10px;background: #fff;font-size: 14px;" id="bz1">
                <p class="mb-10">步骤1</p><i class="remove" onclick="removeElement('bz1')"></i>
                <a id="localImags1" style="padding-right: 2px;" href="javascript:;" class="a-upload">
                    <input type="file" name="img_file" id="imag1" onchange="javascript:setImagePreview(this,1);" />+添加步骤图
                    <span style="display: block;position: absolute;top: 40px;width: 100%;font-size: 12px;text-align: center;color: #999;height: 0;">建议图片小于6M</span>
                </a>
                <div id="localimg1"><div id="localImag1" style="display: none;margin-bottom: 4px;"><input class="opa" type="file" name="img_file" id="img1" onchange="javascript:setImagePreview(this,1);" ><img id="preview1" src=""/></div></div>
                <input type="text" class="w100 bf5" name="cook_step[1][step]" value="" placeholder="添加步骤说明" />
                <input type="hidden" id="src1" name="cook_step[1][src]" value="" />
            </div><?php endif; ?>
        <div id="jai" style="padding: 0 10px;background: #fff;font-size: 14px;" ></div>
        <div style="padding: 0 10px;background: #fff;">
        <button type="button" id="clon">再增加一步</button>
        </div>
        <div style="padding: 0 10px;background: #fff;">
            <p class="fs-18 fs_bg lh" style="padding: 20px 0;background-position: 50% 50%;border: 0;">烹饪小贴士</p>
            <textarea name="tips" style="margin-bottom:20px;border: 0;" value="" class="w100" type="text" placeholder="添加您做这道菜可供他人参考的小技巧或注意事项！" ><?php echo ($info["tips"]); ?></textarea>
        </div>
        <div style="padding: 10px 2%;">
        <?php if($info["id"] > 0): ?><a class="share"><input type="button" class="w100 share" id="save" value="<?php echo ($info["id"]); ?>"/><span style="background: #00c40c;padding: 10px 60px;border-radius: 5px;color: #fff;"><?php if($info["is_draft"] > 0): ?>保存草稿<?php else: ?>确认修改<?php endif; ?></span></a>
            <?php if($info["is_draft"] > 0): ?><a class="share" style="margin-top: 6px;"><input type="button" class="w100 share" id="post" value="<?php echo ($info["id"]); ?>"/><span style="background: #00c40c;padding: 10px 60px;border-radius: 5px;color: #fff;">生成菜谱</span></a><?php endif; ?>
            <input type="hidden" id="is_draft" name="is_draft" value="<?php echo ($info["is_draft"]); ?>" />
        <?php else: ?>
            <a class="share" style="margin-bottom: 6px;"><input type="button" class="w100 share" id="is_draft" value="<?php echo ($info["id"]); ?>"/><span style="background: #00c40c;padding: 10px 60px;border-radius: 5px;color: #fff;">保存草稿</span></a>
            <a class="share"><input type="button" class="w100 share" id="post" value="<?php echo ($info["id"]); ?>"/><span style="background: #00c40c;padding: 10px 60px;border-radius: 5px;color: #fff;">预览菜谱</span></a><?php endif; ?>
        </div>
    </div>
</form>
<script>
    $("#tagtrue").click(function(){
        s='';
        $('input[name="taglist"]:checked').each(function(){
            s+=$(this).val()+',';
        });
        $("#tags").val(s);
        $('#myModal').modal('hide');
    });
    $("#clon").click(function(){
        var member = document.getElementsByClassName("member").length+1;
        $("#jai").append('<div class="member" id="bz'+member+'"><p class="mb-10">步骤'+member+'</p><i class="remove" onclick="removeElement('+"'bz"+member+"'"+')"></i><a id="localImags'+member+'" style="padding-right: 2px;" href="javascript:;" class="a-upload"><input type="file" name="img_file" id="imag'+member+ '" onchange="javascript:setImagePreview(this,'+member+');" >+添加步骤图<span style="display: block;position: absolute;top: 40px;width: 100%;font-size: 12px;text-align: center;color: #999;height: 0;">建议图片小于6M</span></a><div id="localimg'+member+'"><div id="localImag'+member+'" style="display: none;margin-bottom: 4px;"><input class="opa" type="file" name="img_file" id="img'+member+'" onchange="javascript:setImagePreview(this,'+member+');" ><img id="preview'+member+'" src=""/></div></div><input type="text" class="w100 bf5" name="cook_step['+member+'][step]" placeholder="添加步骤说明" /><input type="hidden" name="cook_step['+member+'][src]" id="src'+member+'" value="" /></div>');
    });
    $("#clony").click(function(){
        var memb = document.getElementsByClassName("memb").length+1;
        $("#yl").append('<div class="memb"><input class="w50 mate_l" name="materials['+memb+'][name]" value="" type="text" placeholder="食材：鸡蛋" /><input class="w50 ml-8 mate_r" name="materials['+memb+'][num]" value="" type="text" placeholder="用量：1个" /></div>');
    });

    //下面用于图片上传预览功能
    function setImagePreview(avalue,value,id) {
        $.ajaxFileUpload
        (
                {
                    url:'index.php?s=/Home/Upload/uploadimg',
                    secureuri:false,
                    fileElementId:avalue.id,
                    dataType: 'json',
                    success : function (data, status){
                        if(typeof(data.error) != 'undefined'){
                            if(data.error != ''){
                                alert(data.error);return true;
                            }else{
                                parent.document.getElementById('src'+value).value=data.msg;
                                var docObj=avalue;
                                if(value>=1)
                                {
                                    //        <div id="localImag"><img id="preview" src=""/></div>
                                    var imgObjPreview=document.getElementById("preview"+value);
                                    var localImags=document.getElementById("localImags"+value);
                                    var localImagId=document.getElementById("localImag"+value);
                                }
                                else
                                {
                                    var imgObjPreview=document.getElementById("preview");
                                    var localImags=document.getElementById("localImags");
                                    var localImagId=document.getElementById("localImag");
                                }
                                if(docObj.files &&docObj.files[0])
                                {
                                    //火狐下，直接设img属性
                                    imgObjPreview.style.display = 'block';
                                    imgObjPreview.style.width = '100%';
                                    imgObjPreview.style.height = '200px';
                                    //imgObjPreview.src = docObj.files[0].getAsDataURL();

                                    //火狐7以上版本不能用上面的getAsDataURL()方式获取，需要一下方式
                                    imgObjPreview.src = window.URL.createObjectURL(docObj.files[0]);
                                }
                                else
                                {
                                    //IE下，使用滤镜
                                    docObj.select();
                                    var imgSrc = document.selection.createRange().text;
                                    //            var localImagId = document.getElementById("localImag");
                                    //必须设置初始大小
                                    localImagId.style.width = "150px";
                                    localImagId.style.height = "180px";
                                    //图片异常的捕捉，防止用户修改后缀来伪造图片
                                    try{
                                        localImagId.style.filter="progid:DXImageTransform.Microsoft.AlphaImageLoader(sizingMethod=scale)";
                                        localImagId.filters.item("DXImageTransform.Microsoft.AlphaImageLoader").src = imgSrc;
                                    }
                                    catch(e)
                                    {
                                        alert("您上传的图片格式不正确，请重新选择!");
                                        return false;
                                    }
                                    imgObjPreview.style.display = 'none';
                                    document.selection.empty();
                                }

                                localImags.style.display = 'none';
                                localImagId.style.display = 'block';
                                return true;
                            }
                        }
                    },
                    error: function(data, status, e){
                        console.log(e);
                    }
                }
        )
    }
    $("#post").click(function(){
        if($("input[name='coverpic_url']").val()==false){
            alert("封面不能为空");
            return false
        }
        if($("input[name='name']").val()==false){
            alert("菜谱名不能为空");
            $("input[name='name']").focus();
            return false
        }
        if($("textarea[name='introduction']").val()==false){
            alert("简介不能为空");
            $("textarea[name='introduction']").focus();
            return false
        }
        var materials = new Array();
        var j = 0;
        var jsonObj = {};
        $($("input[name^='materials']")).each(function(i){
            if(i%2 ==0)
            {
                jsonObj={};
                jsonObj['name'] = $(this).val();
            }
            else
            {
                jsonObj['num'] = $(this).val();
                materials[j] = jsonObj;
                j++;
            }

        })
        if(materials[0]['name'] == "" || materials[0]['num'] == "")
        {
            alert("用料不能为空");
            $("input[name='materials[1][name]']").focus();
            return false
        }
        var cook_step = new Array();
        var k = 0;
        var jsonObjstep = {};
        $($("input[name^='cook_step']")).each(function(i){
            if(i%2 ==0)
            {
                jsonObjstep={};
                jsonObjstep['step'] = $(this).val();
            }
            else
            {
                jsonObjstep['src'] = $(this).val();
                cook_step[k] = jsonObjstep;
                k++;
            }

        })
        if(cook_step.length == 0 || cook_step[0]['step'] == "" || cook_step[0]['src'] == "")
        {
            alert("步骤不能为空");
            $("input[name='cook_step[1][step]']").focus();
            return false
        }
        var info_id = document.getElementById("post").value;
        if(info_id>0)
        {
            $.ajax({
                type:'post',
                url:'/index.php?s=/Home/Info/edit',
                data:{
                    id:info_id,
                    coverpic_url:$("input[name='coverpic_url']").val(),
                    name:$("input[name='name']").val(),
                    introduction:$("textarea[name='introduction']").val(),
                    is_draft:0,
                    materials:materials,
                    cook_step:cook_step,
                    tips:$("textarea[name='tips']").val()
                },
                cache: false,
                success:function(msg){
                    if(msg.status == 1)
                    {
                        window.location.href = msg.info;
                    }
                    else
                    {
                        alert(msg.info);
                    }
                }

            });
        }
        else
        {
            $.ajax({
                type:'post',
                url:'/index.php?s=/info.php',
                data:{
                    coverpic_url:$("input[name='coverpic_url']").val(),
                    name:$("input[name='name']").val(),
                    introduction:$("textarea[name='introduction']").val(),
                    materials:materials,
                    cook_step:cook_step,
                    tips:$("textarea[name='tips']").val()
                },
                cache: false,
                success:function(msg){
                    if(msg.status == 1)
                    {
                        window.location.href = msg.info;
                    }
                    else
                    {
                        alert(msg.info);
                    }
                }

            });
        }
    });
    $("#save").click(function(){
        var is_draft = document.getElementById("is_draft").value;
        var materials = new Array();
        var j = 0;
        var jsonObj = {};
        $($("input[name^='materials']")).each(function(i){
            if(i%2 ==0)
            {
                jsonObj={};
                jsonObj['name'] = $(this).val();
            }
            else
            {
                jsonObj['num'] = $(this).val();
                materials[j] = jsonObj;
                j++;
            }

        })
        var cook_step = new Array();
        var k = 0;
        var jsonObjstep = {};
        $($("input[name^='cook_step']")).each(function(i){
            if(i%2 ==0)
            {
                jsonObjstep={};
                jsonObjstep['step'] = $(this).val();
            }
            else
            {
                jsonObjstep['src'] = $(this).val();
                cook_step[k] = jsonObjstep;
                k++;
            }

        })
        if(is_draft == 0)
        {
            if($("input[name='coverpic_url']").val()==false){
                alert("封面不能为空");
                return false
            }
            if($("input[name='name']").val()==false){
                alert("菜谱名不能为空");
                $("input[name='name']").focus();
                return false
            }
            if($("textarea[name='introduction']").val()==false){
                alert("简介不能为空");
                $("textarea[name='introduction']").focus();
                return false
            }
            if(cook_step.length == 0 || cook_step[0]['step'] == "" || cook_step[0]['src'] == "")
            {
                alert("步骤不能为空");
                $("input[name='cook_step[1][step]']").focus();
                return false
            }
            if(materials[0]['name'] == "" || materials[0]['num'] == "")
            {
                alert("用料不能为空");
                $("input[name='materials[1][name]']").focus();
                return false
            }
        }
        var info_id = document.getElementById("save").value;
        if(info_id>0)
        {
            $.ajax({
                type:'post',
                url:'/index.php?s=/Home/Info/edit',
                data:{
                    id:info_id,
                    coverpic_url:$("input[name='coverpic_url']").val(),
                    name:$("input[name='name']").val(),
                    introduction:$("textarea[name='introduction']").val(),
                    is_draft:$("input[name='is_draft']").val(),
                    materials:materials,
                    cook_step:cook_step,
                    tips:$("textarea[name='tips']").val()
                },
                cache: false,
                success:function(msg){
                    if(msg.status == 1)
                    {
                        window.location.href = msg.info;
                    }
                    else
                    {
                        alert(msg.info);
                    }
                }

            });
        }
        else
        {
            alert("菜谱ID异常");
        }
    });

    $("#is_draft").click(function(){
        var materials = new Array();
        var j = 0;
        var jsonObj = {};
        $($("input[name^='materials']")).each(function(i){
            if(i%2 ==0)
            {
                jsonObj={};
                jsonObj['name'] = $(this).val();
            }
            else
            {
                jsonObj['num'] = $(this).val();
                materials[j] = jsonObj;
                j++;
            }

        })
        var cook_step = new Array();
        var k = 0;
        var jsonObjstep = {};
        $($("input[name^='cook_step']")).each(function(i){
            if(i%2 ==0)
            {
                jsonObjstep={};
                jsonObjstep['step'] = $(this).val();
            }
            else
            {
                jsonObjstep['src'] = $(this).val();
                cook_step[k] = jsonObjstep;
                k++;
            }

        })
            $.ajax({
                type:'post',
                url:'/index.php?s=/info.php',
                data:{
                    is_draft:1,
                    coverpic_url:$("input[name='coverpic_url']").val(),
                    name:$("input[name='name']").val(),
                    introduction:$("textarea[name='introduction']").val(),
                    materials:materials,
                    cook_step:cook_step,
                    tips:$("textarea[name='tips']").val()
                },
                cache: false,
                success:function(msg){
                    if(msg.status == 1)
                    {
                        window.location.href = msg.info;
                    }
                    else
                    {
                        alert(msg.info);
                    }
                }

            });
    });
    function removeElement(id){
        var _element = document.getElementById(id);
        var _parentElement = _element.parentNode;
        if(_parentElement){
            _parentElement.removeChild(_element);
        }
    }
    window.alert=function(obj){
        var iframe=document.createElement("iframe");
        iframe.src="javascript:void(0);"
        document.body.appendChild(iframe)
        iframe.contentWindow.alert(obj);
        iframe.parentNode.removeChild(iframe);
    }
    var click_num = 0;
    document.onclick=function(){
        if(click_num == 0 )
        {
            click_num =1;
            alert("温馨提示:请随手保存菜谱");
        }
    }
    //    window.alert = function(name){
//        var iframe = document.createElement("IFRAME");
//        iframe.style.display="none";
//        iframe.setAttribute("src", 'data:text/plain,');
//        document.documentElement.appendChild(iframe);
//        window.frames[0].window.alert(name);
//        iframe.parentNode.removeChild(iframe);
//    }
</script>
</body>