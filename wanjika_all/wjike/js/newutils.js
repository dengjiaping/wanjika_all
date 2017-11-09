
function chkregname(name) {
    var r=document.getElementById('right_img');
    if(name.value.length>0)
    {
        var reg = /^1[3|4|5|7|8][0-9]\d{8}$/;
        if (reg.test(name.value))
        {
            Ajax.call('user.php?act=chkname', 'name=' + name.value, returnForget, 'POST', 'JSON');
        }
        else
        {
            document.getElementById('test1').innerHTML="手机格式不正确！";
            document.getElementById('username_notice').style.display="block";
            document.getElementById('codebtn').setAttribute("disabled", true);
            if(r)
            {
                document.getElementById('right_img').style.display="none";
            }
            else
            {
                document.getElementById('test1').innerHTML='<span style="background-color: #ffa200;padding: 0px 6px;border-radius: 8px;color: #fff;margin-right: 4px;">!</span>手机格式不正确！';
            }
            document.getElementById('codebtn').style.background="#ABA29A"
            document.getElementById('codebtn').style.color="#fff";
        }
    }
    else
    {
        if(r)
        {
            document.getElementById('right_img').style.display="none";
        }
    }
}

function returnForget(result)
{
    var error = document.getElementById('test1');
    var test1 = document.getElementById('username_notice');
    var r=document.getElementById('right_img');
    if(result.error=="0")
    {
        error.innerHTML=result.message;
        test1.style.display="none";
        if(r)
        {
            document.getElementById('right_img').style.display="block";
        }
        else
        {
            document.getElementById('codebtn').style.color="#fff"
        }
        document.getElementById('codebtn').removeAttribute("disabled");
        document.getElementById('codebtn').style.background="#f98611"
    }
    else
    {
        error.innerHTML="账号不存在！";
        test1.style.display="block";
        if(r)
        {
            document.getElementById('right_img').style.display="none";
        }
        document.getElementById('codebtn').style.background="#ABA29A"
    }
}
function chkname(name) {

    var r=document.getElementById('right_img');
    if(name.value.length>0)
    {
        var reg = /^1[3|4|5|7|8][0-9]\d{8}$/;
        if (reg.test(name.value))
        {
            Ajax.call('user.php?act=chkname', 'name=' + name.value, returnTxt, 'POST', 'JSON');
        }
        else
        {
            document.getElementById('codebtn').setAttribute("disabled", true);
            document.getElementById('username_notice').style.display="block";
            document.getElementById('test1').innerHTML="手机格式不正确！";
            if(r)
            {
                document.getElementById('right_img').style.display="none";
            }
            else
            {
                document.getElementById('test1').innerHTML='<span style="background-color: #ffa200;padding: 0px 6px;border-radius: 8px;color: #fff;margin-right: 4px;">!</span>手机格式不正确！';
            }
            document.getElementById('codebtn').style.background="#ABA29A";
            document.getElementById('codebtn').style.color="#fff";
        }
    }
    else
    {
        document.getElementById('username_notice').style.display="none";
        if(r)
        {
            document.getElementById('right_img').style.display="none";
        }
    }
}
function returnTxt(result)
{
    var error = document.getElementById('test1');
    var test1 = document.getElementById('username_notice');
    var r=document.getElementById('right_img');
    if(result.error=="0")
    {;
        error.innerHTML=result.message;
        test1.style.display="block";
        if(r)
        {
            document.getElementById('right_img').style.display="block";
        }
        else
        {
            error.style.display='block';
            document.getElementById('codebtn').style.color="#fff"
        }
        document.getElementById('codebtn').style.background="#ABA29A";
    }
    else
    {
        error.innerHTML=result.message;
        test1.style.display="none";
        if(r)
        {
            document.getElementById('right_img').style.display="none";
        }
        document.getElementById('codebtn').removeAttribute("disabled");
        document.getElementById('codebtn').style.background="#f98611";
        document.getElementById('codebtn').style.color="#fff";
    }
}
function chkpw2(conform_password)
{
    var password = document.getElementById('password').value;

    if ( conform_password.value != password )
    {
        document.getElementById('username_notice').style.display="block";
        document.getElementById('test1').innerHTML = confirm_password_invalid;
        return false;
    }
    else if( conform_password.value.length < 6 )
    {
        document.getElementById('username_notice').style.display="block";
        document.getElementById('test1').innerHTML = "密码不能少于6位！";
    }
    else
    {
        document.getElementById('username_notice').style.display="none";
    }
}
function chkpw(conform_password)
{
    var password = document.getElementById('conform_password').value;

    if ( conform_password.value.length < 6 )
    {
        document.getElementById('username_notice').style.display="block";
        document.getElementById('test1').innerHTML = "密码不能少于6位！";
        return false;
    }
    else if( conform_password.value != password )
    {
        document.getElementById('username_notice').style.display="block";
        document.getElementById('test1').innerHTML = confirm_password_invalid;
    }
    else
    {
        document.getElementById('username_notice').style.display="none";
    }
}
var wait=60;
function getforcode(o)
{
    window.new_o = o;
    if(wait==60)
    {
        var phone = document.getElementById('phone');
        if(Utils.isEmpty(phone.value))
        {
            return alert("手机号不能为空");
        }
        var yzm = document.getElementById('captcha');
        if(yzm)
        {
            if(Utils.isEmpty(yzm.value))
            {
                return alert("验证码不能为空");
            }
            Ajax.call('user.php?act=get_forgetvcode', 'phone=' + phone.value + '&captcha='+yzm.value, returnCode, 'POST', 'JSON');
        }
        else
        {
            Ajax.call('user.php?act=get_forgetvcode', 'phone=' + phone.value, returnCode, 'POST', 'JSON');
        }
    }
    if (wait == 0) {
        o.removeAttribute("disabled");
        o.value="获取验证码";
        wait = 60;
    } else if(wait != 60){

        document.getElementById('codebtn').style.color="#fff";
        document.getElementById('codebtn').style.backgroundColor="#d1d1d1";
        o.setAttribute("disabled", true);
        o.value="重新发送(" + wait + ")";
        wait--;
        setTimeout(function() {
                getcode(o)
            },
            1000)
    }
}
function getcode(o)
{
    window.new_o = o;
    if(wait==60)
    {
        var phone = document.getElementById('phone');
        if(Utils.isEmpty(phone.value))
        {
            return alert("手机号不能为空");
        }
        var yzm = document.getElementById('captcha');
        if(yzm)
        {
            if(Utils.isEmpty(yzm.value))
            {
                return alert("验证码不能为空");
            }
            Ajax.call('user.php?act=get_vcode', 'phone=' + phone.value + '&captcha='+yzm.value, returnCode, 'POST', 'JSON');
        }
        else
        {
            Ajax.call('user.php?act=get_vcode', 'phone=' + phone.value, returnCode, 'POST', 'JSON');
        }
    }
    if (wait == 0) {
        o.removeAttribute("disabled");
        o.value="获取验证码";
        document.getElementById('codebtn').style.backgroundColor="#f98611";
        wait = 60;
    } else if(wait != 60){
        document.getElementById('codebtn').style.color="#fff";
        document.getElementById('codebtn').style.backgroundColor="#d1d1d1"
        o.setAttribute("disabled", true);
        o.value="重新发送(" + wait + ")";
        wait--;
        setTimeout(function() {
                getcode(o)
            },
            1000)
    }
}
function getnewcode(o)
{
    if(wait==60)
    {
        var phone = document.getElementById('nphone');
        Ajax.call('user.php?act=get_vcode', 'phone=' + phone.value, returnCode, 'POST', 'JSON');
    }
    if (wait == 0) {
        o.removeAttribute("disabled");
        o.value="获取验证码";
        wait = 60;
    } else if(wait != 60){
        o.setAttribute("disabled", true);
        o.value="重新发送(" + wait + ")";
        wait--;
        setTimeout(function() {
                getcode(o)
            },
            1000)
    }
}
function returnCode(result)
{
    if(result.errorcode!=0)
    {
        document.getElementById('username_notice').style.display="block";
        document.getElementById('test1').innerHTML = result.msg;
    }
    else
    {
        document.getElementById('username_notice').style.display="none";
        document.getElementById('test1').innerHTML = "验证码发送成功！";

        if (wait == 0) {
            new_o.removeAttribute("disabled");
            new_o.value="获取验证码";
            document.getElementById('codebtn').style.background="#ABA29A";
            wait = 60;
        } else{
            document.getElementById('codebtn').style.color="#fff";
            document.getElementById('codebtn').style.backgroundColor="#d1d1d1"
            new_o.setAttribute("disabled", true);
            new_o.value="重新发送(" + wait + ")";
            wait--;
            getcode(new_o);
        }
    }
}
function returnInvoice(obj)
{
    document.getElementsByName('invoice')[0].className="wrap-fp ta-l";
    document.getElementsByName('invoice')[1].className="wrap-fp ta-l";
    document.getElementById('comsave').className='wrap-fp';
    obj.className+=' ' + 'wrap-fp-s';
}
//function returnSubstance(obj)
//{
//    document.getElementsByName('substance')[0].className="wrap-fp-c";
//    document.getElementsByName('substance')[1].className="wrap-fp-c";
//    document.getElementsByName('substance')[2].className="wrap-fp-c";
//    obj.className+=' ' + 'wrap-fp-s';
//}
function returnsave()
{
    var b =document.getElementsByClassName('wrap-fp-s');
    var type = 1
    var content = 1;
    for (var i=0;i<b.length;i++)
    {
        if(b[i].id=="company")
        {
            type = 2;
        }
    }
    Ajax.call('user.php?act=act_saveinvoice', 'type=' + type+'&content='+content, returnInvoices, 'POST', 'JSON');
}
function returnInvoices(result)
{
    if(result.errorcode!=0)
    {
        alert(result.msg);
    }
}
function edit()
{
    document.getElementById('comsave').className+=' ' + 'wrap-fp-s';
    if(document.getElementById('company').style.display =="none")
    {
        document.getElementById('company').style.display="block";
        document.getElementById('comsave').style.display="none";
    }
    else{
        document.getElementById('company').style.display="none";
        document.getElementById('comsave').style.display="block";
    }
    var company =document.getElementById('companyinfo');
    company.focus();
    var obj = company;
    obj.focus();

}
function save()
{
    var company =document.getElementById('companyinfo').value;
    Ajax.call('user.php?act=act_updatecontent', 'company_info=' + company, returnCompanyinfo, 'POST', 'JSON');
}
function returnCompanyinfo(result)
{
    if(result.errorcode==0)
    {
        document.getElementById('caompanyin').innerHTML=result.companyinfo;
        document.getElementById('company').style.display="block";
        document.getElementById('comsave').style.display="none";
        Dialog.alert(result.msg,closeDialog(),300,70);
    }
    else
    {
        Dialog.alert(result.msg,closeDialog(),300,70);
    }
}
function closeDialog()
{
    setTimeout("Dialog.close();",1000);
}
//
//function $D(objN){
//    var d=document.getElementById(objN);
//    var h=d.offsetHeight;
//    var maxh=300;
//    function dmove(){
//    h+=50; //设置层展开的速度
//    if(h>=maxh){
//    d.style.height='20px';
//    clearInterval(iIntervalId);
//    }else{
//    d.style.display='block';
//    d.style.height=0+'px';
//    }
//}
//iIntervalId=setInterval(dmove,2);
//}
//function $D2(targetid)
//{
//    var d=document.getElementById(targetid);
//    var h=d.offsetHeight;
//    var maxh=300;
//    function dmove(){
//    h-=50;//设置层收缩的速度
//    if(h<=0){
//    d.style.display='none';
//    clearInterval(iIntervalId);
//    }else{
//    d.style.height=0+'px';
//    }
//}
//iIntervalId=setInterval(dmove,2);
//}
//function $use(targetid,objN){
//    var d=document.getElementById(targetid);
//    var sb=document.getElementById(objN);
//    if (d.style.display=="block"){
//    $D2(targetid);
//    d.style.display="none";
//    sb.innerHTML="使用新地址";
//    } else {
//    $D(objN);
//    d.style.display="block";
//    sb.innerHTML='收缩';
//    }
//}
function selectAll(sign)
{
    var select = document.getElementsByName("select");
    var selectall = document.getElementsByName("selectall");
    if(sign==1)
    {
        selectall[0].checked = true;
        for(var i = 0;i<select.length;i++)
        {
            select[i].checked = true;
        }
        returnflow(1);
        return;
    }
    if(selectall[0].checked)
    {
        for(var i = 0;i<select.length;i++)
        {
            if(select[i].type == "checkbox") select[i].checked = true;
        }
    }
    else{
        for(var i = 0;i<select.length;i++)
        {
            if(select[i].type == "checkbox") select[i].checked = false;
        }
    }
    returnflow(1);
}
//传递商品的ID数组
function returnflow(signs)
{
    var  sign=0;
    var flowid=[];
    var select = document.getElementsByName("select");
    var selectall = document.getElementsByName("selectall");
    for(var i =0;i<select.length;i++)
    {
        if(select[i].checked)
        {
            flowid.push(select[i].value);
        }
        else{
            selectall[0].checked = false;
            sign=1;
        }
    }
    if(sign==0)
    {
        selectall[0].checked=true;
    }
    if(signs==1)
    {
        //请求返回实时数量和价格
        Ajax.call('flow.php?step=select_cart_count', 'flow_id=' + flowid, returnNumprice, 'POST', 'JSON');
    }
    else
    {
        if(flowid=='')
        {
            alert("请至少选择一件商品");
        }
        Ajax.call('flow.php?step=select_cart', 'flow_id=' + flowid, returnFlowinfo, 'POST', 'JSON');
    }
}
function returnNumprice(result)
{
    document.getElementById('selnum').innerHTML=result.num;
    document.getElementById('seltotal').innerHTML=result.total;
    if(typeof(result.discount) != "undefined" && result.discount.format_discount != null)
    {
        if(result.discount.format_discount.length>0)
        {
            document.getElementById('yourdiscount').style.display="block";
            document.getElementById('discount').innerHTML="-"+result.discount.format_discount;
            return;
        }
    }
    document.getElementById('yourdiscount').style.display="none";
}
function returnFlowinfo(result)
{
    if(result.errorcode==0)
    {
        location.href="flow.php?step=checkout";
    }
}
function useaddress(id)
{
    var a=document.getElementsByClassName('address_none');
    var b=document.getElementById('address_block');
    if(id==1)
    {
//        console.log(document.getElementsByClassName('address_block'));
    }
    else
    {
        b.style.display="none";
    }
    if(a[0]!=null)
    {
        if(a[0].style.display=="none")
        {
            for(var i=0;i<a.length;i++){
                a[i].style.display = "block";
            }
        }
        else
        {
            for(var i=0;i<a.length;i++){
                a[i].style.display = "none";
            }
            if(id!=1)
            {
                b.style.display="inline-block";
            }
        }
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
        Dialog.alert(result.msg,closeDialog());
    }
}


