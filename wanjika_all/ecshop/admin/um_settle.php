<?php

/**
 * ECSHOP 联动优势交易文件
 * $Author: xuan $
 * $Id: um_settle.php 17217 2014-04-14 06:29:08Z 
*/

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . 'includes/lib_order.php');
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/admin/statistic.php');
require_once(ROOT_PATH . 'includes/modules/payment/umpay.php');
$smarty->assign('lang', $_LANG);
if (isset($_REQUEST['act']) && $_REQUEST['act'] == 'query')
{
	
	$umpay = new umpay();
	//商户号
	$merId = "7145";
    if ($_REQUEST['umpay_area'] == 'shanghai')
    {
        $merId = '6844';
    }
    else if ($_REQUEST['umpay_area'] == 'other')
    {
        $merId = '6877';
    }
	//清算日期
	$settleDate = str_replace("-","",$_REQUEST['start_date']);
	//银行编号
	$gateId = "02";
	//版本
	$version = "3.0";
	//手机钱包商户接入规范3.1V的5.10组装字符串，生成签名
	$url = "merId=" . $merId . "&settleDate=" . $settleDate . "&version=" . $version;
	//签名
	$priv_key_file = ROOT_PATH."includes/7145_WanJiKe.key.pem";
    if ($_REQUEST['umpay_area'] == 'other')
    {
        $priv_key_file = ROOT_PATH."includes/6877_WanJiKe.key.pem";
    }
    else if ($_REQUEST['umpay_area'] == 'shanghai')
    {
        $priv_key_file = ROOT_PATH."includes/6844_WanJiKe.key.pem";
    }
	$sign=$umpay->ssl_sign($url,$priv_key_file);

	echo "<form style=\"display:none\"  name=\"transform\" action=\"http://payment.umpay.com/hfwebbusi/bill/settle.dl\" method=\"post\">
<h2><b>清算数据对帐信息：</b></h2>	
<TABLE width=\"100%\" >
<tr><td width=\"139\">SP平台代码：</td><td><input type=text NAME=\"merId\" value=\"$merId\"></td></tr>
<tr><td width=\"139\">清算日期</td><td><input type=text NAME=\"settleDate\" value=\"$settleDate\"></td></tr>
<tr><td width=\"139\">银行编号</td><td><input type=text NAME=\"gateId\" value=\"$gateId\"></td></tr>
<tr><td width=\"139\">版本号</td><td><input type=text NAME=\"version\" value=\"$version\" ></td></tr>
<tr><td width=\"139\">签名数据L</td><td><input type=text NAME=\"sign\" value=\"$sign\"></td></tr>

<tr><td width=\"139\"><input type=submit name=\"submitbutton\" value=\"取清算数据文件\" size=></td><td></td></tr>
</TABLE>
</form>
<script type=\"text/javascript\">
document.forms[\"transform\"].submit();
</script>
";

	exit;

}
/*------------------------------------------------------ */
//--商品明细列表
/*------------------------------------------------------ */
else
{
	/* 权限判断 */
	admin_priv('sale_order_stats');
	/* 时间参数 */
	if (!isset($_REQUEST['start_date']))
	{
		$start_date = local_strtotime('-1 days');
	}
	/* 赋值到模板 */
	$smarty->assign('ur_here',          $_LANG['sell_stats']);
	$smarty->assign('full_page',        1);
	$smarty->assign('start_date',       local_date('Y-m-d', $start_date));
	$smarty->assign('ur_here',      "清算文件");
	$smarty->assign('cfg_lang',     $_CFG['lang']);
	//	$smarty->assign('action_link',  array('text' => "下载交易文件",'href'=>'#download'));

	$smarty->display('um_settle.htm');
}
/*------------------------------------------------------ */
//--获取销售明细需要的函数
/*------------------------------------------------------ */
/**
 * 取得销售明细数据信息
 * @param   bool  $is_pagination  是否分页
 * @return  array   销售明细数据
 */
function get_sale_list($is_pagination = true){

	/* 时间参数 */
	$filter['start_date'] = empty($_REQUEST['start_date']) ? local_strtotime('-7 days') : local_strtotime($_REQUEST['start_date']);
	$filter['end_date'] = empty($_REQUEST['end_date']) ? local_strtotime('today') : local_strtotime($_REQUEST['end_date']);

	/* 查询数据的条件 */
	$where = " WHERE og.order_id = oi.order_id". order_query_sql('finished', 'oi.') .
	" AND oi.add_time >= '".$filter['start_date']."' AND oi.add_time < '" . ($filter['end_date'] + 86400) . "'";

	$sql = "SELECT COUNT(og.goods_id) FROM " .
	$GLOBALS['ecs']->table('order_info') . ' AS oi,'.
	$GLOBALS['ecs']->table('order_goods') . ' AS og '.
	$where;
	$filter['record_count'] = $GLOBALS['db']->getOne($sql);

	/* 分页大小 */
	$filter = page_and_size($filter);

	$sql = 'SELECT og.goods_id, og.goods_sn, og.goods_name, og.goods_number AS goods_num, og.goods_price '.
	'AS sales_price, oi.add_time AS sales_time, oi.order_id, oi.order_sn '.
	"FROM " . $GLOBALS['ecs']->table('order_goods')." AS og, ".$GLOBALS['ecs']->table('order_info')." AS oi ".
	$where. " ORDER BY sales_time DESC, goods_num DESC";
	if ($is_pagination)
	{
		$sql .= " LIMIT " . $filter['start'] . ', ' . $filter['page_size'];
	}

	$sale_list_data = $GLOBALS['db']->getAll($sql);

	foreach ($sale_list_data as $key => $item)
	{
		$sale_list_data[$key]['sales_price'] = price_format($sale_list_data[$key]['sales_price']);
		$sale_list_data[$key]['sales_time']  = local_date($GLOBALS['_CFG']['time_format'], $sale_list_data[$key]['sales_time']);
	}
	$arr = array('sale_list_data' => $sale_list_data, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
	return $arr;
}
?>