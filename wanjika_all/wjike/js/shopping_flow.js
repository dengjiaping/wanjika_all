/* $Id : shopping_flow.js 4865 2007-01-31 14:04:10Z paulgao $ */

var selectedShipping = null;
var selectedPayment  = null;
var selectedConsignee  = null;
var selectedPack     = null;
var selectedCard     = null;
var selectedSurplus  = '';
var selectedBonus    = 0;
var selectedGift    = 0;
var selectedIntegral = 0;
var selectedOOS      = null;
var alertedSurplus   = false;

var groupBuyShipping = null;
var groupBuyPayment  = null;
var checkSubmitFlg = false;

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
  Ajax.call('flow.php?step=select_shipping', 'shipping=' + obj.value, orderShippingSelectedResponse, 'GET', 'JSON');
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
        Dialog.alert(ex.message,Dialog.close(),300,70);
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
      Dialog.alert(ex.message,Dialog.close(),300,70);
  }

  orderSelectedResponse(result);
}
/* *
 * 改变收货地址
 */
function selectConsignee(obj)
{
    if (selectedConsignee == obj)
    {
        return;
    }
    else
    {
        selectedConsignee = obj;
    }

    Ajax.call('flow.php?step=select_consignee', 'address_id=' + obj.value, orderSelectedResponse, 'POST', 'JSON');
}
/* *
 * 改变支付方式
 */
function selectPayment(obj)
{
    if(obj.value==16)
    {
        document.getElementById('olpayicons').style.display="block";
    }
    else
    {
        document.getElementById('olpayicons').style.display="none";
    }
    var theForm = obj.form;
    for (i = 0; i < theForm.elements.length; i ++ )
    {
        if (theForm.elements[i].name == 'pay_id')
        {
            document.getElementById(theForm.elements[i].value).className = "payments";
            document.getElementById("sel_" + theForm.elements[i].value).style.display = "none";
        }
    }
    document.getElementById(obj.value).className="payment";
    document.getElementById("sel_" + obj.value).style.display = "block";
    if (selectedPayment == obj)
    {
    return;
    }
    else
    {
    selectedPayment = obj;
    }

//  Ajax.call('flow.php?step=select_payment', 'payment=' + obj.value, orderSelectedResponse, 'GET', 'JSON');
}
/* *
 * 团购购物流程 --> 改变配送方式
 */
