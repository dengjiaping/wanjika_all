/* $Id : common.js 4865 2007-01-31 14:04:10Z paulgao $ */

/* *
 * 添加商品到购物车 
 */
function addToCart(goodsId, cp, parentId)
{
  var goods        = new Object();
  var spec_arr     = new Array();
  var fittings_arr = new Array();
  var number       = 1;
  var formBuy      = document.forms['ECS_FORMBUY'];
  var quick		   = 0;

  // 检查是否有商品规格 
  if (formBuy)
  {
    spec_arr = getSelectedAttributes(formBuy);

    if (formBuy.elements['number'])
    {
      number = formBuy.elements['number'].value;
    }

	quick = 1;
  }

  goods.quick    = quick;
//  goods.is_overseas    = formBuy.elements['is_overseas'].value;
  goods.spec     = spec_arr;
  goods.goods_id = goodsId;
  goods.number   = number;
  goods.parent   = (typeof(parentId) == "undefined") ? 0 : parseInt(parentId);
    if(cp=='quk_buy')
    {
        Ajax.call('buy.php?act=one_step_buy', 'cp='+ cp +'&goods=' + goods.toJSONString(), addToCartResponse2, 'POST', 'JSON');
    }
    else
    {
        Ajax.call('buy.php?act=add_to_cart', 'cp='+ cp +'&goods=' + goods.toJSONString(), addToCartResponse, 'POST', 'JSON');
    }
}

function addToCartResponse2(result)
{
    if (result.error > 0)
    {
        // 如果需要缺货登记，跳转
        if (result.error == 2)
        {
            if (confirm(result.message))
            {
                location.href = 'user.php?act=add_booking&id=' + result.goods_id + '&spec=' + result.product_spec;
            }
        }
        // 没选规格，弹出属性选择框
        else if (result.error == 6)
        {
            openSpeDiv(result.message, result.goods_id, result.parent,true);
        }
        else
        {
            alert(result.message);
        }
    }
    else
    {
        var cartInfo = document.getElementById('ECS_CARTINFO');
        var cart_url = 'order.php?act=order_lise';
        if (cartInfo)
        {
            cartInfo.innerHTML = result.content;
        }
        if (result.one_step_buy == '1')
        {
            location.href = cart_url;
        }
        else
        {
            switch(result.confirm_type)
            {
                case '1' :
                    if (confirm(result.message)) location.href = cart_url;
                    break;
                case '2' :
                    if (!confirm(result.message)) location.href = cart_url;
                    break;
                case '3' :
                    location.href = cart_url;
                    break;
                default :
                    break;
            }
        }
    }
}
/**
 * 获得选定的商品属性
 */
function getSelectedAttributes(formBuy)
{
  var spec_arr = new Array();
  var j = 0;

  for (i = 0; i < formBuy.elements.length; i ++ )
  {
    var prefix = formBuy.elements[i].name.substr(0, 5);

    if (prefix == 'spec_' && (
      ((formBuy.elements[i].type == 'radio' || formBuy.elements[i].type == 'checkbox') && formBuy.elements[i].checked) ||
      formBuy.elements[i].tagName == 'SELECT'))
    {
      spec_arr[j] = formBuy.elements[i].value;
      j++ ;
    }
  }

  return spec_arr;
}

/* *
 * 处理添加商品到购物车的反馈信息
 */
function addToCartResponse(result)
{
  if (result.error > 0)
  {
    // 如果需要缺货登记，跳转
    if (result.error == 2)
    {
      if (confirm(result.message))
      {
        //location.href = 'user.php?act=add_booking&id=' + result.goods_id + '&spec=' + result.product_spec;
		location.href = 'kefu.php';
      }
    }
    // 没选规格，弹出属性选择框
    //else if (result.error == 6)
    //{
    //  openSpeDiv(result.message, result.goods_id, result.parent);
    //}
    else
    {
      alert(result.message);
    }
  }
  else
  {
    var cart_url = 'cart.php';

    if (result.ctype == '1')
    {
      	alert(addto_cart_success);
        location.reload();
    }else{
		location.href = cart_url;
	}
   
  }
}

/* *
 * 添加商品到收藏夹
 */
function collect(goodsId)
{
  Ajax.call('user.php?act=collect', 'id=' + goodsId, collectResponse, 'GET', 'JSON');
}

/* *
 * 处理收藏商品的反馈信息
 */
function collectResponse(result)
{
  alert(result.message);
}

/* *
 *  返回属性列表
 */