//Post方式提交表单
function PostSubmit(data, msg) {
    if(msg==17)
    {
        Dialog.alert('微信支付请到微信客户端支付！',closeDialog(),300,70);
        return false;
    }
    var postUrl = 'user.php?act=act_edit_payment';//提交地址
    var postData = data;//第一个数据
    var msgData = msg;//第二个数据
    var ExportForm = document.createElement("FORM");
    document.body.appendChild(ExportForm);
    ExportForm.method = "POST";
    ExportForm.setAttribute("target", "_blank");
    var newElement = document.createElement("input");
    newElement.setAttribute("name", "order_id");
    newElement.setAttribute("type", "hidden");
    var newElement2 = document.createElement("input");
    newElement2.setAttribute("name", "pay_id");
    newElement2.setAttribute("type", "hidden");
    ExportForm.appendChild(newElement);
    ExportForm.appendChild(newElement2);
    newElement.value = postData;
    newElement2.value = msgData;
    ExportForm.action = postUrl;
    ExportForm.submit();
};
function subPay()
{
    var frm = document.forms['payment'];
    var pay_id = frm.elements['pay_id'].value;
    if(pay_id==17)
    {
        Dialog.alert('微信支付请到微信客户端支付！',closeDialog(),300,70);
        return false;
    }
}
function closeDialog()
{
    setTimeout("Dialog.close();",1500);
    setTimeout("location.reload();",1500);
}

