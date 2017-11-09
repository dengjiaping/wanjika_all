<!-- $Id: sms_send_ui.htm 16697 2009-09-24 03:57:47Z liuhui $ -->
<?php echo $this->fetch('pageheader.htm'); ?>

<div class="main-div" id="sms-send">
	<form method="POST" action="sms.php?act=send_template_sms_submit" name="sms-send-form" onsubmit="return validate();">
		<table >
			<tr>
				<td class="label"><?php echo $this->_var['lang']['phone']; ?>:</td>
				<td><input name="send_num" type="text" size="35" /></td>
			</tr>
			<tr>
				<td class="label"><?php echo $this->_var['lang']['choose_template']; ?>:</td>
				<td>
					<input type="radio" name="template_id" value="91000249" /><b>测试模板</b><span style="margin-left:20px;">万集客测试模板，测试码：{code}</span><span style="margin-left:20px;"><b>模板参数</b>：code=xxxxx（把xxxxx换成具体值）</span><br />
					<input type="radio" name="template_id" value="91000262" /><b>万集客购卡短信1</b><span style="margin-left:20px;">尊敬的用户，您购买的{card_name}面值{card_val}，卡号：{card_sn}密码：{card_pass}有效期：{card_sdate}至{card_edate}。【万集客】</span><span style="margin-left:20px;"><b>模板参数</b>：card_name=xxxxx&card_val=xxxxx&card_sn=xxxxx&card_pass=xxxxx&card_sdate=xxxxx&card_edate=xxxxx（把xxxxx换成具体值）</span><br />
					<input type="radio" name="template_id" value="91000263" /><b>万集卡购卡短信1</b><span style="margin-left:20px;">尊敬的用户，您购买的{card_name}面值{card_val}，卡号：{card_sn}密码：{card_pass}有效期：{card_sdate}至{card_edate}。【万集卡】</span><span style="margin-left:20px;"><b>模板参数</b>：card_name=xxxxx&card_val=xxxxx&card_sn=xxxxx&card_pass=xxxxx&card_sdate=xxxxx&card_edate=xxxxx（把xxxxx换成具体值）</span><br />
					<input type="radio" name="template_id" value="91000264" /><b>万集客购卡短信2</b><span style="margin-left:20px;">尊敬的用户，您购买的{card_name}，卡号：{card_sn}密码：{card_pass}有效期：{card_sdate}至{card_edate}。【万集客】</span><span style="margin-left:20px;"><b>模板参数</b>：card_name=xxxxx&card_sn=xxxxx&card_pass=xxxxx&card_sdate=xxxxx&card_edate=xxxxx（把xxxxx换成具体值）</span><br />
					<input type="radio" name="template_id" value="91000265" /><b>万集卡购卡短信2</b><span style="margin-left:20px;">尊敬的用户，您购买的{card_name}，卡号：{card_sn}密码：{card_pass}有效期：{card_sdate}至{card_edate}。【万集卡】</span><span style="margin-left:20px;"><b>模板参数</b>：card_name=xxxxx&card_sn=xxxxx&card_pass=xxxxx&card_sdate=xxxxx&card_edate=xxxxx（把xxxxx换成具体值）</span><br />
				</td>
			</tr>
			<tr>
				<td class="label"><?php echo $this->_var['lang']['template_param']; ?>:</td>
				<td><textarea name="template_param" rows="6" cols="32"></textarea><br /><?php echo $this->_var['lang']['template_param_format']; ?></td>
			</tr>
			<tr>
				<td class="label">
					<input type="submit" name="submit" value="<?php echo $this->_var['lang']['button_submit']; ?>" class="button" />
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
  var msg = f.elements['template_id'].checked;

  if(phone=='' || msg==false)
	{
		alert("phone or sms error!!");
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