function getAttr(cat_id)
{
  var tbodies = document.getElementsByTagName('tbody');
  for (i = 0; i < tbodies.length; i ++ )
  {
    if (tbodies[i].id.substr(0, 10) == 'goods_type')tbodies[i].style.display = 'none';
  }

  var type_body = 'goods_type_' + cat_id;
  try
  {
    document.getElementById(type_body).style.display = '';
  }
  catch (e)
  {
  }
}
function freemembers()
{
    Ajax.call('user.php?act=ajax_free_members', '', returnFreememers, 'POST', 'JSON');
}
function returnFreememers(result)
{
    if(result.errorcode==1)
    {
        location.href="user.php?act=login";
    }
    else
    {
        alert(result.msg);
    }
}
function getbonus(type)
{
    bonus_type=[1,2,3,4];
    if(type==1)
    {
        Ajax.call('wdsnbook.php?act=getbonus', 'bonus_type='+bonus_type + '&name='+name, returngetbonus, 'POST', 'JSON');
    }
    else if(type==2)
    {
        post('mountaineering.php?act=getbonus', {bonus_type :bonus_type,name:name});
    }
    else if(type==3)
    {
        post('yingtaoyeye.php?act=getbonus', {bonus_type :bonus_type,name:name});
    }
    else if(type==4)
    {
        Ajax.call('520hd.php?act=getbonus', 'bonus_type='+bonus_type + '&name='+name, returngetbonus, 'POST', 'JSON');
    }
    else if(type==5)
    {
        Ajax.call('carnival.php?act=getbonus', 'bonus_type='+bonus_type + '&name='+name, returngetbonus, 'POST', 'JSON');
    }
    else if(type==6)
    {
        Ajax.call('recruitment.php?act=getbonus', 'bonus_type='+bonus_type + '&name='+name, returngetbonus, 'POST', 'JSON');
    }
    else if(type==7)
    {
        Ajax.call('latexpillow.php?act=getbonus', 'bonus_type='+bonus_type + '&name='+name, returngetbonus, 'POST', 'JSON');
    }
    else if(type==8)
    {
        var att = [133];
        Ajax.call('starfestival.php?act=getbonus', 'bonus_type='+att + '&name='+name, returngetbonus, 'POST', 'JSON');
    }
    else if(type==9)
    {
        var att = [134];
        Ajax.call('starfestival.php?act=getbonus', 'bonus_type='+att + '&name='+name, returngetbonus, 'POST', 'JSON');
    }
    else if(type==10)
    {
        var att = [135];
        Ajax.call('starfestival.php?act=getbonus', 'bonus_type='+att + '&name='+name, returngetbonus, 'POST', 'JSON');
    }
    else if(type==11)
    {
        Ajax.call('loverday.php?act=getbonus', 'bonus_type='+bonus_type + '&name='+name, returngetbonus, 'POST', 'JSON');
    }
    else if(type==12)
    {
        Ajax.call('stmianmo.php?act=getbonus', 'bonus_type='+bonus_type + '&name='+name, returngetbonus, 'POST', 'JSON');
    }
    else if(type==13)
    {
        Ajax.call('laundry.php?act=getbonus', 'bonus_type='+bonus_type + '&name='+name, returngetbonus, 'POST', 'JSON');
    }
    else
    {
        Ajax.call('coupon.php?act=getbonus', 'bonus_type='+bonus_type + '&name='+name, returngetbonus, 'POST', 'JSON');
    }
}
function returngetbonus(result)
{
    if(result.errorcode==2)
    {
        location.href="user.php?act=login";
    }
    else if(result.errorcode==0)
    {
        alert(result.msg);
        if(result.is_true == 1)
        {
            return;
        }
        location.href="user.php?act=coupons";
    }
    else
    {
        alert(result.msg);
    }
}

