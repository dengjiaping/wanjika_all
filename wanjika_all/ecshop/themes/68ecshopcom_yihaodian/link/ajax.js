var type;
var postid;
var valicode;



function getResult()
{

}

function clear() 
{
    type = "";
    postid = "";
   
    var radios = document.getElementsByName("posttype");
	var comcode=getCookie("comcode");
	//alert(comcode);
	var hascode=false;
	if(typeof(comcode)=="undefined"||comcode==null|| comcode=="")   hascode=false;
	else  hascode=true;
    for (var i = 0; i < radios.length; i++) 
	{
		
        radios[i].checked = false;
		if( hascode&&(comcode==radios[i].value) )
		{
			radios[i].checked = true;
			hascode=false;
			 var json = eval("(" + jsoncom + ")");
   			 json = json.company;
			 
			 for (var i = 0; i < json.length; i++) 
			 {
				if (comcode == json[i].code)
				{
					var name = json[i].companyname;
					var shortname = json[i].shortname;
					var tel = json[i].tel;
					var hasvali = json[i].hasvali;
					
					document.getElementById("companyname").innerHTML = name;
					document.getElementById("tel").innerHTML = "查询电话：" + tel;
					
				}
			 }
		}
		
    }
	var inputvalue=getCookie("inputpostid");
	if(inputvalue==""||inputvalue==null||inputvalue=="请输入您要查询的单号")
	{
	}else
	{
		document.getElementById("postid").value=inputvalue;
	}
	var value= document.getElementById("postid").value;
	if(value==""||value==null||value=="请输入您要查询的单号")
	{
		 document.getElementById("postid").style.fontSize= "14px";
	}else
	{
		document.getElementById("postid").style.fontSize= "24px";
	}
	
		
}
function showcom(obj) {

	
    var code = obj.value;
	type=code;
    obj.checked = true;
    comobj = obj;
	
	

   
   
    var json = eval("(" + jsoncom + ")");
    json = json.company;
	if(document.documentElement.scrollTop>140)
	{
		// window.location.hash="topshow";
		 //window.location.hash=""  ;
		//document.documentElement.scrollTop=200;
		 document.getElementById("topshow").scrollIntoView(140);
	}
    for (var i = 0; i < json.length; i++) {
        if (code == json[i].code) {
            var name = json[i].companyname;
            var shortname = json[i].shortname;
            var tel = json[i].tel;
            var hasvali = json[i].hasvali;
           
            document.getElementById("companyname").innerHTML = name;
            document.getElementById("tel").innerHTML = "查询电话：" + tel;
            break
        }
    }
	
}



function setCookie(name, value) {
	var today = new Date()
	var expires = new Date()
	expires.setTime(today.getTime() + 1000*60*60*24*365)
	document.cookie = name + "=" + escape(value)	+ "; expires=" + expires.toGMTString()
}
 
function getCookie(Name) {
   var search = Name + "="
   if(document.cookie.length > 0) {
      offset = document.cookie.indexOf(search)
      if(offset != -1) {
         offset += search.length
         end = document.cookie.indexOf(";", offset)
         if(end == -1) end = document.cookie.length
         return unescape(document.cookie.substring(offset, end))
      }
      else return ""
   }
}

