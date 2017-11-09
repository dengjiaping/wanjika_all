/* $Id : shopping_flow.js 4865 2007-01-31 14:04:10Z paulgao $ */

var selectedShipping = null;
var selectedPayment  = null;
var selectedPack     = null;
var selectedCard     = null;
var selectedSurplus  = '';
var selectedBonus    = 0;
var selectedIntegral = 0;
var selectedOOS      = null;
var alertedSurplus   = false;

var groupBuyShipping = null;
var groupBuyPayment  = null;

/* *
 * 改变配送方式
 */
function selectShipping(obj)
{
  if (selectedShipping == obj)
  {
    return;
  }
  else
  {
    selectedShipping = obj;
  }

  var supportCod = obj.attributes['supportCod'].value + 0;
  var theForm = obj.form;

  for (i = 0; i < theForm.elements.length; i ++ )
  {
    if (theForm.elements[i].name == 'payment' && theForm.elements[i].attributes['isCod'].value == '1')
    {
      if (supportCod == 0)
      {
        theForm.elements[i].checked = false;
        theForm.elements[i].disabled = true;
      }
      else
      {
        theForm.elements[i].disabled = false;
      }
    }
  }

  if (obj.attributes['insure'].value + 0 == 0)
  {
    document.getElementById('ECS_NEEDINSURE').checked = false;
    document.getElementById('ECS_NEEDINSURE').disabled = true;
  }
  else
  {
    document.getElementById('ECS_NEEDINSURE').checked = false;
    document.getElementById('ECS_NEEDINSURE').disabled = false;
  }

  var now = new Date();
  Ajax.call('order.php?act=select_shipping', 'shipping=' + obj.value, orderShippingSelectedResponse, 'GET', 'JSON');
}

/**
 *
 */
function orderShippingSelectedResponse(result)
{
  if (result.need_insure)
  {
    try
    {
      document.getElementById('ECS_NEEDINSURE').checked = true;
    }
    catch (ex)
    {
      alert(ex.message);
    }
  }

  try
  {
    if (document.getElementById('ECS_CODFEE') != undefined)
    {
      document.getElementById('ECS_CODFEE').innerHTML = result.cod_fee;
    }
  }
  catch (ex)
  {
    alert(ex.message);
  }

  orderSelectedResponse(result);
}

/* *
 * 改变支付方式
 */
function selectPayment(obj)
{
  if (selectedPayment == obj)
  {
    return;
  }
  else
  {
    selectedPayment = obj;
  }
  var payment = document.getElementsByName("pay_id");
    for (i = 0; i < payment.length; i ++ )
    {
        document.getElementById("sel_" + payment[i].value).className="payments";
    }
    document.getElementById("sel_" + obj).className="payment";
//  Ajax.call('order.php?act=select_payment', 'payment=' + obj.value, orderSelectedResponse, 'GET', 'JSON');
}

/* *
 * 改变余额
 */
function changeSurplus(val)
{
    if (selectedSurplus == val)
    {
        return;
    }
    else
    {
        selectedSurplus = val;
    }

    Ajax.call('order.php?act=change_surplus', 'surplus=' + val, changeSurplusResponse, 'GET', 'JSON');
}


/* *
 * 改变积分
 */
function changeIntegral(val)
{
    var amount_formated = document.getElementById("amount_formated").value;
    if (selectedIntegral == val)
    {
        return;
    }
    else
    {
        selectedIntegral = val;
    }
    Ajax.call('order.php?act=change_integral', 'points=' + val+'&amount=' + amount_formated, changeIntegralResponse, 'GET', 'JSON');
}

/* *
 * 改变积分回调函数
 */
function changeIntegralResponse(obj)
{
    if (obj.error)
    {
        try
        {
            document.getElementById('ECS_INTEGRAL_NOTICE').innerHTML = obj.error;
            document.getElementById('ECS_INTEGRAL').value = '0';
            document.getElementById('ECS_INTEGRAL').focus();
        }
        catch (ex) { }
    }
    else
    {
        try
        {
            document.getElementById('ECS_INTEGRAL_NOTICE').innerHTML = '';
        }
        catch (ex) { }
        orderSelectedResponse(obj.content);
    }
}
/* *
 * 改变余额回调函数
 */
function changeSurplusResponse(obj)
{
    if (obj.error)
    {
        try
        {
            document.getElementById("ECS_SURPLUS_NOTICE").innerHTML = obj.error;
            document.getElementById('ECS_SURPLUS').value = '0';
            document.getElementById('ECS_SURPLUS').focus();
        }
        catch (ex) { }
    }
    else
    {
        try
        {
            document.getElementById("ECS_SURPLUS_NOTICE").innerHTML = '';
        }
        catch (ex) { }
        orderSelectedResponse(obj.content);
    }
}
/* *
 * 改变优惠券
 */
function changeBonus(type,bonus_id,gift_id)
{
//  if (selectedBonus == val)
//  {
//    return;
//  }
//  else
//  {
//    selectedBonus = val;
//  }

    Ajax.call('order.php?act=change_bonus', 'bonus=' + bonus_id + '&gift=' + gift_id, orderSelectedResponse, 'GET', 'JSON');
}

/**
 * 验证红包序列号
 * @param string bonusSn 红包序列号
 */
function validateBonus(bonusSn)
{
    Ajax.call('order.php?step=validate_bonus', 'bonus_sn=' + bonusSn, validateBonusResponse, 'GET', 'JSON');
}

function validateBonusResponse(obj)
{

    if (obj.error)
    {
        alert(obj.error);
        orderSelectedResponse(obj.content);
        try
        {
            document.getElementById('ECS_BONUSN').value = '0';
        }
        catch (ex) { }
    }
    else
    {
        orderSelectedResponse(obj.content);
    }
}