function handleGroupBuyShipping(obj)
{
  if (groupBuyShipping == obj)
  {
    return;
  }
  else
  {
    groupBuyShipping = obj;
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

  Ajax.call('group_buy.php?act=select_shipping', 'shipping=' + obj.value, orderSelectedResponse, 'GET');
}

/* *
 * 团购购物流程 --> 改变支付方式
 */
function handleGroupBuyPayment(obj)
{
  if (groupBuyPayment == obj)
  {
    return;
  }
  else
  {
    groupBuyPayment = obj;
  }

  Ajax.call('group_buy.php?act=select_payment', 'payment=' + obj.value, orderSelectedResponse, 'GET');
}

/* *
 * 改变商品包装
 */
function selectPack(obj)
{
  if (selectedPack == obj)
  {
    return;
  }
  else
  {
    selectedPack = obj;
  }

  Ajax.call('flow.php?step=select_pack', 'pack=' + obj.value, orderSelectedResponse, 'GET', 'JSON');
}

/* *
 * 改变祝福贺卡
 */
function selectCard(obj)
{
  if (selectedCard == obj)
  {
    return;
  }
  else
  {
    selectedCard = obj;
  }

  Ajax.call('flow.php?step=select_card', 'card=' + obj.value, orderSelectedResponse, 'GET', 'JSON');
}

/* *
 * 选定了配送保价
 */
function selectInsure(needInsure)
{
  needInsure = needInsure ? 1 : 0;

  Ajax.call('flow.php?step=select_insure', 'insure=' + needInsure, orderSelectedResponse, 'GET', 'JSON');
}

/* *
 * 团购购物流程 --> 选定了配送保价
 */
function handleGroupBuyInsure(needInsure)
{
  needInsure = needInsure ? 1 : 0;

  Ajax.call('group_buy.php?act=select_insure', 'insure=' + needInsure, orderSelectedResponse, 'GET', 'JSON');
}

/* *
 * 回调函数
 */
function orderSelectedResponse(result)
{
    if(result.errorcode==0)
    {
        location.href = "flow.php?step=checkout";
    }
  if (result.error)
  {

    Dialog.alert(result.error,Dialog.close(),300,70);
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

  Ajax.call('flow.php?step=change_surplus', 'surplus=' + val, changeSurplusResponse, 'GET', 'JSON');
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
        Dialog.alert(obj.error,Dialog.close(),300,70);console.log(1);
//      document.getElementById("ECS_SURPLUS_NOTICE").innerHTML = obj.error;
      document.getElementById('ECS_SURPLUS').value = '0';
//      document.getElementById('ECS_SURPLUS').focus();
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
  Ajax.call('flow.php?step=change_integral', 'points=' + val+'&amount=' + amount_formated, changeIntegralResponse, 'GET', 'JSON');
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

    Ajax.call('flow.php?step=change_bonus', 'bonus=' + bonus_id + '&gift=' + gift_id, changeBonusResponse, 'GET', 'JSON');
}

/* *
 * 改变红包的回调函数
 */
function changeBonusResponse(obj)
{
  if (obj.error)
  {

    Dialog.alert(obj.error,Dialog.close(),300,70);

    try
    {
      document.getElementById('ECS_BONUS').value = '0';
    }
    catch (ex) { }
  }
  else
  {
    document.getElementById("msg").innerText=obj.msg;
    orderSelectedResponse(obj.content);
  }
}

/**
 * 验证红包序列号
 * @param string bonusSn 红包序列号
 */
function validateBonus(bonusSn)
{
  Ajax.call('flow.php?step=validate_bonus', 'bonus_sn=' + bonusSn, validateBonusResponse, 'GET', 'JSON');
}

function validateBonusResponse(obj)
{

if (obj.error)
  {
    Dialog.alert(obj.error,Dialog.close(),300,70);
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
 * 改变发票的方式
 */
function changeNeedInv()
{
  var obj        = document.getElementById('ECS_NEEDINV');
  var objType    = document.getElementById('ECS_INVTYPE');
  var objPayee   = document.getElementById('ECS_INVPAYEE');
  var objContent = document.getElementById('ECS_INVCONTENT');
  var needInv    = obj.checked ? 1 : 0;
  var invType    = obj.checked ? (objType != undefined ? objType.value : '') : '';
  var invPayee   = obj.checked ? objPayee.value : '';
  var invContent = obj.checked ? objContent.value : '';
  objType.disabled = objPayee.disabled = objContent.disabled = ! obj.checked;
  if(objType != null)
  {
    objType.disabled = ! obj.checked;
  }

  Ajax.call('flow.php?step=change_needinv', 'need_inv=' + needInv + '&inv_type=' + encodeURIComponent(invType) + '&inv_payee=' + encodeURIComponent(invPayee) + '&inv_content=' + encodeURIComponent(invContent), orderSelectedResponse, 'GET');
}

/* *
 * 改变发票的方式
 */
function groupBuyChangeNeedInv()
{
  var obj        = document.getElementById('ECS_NEEDINV');
  var objPayee   = document.getElementById('ECS_INVPAYEE');
  var objContent = document.getElementById('ECS_INVCONTENT');
  var needInv    = obj.checked ? 1 : 0;
  var invPayee   = obj.checked ? objPayee.value : '';
  var invContent = obj.checked ? objContent.value : '';
  objPayee.disabled = objContent.disabled = ! obj.checked;

  Ajax.call('group_buy.php?act=change_needinv', 'need_idv=' + needInv + '&amp;payee=' + invPayee + '&amp;content=' + invContent, null, 'GET');
}

/* *
 * 改变缺货处理时的处理方式
 */
function changeOOS(obj)
{
  if (selectedOOS == obj)
  {
    return;
  }
  else
  {
    selectedOOS = obj;
  }

  Ajax.call('flow.php?step=change_oos', 'oos=' + obj.value, null, 'GET');
}

/* *
 * 检查提交的订单表单
 */
function checkOrderForm(frm,isreal)
{
  var shippingSelected = false;
  var consigneeSelected = false;

  // 检查是否选择了支付配送方式
  for (i = 0; i < frm.elements.length; i ++ )
  {
    if (frm.elements[i].name == 'shipping' && frm.elements[i].checked)
    {
      shippingSelected = true;
    }

    if (frm.elements[i].name == 'address_id' && frm.elements[i].checked)
    {
            if(frm.elements[i+1].value==1)
            {
                // 身份证号码为15位或者18位，15位时全为数字，18位前17位为数字，最后一位是校验位，可能为数字或字符X
                var reg = /(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/;
                if(reg.test(frm.elements[i+2].value) === false)
                {
                    click_b(frm.elements[i].value);
                    return  false;
                }
            }
        consigneeSelected = true;
    }
  }
//  if(isreal)
//  {
//      if (!shippingSelected)
//      {
//          Dialog.alert("您必须选定一个配送方式",Dialog.close(),300,70);
//          return false;
//      }
//  }
    if (!consigneeSelected && isreal)
    {
        Dialog.alert("您必须选定一个收货地址",Dialog.close(),300,70);
        return false;
    }
  // 检查用户输入的余额
  if (document.getElementById("ECS_SURPLUS"))
  {
    var surplus = document.getElementById("ECS_SURPLUS").value;
    var error   = Utils.trim(Ajax.call('flow.php?step=check_surplus', 'surplus=' + surplus, null, 'GET', 'TEXT', false));

    if (error)
    {
      try
      {
        document.getElementById("ECS_SURPLUS_NOTICE").innerHTML = error;
      }
      catch (ex)
      {
      }
      return false;
    }
  }

  // 检查用户输入的积分
  if (document.getElementById("ECS_INTEGRAL"))
  {
    var integral = document.getElementById("ECS_INTEGRAL").value;
    var amount_formated = document.getElementById("amount_formated").value;
    var error    = Utils.trim(Ajax.call('flow.php?step=check_integral', 'integral=' + integral+'&amount=' + amount_formated, null, 'GET', 'TEXT', false));

    if (error)
    {
      return false;
      try
      {
        document.getElementById("ECS_INTEGRAL_NOTICE").innerHTML = error;
      }
      catch (ex)
      {
      }
    }
  }
    if(!checkSubmitFlg)
    {
        checkSubmitFlg = true;
    }
    else
    {
        return false;
    }
  frm.action = frm.action + '?step=done';
  return true;
}

/* *
 * 检查收货地址信息表单中填写的内容
 */
function checkConsignee(frm)
{
  var msg = new Array();
  var err = false;
  var id_card=frm.elements['id_card'].value;
    if(!Utils.isEmpty(id_card))
    {
        // 身份证号码为15位或者18位，15位时全为数字，18位前17位为数字，最后一位是校验位，可能为数字或字符X
        var reg = /(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/;
        if(reg.test(id_card) === false)
        {
            Dialog.alert("请输入正确的身份证号码。",Dialog.close(),300,70);
            return  false;
        }
    }
    if (frm.elements['country'] && frm.elements['country'].value == 0)
  {
    msg.push(country_not_null);
    err = true;
  }

  if (frm.elements['province'] && frm.elements['province'].value == 0 && frm.elements['province'].length > 1)
  {
    err = true;
    msg.push(province_not_null);
  }

  if (frm.elements['city'] && frm.elements['city'].value == 0 && frm.elements['city'].length > 1)
  {
    err = true;
    msg.push(city_not_null);
  }

  if (frm.elements['district'] && frm.elements['district'].length > 1)
  {
    if (frm.elements['district'].value == 0)
    {
      err = true;
      msg.push(district_not_null);
    }
  }

  if (Utils.isEmpty(frm.elements['consignee'].value))
  {
    err = true;
    msg.push(consignee_not_null);
  }
  else
  {
      if (!/^[\u4E00-\u9FA5]{2,4}$/.test(frm.elements['consignee'].value))
      {
          err = true;
          msg.push('收货人姓名必须为2-4个中文');
      }
  }
//  if ( ! Utils.isEmail(frm.elements['email'].value))
//  {
//    err = true;
//    msg.push(invalid_email);
//  }

  if (frm.elements['address'] && Utils.isEmpty(frm.elements['address'].value))
  {
    err = true;
    msg.push(address_not_null);
  }
  else
  {
      if (!/^[\u4E00-\u9FA5a-zA-Z0-9_\-]{1,50}$/.test(frm.elements['address'].value))
      {
          err = true;
          msg.push('详细地址输入有误');
      }
  }

//  if (frm.elements['zipcode'] && frm.elements['zipcode'].value.length > 0 && (!Utils.isNumber(frm.elements['zipcode'].value)))
//  {
//    err = true;
//    msg.push(zip_not_num);
//  }

  if (Utils.isEmpty(frm.elements['tel'].value))
  {
    err = true;
    msg.push(tele_not_null);
  }
  else
  {
    if (!Utils.isTel(frm.elements['tel'].value))
    {
      err = true;
      msg.push(tele_invaild);
    }
  }

  if (frm.elements['mobile'] && frm.elements['mobile'].value.length > 0 && (!Utils.isTel(frm.elements['mobile'].value)))
  {
    err = true;
    msg.push(mobile_invaild);
  }

  if (err)
  {
    message = msg.join("\n");
    Dialog.alert(message,Dialog.close(),300,70);
  }
  return ! err;
}
function editaddress(id,sn,is_overseas)
{
   var msg = new Array();
   var err = false;
   if(id==0)
   {
       var consignee =  document.getElementById('adconsignee');
       var id_card =  document.getElementById('id_card');
       var province =  document.getElementById('selProvinces_99');
       var city =  document.getElementById('selCities_99');
       var district =  document.getElementById('selDistricts_99');
       var address =  document.getElementById('adaddress');
//       var zipcode =  document.getElementById('adzipcode');
       var tel =  document.getElementById('adtel');
   }
   else
   {
       var consignee =  document.getElementById('consignee_'+sn);
       var id_card =  document.getElementById('id_card_'+sn);
       var province =  document.getElementById('selProvinces_'+sn);
       var city =  document.getElementById('selCities_'+sn);
       var district =  document.getElementById('selDistricts_'+sn);
       var address =  document.getElementById('address_'+sn);
//       var zipcode =  document.getElementById('zipcode_'+sn);
       var tel =  document.getElementById('tel_'+sn);
   }
    if(is_overseas==1)
    {
        if(Utils.isEmpty(id_card.value))
        {
            Dialog.alert("身份证不能为空",Dialog.close(),300,70);
            return  false;
        }
        if (!Utils.isEmpty(id_card.value))
        {
            // 身份证号码为15位或者18位，15位时全为数字，18位前17位为数字，最后一位是校验位，可能为数字或字符X
            var reg = /(^\d{15}$)|(^\d{18}$)|(^\d{17}(\d|X|x)$)/;
            if(reg.test(id_card.value) === false)
            {
                Dialog.alert("请输入正确的身份证号码。",Dialog.close(),300,70);
                return  false;
            }
            var res_id = IdCardValidate(id_card.value);
            if(!res_id)
            {
                Dialog.alert("请输入正确的身份证号码。",Dialog.close(),300,70);
                return  false;
            }
        }
    }
    if (Utils.isEmpty(consignee.value))
    {
        err = true;
        msg.push(consignee_not_null);
    }
    else
    {
        if (!/^[\u4E00-\u9FA5]{2,4}$/.test(consignee.value))
        {
            err = true;
            msg.push('收货人姓名必须为2-4个中文');
        }
    }
    if (province && province.value == 0 && province.length > 1)
    {
        err = true;
        msg.push(province_not_null);
    }

    if (city && city.value == 0 && city.length > 1)
    {
        err = true;
        msg.push(city_not_null);
    }

    if (district && district.length > 1)
    {
        if (district.value == 0)
        {
            err = true;
            msg.push(district_not_null);
        }
    }
    if (address && Utils.isEmpty(address.value))
    {
        err = true;
        msg.push(address_not_null);
    }
    else
    {
        if (!/^[\u4E00-\u9FA5a-zA-Z0-9_\-]{1,50}$/.test(address.value))
        {
            err = true;
            msg.push('详细地址输入有误');
        }
    }

//    if (zipcode && zipcode.value.length > 0 && (!Utils.isNumber(zipcode.value)))
//    {
//        err = true;
//        msg.push(zip_not_num);
//    }

    if (Utils.isEmpty(tel.value))
    {
        err = true;
        msg.push(tele_not_null);
    }
    else
    {
        if (!Utils.isTel(tel.value))
        {
            err = true;
            msg.push("不是有效的电话号码");
        }
    }
    if (err)
    {
        message = msg.join("\n");
        Dialog.alert(message,Dialog.close(),300,70);
        return ! err;
    }
    if(is_overseas==1)
    {
        Ajax.call('user.php?act=ajax_edit_address', 'address_id=' + id+'&consignee='+consignee.value+'&id_card='+id_card.value+'&province='+province.value+'&city='+city.value+'&district='+district.value+'&address='+address.value+'&tel='+tel.value, orderShippingSelectedResponse, 'POST', 'JSON');
    }
    else
    {
        Ajax.call('user.php?act=ajax_edit_address', 'address_id=' + id+'&consignee='+consignee.value+'&province='+province.value+'&city='+city.value+'&district='+district.value+'&address='+address.value+'&tel='+tel.value, orderShippingSelectedResponse, 'POST', 'JSON');
    }
}
function updateconsignee(id)
{
    Ajax.call('user.php?act=act_updateconsignee', 'address_id=' + id, orderSelectedResponse, 'POST', 'JSON');
}
function updateuserconsignee(id)
{
    Ajax.call('user.php?act=act_updateconsignee', 'address_id=' + id, returnUpdateaddress, 'POST', 'JSON');
}
function returnUpdateaddress(result)
{
    if(result.errorcode==0)
    {
        Dialog.alert(result.msg,function(){location.reload();},300,70);
    }
}
function closeDialog()
{
    setTimeout("Dialog.close();",1500);
    setTimeout("location.reload();",1500);
}
var Wi = [ 7, 9, 10, 5, 8, 4, 2, 1, 6, 3, 7, 9, 10, 5, 8, 4, 2, 1 ];    // 加权因子
var ValideCode = [ 1, 0, 10, 9, 8, 7, 6, 5, 4, 3, 2 ];            // 身份证验证位值.10代表X
function IdCardValidate(idCard) {
    idCard = trim(idCard.replace(/ /g, ""));               //去掉字符串头尾空格
    if (idCard.length == 15) {
        return isValidityBrithBy15IdCard(idCard);       //进行15位身份证的验证
    } else if (idCard.length == 18) {
        var a_idCard = idCard.split("");                // 得到身份证数组
        if(isValidityBrithBy18IdCard(idCard)&&isTrueValidateCodeBy18IdCard(a_idCard)){   //进行18位身份证的基本验证和第18位的验证
            return true;
        }else {
            return false;
        }
    } else {
        return false;
    }
}
/**
 * 判断身份证号码为18位时最后的验证位是否正确
 * @param a_idCard 身份证号码数组
 * @return
 */
function isTrueValidateCodeBy18IdCard(a_idCard) {
    var sum = 0;                             // 声明加权求和变量
    if (a_idCard[17].toLowerCase() == 'x') {
        a_idCard[17] = 10;                    // 将最后位为x的验证码替换为10方便后续操作
    }
    for ( var i = 0; i < 17; i++) {
        sum += Wi[i] * a_idCard[i];            // 加权求和
    }
    valCodePosition = sum % 11;                // 得到验证码所位置
    if (a_idCard[17] == ValideCode[valCodePosition]) {
        return true;
    } else {
        return false;
    }
}
/**
 * 验证18位数身份证号码中的生日是否是有效生日
 * @param idCard 18位书身份证字符串
 * @return
 */
function isValidityBrithBy18IdCard(idCard18){
    var year =  idCard18.substring(6,10);
    var month = idCard18.substring(10,12);
    var day = idCard18.substring(12,14);
    var temp_date = new Date(year,parseFloat(month)-1,parseFloat(day));
    // 这里用getFullYear()获取年份，避免千年虫问题
    if(temp_date.getFullYear()!=parseFloat(year)
        ||temp_date.getMonth()!=parseFloat(month)-1
        ||temp_date.getDate()!=parseFloat(day)){
        return false;
    }else{
        return true;
    }
}
/**
 * 验证15位数身份证号码中的生日是否是有效生日
 * @param idCard15 15位书身份证字符串
 * @return
 */
function isValidityBrithBy15IdCard(idCard15){
    var year =  idCard15.substring(6,8);
    var month = idCard15.substring(8,10);
    var day = idCard15.substring(10,12);
    var temp_date = new Date(year,parseFloat(month)-1,parseFloat(day));
    // 对于老身份证中的你年龄则不需考虑千年虫问题而使用getYear()方法
    if(temp_date.getYear()!=parseFloat(year)
        ||temp_date.getMonth()!=parseFloat(month)-1
        ||temp_date.getDate()!=parseFloat(day)){
        return false;
    }else{
        return true;
    }
}
//去掉字符串头尾空格
function trim(str) {
    return str.replace(/(^\s*)|(\s*$)/g, "");
}