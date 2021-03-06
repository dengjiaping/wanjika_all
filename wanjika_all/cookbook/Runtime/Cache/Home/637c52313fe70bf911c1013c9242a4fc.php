<?php if (!defined('THINK_PATH')) exit();?><!DOCTYPE html>
<html lang="zh-cn">
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

	<title>分享菜谱</title>
	<meta name="keywords" content="<?php echo ($key); ?>">
	<meta name="description" content="<?php echo ($desc); ?>">
	<link rel="stylesheet" type="text/css" href="__PUBLIC__/wap/css/css.css" />
	<script src="__PUBLIC__/wap/js/jquery.js" type="text/javascript" charset="utf-8"></script>
	 <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <style>
        #imgBox img{display: block;width:100%;}
    </style>
</head>
<body style="background: #fff;">
<div class="center" style="height: 0;">
    <img id="img1" src="<?php echo ($src); ?>" style="display: none;width: 100%;height: 100%;" alt="" />
    <img id="img2" src="/phpqrcode/qrcode.php?cookid=<?php echo ($id); ?>" style="width: 0;" alt="" />
    <img id="img3" src="__PUBLIC__/wap/images/bj.jpg" style="width: 0;" alt="" />
    <input type="hidden" id="name" value="<?php echo ($name); ?>" />
</div>
<a id="click" href="javascript:;click()" style="position: absolute;color:#fff;height: 100%;width: 100%;background-image: url(/Tpl/Home/wap/images/f.png);background-repeat: no-repeat;background-size: 100% 100%;">
</a>
<div id="imgBox" style="width:100%;">
</div>
<script>
    function click(){
        var c = document.getElementById("click");
        c.style.display = "none";
    }
    function date(){
        var img11 = document.getElementById("img1");
        var img1 = new Image();
        img1.src = img11.src
        img1.onload = function(){
            var img_width = img1.width;//封面图宽度
            var img_height = img1.height;//封面图高度
            var img2 = document.getElementById("img2");
            var img3 = document.getElementById("img3");

            var data=[img3.src,img1.src,img2.src],base64=[];
            var Mycanvas=document.createElement("canvas"),
                    ct=Mycanvas.getContext("2d"),
                    len=data.length;
            var body_width = $(window).width()*2;
            var body_height = window.screen.height*2;
            Mycanvas.width=body_width;
            Mycanvas.height=body_height;
            ct.rect(0,0,Mycanvas.width,Mycanvas.height);
            ct.fillStyle='#fff';
            ct.fill();
            function draw(n){
                if(n<len){
                    var img=new Image;
                    img.crossOrigin = 'Anonymous'; //解决跨域
                    img.src=data[n];
                    img.onload=function(){
                        if(n+1 == len)
                        {
//                        ct.fillStyle="#decf95";                //设置或返回用于填充绘画的颜色、渐变或模式
//                        ct.fillRect(body_width*0.2-5,body_height*0.37-5,body_width*0.6+10,body_width*0.8+10);//填充图
//                        ct.fillStyle="#222";                //设置或返回用于填充绘画的颜色、渐变或模式
//                        ct.fillRect(body_width*0.2-5,body_height*0.37,body_width*0.6+10,body_width*0.8);//填充图
                            ct.drawImage(this,body_width*0.435,body_height*0.805,body_width*0.13,body_height*0.078);//二维码
                            var name = document.getElementById("name");
                            //设置用户文本的大小字体等属性
                            ct.font = "36px Microsoft Yahei";
                            //设置用户文本填充颜色
                            ct.fillStyle = "#000";
                            var w = ct.measureText(name.value);
                            ct.fillText(name.value,(Mycanvas.width- w.width)/2,body_height*0.218); //文字居中水平垂直
                            ct.font = "30px Microsoft Yahei";
                            var wa = ct.measureText("@<?php echo ($author); ?>");
                            ct.fillText("@<?php echo ($author); ?>",(Mycanvas.width- wa.width)/2,body_height*0.62); //文字居中水平垂直
                            ct.font = "italic 26px Microsoft Yahei";
                            ct.fillStyle = "#b26b3d";
                            var introduction = '<?php echo ($introduction); ?>';
                            var namew = ct.measureText(introduction);
//                        ct.fillText(introduction,(Mycanvas.width- 400)/2,body_height*0.75,body_width*0.8); //文字居中水平垂直
                            canvasTextAutoLine(introduction,Mycanvas,(Mycanvas.width- body_width*0.525)/2,body_height*0.7,32,body_width);
                        }
                        else
                        {
                            if(n == 0)
                            {
                                ct.drawImage(this,0,0,body_width,body_height);
                            }
                            else
                            {
                                var width_rate = img_width/(body_width*0.8);
                                var height_rate = img_height/(body_height*0.28);
                                if(width_rate>height_rate)
                                {
                                    var rate = (body_width*0.8)/img_width;
                                    img_width = body_width*0.8;
                                    img_height = img_height * rate;
                                }
                                else
                                {
                                    var rate = (body_height*0.28)/img_height;
                                    img_height = body_height*0.28;
                                    img_width = img_width * rate;
                                }
                                ct.drawImage(this,body_width/2-img_width/2,body_height*0.29,img_width,img_height);//封面图
                            }
                        }
                        draw(n+1);
                    }
                }else{
                    base64.push(Mycanvas.toDataURL("image/png"));
                    document.getElementById("imgBox").innerHTML='<img src="'+base64[0]+'">';
//                window.open(base64[0], "Canvas Image");
//                dataURL = dataURL.replace("image/png", "image/octet-stream");//下载
//                document.location.href = dataURL;
                }
            }
            draw(0)
        }
    }
    window.onload=date();
    /*
     str:要绘制的字符串
     canvas:canvas对象
     initX:绘制字符串起始x坐标
     initY:绘制字符串起始y坐标
     lineHeight:字行高
     */
    function canvasTextAutoLine(str,canvas,initX,initY,lineHeight,body_width){
        var ctx = canvas.getContext("2d");
        var lineWidth = 0;
        var maxheight = 0;
        var canvasWidth = body_width;
        var lastSubStrIndex= 0;
        var i = 0;
        for(i=0;i<str.length;i++)
        {
            if(i>=imax)
            {
                break;
            }
            lineWidth+=ctx.measureText(str[i]).width;
            if(lineWidth>canvasWidth/10*5){
                if(maxheight == 0)
                {
                    maxheight++
                    continue;
                }
                ctx.fillText(str.substring(lastSubStrIndex,i),initX,initY);
                initY+=lineHeight;
                lineWidth=0;
                lastSubStrIndex=i;
                maxheight++;
                if(maxheight*lineHeight >= body_width*0.175)
                {
                    var imax = i;
                }
            }
            if(i==str.length-1){
                var w = ctx.measureText(str.substring(lastSubStrIndex,i+1));
//                var str = str.substring(lastSubStrIndex,i+1).length;
                ctx.fillText(str.substring(lastSubStrIndex,i+1),(body_width- w.width)/2,initY);
            }
        }
    }
</script>