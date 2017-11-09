<?php

/**
 *导出用户列表
 *
 *Author qihua
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');

/*------------------------------------------------------ */
//-- 用户帐号列表
/*------------------------------------------------------ */

/* 检查权限 */
    admin_priv('users_manage');
    
	$file_name = "user_list_". date ("Ymd") . ".csv";
	$str_down = "编号,会员名称,邮件地址,是否已验证,可用资金,冻结资金,等级积分,消费积分,注册日期,是否导入";
	$str_down = mb_convert_encoding($str_down, "gb2312", "UTF-8");
    
	$start = 0;
	$step = 20000;
	do
	{
	     $sql = "SELECT user_id, user_name, email, is_validated, user_money, frozen_money, rank_points, pay_points, reg_time ".
	                " FROM " . $GLOBALS['ecs']->table('users') . " ORDER BY user_id LIMIT $start, $step";
	     
	     $start += $step;

	     $user_list = $GLOBALS['db']->getAll($sql);
	
	     foreach ($user_list as $key => $val)
	     {
			$reg_time = local_date('Y-m-d H:i:s', $val['add_time']);
			$is_validated = $val['is_validated'] ? mb_convert_encoding('是', "gb2312", "UTF-8") : mb_convert_encoding('否', "gb2312", "UTF-8");
			$user_name = str_replace("\r", '', $val['user_name']);
			$email = str_replace("\r", '', $val['email']);
			$is_patch = ($email == $user_name . "@139.com") ? mb_convert_encoding('是', "gb2312", "UTF-8") : mb_convert_encoding('否', "gb2312", "UTF-8");
			
			$str_down .= "\n" . $val['user_id'] . "," . mb_convert_encoding($user_name, "gb2312", "UTF-8") . ',' . 
								$email . "," . $is_validated . "," . $val['user_money'] . ',' . $val['frozen_money'] . "," . 
								$val['rank_points'] . ',' . $val['pay_points'] . "," . $reg_time . "," . $is_patch;
		}
	}
	while (count($user_list) >= $step);
	
	header('Content-Description: File Transfer');
	header('Content-Type: application/vnd.ms-excel ; charset=utf8');
	header('Content-Disposition: attachment; filename=' . $file_name);
	header('Expires: 0');
	header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
	header('Pragma: public');
	header('Content-Length: ' . strlen($str_down));
	
	ob_clean();
	flush();

	echo $str_down;
?>