<?php
/**
 * ECSHOP 短信模块语言文件
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liubo $
 * $Id: sms.php 17217 2011-01-19 06:29:08Z liubo $
*/

/* 发送短信 */
$_LANG['integral'] = '按会员积分发送短消息';
$_LANG['user_rand'] = '按用户等级发送短消息';
$_LANG['msg'] = '消息内容';
$_LANG['msg_notice'] = '最长70字符';
$_LANG['user_list'] = '全体会员';
$_LANG['please_select'] = '请选择会员等级';

$_LANG['send_ok'] = '恭喜，您的短信发送已提交，已向您的邮箱发送结果，请注意查收！';
$_LANG['send_mail_error'] = '恭喜，您的短信发送已提交，但向您的邮箱发送结果失败。失败信息为:';
$_LANG['send_sms_error'] = '对不起，在发送短信过程中发生错误，接收短信的号码列表为空。';

/* 客户端JS语言项 */
$_LANG['js_languages']['send_empty_error'] = '发送积分范围与发送等级至少填写一项！';
$_LANG['js_languages']['content_empty_error'] = '发送内容不能为空！';
$_LANG['js_languages']['content_size_error'] = '发送内容不能超过70字！';
?>