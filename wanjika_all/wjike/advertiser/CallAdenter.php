<?php
include_once(dirname(__FILE__) . '/../util/Config.php');
include_once(dirname(__FILE__) . "/Adenter.php");

	/**
	 * 广告入口类
	 *
	 * ==============================================================================================================================================
	 * 作用：
	 * 		接收亿起发相关参数，之后调用AdEnter#jump($src,$channel,$campagin_id,$yiqifa_wi,$target_url}方法。
	 * 
	 * ==============================================================================================================================================
     * @auther lsj
	 * @see Adenter 
	 * @version 0.2
	 */
	
	$yqf_src = $_GET ['source'];
	$yqf_channel = $_GET ['channel'];
	$yqf_cid = $_GET ['cid'];
	$yqf_wi = $_GET ['wi'];
	$target_url = $_GET ['target'];

	$write_cookie = new Adenter();
	$write_cookie->jump ( $yqf_src, $yqf_channel, $yqf_cid, $yqf_wi, $target_url );
?>