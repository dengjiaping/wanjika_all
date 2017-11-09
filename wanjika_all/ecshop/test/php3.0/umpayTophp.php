<!-- 
	===============================================================
				联动优势 商户web 下订单 的示例与开发模板
				作者:  郝静华
				日期:2005-09-14
		
		该模板提供了用PHP页的WEB网站接收手机钱包业务平台发来订单的例子.
	    
	      商户接收到来自手机钱包业务平台发来的订单之后，首先按照按照
	      手机钱包商户接入规范2.2V文档的5.1.2.1的格式组装成字符串，然后
	      验证签名。然后根据接收到的参数做相关的商业业务处理，最后根据
	      文档的5.1.2.2的格式组装成字符串，并生成签名，响应手机钱包业务平台的请求
	    
	     处理流程
	     1、接收参数
	     2、定义响应参数
	     3、验签
	     4、商户订单处理
	     5、生成签名
	     6、组装响应串并返回
		具体参数格式请参见 手机钱包商户接入规范文档2.2 的5.1.2
	===============================================================
-->
<?php include("umpayHead.php");?>
<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN">
<html>
<head>
<!--------------------------------------------->
<!--1、接收参数                              -->
<!--------------------------------------------->	
<?php
//商户号码
$merId = $_REQUEST['merId'];
//商品号
$goodsId = $_REQUEST['goodsId']; 
//商品描述
$goodsInf = $_REQUEST['goodsInf'];
//手机号
$mobileId = $_REQUEST['mobileId'];
//金额类型
$amtType = $_REQUEST['amtType'];
//银行类型
$bankType = $_REQUEST['bankType'];
//版本号 
$version = $_REQUEST['version'];
//商品描述
$sign = $_REQUEST['sign'];
?>
<!--------------------------------------------->
<!--2、定义响应参数                          -->
<!--------------------------------------------->
<?php
//支付通知地址
$notifyUrl="";
//返回码
$retCode="";
//支付金额，这个需要商户根据自己的产品确定金额的大小,这个是商户要完成的主要工作。
$amount=0;
//订单号，这个需要商户根据自己的系统确定。
$orderId="200510100";
//商户下单日期
$merDate=date("Ymd")
$merPriv = "";
$expand = "";
$retMsg = "";
?>
<!--------------------------------------------->
<!--3、验签                                   -->
<!--------------------------------------------->
<?php
//重新组装签名字符串
//根据手机钱包商户接入规范2.2V的5.1.2.1组装字符串，进行验签
        $paramNew="";  
	$paramNew =$paramNew . "merId=" . trim($merId,"\x00..\x1F");
	$paramNew =$paramNew . "&goodsId=" . trim($goodsId,"\x00..\x1F");
        if(!empty(goodsInf)){
        $paramNew =$paramNew . "&goodsInf=" . trim($goodsInf,"\x00..\x1F");
         }
        if(!empty(mobileId)){
        $paramNew =$paramNew . "&mobileId=" . trim($mobileId,"\x00..\x1F");
        }
        $paramNew = $paramNew . "&amtType=" . trim($amtType,"\x00..\x1F");   
        $paramNew = $paramNew . "&bankType=" . trim($bankType,"\x00..\x1F);
        $paramNew = $paramNew . "&version=". trim($version,"\x00..\x1F");
//验签
$result=ssl_verify($paramNew,$sign,$certfile);

?>
<!--------------------------------------------->
<!--4、商户订单处理                          -->
<!--------------------------------------------->
<?php
     if($result){
     }
     if($PRODUCTID=="999701")
     {
	   //包月产品
        $amount=500;
	$retCode="0000";
	$orderId="999701";
     }
     else if ($PRODUCTID=="010")
	 {
		$retCode="0000";
		$orderId="" . mt_rand(100000,999999);
		$amount=100;
	 }
     else{    
       $ret_amount=0;
       $ret_code="0002";
      }
?>
<!--------------------------------------------->
<!--5、生成签名                                     -->
<!--------------------------------------------->
<?php
$ret_signtext = $merId . "|" . $goodsId . "|" . $orderId . "|" . $merDate . "|" . $amount . "|" . $notifyUrl . "|" . $merPriv . "|" . $expand . $retCode . "|" . $retMsg . "|" . $version;
//生成签名
$ret_sign=ssl_sign(ret_signtext);
?>
<!--------------------------------------------->
<!--6、组装响应串并返回                       -->
<!--------------------------------------------->
<?php
//组装响应字符串
$ret_signtext=$ret_signtext . "|" . $ret_sign;
?>
<title>联动优势商户</title>
</head>
<!-- 这个就是响应手机钱包业务平台的信息 -->
<META NAME="MobilePayPlatform" CONTENT="<?php echo $ret_signtext;?>"><body></body></html>