/* *
 * 选定了配送保价
 */
function selectInsure(needInsure)
{
  needInsure = needInsure ? 1 : 0;

  Ajax.call('order.php?act=select_insure', 'insure=' + needInsure, orderSelectedResponse, 'GET', 'JSON');
}

/* *
 * 回调函数
 */
function orderSelectedResponse(result)
{
  if (result.error)
  {
    alert(result.error);
    location.href = './';
  }

  try
  {
    var layer = document.getElementById("ECS_ORDERTOTAL");

    layer.innerHTML = (typeof result == "object") ? result.content : result;

    if (result.payment != undefined)
    {
      var surplusObj = document.forms['theForm'].elements['surplus'];
      if (surplusObj != undefined)
      {
        surplusObj.disabled = result.pay_code == 'balance';
      }
    }
  }
  catch (ex) { }
}
function changeSelected(id,is_true)
{
    var li_id = document.getElementById('li_'+id);
    var id = document.getElementById(id);
    //如果选中的
    if(hasClass(id,'cart_iconun') || is_true)
    {
        removeClass(id,'cart_iconun');
        addClass(id,'cart_icons');
        addClass(li_id,'cart_li_sel');
    }
    else
    {
        removeClass(id,'cart_icons');
        removeClass(li_id,'cart_li_sel');
        addClass(id,'cart_iconun');
    }
    returnflow(1);
}
function changeSelectedAll(sign)
{
    var select = document.getElementsByName("select");
    var selectall = document.getElementsByName("selectall");
    if(selectall[0].className == 'cart_iconun')
    {
        for(var i = 0;i<select.length;i++)
        {
            var li_id = document.getElementById('li_'+select[i].id);
            if(hasClass(select[i],'cart_iconun'))
            {
                removeClass(select[i],'cart_iconun');
                addClass(select[i],'cart_icons');
                addClass(li_id,'cart_li_sel');
            }
        }
        selectall[0].className='cart_icons';
    }
    else
    {
        for(var i = 0;i<select.length;i++)
        {
            var li_id = document.getElementById('li_'+select[i].id);
            if(hasClass(select[i],'cart_icons'))
            {
                removeClass(select[i],'cart_icons');
                removeClass(li_id,'cart_li_sel');
                addClass(select[i],'cart_iconun');
            }
        }

        selectall[0].className='cart_iconun';
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
        if(hasClass(select[i],'cart_icons'))
        {
            flowid.push(select[i].id);
        }
        else{
            selectall[0].className='cart_iconun';
            sign=1;
        }
    }
    if(sign==0)
    {
        selectall[0].className='cart_icons';
    }
    if(signs==1)
    {
        //请求返回实时数量和价格
        Ajax.call('cart.php?act=select_cart_count', 'flow_id=' + flowid, returnNumprice, 'POST', 'JSON');
    }
    else
    {
        if(flowid=='')
        {
            alert("请至少选择一件商品");
            return false;
        }
        Ajax.call('cart.php?act=select_cart', 'flow_id=' + flowid, returnFlowinfo, 'POST', 'JSON');
    }
}
function returnNumprice(result)
{
    document.getElementById('cart_amount_desc').innerHTML=result.total;
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
        location.href="order.php?act=order_lise";
    }
}
function selectBonus(bonus_id,address_id,gift_id)
{
    post('order.php?act=order_lise', {bonus_id :bonus_id,address_id :address_id,gift_id :gift_id});
}
function selectAddress(gift_id,bonus_id,is_overseas)
{
    post('user.php?act=address_list', {address_flow :1,gift_id:gift_id,bonus_id:bonus_id,is_overseas:is_overseas});
}
function selectAddressId(address_id,bonus_id,gift_id)
{
    post('order.php?act=order_lise', {address_id :address_id,bonus_id:bonus_id,gift_id:gift_id});
}
function combinSubmitPay(theForm)
{
    var paymentSelected = false;
    for (i = 0; i < theForm.elements.length; i ++ ){
        if (theForm.elements[i].name == 'pay_id' && theForm.elements[i].checked){
            paymentSelected = true;
        }
    }
    if(!paymentSelected)
    {
        alert("请选择一个支付方式");
        return false;
    }
    theForm.submit();
}
function hasClass(obj, cls) {
    return obj.className.match(new RegExp('(\\s|^)' + cls + '(\\s|$)'));
}
function addClass(obj, cls) {
    if (!this.hasClass(obj, cls)) obj.className += " " + cls;
}

function removeClass(obj, cls) {
    if (hasClass(obj, cls)) {
        var reg = new RegExp('(\\s|^)' + cls + '(\\s|$)');
        obj.className = obj.className.replace(reg, ' ');
    }
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

/* *
 * 添加礼包到购物车
 */
function addPackageToCartM(packageId)
{
    var package_info = new Object();
    var number       = 1;

    package_info.package_id = packageId
    package_info.number     = number;

    Ajax.call('buy.php?act=add_package_to_cart', 'package_info=' + package_info.toJSONString(), addPackageToCartMResponse, 'POST', 'JSON');
}

/* *
 * 处理添加礼包到购物车的反馈信息
 */
function addPackageToCartMResponse(result)
{
    if (result.error > 0)
    {
        if (result.error == 2)
        {
            if (confirm(result.message))
            {
                //缺货登记
//                location.href = 'user.php?act=add_booking&id=' + result.goods_id;
            }
        }
        else
        {
            alert(result.message);
        }
    }
    else
    {
        var cart_url = 'cart.php';
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
//                    location.href = cart_url;
                    confirm("该礼包已添加到购物车");
                    break;
                default :
                    break;
            }
        }
    }
}