function updateuserconsignee(id)
{
    Ajax.call('user.php?act=act_updateconsignee', 'address_id=' + id, returnUpdateaddress, 'POST', 'JSON');
}
function returnUpdateaddress(result)
{
    if(result.errorcode==0)
    {
        location.reload();
    }
    else
    {
        alert(result.msg);
    }
}
function search_session()
{
    sessionStorage.setItem("page",1);
    sessionStorage.setItem("top",0);
}
var  page =1;
//分类加载分页
function category_load()
{
    page++;
    sessionStorage.setItem("page",page);
    var c_id = document.getElementById("c_id");
    var pages1 = document.getElementById("pages1").value;
    if(page>pages1)
    {
        document.getElementById("slideDown1").style.display = "none";
        document.getElementById("slideDown3").style.display = "block";
    }
    else
    {
        slideDownStep1();
        slideDownStep2();
        Ajax.call('category.php?act=page', 'cid=' + c_id.value+'&order_price=0'+'&page='+page, returnPage, 'POST', 'JSON');
        setTimeout(function(){
            slideDownStep3();
        },1000);
    }
}
//搜索加载分页
function search_load()
{
    page++;
    sessionStorage.setItem("page",page);
    var keywords = document.getElementById("keywords");
    var pages1 = document.getElementById("pages1").value;
    if(page>pages1)
    {
        document.getElementById("slideDown1").style.display = "none";
        document.getElementById("slideDown2").style.display = "none";
        document.getElementById("slideDown3").style.display = "block";
    }
    else
    {
        slideDownStep1();
        slideDownStep2();
        Ajax.call('search.php?', "keywords="+keywords.value+'&search_load=1&page='+page, returnPage, 'POST', 'JSON');
        setTimeout(function(){
            slideDownStep3();
        },1000);
    }
}
//第一步：下拉过程
function slideDownStep1(){  // dist 下滑的距离，用以拉长背景模拟拉伸效果
    var slideDown1 = document.getElementById("slideDown1"),
        slideDown2 = document.getElementById("slideDown2");
    slideDown2.style.display = "none";
    slideDown1.style.display = "block";
    slideDown1.style.height = "20px";
}
//第二步：下拉，然后松开，
function slideDownStep2(){
    var slideDown1 = document.getElementById("slideDown1"),
        slideDown2 = document.getElementById("slideDown2");
    slideDown1.style.display = "none";
    slideDown1.style.height = "20px";
    slideDown2.style.display = "block";
    //刷新数据
    //location.reload();
}
//第三步：刷新完成，回归之前状态
function slideDownStep3(){
    var slideDown1 = document.getElementById("slideDown1"),
        slideDown2 = document.getElementById("slideDown2");
    slideDown1.style.display = "block";
    slideDown2.style.display = "none";
}
function returnPage(result)
{
    if(result.error==1)
    {
        document.getElementById("slideDown").display='none';return false;
    }
    for(var i=0;i<result.content.length;i++)
    {
        var ttt = '<a href="goods.php?id='+result.content[i].goods_id+'">'+'<div class="default_div">'+'<img src="/'+result.content[i].thumb+'"  width="100" height="100" alt=""/></div>'+'<div style="margin: 10px;"><div style="display: inline-block;height: 20px;width: 100%;">';
        if(result.content[i].is_overseas == 1)
        {
            if(result.content[i].overseas_logo != 'taiwan')
            {
                ttt +='<img class="fl" src="../../themes/default/images/'+result.content[i].overseas_logo+'.jpg" style="margin: 2px 5px 0 0;width: 25px;height: 15px;"/>';
            }
            ttt +='<p style="width: 76%;overflow: hidden;text-overflow: ellipsis;white-space: nowrap;float: left;color: #9c9c9c;">'+result.content[i].overseas_logo_name+'品牌 官方直供</p></div>';
        }
        ttt += '</div><div class="p-name default_pname" >'+result.content[i].name+'</div><div class="p-detail" style="width:180px;height: 20px;"><span style="font-family:Arial;font-weight:bold;color: #db2929;">'+result.content[i].price+'</span></div></div></a></li>';
//        if(result.content[i].goods_discount < 10)
//        {
//            ttt +='<span style="padding-left: 6px !important;color:#333333;"><i style="padding:0 10px;background-image:url(/mobile/images/1_03.png);background-repeat:no-repeat;background-size: 45px 16px;color:#f9f9f9;font-style: normal;">'+result.content[i].goods_discount+'折</i><span style="display: block;padding-top: 6px;color: #d6d6d6;">国内参考价:'+result.content[i].market_price;
//        }
//        ttt += '</div></td></tr></tbody></table></div></a>';
        var aaa = document.createElement("li");
        if((i&1) === 0)
        {
            aaa.className="default_fr default";
        }
        else
        {
            aaa.className="default_fl default";
        }
        aaa.innerHTML = ttt;
        document.getElementById("test").appendChild(aaa);
//        var aaa = document.createElement("a");
//        aaa.href='goods.php?id='+result.content[i].id;
//        var textNode = document.createTextNode(ttt);
//        aaa.appendChild(textNode);

//        document.getElementById("test").append(ttt);
//        console.log(ttt);
//        if(result.content[i].is_overseas == 1)
//        {
//            myoverseasdiv = document.createElement("div");
//            if(result.content[i].overseas_logo != 'taiwan')
//            {
//                myimg = document.createElement("img");
//                myimg.className='fl my_img';
//                myoverseasdiv.appendChild(myimg);
//            }
//
//            myp = document.createElement("p");
//            myp.className='my_p';
//            var node=document.createTextNode(result.content[i].overseas_logo_name + "品牌 官方直供");
//            myp.appendChild(node);
//
//            myoverseasdiv.appendChild(myp);
//            my_p_name.className="my_overseas_div";
//        }
//
//        my_p_name = document.createElement("div");
//        my_p_name.innerText=result.content[i].name;
//        my_p_name.className="p-name my_p_name";
//
//        my_p_detail = document.createElement("div");
//        my_p_detail.innerText=result.content[i].name;
//        my_p_detail.className="p-name my_p_name";

//    var newP = document.createElement("a");
//    newP.href  = 'goods.php?id='+result.content[0].id;
//    var text1="<div class='box{if $smarty.foreach.goo.iteration == 1} first{/if}' ></div>";console.log(text1);
////    var textNode = document.createTextNode('');
//    newP.appendChild(text1);
//    document.getElementById("test").appendChild(newP);
    }
}

