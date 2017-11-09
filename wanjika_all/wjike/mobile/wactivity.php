<?php

/**
 * ECSHOP 用户中心
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liubo $
 * $Id: user.php 16643 2009-09-08 07:02:13Z liubo $
*/

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
include_once(ROOT_PATH . 'includes/lib_order.php');
/* 载入语言文件 */
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/user.php');
$act = isset($_REQUEST['act']) ? $_REQUEST['act'] : '';
$weixin_url = "http://www.wjike.com/weixin/?s=/addon/IdouSpread/WxWeb/index/publicid/2/token//html";

if ($act == 'do_check')
{
	$user_name = !empty($_POST['username']) ? $_POST['username'] : '';
	$pwd = !empty($_POST['pwd']) ? $_POST['pwd'] : '';
    $openid = !empty($_POST['openid']) ? $_POST['openid'] : '';
	if (empty($user_name) || empty($pwd))
	{
        $location_url = "/mobile/wactivity.php?act=check&openid=$openid";
        show_message('账号或密码不能为空', '', "$location_url", 'error');
	}
	else
	{
		if ($user->check_user($user_name, $pwd) > 0)
		{
            if(is_user($openid)){
                show_message('验证通过', '', "$weixin_url", 'error');
            }
            if(has_user($user_name)){
                $location_url = "/mobile/wactivity.php?act=check&openid=$openid";
                show_message('该账号已被其他微信号认证，验证失败', '', "$location_url", 'error');
            }
            else
            {
                insert_weixin($openid,$user_name);
                show_message('验证通过', '', "$weixin_url", 'error');
            }
		}
		else
		{
            $location_url = "/mobile/wactivity.php?act=check&openid=$openid";
            show_message('账户名或密码错误，验证失败', '', "$location_url", 'error');
		}
	}
}
elseif ($act == 'check')
{
    if(is_user($_REQUEST['openid'])){
        show_message('验证通过', '', "$weixin_url", 'error');
    }
    $smarty->assign('openid', $_REQUEST['openid']);
    $smarty->display('wcheck.html');
}
/* 显示会员注册界面 */
elseif ($act == 'register')
{
	/* 取出注册扩展字段 */
	$sql = 'SELECT * FROM ' . $ecs->table('reg_fields') . ' WHERE type < 2 AND display = 1 ORDER BY dis_order, id';
	$extend_info_list = $db->getAll($sql);
	$smarty->assign('extend_info_list', $extend_info_list);

    /* 验证码相关设置 */
    if ((intval($_CFG['captcha']) & CAPTCHA_REGISTER) && gd_version() > 0)
    {
        $smarty->assign('enabled_captcha', 1);
        $smarty->assign('rand',            mt_rand());
    }
    /* 注册错误信息 */
    if(!empty($_SESSION['errormsg'])){
        $smarty->assign('errormsg', $_SESSION['errormsg']);
        $_SESSION['errormsg'] = '';
    }
	$smarty->assign('footer', get_footer());
    $smarty->assign('openid', $_REQUEST['openid']);
	$smarty->display('wregister.html');
}
/* 注册会员的处理 */
elseif ($act == 'act_register')
{
    if($_CFG['shop_reg_closed']){
        $smarty->assign('action',     'register');
        $smarty->assign('shop_reg_closed', $_CFG['shop_reg_closed']);
        $smarty->display('user_passport.dwt');
    }
    else{
        include_once(ROOT_PATH . 'includes/lib_passport.php');

        $username = isset($_POST['phone']) ? trim($_POST['phone']) : '';
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';
        $confirm_password = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';
        $vcode = isset($_POST['vcode']) ? trim($_POST['vcode']) : '';
        $email	= isset($_POST['email']) ? trim($_POST['email']) : '';
        $other['msn'] = isset($_POST['extend_field1']) ? $_POST['extend_field1'] : '';
        $other['qq'] = isset($_POST['extend_field2']) ? $_POST['extend_field2'] : '';
        $other['office_phone'] = isset($_POST['extend_field3']) ? $_POST['extend_field3'] : '';
        $other['home_phone'] = isset($_POST['extend_field4']) ? $_POST['extend_field4'] : '';
        $other['mobile_phone'] = isset($_POST['phone']) ? $_POST['phone'] : '';
        $sel_question = empty($_POST['sel_question']) ? '' : $_POST['sel_question'];
        $passwd_answer = isset($_POST['passwd_answer']) ? trim($_POST['passwd_answer']) : '';
        $openid = !empty($_POST['openid']) ? $_POST['openid'] : '';

        $back_act = isset($_POST['back_act']) ? trim($_POST['back_act']) : '';
        if (strlen($username) < 11)
        {
            $_SESSION['errormsg'] = '<span style="background-color: #ffa200;padding: 0px 6px;border-radius: 8px;color: #fff;margin-right: 4px;">!</span>输入手机号位数少于11位';
            ecs_header("Location:./wactivity.php?act=register&openid=$openid\n");
        }

        if (strlen($password) < 6)
        {
            $_SESSION['errormsg'] = '<span style="background-color: #ffa200;padding: 0px 6px;border-radius: 8px;color: #fff;margin-right: 4px;">!</span>密码长度不能少于6位。';
            ecs_header("Location:./wactivity.php?act=register&openid=$openid\n");
        }

        if (strpos($password, ' ') > 0)
        {
            $_SESSION['errormsg'] = '<span style="background-color: #ffa200;padding: 0px 6px;border-radius: 8px;color: #fff;margin-right: 4px;">!</span>'.$_LANG['passwd_balnk'];
            ecs_header("Location:./wactivity.php?act=register&openid=$openid\n");
        }

        if (strlen($vcode) < 6)
        {
            $_SESSION['errormsg'] = '<span style="background-color: #ffa200;padding: 0px 6px;border-radius: 8px;color: #fff;margin-right: 4px;">!</span>手机验证码错误';
            ecs_header("Location:./wactivity.php?act=register&openid=$openid\n");
        }

        if ($password != $confirm_password)
        {
            $_SESSION['errormsg'] = '<span style="background-color: #ffa200;padding: 0px 6px;border-radius: 8px;color: #fff;margin-right: 4px;">!</span>两次输入密码不一致';
            ecs_header("Location:./wactivity.php?act=register&openid=$openid\n");
        }

        /* 手机验证码检查 */
        $vcode_str = $username.$vcode;
        if($_SESSION['vcode_str'] != $vcode_str){
            $_SESSION['errormsg'] = '<span style="background-color: #ffa200;padding: 0px 6px;border-radius: 8px;color: #fff;margin-right: 4px;">!</span>手机验证码错误';
            ecs_header("Location:./wactivity.php?act=register&openid=$openid\n");
        }
        else{
            unset($_SESSION['vcode_str']);
        }

        if (m_register($username, $password, $email, $other) !== false)
        {
            /*把新注册用户的扩展信息插入数据库*/
            $sql = 'SELECT id FROM ' . $ecs->table('reg_fields') . ' WHERE type = 0 AND display = 1 ORDER BY dis_order, id';   //读出所有自定义扩展字段的id
            $fields_arr = $db->getAll($sql);

            $extend_field_str = '';	//生成扩展字段的内容字符串
            foreach ($fields_arr AS $val)
            {
                $extend_field_index = 'extend_field' . $val['id'];
                if(!empty($_POST[$extend_field_index]))
                {
                    $temp_field_content = strlen($_POST[$extend_field_index]) > 100 ? mb_substr($_POST[$extend_field_index], 0, 99) : $_POST[$extend_field_index];
                    $extend_field_str .= " ('" . $_SESSION['user_id'] . "', '" . $val['id'] . "', '" . compile_str($temp_field_content) . "'),";
                }
            }
            $extend_field_str = substr($extend_field_str, 0, -1);

            if ($extend_field_str)	  //插入注册扩展数据
            {
                $sql = 'INSERT INTO '. $ecs->table('reg_extend_info') . ' (`user_id`, `reg_field_id`, `content`) VALUES' . $extend_field_str;
                $db->query($sql);
            }

            /* 写入密码提示问题和答案 */
            if (!empty($passwd_answer) && !empty($sel_question))
            {
                $sql = 'UPDATE ' . $ecs->table('users') . " SET `passwd_question`='$sel_question', `passwd_answer`='$passwd_answer'  WHERE `user_id`='" . $_SESSION['user_id'] . "'";
                $db->query($sql);
            }

            $ucdata = empty($user->ucdata)? "" : $user->ucdata;
            //注册成功直接成为体验会员
            $members_deadline = gmtime() + (90 * 24 * 3600);
            $time = gmtime();
            $msql  = "UPDATE " .$GLOBALS['ecs']->table('users'). " SET is_members='1', members_deadline='$members_deadline'".
                " WHERE user_id='" . $_SESSION['user_id'] . "'";
            $result = $GLOBALS['db']->query($msql);
            if($result){
                $sql = "INSERT INTO " .$GLOBALS['ecs']->table('freemembers_log'). " (user_id, has_get, add_time)" .
                    "VALUES ('$_SESSION[user_id]', '1', '$time')";

                $GLOBALS['db']->query($sql);
            }
            //新注册用户发放优惠券
            send_bonuns($_SESSION['user_id']);

            insert_weixin($openid,$username);
            show_message('注册成功，验证通过', '', "$weixin_url", 'error');
        }
        else
        {
            $location_url = "/mobile/wactivity.php?act=register&openid=$openid";
            show_message('注册失败！', '', "$location_url", 'error');
        }
    }
}

