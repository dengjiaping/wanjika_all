<?php

/**
 * ECSHOP 站点地图生成程序
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liubo $
 * $Id: sitemap.php 17217 2011-01-19 06:29:08Z liubo $
*/

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');

$act = trim($_REQUEST['act']);
if ($act == 'validate')			//验证院线码
{
	admin_priv('moviecode_validate');
	
	$sql = "SELECT * FROM " . $ecs->table('moviecode_applier');
	$list = $db->getAll($sql);
	$applier_list = array();
	if (!empty($list))
	{
		foreach ($list as $info)
		{
			$admin_ids_arr = array();
			if (!empty($info['admin_ids']))
			{
				$admin_ids_arr = explode(',', $info['admin_ids']);
			}
			if ($_SESSION['admin_id'] == 1 || $info['admin_ids'] == '' || in_array($_SESSION['admin_id'], $admin_ids_arr))
			{
				$applier_list[] = array('applier_id' => $info['applier_id'], 'applier_name' => $info['applier_name']);
			}
		}
	}
	
    assign_query_info();
    $smarty->assign('ur_here', '验证院线码');
    $smarty->assign('applier_list', $applier_list);
    $smarty->assign('status', '');
    $smarty->display('movie_code_validate.htm');
}
else if ($act == 'validate_check')
{
	admin_priv('moviecode_validate');
	
	$applier_id = intval($_REQUEST['applier_id']);
	$code_sn = trim($_REQUEST['code']);
	
	if ($applier_id == 0)
	{
		echo "请选择供应商！";
		exit;
	}
	
	if (empty($code_sn))
	{
		echo '请输入码！';
		exit;
	}

	$sql = "SELECT * FROM " . $ecs->table('moviecode_applier');
	$list = $db->getAll($sql);
	$applier_name = '';
	$applier_list = array();
	if (!empty($list))
	{
		foreach ($list as $info)
		{
			if ($info['applier_id'] == $applier_id)
			{
				$applier_name = $info['applier_name'];
			}
			$admin_ids_arr = array();
			if (!empty($info['admin_ids']))
			{
				$admin_ids_arr = explode(',', $info['admin_ids']);
			}
			if ($_SESSION['admin_id'] == 1 || $info['admin_ids'] == '' || in_array($_SESSION['admin_id'], $admin_ids_arr))
			{
				$applier_list[] = array('applier_id' => $info['applier_id'], 'applier_name' => $info['applier_name']);
			}
		}
	}
	
	$sql = "SELECT * FROM " . $ecs->table('moviecode') . "WHERE applier_id = '$applier_id' AND code_sn = '$code_sn'";
	$info = $db->getRow($sql);
	$status = 'notexist';
	if (!empty($info))
	{
		$status = 'exist';
	}
	$isvalid = true;
	if ($info['over_date'] != 0 && time() >= $info['over_date'])
	{
		$isvalid = false;
	}
	if ($info['isvalid'] == 0 && $info['validate_date'] != 0)
	{
		$isvalid = false;
	}
	
    assign_query_info();
    $smarty->assign('ur_here', '验证院线码');
    $smarty->assign('applier_list', $applier_list);
    $smarty->assign('applier_id', $applier_id);
    $smarty->assign('applier_name', $applier_name);
    $smarty->assign('code_sn', $code_sn);
    $smarty->assign('isvalid', ($isvalid ? '是' : '否'));
    $smarty->assign('mobile', $info['mobile']);
    $smarty->assign('over_date', ($info['over_date'] != 0 ? date('Y-m-d H:i:s', $info['over_date']) : ''));
    $smarty->assign('validate_date', ($info['validate_date'] != 0 ? date('Y-m-d H:i:s', $info['validate_date']) : ''));
    $smarty->assign('status', $status);
    $smarty->display('movie_code_validate.htm');
}
else if ($act == 'validate_submit')
{
	admin_priv('moviecode_validate');
	
	$applier_id = intval($_REQUEST['applier_id']);
	$code_sn = trim($_REQUEST['code_sn']);
	
	$sql = "UPDATE " . $ecs->table('moviecode') . " SET isvalid = 0, validate_date = " . time() . ", validate_admin_id = '{$_SESSION['admin_id']}' WHERE applier_id = '$applier_id' AND code_sn = '$code_sn'";
	$db->query($sql);

	/* 提示信息 */
    $link = array();
    $link[0]['text'] = $_LANG['continus_add'];
    $link[0]['href'] = 'moviecode.php?act=validate';

    $link[1]['text'] = $_LANG['back_list'];
    $link[1]['href'] = 'moviecode.php?act=validate';

    sys_msg('院线码验证完成',0, $link);
}
else if ($act == 'code_list')
{
	admin_priv('moviecode_code_list');
	
	$sql = "SELECT * FROM " . $ecs->table('moviecode_applier');
	$list = $db->getAll($sql);
	$applier_list = array();
	if (!empty($list))
	{
		foreach ($list as $info)
		{
			$applier_list[$info['applier_id']] = $info['applier_name'];
		}
	}
	 
	 $sql = "SELECT * FROM " . $ecs->table('moviecode');
	 $list = $db->getAll($sql);
	 if (!empty($list))
	 {
	 	foreach ($list as $key => $info)
	 	{
	 		$list[$key]['add_date'] = local_date('Y-m-d H:i:s', $info ['add_date']);
	 		$list[$key]['over_date'] = local_date('Y-m-d H:i:s', $info ['over_date']) ? local_date('Y-m-d H:i:s', $info ['over_date']) : '——';
	 		$list[$key]['applier_name'] = $applier_list[$info['applier_id']];
	 		$list[$key]['isvalid'] = $info['isvalid'] ? '有效' : '无效';
	 	}
	 }

	assign_query_info();
	$smarty->assign('full_page',   1);
	$smarty->assign('code_list', $list);
	 $smarty->assign('ur_here',     '院线码列表');
    $smarty->assign('action_link', array('href'=>'moviecode.php?act=add', 'text' => '上传院线码'));
	$smarty->display('moviecode_code_list.htm');
}
else if ($act == 'owner_list')
{
	admin_priv('moviecode_owner_list');
	 
	$sql = "SELECT * FROM " . $ecs->table('moviecode_applier');
	$list = $db->getAll($sql);

	assign_query_info();
	$smarty->assign('full_page',   1);
	$smarty->assign('ur_here',     '院线码供应商列表');
	$smarty->assign('applier_list', $list);
    $smarty->assign('action_link', array('href'=>'moviecode.php?act=add', 'text' => '新增院线供应商'));
	$smarty->display('moviecode_applier_list.htm');
}
else if ($act == 'upload')		//上传院线码
{
	admin_priv('moviecode_upload');
	
	$sql = "SELECT * FROM " . $ecs->table('moviecode_applier');
	$list = $db->getAll($sql);
	$applier_list = array();
	if (!empty($list))
	{
		foreach ($list as $key => $info)
	 	{
	 		$applier_list[] = array('applier_id' => $info['applier_id'], 'applier_name' => $info['applier_name']);
	 	}
	}

	assign_query_info();
	$smarty->assign('full_page',   1);
	$smarty->assign('ur_here',     '上传院线码');
	$smarty->assign('applier_list', $applier_list);
	$smarty->display('movie_code_upload.htm');
}
else if ($act == 'upload_submit')
{
	admin_priv('moviecode_upload');
	
	$applier_id = intval($_REQUEST['applier_id']);
	$over_date = trim($_REQUEST['over_date']);
	$codes = trim($_REQUEST['codes']);
	
	if ($applier_id == 0)
	{
		echo "请选择供应商！";
		exit;
	}
	
	if (empty($codes))
	{
		echo '请输入码！';
		exit;
	}
	
	if (!empty($over_date) && strtotime($over_date) == 0)
	{
		echo '日期格式不正确！';
		exit;
	}
	
	$over_date = strtotime($over_date);
	$now = time();
	$codesArr = explode("\n", $codes);
	
	$sql = "SELECT code_sn FROM " . $ecs->table('moviecode') . " WHERE applier_id = '{$applier_id}' AND code_sn " . db_create_in($codesArr);
	$res = $db->getAll($sql);
	if (!empty($res))
	{
		$doubleCodes = '';
		foreach ($res as $item)
		{
			$doubleCodes .= $item['code_sn'] . '；';
		}
		echo '上传失败！以下码重复：', $doubleCodes . '请删除重复的码重新上传。';
		exit;
	}
	
	$len = count($codesArr);
	$count = 0;
	$valuesStr = '';
	for ($i = 0; $i < $len; $i++)
	{
		$code = trim($codesArr[$i]);
		$count++;
		if ($count <= 20 && $count < $len)
		{
			$valuesStr .= "('{$code}', '$applier_id', '{$_SESSION['admin_id']}', '$over_date', '$now'),";
			continue;
		}

		$valuesStr = substr($valuesStr, 0, -1);
		$sql = "INSERT INTO " . $ecs->table('moviecode') . 
				" ( `code_sn`, `applier_id`, `add_admin_id`, `over_date`, `add_date`) " .
				" values $valuesStr";
		
		$db->query($sql);
	}
	
	 /* 提示信息 */
    $link = array();
    $link[0]['text'] = $_LANG['continus_add'];
    $link[0]['href'] = 'moviecode.php?act=upload';

    $link[1]['text'] = $_LANG['back_list'];
    $link[1]['href'] = 'moviecode.php?act=code_list';

    sys_msg($_LANG['add'] . "&nbsp;" . '院线码' . "&nbsp;" . $_LANG['attradd_succed'],0, $link);
}
else if ($act == 'add')				//新增院线
{
   admin_priv('moviecode_add');

	assign_query_info();
	
	$smarty->assign('act', 'insert');
	$smarty->assign('actstr', '添加');
	$smarty->display('movie_applier_info.htm');
}
else if ($act == 'insert')			//新增院线表单提交
{
	admin_priv('moviecode_add');
	
	$applier_name = trim($_REQUEST['applier_name']);
	$admin_ids = trim($_REQUEST['admin_ids']);
	if (empty($applier_name))
	{
		echo 'applier name can not empty!';
		exit;
	}
	
	$sql = 'INSERT INTO ' . $ecs->table('moviecode_applier') .
	"(`applier_name`, `add_admin_id`, `add_date`, `admin_ids`)" . 
	" VALUES ('$applier_name', '{$_SESSION['admin_id']}', " . time() . ", '{$admin_ids}')";
	$res = $db->query($sql);
    
    /* 提示信息 */
    $link = array();
    $link[0]['text'] = $_LANG['continus_add'];
    $link[0]['href'] = 'moviecode.php?act=add';

    $link[1]['text'] = $_LANG['back_list'];
    $link[1]['href'] = 'moviecode.php?act=applier_list';

    sys_msg($_LANG['add'] . "&nbsp;" .$applier_name . "&nbsp;" . $_LANG['attradd_succed'],0, $link);
}
else if ($act == 'edit')
{
	admin_priv('moviecode_add');
	
	$applier_id = intval($_REQUEST['applier_id']);
	
	$sql = "SELECT * FROM " . $ecs->table('moviecode_applier') . " WHERE applier_id = '$applier_id'";
	$info = $db->getRow($sql);
	
	assign_query_info();
	
	$smarty->assign('act', 'update');
	$smarty->assign('actstr', '修改');
	$smarty->assign('applier_id', $info['applier_id']);
	$smarty->assign('applier_name', $info['applier_name']);
	$smarty->assign('admin_ids', $info['admin_ids']);
	$smarty->display('movie_applier_info.htm');
}
else if ($act == 'update')
{
	admin_priv('moviecode_add');
	
	$applier_id = intval($_REQUEST['applier_id']);
	$applier_name = trim($_REQUEST['applier_name']);
	$admin_ids = trim($_REQUEST['admin_ids']);
	if (empty($applier_name))
	{
		echo '名字不能为空！';
		exit;
	}
	
	$sql = 'UPDATE ' . $ecs->table('moviecode_applier') .
	" SET applier_name = '$applier_name', admin_ids = '$admin_ids' WHERE applier_id = '$applier_id'";
	$res = $db->query($sql);
    
    /* 提示信息 */
    $link = array();
    $link[0]['text'] = $_LANG['continus_add'];
    $link[0]['href'] = 'moviecode.php?act=applier_list';

    $link[1]['text'] = $_LANG['back_list'];
    $link[1]['href'] = 'moviecode.php?act=applier_list';

    sys_msg('修改成功！',0, $link);
}
else if ($act == "send")
{
	admin_priv('moviecode_add');
	
	$code_id = intval($_REQUEST['code_id']);
	
	$sql = "SELECT * FROM " . $ecs->table('moviecode') . " WHERE code_id = '$code_id'";
	$info = $db->getRow($sql);
	
	$sql = "SELECT * FROM " . $ecs->table('moviecode_applier') . " WHERE applier_id = '{$info['applier_id']}'";
	$applier_info = $db->getRow($sql);
	
	assign_query_info();

	$smarty->assign('code_id', $info['code_id']);
	$smarty->assign('code_sn', $info['code_sn']);
	$smarty->assign('applier_name', $applier_info['applier_name']);
	$smarty->assign('mobile', $info['mobile']);
	$smarty->display('movie_code_send.htm');
}
else if ($act == 'send_submit')
{
	admin_priv('moviecode_add');
	
	$code_id = intval($_REQUEST['code_id']);
	$mobile = trim($_REQUEST['mobile']);
	
	if (empty($mobile))
	{
		echo '手机号不能为空！';
		exit;
	}
	
	$sql = 'UPDATE ' . $ecs->table('moviecode') .
	" SET mobile = '$mobile' WHERE code_id = '$code_id'";
	$res = $db->query($sql);
    
    /* 提示信息 */
    $link = array();
    $link[0]['text'] = $_LANG['continus_add'];
    $link[0]['href'] = 'moviecode.php?act=code_list';

    $link[1]['text'] = $_LANG['back_list'];
    $link[1]['href'] = 'moviecode.php?act=code_list';

    sys_msg('发放成功！',0, $link);
}

?>