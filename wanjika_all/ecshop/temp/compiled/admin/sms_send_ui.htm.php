<!-- $Id: sms_send_ui.htm 16697 2009-09-24 03:57:47Z liuhui $ -->
<?php echo $this->fetch('pageheader.htm'); ?>

<div class="main-div" id="sms-send">
<form method="POST" action="sms.php?act=send_sms" name="sms-send-form" onsubmit="return validate();">
<table >
  <tr>
    <td class="label"><?php echo $this->_var['lang']['phone']; ?>:</td>
    <td><input name="send_num" type="text" size="35" />
        <?php echo $this->_var['lang']['phone_notice']; ?>
    </td>
  </tr>
    <tr>
    <td class="label">签名后缀:</td>
    <td><select name="signtype">
        <option value='1'>万集客</option>
        <option value='2'>万集卡</option>
        <option value='3'>12580商城</option>
        <option value='4'>C4U</option>
        </select></td>
  </tr>
  <tr>
    <td class="label"><?php echo $this->_var['lang']['msg']; ?>:</td>
    <td><textarea name="msg" rows="6" cols="32"></textarea></td>
  </tr>
  
  <tr>
    <td colspan="2" align="center">
      <input type="submit" name="submit" value="<?php echo $this->_var['lang']['button_submit']; ?>" class="button" />
      <input type="reset" value="<?php echo $this->_var['lang']['button_reset']; ?>" class="button" />
    </td>
  </tr>
</table>
</form>
</div>

<script type="text/javascript" language="JavaScript">
<!--

function  validate() {
  var f = document['sms-send-form'];
  var phone = f.elements['send_num'].value;
  var msg = f.elements['msg'].value;
  if(phone=='')
	{
		alert(send_num_error);
		return false;
	}
  else if(msg == '')
  {
      alert(content_empty_error);
      return false;
  }
	else
	{
	  return true;
	}
}

//-->
</script>
<?php echo $this->fetch('pagefooter.htm'); ?>