elseif ($act == 'check_user')
{
    include_once(ROOT_PATH . 'includes/cls_json.php');

    $json = new JSON;
    $openid = $_REQUEST['openid'];

    $r['status'] = is_user($openid);

    die($json->encode($r));
}

else
{
    if(is_user($_REQUEST['openid'])){
        show_message('验证通过', '', "$weixin_url", 'error');
    }
    $smarty->assign('openid', $_REQUEST['openid']);
    $smarty->display('wcheckusers.html');
}

/**
 * 手机注册
 */
function m_register($username, $password, $email, $other = array())
{
	/* 检查username */
	if (empty($username))
	{
        show_regmessage('用户名不能为空！', '', 'user.php?act=register', 'error');
		return false;
	}
	if (preg_match('/\'\/^\\s*$|^c:\\\\con\\\\con$|[%,\\*\\"\\s\\t\\<\\>\\&\'\\\\]/', $username))
	{
        show_regmessage('用户名错误！', '', 'user.php?act=register', 'error');
		return false;
	}

	/* 检查是否和管理员重名 */
	if (admin_registered($username))
	{
        show_regmessage('此用户已存在！', '', 'user.php?act=register', 'error');
		return false;
	}

	if (!$GLOBALS['user']->add_user($username, $password, $email))
	{
        if ($GLOBALS['user']->error == ERR_INVALID_USERNAME)
        {
            $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['username_invalid'], $username));
        }
        elseif ($GLOBALS['user']->error == ERR_USERNAME_NOT_ALLOW)
        {
            $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['username_not_allow'], $username));
        }
        elseif ($GLOBALS['user']->error == ERR_USERNAME_EXISTS)
        {
            $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['username_exist'], $username));
        }
        elseif ($GLOBALS['user']->error == ERR_INVALID_EMAIL)
        {
            $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['email_invalid'], $email));
        }
        elseif ($GLOBALS['user']->error == ERR_EMAIL_NOT_ALLOW)
        {
            $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['email_not_allow'], $email));
        }
        elseif ($GLOBALS['user']->error == ERR_EMAIL_EXISTS)
        {
            $GLOBALS['err']->add(sprintf($GLOBALS['_LANG']['email_exist'], $email));
        }
        else
        {
            $GLOBALS['err']->add('UNKNOWN ERROR!');
        }

        show_regmessage($GLOBALS['err']->_message[0], '', 'user.php?act=register', 'error');
		//注册失败
		return false;
	}
	else
	{
		//注册成功

		/* 设置成登录状态 */
		$GLOBALS['user']->set_session($username);
		$GLOBALS['user']->set_cookie($username);

	}

		//定义other合法的变量数组
		$other_key_array = array('msn', 'qq', 'office_phone', 'home_phone', 'mobile_phone');
		$update_data['reg_time'] = local_strtotime(local_date('Y-m-d H:i:s'));
		if ($other)
		{
			foreach ($other as $key=>$val)
			{
				//删除非法key值
				if (!in_array($key, $other_key_array))
				{
					unset($other[$key]);
				}
				else
				{
					$other[$key] =  htmlspecialchars(trim($val)); //防止用户输入javascript代码
				}
			}
			$update_data = array_merge($update_data, $other);
		}
		$GLOBALS['db']->autoExecute($GLOBALS['ecs']->table('users'), $update_data, 'UPDATE', 'user_id = ' . $_SESSION['user_id']);

		update_user_info();	  // 更新用户信息

		return true;

}

