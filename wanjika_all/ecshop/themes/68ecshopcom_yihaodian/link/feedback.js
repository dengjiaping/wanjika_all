var issending=false;

function sendmailfunc() 
{
	if(issending) return;
	 document.getElementById("showmessage1").style.display="none";
	 document.getElementById("showmessage2").style.display="none";
	 
	var context=document.getElementById("context").value;
	var sender=document.getElementById("sender").value;
	if(context=="" )
	{
		document.getElementById("showmessage2").style.display="block";
		document.getElementById("message2").innerHTML="内容不能为空，请您重新输入。";
		return;
	}
	if(context.length<10)
	{
		document.getElementById("showmessage2").style.display="block";
		document.getElementById("message2").innerHTML="请您输入至少10个文字。";
		movePoint(context.length);
		return;
	}
	if(context.length>5000)
	{
		document.getElementById("showmessage2").style.display="block";
		document.getElementById("message2").innerHTML="请您输入不超过5000个文字。";
		movePoint(context.length);
		return;
	}
	
	if(sender!=""&&!checkEmail(sender) )
	{
		document.getElementById("showmessage2").style.display="block";
		document.getElementById("message2").innerHTML="邮件格式不正确，请您重新输入。";
		document.getElementById("sender").focus();
		return;
	}
    var url = "/Mailsend?temp=" + Math.random();
    if (window.XMLHttpRequest)
	{
        req = new XMLHttpRequest()
    } else if (window.ActiveXObject)
	{
        req = new ActiveXObject("Microsoft.XMLHTTP")
    }
	
    if (req) 
	{
        req.open("POST", url, true);
		issending=true;
		//req.setRequestHeader("Content-Type","application/x-www-form-urlencoded");
		//req.setrequestheader("cache-control","no-cache"); 
		
	
			 var form = document.getElementById("sendmail");
  			var formBody =analyseForm(form);
    		 req.setRequestHeader("Content-Type","application/x-www-form-urlencoded; charset=utf-8");
       		 req.send(formBody);
		    req.onreadystatechange = complete;
    }
}


function complete()
{
    if (req.readyState == 4)
	{
        if (req.status == 200) 
		{
            var resultcontext = req.responseText;
            var json = eval("(" + resultcontext + ")");
				if (json.stauts == 20)
				{
					 var form = document.getElementById("sendmail");
					 clearForm(form);
					issending=false;
				} 
				document.getElementById("showmessage1").style.display="block";
				document.getElementById("message1").innerHTML=json.message;
        }
    }
}


function analyseForm(form) // 分析form的元素

 {
  var params = new Array();
  for(var i = 0; i < form.elements.length; i++)
  {
     var param = form.elements[i].name;
     param += "=";
     param += form.elements[i].value;
     params.push(param);
  }
  return params.join("&");//返回的是一个数组

 }
 
 function clearForm(form) // 分析form的元素

 {
  var params = new Array();
  for(var i = 0; i < form.elements.length; i++)
  {
    	form.elements[i].value="";
   
  }
 }
 
 function checkEmail(str) 
 {   
      var re = /^\w+([-+.]\w+)*@\w+([-.]\w+)*\.\w+([-.]\w+)*$/ 
      return re.test(str);   
 }   
 function hidemessge()
 {
	 document.getElementById("showmessage1").style.display="none";
	 document.getElementById("showmessage2").style.display="none";
 }
 function movePoint(pn)
{	
	if(isNaN(pn))
	return;
	var rng = document.getElementById("context").createTextRange();
	rng.moveStart("character",pn);
	rng.collapse(true);
	rng.select();

}

var title="快递查询";


function addfavir()
{
	
	   var url="http://"+document.domain;
		var ua = navigator.userAgent.toLowerCase();
		if(ua.indexOf("msie 8")>-1)
		{
			external.AddToFavoritesBar(url,title,'快递查询');//IE8
		}else
		{
			try
			{
				window.external.addFavorite(url, title);
			} catch(e)
			{
				try
				{
				window.sidebar.addPanel(title, url, "");//firefox
				} catch(e) 
				{
				alert("加入收藏失败，请使用Ctrl+D进行添加");
				}
			}
		}
}