/* *
 *  处理用户添加一张优惠券
 */
function addCoupons()
{
    var frm      = document.forms['addToCoupons'];
    var bonus_sn = frm.elements['bonus_sn'].value;

    if (bonus_sn.length == 0)
    {
        alert("优惠码不能为空");
        return false;
    }
    else
    {
    }

    return true;
}
/* *
 *  处理用户添加一个礼品卡
 */
function addGift()
{
    var frm      = document.forms['addToGift'];
    var gift_sn = frm.elements['gift_sn'].value;
    var gift_password = frm.elements['gift_password'].value;

    if (gift_sn.length == 0||gift_password.length == 0)
    {
        alert(gift_sn_empty);
        return false;
    }
    else
    {
    }

    return true;
}
function mergeGift()
{
    var frm      = document.forms['mergeToGift'];
    var gift_sn = frm.elements['gift_sn'].value;
    var merge_gift_sn = frm.elements['merge_gift_sn'].value;

    if (gift_sn.length == 0||merge_gift_sn.length == 0)
    {
        alert(gift_sn_empty);
        return false;
    }
    else
    {
    }

    return true;
}

//选择规格
function selected(obj)
{
    var collect_name = document.getElementsByClassName("collect_name");
    var collect_count = collect_name.length;
    var selected = document.getElementsByClassName("selected");
    var selected_count = selected.length;
    var sel = obj.parentNode.getAttribute('clstag');
    var div = obj.parentNode.parentNode.getElementsByTagName('div');
    window.v_div = div;
    for(var i=0;i<div.length;i++){
        if(div[i].getAttribute('clstag') == sel)
        {
            div[i].className = "item selected";
        }
        else
        {
            if(div[i].className != 'item')
            {
                window.v_i = i;
            }
            div[i].className = "item";
        }
    }
    if(collect_count == selected_count)
    {
        collect_value = new Array();
        for(var i=0;i<selected.length;i++){
            collect_value[i] = selected[i].getAttribute('clstag');
        }
        if(collect_value.length == collect_count)
        {
            collect_value = arraysort(collect_value);
            Ajax.call('goods.php?act=select_goods', "collect_value=" + collect_value, selectedGoodsValue, "POST", "JSON");
        }
    }
    else
    {
        for(var i=0;i<v_div.length;i++){
            v_div[i].className = "item";
        }
        v_div[v_i].className = "item selected";
        Dialog.alert('暂无商品',Dialog.close(),300,70);
    }
}
function selectedGoodsValue(result)
{
    if(result.error == 0)
    {
        location.href=result.msg;
    }
    else
    {
        for(var i=0;i<v_div.length;i++){
            v_div[i].className = "item";
        }
        v_div[v_i].className = "item selected";
        Dialog.alert('暂无商品',Dialog.close(),300,70);
    }
}
function arraysort(array)
{
    var i = 0,
        len = array.length,
        j, d;
    for (; i < len; i++) {
        for (j = 0; j < len; j++) {
            if (array[i] < array[j]) {
                d = array[j];
                array[j] = array[i];
                array[i] = d;
            }
        }
    }
    return array;
}