function has_user($user_name)
{
    if(!empty($user_name)){
        $sql="SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('wuser_check') .  " WHERE user_name='$user_name' ";
        $res = $GLOBALS['db']->getOne($sql);

        if($res > 0){
            return true;
        }
    }

    return  false;
}

function is_user($openid)
{
    if(!empty($openid)){
        $sql="SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('wuser_check') .  " WHERE openid='$openid' ";
        $res = $GLOBALS['db']->getOne($sql);

        if($res > 0){
            return true;
        }
    }

    return  false;
}

function insert_weixin($openid,$user_name)
{
    $sql = "INSERT INTO " .$GLOBALS['ecs']->table('wuser_check'). " (openid,user_name)" .
        " VALUES ('$openid', '$user_name')";
    return $GLOBALS['db']->query($sql);
}

function show_regmessage($content, $links = '', $hrefs = '', $type = 'info', $auto_redirect = true)
{
    assign_template();

    $msg['content'] = $content;
    if (is_array($links) && is_array($hrefs))
    {
        if (!empty($links) && count($links) == count($hrefs))
        {
            foreach($links as $key =>$val)
            {
                $msg['url_info'][$val] = $hrefs[$key];
            }
            $msg['back_url'] = $hrefs['0'];
        }
    }
    else
    {
        $link   = empty($links) ? $GLOBALS['_LANG']['back_up_page'] : $links;
        $href    = empty($hrefs) ? 'javascript:history.back()'       : $hrefs;
        $msg['url_info'][$link] = $href;
        $msg['back_url'] = $href;
    }

    $msg['type']    = $type;
    $position = assign_ur_here(0, $GLOBALS['_LANG']['sys_msg']);
    $GLOBALS['smarty']->assign('page_title', $position['title']);   // 页面标题

    $GLOBALS['smarty']->assign('auto_redirect', $auto_redirect);
    $GLOBALS['smarty']->assign('message', $msg);
    $GLOBALS['smarty']->assign('user_id', $_SESSION['user_id']);
    $GLOBALS['smarty']->display('showmsg.html');

    exit;
}
?>