//文档高度
function getDocumentTop() {
    var scrollTop = 0, bodyScrollTop = 0, documentScrollTop = 0;
    if (document.body) {
        bodyScrollTop = document.body.scrollTop;
    }
    if (document.documentElement) {
        documentScrollTop = document.documentElement.scrollTop;
    }
    scrollTop = (bodyScrollTop - documentScrollTop > 0) ? bodyScrollTop : documentScrollTop;    return scrollTop;
}

//可视窗口高度
function getWindowHeight() {
    var windowHeight = 0;    if (document.compatMode == "CSS1Compat") {
        windowHeight = document.documentElement.clientHeight;
    } else {
        windowHeight = document.body.clientHeight;
    }
    return windowHeight;
}

//滚动条滚动高度
function getScrollHeight() {
    var scrollHeight = 0, bodyScrollHeight = 0, documentScrollHeight = 0;
    if (document.body) {
        bodyScrollHeight = document.body.scrollHeight;
    }
    if (document.documentElement) {
        documentScrollHeight = document.documentElement.scrollHeight;
    }
    scrollHeight = (bodyScrollHeight - documentScrollHeight > 0) ? bodyScrollHeight : documentScrollHeight;    return scrollHeight;
}

function post(URL, PARAMS) {
    var temp = document.createElement("form");
    temp.action = URL;
    temp.method = "post";
    temp.style.display = "none";
    for (var x in PARAMS) {
        var opt = document.createElement("textarea");
        opt.name = x;
        opt.value = PARAMS[x];
        temp.appendChild(opt);
    }
    document.body.appendChild(temp);
    temp.submit();
}

function is_binding(){

    var wx = document.getElementById('wx_bd').innerHTML;

    if(wx=="绑定微信")
    {
        sweetAlert({
            title: "绑定微信?",
            showCancelButton: true,
            confirmButtonText: "确定",
            cancelButtonText:"取消",
            imageUrl:"/mobile/images/weixin.png",
            closeOnConfirm: false
        }, function(){
            Ajax.call('user.php?act=wx_binding', ''.toJSONString(), wxBindingResponse, 'POST', 'JSON');
        });
    }
    else
    {
        sweetAlert({
            title: "解除绑定?",
            showCancelButton: true,
            confirmButtonText: "确定",
            cancelButtonText:"取消",
            imageUrl:"/mobile/images/weixin.png",
            closeOnConfirm: false
        }, function(){
            Ajax.call('user.php?act=wx_delete_binding', ''.toJSONString(), wxBindingResponse, 'POST', 'JSON');
        });
    }
}
function wxBindingResponse(result){
    var wx = document.getElementById('wx_bd');
    var name = wx.innerHTML=="绑定微信" ? "解除绑定" :"绑定微信"
    if(result.errorcode == 0)
    {
        wx.innerText=name;
        swal(result.msg,
            "",
            "success")
    }
    else
    {
        swal(result.msg,
            "",
            "error")
    }
}
function returnshowSign(obj)
{
    Ajax.call('exchange.php?act=sign', '', SignResponse, 'POST', 'JSON');
}

function SignResponse(result)
{
    if(result.error == -2)
    {
        location.href = "user.php?act=login";
    }
    else if(result.error == -1)
    {
//        alert(result.content);
    }
    else
    {
        alert("签到成功");
        document.getElementById("sign_btn").onclick = "";
        document.getElementById("sign_btn").innerText = result.msg;
    }
}