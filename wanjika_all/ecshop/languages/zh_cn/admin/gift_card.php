<?php

/**
 * ECSHOP 礼品卡管理
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liubo $
 * $Id: gift_card.php 17217 2011-01-19 06:29:08Z liubo $
*/

/*------------------------------------------------------ */
//-- 卡片信息
/*------------------------------------------------------ */
$_LANG['rule_attr_id']  = 217;
$_LANG['passlength_attr_id']  = 218;
$_LANG['validtime_attr_id']  = 220;
$_LANG['money_attr_id']  = 221;
$_LANG['use_status']['1']  = '未使用';
$_LANG['use_status']['2']  = '已发放';
$_LANG['use_status']['3']  = '已使用';

$_LANG['card_status']['1']  = '正常';
$_LANG['card_status']['2']  = '冻结';
$_LANG['card_status']['3']  = '作废';
$_LANG['card_status']['4']  = '已合并';

$_LANG['gift_card_list'] = '虚拟商品列表'; 
$_LANG['lab_goods_name'] = '商品名称';
$_LANG['replenish'] = '补货';
$_LANG['lab_card_id'] = '编号';
$_LANG['lab_card_sn'] = '卡片序号';
$_LANG['lab_card_password'] = '卡片密码';
$_LANG['lab_card_money'] = '卡片金额';
$_LANG['lab_leave_money'] = '卡片剩余金额';
$_LANG['lab_end_date'] = '截至使用日期';
$_LANG['lab_use_status'] = '使用状态';
$_LANG['lab_card_status'] = '状态';
$_LANG['lab_patch_id'] = '批次号';
$_LANG['lab_goods_id'] = '商品编号';
$_LANG['lab_order_sn'] = '订单号';
$_LANG['action_success'] = '操作成功';
$_LANG['action_fail'] = '操作失败';
$_LANG['card'] = '卡片列表';

$_LANG['batch_card_add'] = '批量添加补货';
$_LANG['batch_card_merge'] = '批量合并礼品卡';
$_LANG['download_file'] = '下载批量CSV文件';
$_LANG['separator'] = '分隔符';
$_LANG['uploadfile'] = '上传文件';
$_LANG['patchNum'] = '批量生成的数量';
$_LANG['validDate'] = '有效期';
$_LANG['sql_error'] = '第 %s 条信息出错：<br /> ';

/* 提示信息 */
$_LANG['replenish_no_goods_id'] = '缺少商品ID参数，无法进行补货操作';
$_LANG['replenish_no_get_goods_name'] = '商品ID参数有误，无法获取商品名';
$_LANG['drop_card_success'] = '该记录已成功删除';
$_LANG['batch_drop'] = '批量删除';
$_LANG['drop_card_confirm'] = '你确定要删除该记录吗？';
$_LANG['card_sn_exist'] = '卡片序号 %s 已经存在，请重新输入';
$_LANG['go_list'] = '返回补货列表';
$_LANG['continue_add'] = '继续补货';
$_LANG['uploadfile_fail'] = '文件上传失败';
$_LANG['not_set_patchnum'] = '未指定礼品卡数量';
$_LANG['not_set_merge_param'] = '请选择合并订单';
$_LANG['batch_card_add_ok'] = '已成功添加了 %s 条补货信息';
$_LANG['batch_card_merge_ok'] = '已成功合并了 %s 条礼品卡';
$_LANG['batch_card_delay_ok'] = '已成功延期了 %s 条礼品卡';
$_LANG['merge_confirm'] = '你确定要合并这些卡片吗？';
$_LANG['down_confirm'] = '你确定要导出这些卡片吗？';

$_LANG['delay_confirm'] = '确定要延迟这些卡片的过期时间吗？';
$_LANG['delay'] = '延迟';

$_LANG['js_languages']['no_card_sn'] = '卡片序号和卡片密码不能都为空。';
$_LANG['js_languages']['separator_not_null'] = '批量生成的数量不能为空';
$_LANG['js_languages']['uploadfile_not_null'] = '请指定礼品卡有效期';

$_LANG['use_help'] = '使用说明：' .
        '<ol>' .
          '<li>礼品卡批量生成规则在对应礼品卡商品的属性中选择配置<br />' .
              '只需要指定批量生成的礼品卡数量和失效时间即可<br />'.
              '如果未明确指定有效期，则采用商品属性中默认设置<br />'.
        '</ol>';

/*------------------------------------------------------ */
//-- 改变加密串
/*------------------------------------------------------ */

$_LANG['gift_card_change'] = '更改加密串';
$_LANG['user_guide'] = '使用说明：' .
        '<ol>' .
          '<li>加密串是在加密虚拟卡类商品的卡号和密码时使用的</li>' .
          '<li>加密串保存在文件 data/config.php 中，对应的常量是 AUTH_KEY</li>' .
          '<li>如果要更改加密串在下面的文本框中输入原加密串和新加密串，点\'确定\'按钮后即可</li>' .
        '</ol>';
$_LANG['label_old_string'] = '原加密串';
$_LANG['label_new_string'] = '新加密串';

$_LANG['invalid_old_string'] = '原加密串不正确';
$_LANG['invalid_new_string'] = '新加密串不正确';
$_LANG['change_key_ok'] = '更改加密串成功';
$_LANG['same_string'] = '新加密串跟原加密串相同';

$_LANG['update_log'] = '更新记录';
$_LANG['old_stat'] = '总共有记录 %s 条。已使用新串加密的记录有 %s 条，使用原串加密（待更新）的记录有 %s 条，使用未知串加密的记录有 %s 条。';
$_LANG['new_stat'] = '<strong>更新完毕</strong>，现在使用新串加密的记录有 %s 条，使用未知串加密的记录有 %s 条。';
$_LANG['update_error'] = '更新过程中出错：%s';
$_LANG['js_languages']['updating_info'] = '<strong>正在更新</strong>（每次 100 条记录）';
$_LANG['js_languages']['updated_info'] = '<strong>已更新</strong> <span id=\"updated\">0</span> 条记录。';
?>