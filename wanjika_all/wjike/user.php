﻿<?php

/**
 * ECSHOP 会员中心
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: liubo $
 * $Id: user.php 17217 2011-01-19 06:29:08Z liubo $
*/

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');

/* 载入语言文件 */
require_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/user.php');

$user_id = $_SESSION['user_id'];
$action  = isset($_REQUEST['act']) ? trim($_REQUEST['act']) : 'default';

$affiliate = unserialize($GLOBALS['_CFG']['affiliate']);
$smarty->assign('affiliate', $affiliate);
$back_act='';


// 不需要登录的操作或自己验证是否登录（如ajax处理）的act
$not_login_arr =
array('login','chkname','act_login','register','forget_password','act_register','act_forgetpassword','act_checkchange','act_changemobile','act_forgetpassword','act_edit_password','get_password','send_pwd_email','password', 'signin', 'add_tag', 'collect', 'return_to_cart', 'logout', 'email_list', 'validate_email', 'send_hash_mail', 'order_query', 'is_registered', 'check_email','clear_history','qpassword_name', 'get_passwd_question', 'check_answer','get_vcode','get_forgetvcode','act_updatecontent
','act_saveinvoice','act_updateconsignee','ajax_login','ajax_free_members','ajax_yt_yhq','ajax_hd_yhq','ajax_Valentine','ajax_hd_rjz','ajax_hd_xyf','ajax_carnival_yhq','ajax_recruitment_yhq','ajax_hd_qx','ajax_hd_wy');

/* 显示页面的action列表 */
$ui_arr = array('register','forget_password', 'login', 'profile', 'order_list', 'order_detail','order_pay', 'address_list', 'collection_list',
'message_list', 'tag_list', 'get_password', 'reset_password', 'booking_list', 'add_booking', 'account_raply',
'account_deposit', 'account_log', 'account_detail', 'act_account', 'pay', 'default', 'bonus', 'group_buy', 'group_buy_detail', 'affiliate','follow','coupons','gift_card','integral','invoice_information','update_pwd','member_rights','consult_list', 'comment_list','validate_email','track_packages', 'transform_points','qpassword_name', 'get_passwd_question', 'check_answer', 'ajax_edit_address');

$smarty->assign('is_local',is_local());
$smarty->assign('categories', get_categories_tree()); // 分类树
/* 未登录处理 */
if (empty($_SESSION['user_id']))
{
    if (!in_array($action, $not_login_arr))
    {
        if (in_array($action, $ui_arr))
        {
            /* 如果需要登录,并是显示页面的操作，记录当前操作，用于登录后跳转到相应操作
            if ($action == 'login')
            {
                if (isset($_REQUEST['back_act']))
                {
                    $back_act = trim($_REQUEST['back_act']);
                }
            }
            else
            {}*/
            if (!empty($_SERVER['QUERY_STRING']))
            {
                $back_act = 'user.php?' . strip_tags($_SERVER['QUERY_STRING']);
            }
            $action = 'login';
        }
        else
        {
            //未登录提交数据。非正常途径提交数据！
            die($_LANG['require_login']);
        }
    }
}

/* 如果是显示页面，对页面进行相应赋值 */
if (in_array($action, $ui_arr))
{
    assign_template();
    $smarty->assign('userphp', true);
    $position = assign_ur_here(0, $_LANG['user_center']);
    $smarty->assign('page_title', $position['title']); // 页面标题
    $smarty->assign('ur_here',    $position['ur_here']);
    $sql = "SELECT value FROM " . $ecs->table('shop_config') . " WHERE id = 419";
    $row = $db->getRow($sql);
    $car_off = $row['value'];
    $smarty->assign('car_off',       $car_off);
    /* 是否显示积分兑换 */
    if (!empty($_CFG['points_rule']) && unserialize($_CFG['points_rule']))
    {
        $smarty->assign('show_transform_points',     1);
    }
    $smarty->assign('helps',      get_shop_help());        // 网店帮助
    $smarty->assign('data_dir',   DATA_DIR);   // 数据目录
    $smarty->assign('action',     $action);
    $smarty->assign('lang',       $_LANG);
}

//用户中心欢迎页
if ($action == 'default')
{
    include_once(ROOT_PATH .'includes/lib_clips.php');
    include_once(ROOT_PATH . 'includes/lib_transaction.php');
    if ($rank = get_rank_info())
    {
        $smarty->assign('rank_name', sprintf($_LANG['your_level'], $rank['rank_name']));
        if (!empty($rank['next_rank_name']))
        {
            $smarty->assign('next_rank_name', sprintf($_LANG['next_level'], $rank['next_rank'] ,$rank['next_rank_name']));
        }
    }

    $orders = get_user_orders($user_id, 3, 0);
    $smarty->assign('info',        get_user_default($user_id));
    $smarty->assign('orders', $orders);
    $is_members = check_user_members($_SESSION['user_id']);
    if($is_members){
        $members_info = get_members_byuser($user_id);
        $time = local_date('Y年m月d日', $members_info['members_deadline']);
        $smarty->assign('levelname', $members_info['goods_name']);
        $smarty->assign('time',      $time);
    }
    $smarty->assign('is_members', $is_members);
    $smarty->display('user_clips.dwt');
}

/* 显示会员注册界面 */
if ($action == 'register')
{
    if ((!isset($back_act)||empty($back_act)) && isset($GLOBALS['_SERVER']['HTTP_REFERER']))
    {
        $back_act = strpos($GLOBALS['_SERVER']['HTTP_REFERER'], 'user.php') ? './index.php' : $GLOBALS['_SERVER']['HTTP_REFERER'];
    }

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

    /* 密码提示问题 */
    $smarty->assign('passwd_questions', $_LANG['passwd_questions']);

    /* 增加是否关闭注册 */
    $smarty->assign('shop_reg_closed', $_CFG['shop_reg_closed']);

    /* 注册错误信息 */
    if(!empty($_SESSION['errormsg'])){
        $smarty->assign('errormsg', $_SESSION['errormsg']);
        $_SESSION['errormsg'] = '';
    }
//    $smarty->assign('back_act', $back_act);
    $smarty->display('user_passport.dwt');
}

/* 显示忘记密码界面 */
elseif ($action == 'forget_password')
{
    if ((!isset($back_act)||empty($back_act)) && isset($GLOBALS['_SERVER']['HTTP_REFERER']))
    {
        $back_act = strpos($GLOBALS['_SERVER']['HTTP_REFERER'], 'user.php') ? './index.php' : $GLOBALS['_SERVER']['HTTP_REFERER'];
    }

    /* 取出注册扩展字段 */
//    $sql = 'SELECT * FROM ' . $ecs->table('reg_fields') . ' WHERE type < 2 AND display = 1 ORDER BY dis_order, id';
//    $extend_info_list = $db->getAll($sql);
//    $smarty->assign('extend_info_list', $extend_info_list);

    /* 验证码相关设置 */
    if ((intval($_CFG['captcha']) & CAPTCHA_REGISTER) && gd_version() > 0)
    {
        $smarty->assign('enabled_captcha', 1);
        $smarty->assign('rand',            mt_rand());
    }

    /* 密码提示问题 */
//    $smarty->assign('passwd_questions', $_LANG['passwd_questions']);

    /* 增加是否关闭注册 */
    $smarty->assign('shop_reg_closed', $_CFG['shop_reg_closed']);

    /* 注册错误信息 */
    if(!empty($_SESSION['errormsg'])){
        $smarty->assign('errormsg', $_SESSION['errormsg']);
        $_SESSION['errormsg'] = '';
    }
//    $smarty->assign('back_act', $back_act);
    $smarty->display('user_passport.dwt');
}

/* 注册会员的处理 */
elseif ($action == 'act_register')
{
    /* 增加是否关闭注册 */
    if ($_CFG['shop_reg_closed'])
    {
        $smarty->assign('action',     'register');
        $smarty->assign('shop_reg_closed', $_CFG['shop_reg_closed']);
        $smarty->display('user_passport.dwt');
    }
    else
    {
        include_once(ROOT_PATH . 'includes/lib_passport.php');

        $username = isset($_POST['phone']) ? trim($_POST['phone']) : '';
        $password = isset($_POST['password']) ? trim($_POST['password']) : '';
        $confirm_password = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';
        $vcode = isset($_POST['vcode']) ? trim($_POST['vcode']) : '';
        $email    = isset($_POST['email']) ? trim($_POST['email']) : '';
        $other['msn'] = isset($_POST['extend_field1']) ? $_POST['extend_field1'] : '';
        $other['qq'] = isset($_POST['extend_field2']) ? $_POST['extend_field2'] : '';
        $other['office_phone'] = isset($_POST['extend_field3']) ? $_POST['extend_field3'] : '';
        $other['home_phone'] = isset($_POST['extend_field4']) ? $_POST['extend_field4'] : '';
        $other['mobile_phone'] = isset($_POST['phone']) ? $_POST['phone'] : '';
        $sel_question = empty($_POST['sel_question']) ? '' : compile_str($_POST['sel_question']);
        $passwd_answer = isset($_POST['passwd_answer']) ? compile_str(trim($_POST['passwd_answer'])) : '';


        $back_act = isset($_POST['back_act']) ? trim($_POST['back_act']) : '';

//        if(empty($_POST['agreement']))
//        {
//            show_message($_LANG['passport_js']['agreement']);
//        }
        if (strlen($username) < 11)
        {
            $_SESSION['errormsg'] = '输入手机号位数少于11位';
            ecs_header("Location:./user.php?act=register\n");
        }

        if (strlen($password) < 6)
        {
            $_SESSION['errormsg'] = $_LANG['passport_js']['password_shorter'];
            ecs_header("Location:./user.php?act=register\n");
        }

        if (strpos($password, ' ') > 0)
        {
            $_SESSION['errormsg'] = $_LANG['passwd_balnk'];
            ecs_header("Location:./user.php?act=register\n");
        }

        if (strlen($vcode) < 6)
        {
            $_SESSION['errormsg'] = '手机验证码错误';
            ecs_header("Location:./user.php?act=register\n");
        }

        /* 手机验证码检查 */
        $vcode_str = $username.$vcode;
        if($_SESSION['vcode_str'] != $vcode_str){
            $_SESSION['errormsg'] = '手机验证码错误';
            ecs_header("Location:./user.php?act=register\n");
        }
        else{
            unset($_SESSION['vcode_str']);
        }

        if ($password != $confirm_password)
        {
            $_SESSION['errormsg'] = '两次输入密码不一致';
            ecs_header("Location:./user.php?act=register\n");
        }

        /* 网站验证码检查 */
        if ((intval($_CFG['captcha']) & CAPTCHA_REGISTER) && gd_version() > 0)
        {
            if (empty($_POST['captcha']))
            {
                $_SESSION['errormsg'] = $_LANG['invalid_captcha'];
                ecs_header("Location:./user.php?act=register\n");
            }

            /* 检查验证码 */
            include_once('includes/cls_captcha.php');

            $validator = new captcha();
            if (!$validator->check_word($_POST['captcha']))
            {
                $_SESSION['errormsg'] = $_LANG['invalid_captcha'];
                ecs_header("Location:./user.php?act=register\n");
            }
        }

        if (register($username, $password, $email, $other) !== false)
        {
            /*把新注册用户的扩展信息插入数据库*/
            $sql = 'SELECT id FROM ' . $ecs->table('reg_fields') . ' WHERE type = 0 AND display = 1 ORDER BY dis_order, id';   //读出所有自定义扩展字段的id
            $fields_arr = $db->getAll($sql);

            $extend_field_str = '';    //生成扩展字段的内容字符串
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

            if ($extend_field_str)      //插入注册扩展数据
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
            /* 判断是否需要自动发送注册邮件 */
            if ($GLOBALS['_CFG']['member_email_validate'] && $GLOBALS['_CFG']['send_verify_email'])
            {
                send_regiter_hash($_SESSION['user_id']);
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

            show_message($username, '', '', 'info', false, 'reg_succeed');
        }
        else
        {
            $_SESSION['errormsg'] = '用户注册失败';
            ecs_header("Location:./user.php?act=register\n");
        }
    }
}

/* 忘记会员密码 */
elseif ($action == 'act_forgetpassword')
{
    include_once(ROOT_PATH . 'includes/lib_passport.php');
    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $confirm_password = isset($_POST['confirm_password']) ? trim($_POST['confirm_password']) : '';
    $vcode = isset($_POST['vcode']) ? trim($_POST['vcode']) : '';

    if (strlen($phone) < 11)
    {
        $_SESSION['errormsg'] = '输入手机号位数少于11位';
        ecs_header("Location:./user.php?act=forget_password\n");
    }

    //检查用户名是否存在
    $sql = "SELECT user_id FROM " . $ecs->table('users') .
        " WHERE mobile_phone = '" . $phone . "' ";
    $user_id = $db->getOne($sql);

    if ($user_id <= 0)
    {
        $_SESSION['errormsg'] = '该手机号注册用户名不存在';
        ecs_header("Location:./user.php?act=forget_password\n");
    }

    if (strlen($password) < 6)
    {
        $_SESSION['errormsg'] = $_LANG['passport_js']['password_shorter'];
        ecs_header("Location:./user.php?act=forget_password\n");
    }

    if (strpos($password, ' ') > 0)
    {
        $_SESSION['errormsg'] = $_LANG['passwd_balnk'];
        ecs_header("Location:./user.php?act=forget_password\n");
    }

    if (strlen($vcode) < 6)
    {
        $_SESSION['errormsg'] = '手机验证码错误';
        ecs_header("Location:./user.php?act=forget_password\n");
    }

    if ($password != $confirm_password)
    {
        $_SESSION['errormsg'] = '两次输入密码不一致';
        ecs_header("Location:./user.php?act=forget_password\n");
    }

    /* 手机验证码检查 */
    $vcode_str = $phone.$vcode;
    if($_SESSION['vcode_str'] != $vcode_str){
        $_SESSION['errormsg'] = '手机验证码错误';
        ecs_header("Location:./user.php?act=forget_password\n");
    }
    else{
        unset($_SESSION['vcode_str']);
    }

    /* 网站验证码检查 */
    if ((intval($_CFG['captcha']) & CAPTCHA_REGISTER) && gd_version() > 0)
    {
        if (empty($_POST['captcha']))
        {
            $_SESSION['errormsg'] = $_LANG['invalid_captcha'];
            ecs_header("Location:./user.php?act=register\n");
        }

        /* 检查验证码 */
        include_once('includes/cls_captcha.php');

        $validator = new captcha();
        if (!$validator->check_word($_POST['captcha']))
        {
            $_SESSION['errormsg'] = $_LANG['invalid_captcha'];
            ecs_header("Location:./user.php?act=register\n");
        }
    }

    $user_info = $user->get_profile_by_id($user_id); //论坛记录

    if ($user->edit_user(array('username'=> $user_info['user_name'], 'old_password'=>'', 'password'=>$password), empty($code) ? 0 : 1))
    {
        $sql="UPDATE ".$ecs->table('users'). "SET `ec_salt`='0' WHERE user_id= '".$user_id."'";
        $db->query($sql);
        $user->logout();

        show_message($_LANG['edit_password_success'], $_LANG['relogin_lnk'], 'user.php?act=login', 'info', true, 'forget_succeed');
    }
    else
    {
        $_SESSION['errormsg'] = $_LANG['edit_password_failure'];
        ecs_header("Location:./user.php?act=forget_password\n");
    }

}

/* 获取短信验证码 */
elseif ($action == 'get_vcode')
{
    include_once(ROOT_PATH . 'includes/cls_msgsend.php');
    include_once('includes/cls_json.php');

    $msgsend = new msgsend();
    $json = new JSON;
    $phone = $_POST['phone'];

    $r['errorcode']   = 0;
    $r['msg'] = '校验码发送成功！';

    /* 网站验证码检查 */
    if ((intval($_CFG['captcha']) & CAPTCHA_REGISTER) && gd_version() > 0)
    {
        /* 检查验证码 */
        include_once('includes/cls_captcha.php');

        $validator = new captcha();
        if (empty($_POST['captcha']) || !$validator->check_word($_POST['captcha']))
        {
            $r['errorcode']   = 5;
            $r['msg'] = '请输入正确的图片验证码';
            die($json->encode($r));
        }
    }

    if($_SERVER['HTTP_REFERER'] != 'http://'.$_SERVER['HTTP_HOST'].'/user.php?act=register'){
        $r['errorcode']   = 3;
        $r['msg'] = '短信接口错误';
        die($json->encode($r));
    }

    //检查短信ip记录表记录是否存在
    $sql = "SELECT COUNT(*) FROM " . $ecs->table('message_iprecord') .
        " WHERE user_ip = '" . real_ip() . "'";

    if ($db->getOne($sql) > 0)
    {
        $r['errorcode']   = 4;
        $r['msg'] = '请勿频繁发送(两分钟内只能发一次)';
        die($json->encode($r));
    }

    //检查用户名是否存在
    $sql = "SELECT COUNT(*) FROM " . $ecs->table('users') .
        " WHERE user_name = '" . $phone . "' or mobile_phone = '" . $phone . "'";
    if ($db->getOne($sql) > 0)
    {
        $r['errorcode']   = 2;
        $r['msg'] = '该手机号已经注册';
        die($json->encode($r));
    }

    $vcode = rand(100000,999999);
    $_SESSION['vcode_str'] = $phone.$vcode;
    $msg = '尊敬的用户您好，您本次的短信验证码为：'. $vcode .'。请妥善保管，不要告诉他人';
    $result = $msgsend->send($phone,$msg,"webreg");

    //插入短信发送ip记录
    $db->query("INSERT INTO ".$ecs->table('message_iprecord')." (user_ip) VALUES('".real_ip()."')");

    if(!$result['status']){
        $r['errorcode']   = 1;
        $r['msg'] = $result['code'];
    }
    die($json->encode($r));
}

/* 忘记密码获取短信验证码 */
elseif ($action == 'get_forgetvcode')
{
    include_once(ROOT_PATH . 'includes/cls_msgsend.php');
    include_once('includes/cls_json.php');

    $msgsend = new msgsend();
    $json = new JSON;
    $phone = $_REQUEST['phone'];

    $r['errorcode']   = 0;
    $r['msg'] = '校验码发送成功！';

    if($_SERVER['HTTP_REFERER'] != 'http://'.$_SERVER['HTTP_HOST'].'/user.php?act=forget_password'){
        $r['errorcode']   = 3;
        $r['msg'] = '短信接口错误';
        die($json->encode($r));
    }

    //检查短信ip记录表记录是否存在
    $sql = "SELECT COUNT(*) FROM " . $ecs->table('message_iprecord') .
        " WHERE user_ip = '" . real_ip() . "'";
    if ($db->getOne($sql) > 0)
    {
        $r['errorcode']   = 4;
        $r['msg'] =  '请勿频繁发送(两分钟内只能发一次)';
        die($json->encode($r));
    }

    //检查用户名是否存在
    $sql = "SELECT COUNT(*) FROM " . $ecs->table('users') .
        " WHERE mobile_phone = '" . $phone . "' ";
    if ($db->getOne($sql) <= 0)
    {
        $r['errorcode']   = 2;
        $r['msg'] = '该手机号未绑定';
        die($json->encode($r));
    }

    $vcode = rand(100000,999999);
    $_SESSION['vcode_str'] = $phone.$vcode;
    $msg = '尊敬的用户您好，您本次的短信验证码为：'. $vcode .'。请妥善保管，不要告诉他人';
    $result = $msgsend->send($phone,$msg,"webfp");

    //插入短信发送ip记录
    $db->query("INSERT INTO ".$ecs->table('message_iprecord')." (user_ip) VALUES('".real_ip()."')");

    if(!$result['status']){
        $r['errorcode']   = 1;
        $r['msg'] = $result['code'];
    }
    die($json->encode($r));
}

/* 验证用户注册邮件 */
elseif ($action == 'validate_email')
{
    $hash = empty($_GET['hash']) ? '' : trim($_GET['hash']);
    if ($hash)
    {
        include_once(ROOT_PATH . 'includes/lib_passport.php');
        $id = register_hash('decode', $hash);
        if ($id > 0)
        {
            $sql = "UPDATE " . $ecs->table('users') . " SET is_validated = 1 WHERE user_id='$id'";
            $db->query($sql);
            $sql = 'SELECT user_name, email FROM ' . $ecs->table('users') . " WHERE user_id = '$id'";
            $row = $db->getRow($sql);
            show_message(sprintf($_LANG['validate_ok'], $row['user_name'], $row['email']),$_LANG['profile_lnk'], 'user.php');
        }
    }
    show_message($_LANG['validate_fail']);
}

/* 验证用户注册用户名是否可以注册 */
elseif ($action == 'is_registered')
{
    include_once(ROOT_PATH . 'includes/lib_passport.php');

    $username = trim($_GET['username']);
    $username = json_str_iconv($username);

    if ($user->check_user($username) || admin_registered($username))
    {
        echo 'false';
    }
    else
    {
        echo 'true';
    }
}

/* 验证用户邮箱地址是否被注册 */
elseif($action == 'check_email')
{
    $email = trim($_GET['email']);
    if ($user->check_email($email))
    {
        echo 'false';
    }
    else
    {
        echo 'ok';
    }
}
/* 用户登录界面 */
elseif ($action == 'login')
{
    if (empty($back_act))
    {
        if (empty($back_act) && isset($GLOBALS['_SERVER']['HTTP_REFERER']))
        {
            $back_act = strpos($GLOBALS['_SERVER']['HTTP_REFERER'], 'user.php') ? './index.php' : $GLOBALS['_SERVER']['HTTP_REFERER'];
        }
        else
        {
            $back_act = 'user.php';
        }

    }


    $captcha = intval($_CFG['captcha']);
    if (($captcha & CAPTCHA_LOGIN) && (!($captcha & CAPTCHA_LOGIN_FAIL) || (($captcha & CAPTCHA_LOGIN_FAIL) && $_SESSION['login_fail'] > 2)) && gd_version() > 0)
    {
        $GLOBALS['smarty']->assign('enabled_captcha', 1);
        $GLOBALS['smarty']->assign('rand', mt_rand());
    }

    /* 注册错误信息 */
    if(!empty($_SESSION['errormsg'])){
        $smarty->assign('errormsg', $_SESSION['errormsg']);
        $_SESSION['errormsg'] = '';
    }

    $smarty->assign('back_act', $back_act);
    $smarty->display('user_passport.dwt');
}

elseif ($action == 'chkname')
{
    include_once(ROOT_PATH .'includes/cls_json.php');
    $json = new JSON();

    $result = array('error' => 0, 'message' => '', 'content' => '');
    $name = $_POST['name'];
    $sql ="SELECT COUNT(*) FROM " . "ecs_users" . "  WHERE user_name = '$name'";
    $id = $db->getOne($sql);
    $result['content']=$id;
    if($id>0)
    {
        $result['message']   = "该手机号已被注册！";
    }
    else
    {
        $result['error']=1;
        $result['message']   = "";
    }
    die($json->encode($result));
    exit;
}

/* 处理会员的登录 */
elseif ($action == 'act_login')
{
    $username = isset($_POST['username']) ? trim($_POST['username']) : '';
    $password = isset($_POST['password']) ? trim($_POST['password']) : '';
    $back_act = isset($_POST['back_act']) ? trim($_POST['back_act']) : '';


    $captcha = intval($_CFG['captcha']);
    if (($captcha & CAPTCHA_LOGIN) && (!($captcha & CAPTCHA_LOGIN_FAIL) || (($captcha & CAPTCHA_LOGIN_FAIL) && $_SESSION['login_fail'] > 2)) && gd_version() > 0)
    {
        if (empty($_POST['captcha']))
        {
            $_SESSION['errormsg'] = $_LANG['invalid_captcha'];
            ecs_header("Location:./user.php?act=login\n");
        }

        /* 检查验证码 */
        include_once('includes/cls_captcha.php');

        $validator = new captcha();
        $validator->session_word = 'captcha_login';
        if (!$validator->check_word($_POST['captcha']))
        {
            $_SESSION['errormsg'] = $_LANG['invalid_captcha'];
            ecs_header("Location:./user.php?act=login\n");
        }
    }

    if ($user->login($username, $password,isset($_POST['remember'])))
    {
        update_user_info();
        recalculate_price();
        //校验用户会员身份
        $is_members = check_user_members($_SESSION['user_id']);
        if(!$is_members){
            members_overtime($_SESSION['user_id']);
        }

        $ucdata = isset($user->ucdata)? $user->ucdata : '';
        if(!empty($back_act))
        {
            ecs_header("Location:$back_act\n");
        }
        ecs_header("Location:./\n");
        //show_message($_LANG['login_success'] . $ucdata , array($_LANG['back_up_page'], $_LANG['profile_lnk']), array($back_act,'user.php'), 'info');
    }
    else
    {
        $_SESSION['login_fail'] ++ ;
        $_SESSION['errormsg'] = $_LANG['login_failure'];
        ecs_header("Location:./user.php?act=login\n");
    }
}

/* 处理 ajax 的登录请求 */
elseif ($action == 'signin')
{
    include_once('includes/cls_json.php');
    $json = new JSON;

    $username = !empty($_POST['username']) ? json_str_iconv(trim($_POST['username'])) : '';
    $password = !empty($_POST['password']) ? trim($_POST['password']) : '';
    $captcha = !empty($_POST['captcha']) ? json_str_iconv(trim($_POST['captcha'])) : '';
    $result   = array('error' => 0, 'content' => '');

    $captcha = intval($_CFG['captcha']);
    if (($captcha & CAPTCHA_LOGIN) && (!($captcha & CAPTCHA_LOGIN_FAIL) || (($captcha & CAPTCHA_LOGIN_FAIL) && $_SESSION['login_fail'] > 2)) && gd_version() > 0)
    {
        if (empty($captcha))
        {
            $result['error']   = 1;
            $result['content'] = $_LANG['invalid_captcha'];
            die($json->encode($result));
        }

        /* 检查验证码 */
        include_once('includes/cls_captcha.php');

        $validator = new captcha();
        $validator->session_word = 'captcha_login';
        if (!$validator->check_word($_POST['captcha']))
        {

            $result['error']   = 1;
            $result['content'] = $_LANG['invalid_captcha'];
            die($json->encode($result));
        }
    }

    if ($user->login($username, $password))
    {
        update_user_info();  //更新用户信息
        recalculate_price(); // 重新计算购物车中的商品价格
        $smarty->assign('user_info', get_user_info());
        $ucdata = empty($user->ucdata)? "" : $user->ucdata;
        $result['ucdata'] = $ucdata;
        $result['content'] = $smarty->fetch('library/member_info.lbi');
    }
    else
    {
        $_SESSION['login_fail']++;
        if ($_SESSION['login_fail'] > 2)
        {
            $smarty->assign('enabled_captcha', 1);
            $result['html'] = $smarty->fetch('library/member_info.lbi');
        }
        $result['error']   = 1;
        $result['content'] = $_LANG['login_failure'];
    }
    die($json->encode($result));
}

/* 退出会员中心 */
elseif ($action == 'logout')
{
    if ((!isset($back_act)|| empty($back_act)) && isset($GLOBALS['_SERVER']['HTTP_REFERER']))
    {
        $back_act = strpos($GLOBALS['_SERVER']['HTTP_REFERER'], 'user.php') ? './index.php' : $GLOBALS['_SERVER']['HTTP_REFERER'];
    }

    /* 删除is_immediately=1的商品 */
    $sql = "DELETE FROM " . $GLOBALS['ecs']->table('cart') ."
            WHERE session_id = '" . SESS_ID . "'
            AND is_immediately=1";
    $GLOBALS['db']->query($sql);
    $user->logout();
    $ucdata = empty($user->ucdata)? "" : $user->ucdata;
    show_message($_LANG['logout'] . $ucdata, array($_LANG['back_up_page'], $_LANG['back_home_lnk']), array($back_act, 'index.php'), 'info');
}

/* 个人资料页面 */
elseif ($action == 'profile')
{
    include_once(ROOT_PATH . 'includes/lib_transaction.php');

    $user_info = get_profile($user_id);

    /* 取出注册扩展字段 */
    $sql = 'SELECT * FROM ' . $ecs->table('reg_fields') . ' WHERE type < 2 AND display = 1 ORDER BY dis_order, id';
    $extend_info_list = $db->getAll($sql);

    $sql = 'SELECT reg_field_id, content ' .
           'FROM ' . $ecs->table('reg_extend_info') .
           " WHERE user_id = $user_id";
    $extend_info_arr = $db->getAll($sql);

    $temp_arr = array();
    foreach ($extend_info_arr AS $val)
    {
        $temp_arr[$val['reg_field_id']] = $val['content'];
    }

    foreach ($extend_info_list AS $key => $val)
    {
        switch ($val['id'])
        {
            case 1:     $extend_info_list[$key]['content'] = $user_info['msn']; break;
            case 2:     $extend_info_list[$key]['content'] = $user_info['qq']; break;
            case 3:     $extend_info_list[$key]['content'] = $user_info['office_phone']; break;
            case 4:     $extend_info_list[$key]['content'] = $user_info['home_phone']; break;
            case 5:     $extend_info_list[$key]['content'] = $user_info['mobile_phone']; break;
            default:    $extend_info_list[$key]['content'] = empty($temp_arr[$val['id']]) ? '' : $temp_arr[$val['id']] ;
        }
    }

    //手机号隐藏中间四位
    $user_info['mobilephone'] = substr_replace($user_info['mobile_phone'],'****',3,4);

    $smarty->assign('extend_info_list', $extend_info_list);
    /* 密码提示问题 */
    $smarty->assign('passwd_questions', $_LANG['passwd_questions']);
    $smarty->assign('profile', $user_info);
    $smarty->display('user_transaction.dwt');
}

/* 验证个人绑定手机 */
elseif ($action == 'act_checkchange')
{
    include_once('includes/cls_json.php');
    include_once(ROOT_PATH . 'includes/lib_passport.php');
    $json = new JSON;

    $r['errorcode']   = 0;
    $r['msg'] = '原手机号验证通过！';

    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $vcode = isset($_POST['vcode']) ? trim($_POST['vcode']) : '';

    if (strlen($phone) < 11)
    {
        $r['errorcode']   = 1;
        $r['msg'] = '输入手机号位数少于11位';
    }

    if (strlen($vcode) < 6)
    {
        $r['errorcode']   = 2;
        $r['msg'] = '手机验证码错误';
    }

    /* 手机验证码检查 */
    $vcode_str = $phone.$vcode;
    if($_SESSION['vcode_str'] != $vcode_str){
        $r['errorcode']   = 2;
        $r['msg'] = '手机验证码错误';
    }
    else{
        unset($_SESSION['vcode_str']);
    }

    die($json->encode($r));
}

/* 修改个人绑定手机 */
elseif ($action == 'act_changemobile')
{
    include_once('includes/cls_json.php');
    include_once(ROOT_PATH . 'includes/lib_passport.php');
    $json = new JSON;

    $r['errorcode']   = 0;
    $r['msg'] = '修改个人绑定手机成功！';

    $phone = isset($_POST['phone']) ? trim($_POST['phone']) : '';
    $vcode = isset($_POST['vcode']) ? trim($_POST['vcode']) : '';

    if (strlen($phone) < 11)
    {
        $r['errorcode']   = 1;
        $r['msg'] = '输入手机号不合法！';
    }

    if(!is_mobile($phone)){
        $r['errorcode']   = 2;
        $r['msg'] = '输入手机号位数少于11位';
    }

    if (strlen($vcode) < 6)
    {
        $r['errorcode']   = 3;
        $r['msg'] = '手机验证码错误';
    }

    /* 手机验证码检查 */
    $vcode_str = $phone.$vcode;
    if($_SESSION['vcode_str'] != $vcode_str){
        $r['errorcode']   = 3;
        $r['msg'] = '手机验证码错误';
    }
    else{
        unset($_SESSION['vcode_str']);
    }

    if($r['errorcode'] == 0){
        $sql = 'UPDATE ' . $ecs->table('users') . " SET `mobile_phone`='$phone' WHERE `user_id`='" . $_SESSION['user_id'] . "'";
        $result = $db->query($sql);
        if(!$result){
            $r['errorcode']   = 4;
            $r['msg'] = '修改个人绑定手机失败!';
        }
    }

    die($json->encode($r));
}

/* 修改发票信息单位名称 */
elseif ($action == 'act_updatecontent')
{
    include_once('includes/cls_json.php');
    include_once(ROOT_PATH . 'includes/lib_clips.php');
    $json = new JSON;

    $r['errorcode']   = 0;
    $r['msg'] = '修改发票信息单位名称成功！';

    $company_info = isset($_POST['company_info']) ? trim($_POST['company_info']) : '';
    $r['companyinfo'] = $company_info;

    $invoice = get_invoice_info($user_id);
    if($invoice['type'] == 0 && $invoice['content'] == 0){
        $insert_sql = 'INSERT INTO '. $ecs->table('invoice') . " (`type`, `company_info`, `content`) VALUES ('2', '$company_info', '1')";
        $result = $db->query($insert_sql);
        $id = $db->insert_id();
        if($result && $id > 0){
            $update_sql = 'UPDATE '. $ecs->table('users') . " SET invoice_id='$id'".
                " WHERE user_id='" . $user_id . "'";
            $u_result = $db->query($update_sql);
            if(!$u_result){
                $r['errorcode']   = 2;
                $r['msg'] = '添加用户发票失败!';
            }
        }
        else{
            $r['errorcode']   = 1;
            $r['msg'] = '添加发票信息单位名称失败!';
        }
    }
    else{
        $sql  = "UPDATE " .$ecs->table('invoice'). " AS i, ". $ecs->table('users')." AS u SET i.company_info='$company_info'".
            " WHERE u.invoice_id=i.id AND u.user_id='" . $user_id . "'";
        $result = $db->query($sql);
        if(!$result){
            $r['errorcode']   = 3;
            $r['msg'] = '修改发票信息单位名称失败!';
        }
    }

    die($json->encode($r));
}

/* 保存发票信息 */
elseif ($action == 'act_saveinvoice')
{
    include_once('includes/cls_json.php');
    include_once(ROOT_PATH . 'includes/lib_clips.php');
    $json = new JSON;

    $r['errorcode']   = 0;
    $r['msg'] = '保存发票信息成功！';

    $type = isset($_POST['type']) ? intval($_POST['type']) : 0;
    $content = isset($_POST['content']) ? intval($_POST['content']) : 0;

    $invoice = get_invoice_info($user_id);
    if($invoice['type'] == 0 && $invoice['content'] == 0){
        $insert_sql = 'INSERT INTO '. $ecs->table('invoice') . " (`type`, `content`) VALUES ($type, $content)";
        $result = $db->query($insert_sql);
        $id = $db->insert_id();
        if($result && $id > 0){
            $update_sql = 'UPDATE '. $ecs->table('users') . " SET invoice_id='$id'".
                " WHERE user_id='" . $user_id . "'";
            $u_result = $db->query($update_sql);
            if(!$u_result){
                $r['errorcode']   = 2;
                $r['msg'] = '添加用户发票失败!';
            }
        }
        else{
            $r['errorcode']   = 1;
            $r['msg'] = '添加发票信息单位名称失败!';
        }
    }
    else{
        $sql  = "UPDATE " .$ecs->table('invoice'). " AS i, ". $ecs->table('users')." AS u SET i.type='$type', i.content='$content'".
            " WHERE u.invoice_id=i.id AND u.user_id='" . $user_id . "'";
        $result = $db->query($sql);
        if(!$result){
            $r['errorcode']   = 3;
            $r['msg'] = '保存发票信息失败!';
        }
    }

    die($json->encode($r));
}

/* 修改默认收货人信息 */
elseif ($action == 'act_updateconsignee')
{
    include_once('includes/cls_json.php');
    $json = new JSON;

    $r['errorcode']   = 0;
    $r['msg'] = '修改默认收货人成功！';

    $address_id = isset($_POST['address_id']) ? intval($_POST['address_id']) : 0;
    $user_id = $_SESSION['user_id'];
    if($address_id <= 0){
        $r['errorcode']   = 1;
        $r['msg'] = '收货人ID不能为空!';
    }
    if($user_id <= 0){
        $r['errorcode']   = 2;
        $r['msg'] = '用户ID不能为空!';
    }

    if($address_id > 0 && $_SESSION['user_id'] > 0){
        $sql = 'UPDATE ' . $ecs->table('users') . " SET `address_id`='$address_id' WHERE `user_id`='$user_id'";
        $result = $db->query($sql);
        if(!$result){
            $r['errorcode']   = 3;
            $r['msg'] = '更新操作失败!';
        }
    }

    die($json->encode($r));
}

/* 异步登录 */
elseif ($action == 'ajax_login')
{
    include_once('includes/cls_json.php');

    $username = isset($_POST['name']) ? trim($_POST['name']) : '';
    $password = isset($_POST['pw']) ? trim($_POST['pw']) : '';
    $json = new JSON;

    $r['errorcode']   = 0;
    $r['msg'] = '登录成功！';


    $captcha = intval($_CFG['captcha']);
    if (($captcha & CAPTCHA_LOGIN) && (!($captcha & CAPTCHA_LOGIN_FAIL) || (($captcha & CAPTCHA_LOGIN_FAIL) && $_SESSION['login_fail'] > 2)) && gd_version() > 0)
    {
        if (empty($_POST['code']))
        {
            $r['errorcode']   = 1;
            $r['msg'] = '验证码不能为空！';
        }

        /* 检查验证码 */
        include_once('includes/cls_captcha.php');

        $validator = new captcha();
        $validator->session_word = 'captcha_login';
        if (!$validator->check_word($_POST['code']))
        {
            $r['errorcode']   = 2;
            $r['msg'] = '验证码输入错误！';
        }
    }

    if ($user->login($username, $password,isset($_POST['remember'])))
    {
        update_user_info();
        recalculate_price();
        //校验用户会员身份
        $is_members = check_user_members($_SESSION['user_id']);
        if(!$is_members){
            members_overtime($_SESSION['user_id']);
        }

        /* 删除is_immediately=1的商品 */
        $sql = "DELETE FROM " . $GLOBALS['ecs']->table('cart') ."
            WHERE user_id = '" . $_SESSION['user_id'] . "'
            AND is_immediately=1";
        $GLOBALS['db']->query($sql);
    }
    else
    {
        $r['errorcode']   = 3;
        $r['msg'] = '账号或密码输入错误！';
    }

    die($json->encode($r));
}

/* 修改个人资料的处理 */
elseif ($action == 'act_edit_profile')
{
    include_once(ROOT_PATH . 'includes/lib_transaction.php');

//    $birthday = trim($_POST['birthday']);
//    $email = trim($_POST['email']);
//    $other['msn'] = $msn = isset($_POST['extend_field1']) ? trim($_POST['extend_field1']) : '';
//    $other['qq'] = $qq = isset($_POST['extend_field2']) ? trim($_POST['extend_field2']) : '';
//    $other['office_phone'] = $office_phone = isset($_POST['extend_field3']) ? trim($_POST['extend_field3']) : '';
//    $other['home_phone'] = $home_phone = isset($_POST['extend_field4']) ? trim($_POST['extend_field4']) : '';
//    $other['mobile_phone'] = $mobile_phone = isset($_POST['extend_field5']) ? trim($_POST['extend_field5']) : '';
//    $sel_question = empty($_POST['sel_question']) ? '' : compile_str($_POST['sel_question']);
//    $passwd_answer = isset($_POST['passwd_answer']) ? compile_str(trim($_POST['passwd_answer'])) : '';

    /* 更新用户扩展字段的数据 */
//    $sql = 'SELECT id FROM ' . $ecs->table('reg_fields') . ' WHERE type = 0 AND display = 1 ORDER BY dis_order, id';   //读出所有扩展字段的id
//    $fields_arr = $db->getAll($sql);

//    foreach ($fields_arr AS $val)       //循环更新扩展用户信息
//    {
//        $extend_field_index = 'extend_field' . $val['id'];
//        if(isset($_POST[$extend_field_index]))
//        {
//            $temp_field_content = strlen($_POST[$extend_field_index]) > 100 ? mb_substr(htmlspecialchars($_POST[$extend_field_index]), 0, 99) : htmlspecialchars($_POST[$extend_field_index]);
//            $sql = 'SELECT * FROM ' . $ecs->table('reg_extend_info') . "  WHERE reg_field_id = '$val[id]' AND user_id = '$user_id'";
//            if ($db->getOne($sql))      //如果之前没有记录，则插入
//            {
//                $sql = 'UPDATE ' . $ecs->table('reg_extend_info') . " SET content = '$temp_field_content' WHERE reg_field_id = '$val[id]' AND user_id = '$user_id'";
//            }
//            else
//            {
//                $sql = 'INSERT INTO '. $ecs->table('reg_extend_info') . " (`user_id`, `reg_field_id`, `content`) VALUES ('$user_id', '$val[id]', '$temp_field_content')";
//            }
//            $db->query($sql);
//        }
//    }

    /* 写入密码提示问题和答案 */
//    if (!empty($passwd_answer) && !empty($sel_question))
//    {
//        $sql = 'UPDATE ' . $ecs->table('users') . " SET `passwd_question`='$sel_question', `passwd_answer`='$passwd_answer'  WHERE `user_id`='" . $_SESSION['user_id'] . "'";
//        $db->query($sql);
//    }
//
//    if (!empty($office_phone) && !preg_match( '/^[\d|\_|\-|\s]+$/', $office_phone ) )
//    {
//        show_message($_LANG['passport_js']['office_phone_invalid']);
//    }
//    if (!empty($home_phone) && !preg_match( '/^[\d|\_|\-|\s]+$/', $home_phone) )
//    {
//         show_message($_LANG['passport_js']['home_phone_invalid']);
//    }
//    if (!is_email($email))
//    {
//        show_message($_LANG['msg_email_format']);
//    }
//    if (!empty($msn) && !is_email($msn))
//    {
//         show_message($_LANG['passport_js']['msn_invalid']);
//    }
//    if (!empty($qq) && !preg_match('/^\d+$/', $qq))
//    {
//         show_message($_LANG['passport_js']['qq_invalid']);
//    }
//    if (!empty($mobile_phone) && !preg_match('/^[\d-\s]+$/', $mobile_phone))
//    {
//        show_message($_LANG['passport_js']['mobile_phone_invalid']);
//    }


    $profile  = array(
        'user_id'  => $user_id,
        'email'    => isset($_POST['email']) ? trim($_POST['email']) : '',
        'sex'      => isset($_POST['sex'])   ? intval($_POST['sex']) : 0,
        'birthday' => isset($_POST['birthday'])   ? trim($_POST['birthday']) : '',
        'other'    => isset($other) ? $other : array()
        );


    if (edit_profile($profile))
    {
        show_message($_LANG['edit_profile_success'], $_LANG['profile_lnk'], 'user.php?act=profile', 'info');
    }
    else
    {
        if ($user->error == ERR_EMAIL_EXISTS)
        {
            $msg = sprintf($_LANG['email_exist'], $profile['email']);
        }
        else
        {
            $msg = $_LANG['edit_profile_failed'];
        }
        show_message($msg, '', '', 'info');
    }
}

/* 密码找回-->修改密码界面 */
elseif ($action == 'get_password')
{
    include_once(ROOT_PATH . 'includes/lib_passport.php');

    if (isset($_GET['code']) && isset($_GET['uid'])) //从邮件处获得的act
    {
        $code = trim($_GET['code']);
        $uid  = intval($_GET['uid']);

        /* 判断链接的合法性 */
        $user_info = $user->get_profile_by_id($uid);
        if (empty($user_info) || ($user_info && md5($user_info['user_id'] . $_CFG['hash_code'] . $user_info['reg_time']) != $code))
        {
            show_message($_LANG['parm_error'], $_LANG['back_home_lnk'], './', 'info');
        }

        $smarty->assign('uid',    $uid);
        $smarty->assign('code',   $code);
        $smarty->assign('action', 'reset_password');
        $smarty->display('user_passport.dwt');
    }
    else
    {
        //显示用户名和email表单
        $smarty->display('user_passport.dwt');
    }
}

/* 密码找回-->输入用户名界面 */
elseif ($action == 'qpassword_name')
{
    //显示输入要找回密码的账号表单
    $smarty->display('user_passport.dwt');
}

/* 密码找回-->根据注册用户名取得密码提示问题界面 */
elseif ($action == 'get_passwd_question')
{
    if (empty($_POST['user_name']))
    {
        show_message($_LANG['no_passwd_question'], $_LANG['back_home_lnk'], './', 'info');
    }
    else
    {
        $user_name = trim($_POST['user_name']);
    }

    //取出会员密码问题和答案
    $sql = 'SELECT user_id, user_name, passwd_question, passwd_answer FROM ' . $ecs->table('users') . " WHERE user_name = '" . $user_name . "'";
    $user_question_arr = $db->getRow($sql);

    //如果没有设置密码问题，给出错误提示
    if (empty($user_question_arr['passwd_answer']))
    {
        show_message($_LANG['no_passwd_question'], $_LANG['back_home_lnk'], './', 'info');
    }

    $_SESSION['temp_user'] = $user_question_arr['user_id'];  //设置临时用户，不具有有效身份
    $_SESSION['temp_user_name'] = $user_question_arr['user_name'];  //设置临时用户，不具有有效身份
    $_SESSION['passwd_answer'] = $user_question_arr['passwd_answer'];   //存储密码问题答案，减少一次数据库访问

    $captcha = intval($_CFG['captcha']);
    if (($captcha & CAPTCHA_LOGIN) && (!($captcha & CAPTCHA_LOGIN_FAIL) || (($captcha & CAPTCHA_LOGIN_FAIL) && $_SESSION['login_fail'] > 2)) && gd_version() > 0)
    {
        $GLOBALS['smarty']->assign('enabled_captcha', 1);
        $GLOBALS['smarty']->assign('rand', mt_rand());
    }

    $smarty->assign('passwd_question', $_LANG['passwd_questions'][$user_question_arr['passwd_question']]);
    $smarty->display('user_passport.dwt');
}

/* 密码找回-->根据提交的密码答案进行相应处理 */
elseif ($action == 'check_answer')
{
    $captcha = intval($_CFG['captcha']);
    if (($captcha & CAPTCHA_LOGIN) && (!($captcha & CAPTCHA_LOGIN_FAIL) || (($captcha & CAPTCHA_LOGIN_FAIL) && $_SESSION['login_fail'] > 2)) && gd_version() > 0)
    {
        if (empty($_POST['captcha']))
        {
            show_message($_LANG['invalid_captcha'], $_LANG['back_retry_answer'], 'user.php?act=qpassword_name', 'error');
        }

        /* 检查验证码 */
        include_once('includes/cls_captcha.php');

        $validator = new captcha();
        $validator->session_word = 'captcha_login';
        if (!$validator->check_word($_POST['captcha']))
        {
            show_message($_LANG['invalid_captcha'], $_LANG['back_retry_answer'], 'user.php?act=qpassword_name', 'error');
        }
    }

    if (empty($_POST['passwd_answer']) || $_POST['passwd_answer'] != $_SESSION['passwd_answer'])
    {
        show_message($_LANG['wrong_passwd_answer'], $_LANG['back_retry_answer'], 'user.php?act=qpassword_name', 'info');
    }
    else
    {
        $_SESSION['user_id'] = $_SESSION['temp_user'];
        $_SESSION['user_name'] = $_SESSION['temp_user_name'];
        unset($_SESSION['temp_user']);
        unset($_SESSION['temp_user_name']);
        $smarty->assign('uid',    $_SESSION['user_id']);
        $smarty->assign('action', 'reset_password');
        $smarty->display('user_passport.dwt');
    }
}

/* 发送密码修改确认邮件 */
elseif ($action == 'send_pwd_email')
{
    include_once(ROOT_PATH . 'includes/lib_passport.php');

    /* 初始化会员用户名和邮件地址 */
    $user_name = !empty($_POST['user_name']) ? trim($_POST['user_name']) : '';
    $email     = !empty($_POST['email'])     ? trim($_POST['email'])     : '';

    //用户名和邮件地址是否匹配
    $user_info = $user->get_user_info($user_name);

    if ($user_info && $user_info['email'] == $email)
    {
        //生成code
         //$code = md5($user_info[0] . $user_info[1]);

        $code = md5($user_info['user_id'] . $_CFG['hash_code'] . $user_info['reg_time']);
        //发送邮件的函数
        if (send_pwd_email($user_info['user_id'], $user_name, $email, $code))
        {
            show_message($_LANG['send_success'] . $email, $_LANG['back_home_lnk'], './', 'info');
        }
        else
        {
            //发送邮件出错
            show_message($_LANG['fail_send_password'], $_LANG['back_page_up'], './', 'info');
        }
    }
    else
    {
        //用户名与邮件地址不匹配
        show_message($_LANG['username_no_email'], $_LANG['back_page_up'], '', 'info');
    }
}

/* 重置新密码 */
elseif ($action == 'send_pwd_email')
{
    //显示重置密码的表单
    $smarty->display('user_passport.dwt');
}

/* 修改会员密码 */
elseif ($action == 'act_edit_password')
{
    include_once(ROOT_PATH . 'includes/lib_passport.php');

    $old_password = isset($_POST['old_password']) ? trim($_POST['old_password']) : null;
    $new_password = isset($_POST['new_password']) ? trim($_POST['new_password']) : '';
    $user_id      = isset($_POST['uid'])  ? intval($_POST['uid']) : $user_id;
    $code         = isset($_POST['code']) ? trim($_POST['code'])  : '';

    if (strlen($new_password) < 6)
    {
        show_message($_LANG['passport_js']['password_shorter']);
    }

    $user_info = $user->get_profile_by_id($user_id); //论坛记录

    if (($user_info && (!empty($code) && md5($user_info['user_id'] . $_CFG['hash_code'] . $user_info['reg_time']) == $code)) || ($_SESSION['user_id']>0 && $_SESSION['user_id'] == $user_id && $user->check_user($_SESSION['user_name'], $old_password)))
    {

        if ($user->edit_user(array('username'=> (empty($code) ? $_SESSION['user_name'] : $user_info['user_name']), 'old_password'=>$old_password, 'password'=>$new_password), empty($code) ? 0 : 1))
        {
			$sql="UPDATE ".$ecs->table('users'). "SET `ec_salt`='0' WHERE user_id= '".$user_id."'";
			$db->query($sql);
            $user->logout();
            show_message($_LANG['edit_password_success'], $_LANG['relogin_lnk'], 'user.php?act=login', 'info');
        }
        else
        {
            show_message($_LANG['edit_password_failure'], $_LANG['back_page_up'], '', 'info');
        }
    }
    else
    {
        show_message($_LANG['edit_password_failure'], $_LANG['back_page_up'], '', 'info');
    }

}

/* 添加一个红包 */
elseif ($action == 'act_add_bonus')
{
    include_once(ROOT_PATH . 'includes/lib_transaction.php');

    $bouns_sn = isset($_POST['bonus_sn']) ? intval($_POST['bonus_sn']) : '';

    if (add_bonus($user_id, $bouns_sn))
    {
        show_message($_LANG['add_bonus_sucess'], $_LANG['back_up_page'], 'user.php?act=bonus', 'info');
    }
    else
    {
        $err->show($_LANG['back_up_page'], 'user.php?act=bonus');
    }
}

/* 查看订单列表 */
elseif ($action == 'order_list')
{
    include_once(ROOT_PATH . 'includes/lib_transaction.php');

    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

    $status = isset($_REQUEST['status']) ? $_REQUEST['status'] : 'all';
    $where = ' and 1';
    switch($status)
    {
        case 'unpay' :
            $where .= ' and pay_status=0 and order_status<>2 and order_status<>4';
            break;
        case 'deliveryd' :
            $where .= ' and pay_status=2 and shipping_status<>2';
            break;
        case 'complete' :
            $where .= ' and pay_status=2 and shipping_status=2';
            break;
        default:
    }

    $record_count = $db->getOne("SELECT COUNT(*) FROM " .$ecs->table('order_info'). " WHERE user_id = '$user_id'".$where);
    $total_count = $db->getOne("SELECT COUNT(*) FROM " .$ecs->table('order_info'). " WHERE user_id = '$user_id'");
    $unpay_count = $db->getOne("SELECT COUNT(*) FROM " .$ecs->table('order_info'). " WHERE user_id = '$user_id' and pay_status=0 and order_status<>2 and order_status<>4");
    $deliveryd_count = $db->getOne("SELECT COUNT(*) FROM " .$ecs->table('order_info'). " WHERE user_id = '$user_id' and pay_status=2 and shipping_status<>2");
    $complete_count = $db->getOne("SELECT COUNT(*) FROM " .$ecs->table('order_info'). " WHERE user_id = '$user_id' and pay_status=2 and shipping_status=2");
    $count = array('total_count'=>$total_count,'unpay_count'=>$unpay_count,'deliveryd_count'=>$deliveryd_count,'complete_count'=>$complete_count);
    $pager  = get_pager('user.php', array('act' => $action,'status' => $status), $record_count, $page);

    $orders = get_user_orders($user_id, $pager['size'], $pager['start'],$where);
    $merge  = get_user_merge($user_id);

    $smarty->assign('merge',  $merge);
    $smarty->assign('status',  $status);
    $smarty->assign('count',  $count);
    $smarty->assign('pager',  $pager);
    $smarty->assign('orders', $orders);
    $smarty->display('user_transaction.dwt');
}

/* 查看订单详情 */
elseif ($action == 'order_detail')
{
    include_once(ROOT_PATH . 'includes/lib_transaction.php');
    include_once(ROOT_PATH . 'includes/lib_payment.php');
    include_once(ROOT_PATH . 'includes/lib_order.php');
    include_once(ROOT_PATH . 'includes/lib_clips.php');

    $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

    /* 订单详情 */
    $order = get_order_detail($order_id, $user_id);

    if ($order === false)
    {
        $err->show($_LANG['back_home_lnk'], './');

        exit;
    }

    /* 是否显示添加到购物车 */
    if ($order['extension_code'] != 'group_buy' && $order['extension_code'] != 'exchange_goods')
    {
        $smarty->assign('allow_to_cart', 1);
    }

    /* 订单商品 */
    $goods_list = order_goods($order_id);
    $is_real = true;
    $sum_goodsnum = 0;
    foreach ($goods_list AS $key => $value)
    {
        //判断商品类型为虚拟商品时，跳过收货人填写
        if($value["extension_code"] == 'goods_members'){
            $is_real = false;
        }
        $sum_goodsnum += $goods_list[$key]['goods_number'];
        $goods_list[$key]['market_price'] = price_format($value['market_price'], false);
        $goods_list[$key]['goods_price']  = price_format($value['goods_price'], false);
        $goods_list[$key]['subtotal']     = price_format($value['subtotal'], false);
    }

     /* 设置能否修改使用余额数 */
    if ($order['order_amount'] > 0)
    {
        if ($order['order_status'] == OS_UNCONFIRMED || $order['order_status'] == OS_CONFIRMED)
        {
            $user = user_info($order['user_id']);
            if ($user['user_money'] + $user['credit_line'] > 0)
            {
                $smarty->assign('allow_edit_surplus', 1);
                $smarty->assign('max_surplus', sprintf($_LANG['max_surplus'], $user['user_money']));
            }
        }
    }

    /* 未发货，未付款时允许更换支付方式 */
    if ($order['order_amount'] > 0 && $order['pay_status'] == PS_UNPAYED && $order['shipping_status'] == SS_UNSHIPPED)
    {
        $goods_ids = array();
        foreach ($goods_list as $goods)
        {
            $goods_ids[] = $goods['goods_id'];
        }
        $goods_ids = array_unique(array_filter($goods_ids));
        //获取可用的支付方式（如果有多个物品，要取多个物品公共的）- add by qihua on 20130816
        $payment_list = available_payment_list_by_goods($goods_ids, false, 0, true);
        //过滤掉微信支付
        foreach ($payment_list as $key=>$val)
        {
            if ($val['pay_code'] == 'weixinpay')
            {
                unset($payment_list[$key]);
            }
        }
        //$payment_list = available_payment_list(false, 0, true);

        /* 过滤掉余额支付方式 */
        if(is_array($payment_list))
        {
            foreach ($payment_list as $key => $payment)
            {
                if ($payment['pay_code'] == 'balance')
                {
                    unset($payment_list[$key]);
                }
            }
        }
        $smarty->assign('payment_list', $payment_list);
    }

    /* 发票信息 */
    $invoice = order_invoice($user_id);
    if($order['has_invoiced'] == 1 && $invoice){
        $order['invoice_content'] = '商品明细';
        if($invoice['type'] == 1){
            $order['invoice_type'] = '个人';
        }
        else{
            $order['invoice_type'] = $invoice['company_info'];
        }
    }

    $has_payed = false;
    if($order['order_status'] == 2)
    {
        $has_payed = true;
    }
    else
    {
        if($order['pay_status'] == 2){
            $has_payed = true;
        }
    }
    if($order['pay_id']==18)
    {
        $has_payed = true;
    }
    $smarty->assign('order_status', $order['order_status']);
    /* 订单 支付 配送 状态语言项 */
    $order['show_order_status']=$order['order_status'];
    $order['order_status'] = $_LANG['os'][$order['order_status']];
    $order['pay_status'] = $_LANG['ps'][$order['pay_status']];
    $order['shipping_status'] = $_LANG['ss'][$order['shipping_status']];

    $order['add_time'] = local_date('Y-m-d H:i:s', $order['add_time']);
    /* 取得区域名 */
    $sql = "SELECT concat(IFNULL(p.region_name, ''), " .
        "'  ', IFNULL(t.region_name, ''), '  ', IFNULL(d.region_name, '')) AS region " .
        "FROM " . $ecs->table('order_info') . " AS u " .
        "LEFT JOIN " . $ecs->table('region') . " AS p ON u.province = p.region_id " .
        "LEFT JOIN " . $ecs->table('region') . " AS t ON u.city = t.region_id " .
        "LEFT JOIN " . $ecs->table('region') . " AS d ON u.district = d.region_id " .
        "WHERE u.order_id = '$order[order_id]'";
    $region = $db->getOne($sql);
    if(!empty($region)){
        $order["address_name"] = $region.' '.$order["address"];
    }
    //获取物流信息
    $postcom = get_shipping_code($order["shipping_name"]);
    $getNu = $order['shipping_no'];
    $url = "http://www.kuaidi100.com/query?type=$postcom&postid=$getNu";
    $json_info = array(json_decode(file_get_contents($url)));
    $shipping_info['status'] = 0;
    $shipping_info['data'] = '';
    $data = array();
    if($json_info[0]->status == 200){
        $shipping_info['status'] = 1;
        foreach($json_info[0]->data as $val){
            $arr['time'] = $val->time;
            $arr['context'] = $val->context;
            array_push($data,$arr);
        }
        $shipping_info['data'] = $data;
    }
    $exchange_goods = false;
    //如果是积分订单
    if($order['extension_code'] == 'exchange_goods')
    {
        $exchange_goods = true;
        $order['formated_money_paid'] = $order['integral'];
        $order['pay_name'] = "积分";
    }
    $smarty->assign('extension_code',      $exchange_goods);
    $smarty->assign('shipping_info',      $shipping_info);
    $smarty->assign('sum_goodsnum',      $sum_goodsnum);
    $smarty->assign('order',      $order);
    $smarty->assign('goods_list', $goods_list);
    $smarty->assign('is_real', $is_real);
    $smarty->assign('has_payed', $has_payed);
    $smarty->assign('has_invoiced', $order['has_invoiced']);
    $smarty->display('user_transaction.dwt');
}

/* 取消订单 */
elseif ($action == 'cancel_order')
{
    include_once(ROOT_PATH . 'includes/lib_transaction.php');
    include_once(ROOT_PATH . 'includes/lib_order.php');

    $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

    if (cancel_order($order_id, $user_id))
    {
        ecs_header("Location: user.php?act=order_list\n");
        exit;
    }
    else
    {
        $err->show($_LANG['order_list_lnk'], 'user.php?act=order_list');
    }
}

/* 收货地址列表界面*/
elseif ($action == 'address_list')
{
    include_once(ROOT_PATH . 'includes/lib_transaction.php');
    include_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/shopping_flow.php');
    $smarty->assign('lang',  $_LANG);

    /* 取得国家列表、商店所在国家、商店所在国家的省列表 */
    //$smarty->assign('country_list',       get_regions());
    $smarty->assign('shop_province_list', get_regions(1, $_CFG['shop_country']));

    /* 获得用户所有的收货人信息 */
    $consignee_list = get_consignee_list($_SESSION['user_id']);


//    if (count($consignee_list) < 5 && $_SESSION['user_id'] > 0)
//    {
//        /* 如果用户收货人信息的总数小于5 则增加一个新的收货人信息 */
//        $consignee_list[] = array('country' => $_CFG['shop_country'], 'email' => isset($_SESSION['email']) ? $_SESSION['email'] : '');
//    }
    if (count($consignee_list)>=5 && $_SESSION['user_id'] > 0)
    {
        $smarty->assign('address_count', 1);
    }
    //取得国家列表，如果有收货人列表，取得省市区列表
    foreach ($consignee_list AS $region_id => $consignee)
    {
        $consignee['country']  = isset($consignee['country'])  ? intval($consignee['country'])  : 0;
        $consignee['province'] = isset($consignee['province']) ? intval($consignee['province']) : 0;
        $consignee['city']     = isset($consignee['city'])     ? intval($consignee['city'])     : 0;
        /* 取得区域名 */
        $sql = "SELECT concat(IFNULL(p.region_name, ''), " .
            "'  ', IFNULL(t.region_name, ''), '  ', IFNULL(d.region_name, '')) AS region " .
            "FROM " . $ecs->table('user_address') . " AS u " .
            "LEFT JOIN " . $ecs->table('region') . " AS p ON u.province = p.region_id " .
            "LEFT JOIN " . $ecs->table('region') . " AS t ON u.city = t.region_id " .
            "LEFT JOIN " . $ecs->table('region') . " AS d ON u.district = d.region_id " .
            "WHERE u.address_id = '$consignee[address_id]'";
        $region = $db->getOne($sql);
        if(!empty($region)){
            $consignee_list[$region_id]["address_name"] = $region.' '.$consignee["address"];
        }

        $province_list[$region_id] = get_regions(1, $consignee['country']);
        $city_list[$region_id]     = get_regions(2, $consignee['province']);
        $district_list[$region_id] = get_regions(3, $consignee['city']);
    }
    $smarty->assign('consignee_list', $consignee_list);

    /* 获取默认收货ID */
    $address_id  = $db->getOne("SELECT address_id FROM " .$ecs->table('users'). " WHERE user_id='$user_id'");

    //赋值于模板
    $smarty->assign('real_goods_count', 1);
    $smarty->assign('address_id', $address_id);
    $smarty->assign('shop_country',     $_CFG['shop_country']);
    $smarty->assign('shop_province',    get_regions(1, $_CFG['shop_country']));
    $smarty->assign('province_list',    $province_list);
    $smarty->assign('city_list',        $city_list);
    $smarty->assign('district_list',    $district_list);
    $smarty->assign('currency_format',  $_CFG['currency_format']);
    $smarty->assign('integral_scale',   $_CFG['integral_scale']);
    $smarty->assign('name_of_region',   array($_CFG['name_of_region_1'], $_CFG['name_of_region_2'], $_CFG['name_of_region_3'], $_CFG['name_of_region_4']));

    $smarty->display('user_transaction.dwt');
}

/* 添加/编辑收货地址的处理 */
elseif ($action == 'act_edit_address')
{
    include_once(ROOT_PATH . 'includes/lib_transaction.php');
    include_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/shopping_flow.php');
    $smarty->assign('lang', $_LANG);

    $address = array(
        'user_id'    => $user_id,
        'default'    => 1,
        'address_id' => intval($_POST['address_id']),
        'country'    => isset($_POST['country'])   ? intval($_POST['country'])  : 1,
        'province'   => isset($_POST['province'])  ? intval($_POST['province']) : 0,
        'city'       => isset($_POST['city'])      ? intval($_POST['city'])     : 0,
        'district'   => isset($_POST['district'])  ? intval($_POST['district']) : 0,
        'address'    => isset($_POST['address'])   ? compile_str(trim($_POST['address']))    : '',
        'consignee'  => isset($_POST['consignee']) ? compile_str(trim($_POST['consignee']))  : '',
        'email'      => isset($_POST['email'])     ? compile_str(trim($_POST['email']))      : '',
        'tel'        => isset($_POST['tel'])       ? compile_str(make_semiangle(trim($_POST['tel']))) : '',
        'mobile'     => isset($_POST['mobile'])    ? compile_str(make_semiangle(trim($_POST['mobile']))) : '',
        'id_card'     => isset($_POST['id_card'])    ? compile_str(trim($_POST['id_card'])) : '',
        'best_time'  => isset($_POST['best_time']) ? compile_str(trim($_POST['best_time']))  : '',
        'sign_building' => isset($_POST['sign_building']) ? compile_str(trim($_POST['sign_building'])) : '',
        'zipcode'       => isset($_POST['zipcode'])       ? compile_str(make_semiangle(trim($_POST['zipcode']))) : '',
        );

    if (update_address($address))
    {
        show_message($_LANG['edit_address_success'], $_LANG['address_list_lnk'], 'user.php?act=address_list');
    }
}

/* 异步添加/编辑收货地址的处理 */
elseif ($action == 'ajax_edit_address')
{
    include_once(ROOT_PATH . 'includes/lib_transaction.php');
    include_once(ROOT_PATH . 'languages/' .$_CFG['lang']. '/shopping_flow.php');
    include_once('includes/cls_json.php');
    $smarty->assign('lang', $_LANG);
    $json = new JSON;

    $r['errorcode']   = 0;
    $r['msg'] = '操作成功！';

    $address = array(
        'user_id'    => $user_id,
        'default'    => 1,
        'address_id' => intval($_POST['address_id']),
        'country'    => isset($_POST['country'])   ? intval($_POST['country'])  : 1,
        'province'   => isset($_POST['province'])  ? intval($_POST['province']) : 0,
        'city'       => isset($_POST['city'])      ? intval($_POST['city'])     : 0,
        'district'   => isset($_POST['district'])  ? intval($_POST['district']) : 0,
        'address'    => isset($_POST['address'])   ? compile_str(trim($_POST['address']))    : '',
        'consignee'  => isset($_POST['consignee']) ? compile_str(trim($_POST['consignee']))  : '',
        'email'      => isset($_POST['email'])     ? compile_str(trim($_POST['email']))      : '',
        'tel'        => isset($_POST['tel'])       ? compile_str(make_semiangle(trim($_POST['tel']))) : '',
        'mobile'     => isset($_POST['mobile'])    ? compile_str(make_semiangle(trim($_POST['mobile']))) : '',
        'best_time'  => isset($_POST['best_time']) ? compile_str(trim($_POST['best_time']))  : '',
        'id_card'  => isset($_POST['id_card']) ? compile_str(trim($_POST['id_card']))  : '',
        'sign_building' => isset($_POST['sign_building']) ? compile_str(trim($_POST['sign_building'])) : '',
        'zipcode'       => isset($_POST['zipcode'])       ? compile_str(make_semiangle(trim($_POST['zipcode']))) : '',
    );
    if($address['id_card']=='')
    {
        unset($address['id_card']);
    }
    if (!update_address($address))
    {
        $r['errorcode']   = 1;
        $r['msg'] = '操作失败！';
    }
    //修复结算页修改地址后默认选中地址没有变化BUG

    die($json->encode($r));
}

/* 删除收货地址 */
elseif ($action == 'drop_consignee')
{
    include_once('includes/lib_transaction.php');

    $consignee_id = intval($_GET['id']);
    if($consignee_id == $_SESSION['flow_consignee']['address_id']){
        unset($_SESSION['flow_consignee']);
    }
    $flowconsignee_id = intval($_GET['flowconsignee_id']);
    if($flowconsignee_id==1)
    {
        if (drop_consignee($consignee_id))
        {
            ecs_header("Location: flow.php?step=checkout\n");
            exit;
        }
        else
        {
            show_message($_LANG['del_address_false']);
        }
    }
    else
    {
        if (drop_consignee($consignee_id))
        {
            ecs_header("Location: user.php?act=address_list\n");
            exit;
        }
        else
        {
            show_message($_LANG['del_address_false']);
        }
    }
}

/* 显示收藏商品列表 */
elseif ($action == 'collection_list')
{
    include_once(ROOT_PATH . 'includes/lib_clips.php');

    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

    $record_count = $db->getOne("SELECT COUNT(*) FROM " .$ecs->table('collect_goods').
                                " WHERE user_id='$user_id' ORDER BY add_time DESC");

    $pager = get_pager('user.php', array('act' => $action), $record_count, $page);
    $smarty->assign('pager', $pager);
    $smarty->assign('goods_list', get_collection_goods($user_id, $pager['size'], $pager['start']));
    $smarty->assign('url',        $ecs->url());
    $lang_list = array(
        'UTF8'   => $_LANG['charset']['utf8'],
        'GB2312' => $_LANG['charset']['zh_cn'],
        'BIG5'   => $_LANG['charset']['zh_tw'],
    );
    $smarty->assign('lang_list',  $lang_list);
    $smarty->assign('user_id',  $user_id);
    $smarty->display('user_clips.dwt');
}

/* 删除收藏的商品 */
elseif ($action == 'delete_collection')
{
    include_once(ROOT_PATH . 'includes/lib_clips.php');

    $collection_id = isset($_GET['collection_id']) ? intval($_GET['collection_id']) : 0;

    if ($collection_id > 0)
    {
        $db->query('DELETE FROM ' .$ecs->table('collect_goods'). " WHERE rec_id='$collection_id' AND user_id ='$user_id'" );
    }

    ecs_header("Location: user.php?act=collection_list\n");
    exit;
}

/* 添加关注商品 */
elseif ($action == 'add_to_attention')
{
    $rec_id = (int)$_GET['rec_id'];
    if ($rec_id)
    {
        $db->query('UPDATE ' .$ecs->table('collect_goods'). "SET is_attention = 1 WHERE rec_id='$rec_id' AND user_id ='$user_id'" );
    }
    ecs_header("Location: user.php?act=collection_list\n");
    exit;
}
/* 取消关注商品 */
elseif ($action == 'del_attention')
{
    $rec_id = (int)$_GET['rec_id'];
    if ($rec_id)
    {
        $db->query('UPDATE ' .$ecs->table('collect_goods'). "SET is_attention = 0 WHERE rec_id='$rec_id' AND user_id ='$user_id'" );
    }
    ecs_header("Location: user.php?act=collection_list\n");
    exit;
}
/* 显示留言列表 */
elseif ($action == 'message_list')
{
    include_once(ROOT_PATH . 'includes/lib_clips.php');

    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

    $order_id = empty($_GET['order_id']) ? 0 : intval($_GET['order_id']);
    $order_info = array();

    /* 获取用户留言的数量 */
    if ($order_id)
    {
        $sql = "SELECT COUNT(*) FROM " .$ecs->table('feedback').
                " WHERE parent_id = 0 AND order_id = '$order_id' AND user_id = '$user_id'";
        $order_info = $db->getRow("SELECT * FROM " . $ecs->table('order_info') . " WHERE order_id = '$order_id' AND user_id = '$user_id'");
        $order_info['url'] = 'user.php?act=order_detail&order_id=' . $order_id;
    }
    else
    {
        $sql = "SELECT COUNT(*) FROM " .$ecs->table('feedback').
           " WHERE parent_id = 0 AND user_id = '$user_id' AND user_name = '" . $_SESSION['user_name'] . "' AND order_id=0";
    }

    $record_count = $db->getOne($sql);
    $act = array('act' => $action);

    if ($order_id != '')
    {
        $act['order_id'] = $order_id;
    }

    $pager = get_pager('user.php', $act, $record_count, $page, 5);

    $smarty->assign('message_list', get_message_list($user_id, $_SESSION['user_name'], $pager['size'], $pager['start'], $order_id));
    $smarty->assign('pager',        $pager);
    $smarty->assign('order_info',   $order_info);
    $smarty->display('user_clips.dwt');
}
/* 显示咨询列表 */
elseif ($action == 'consult_list')
{
    include_once(ROOT_PATH . 'includes/lib_clips.php');

    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

    $smarty->display('user_clips.dwt');
}
/* 显示评论列表 */
elseif ($action == 'comment_list')
{
    include_once(ROOT_PATH . 'includes/lib_clips.php');

    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

    /* 获取用户留言的数量 */
    $sql = "SELECT COUNT(*) FROM " .$ecs->table('comment').
           " WHERE parent_id = 0 AND user_id = '$user_id'";
    $record_count = $db->getOne($sql);
    $pager = get_pager('user.php', array('act' => $action), $record_count, $page, 5);

    $smarty->assign('comment_list', get_comment_list($user_id, $pager['size'], $pager['start']));
    $smarty->assign('pager',        $pager);
    $smarty->display('user_clips.dwt');
}
/*我的会员权益*/
elseif ($action == 'member_rights')
{
    include_once(ROOT_PATH . 'includes/lib_clips.php');

    $smarty->display('user_clips.dwt');
}
/*修改密码*/
elseif ($action == 'update_pwd')
{
    include_once(ROOT_PATH . 'includes/lib_clips.php');

    $smarty->display('user_clips.dwt');
}
/*    发票信息   */
elseif ($action == 'invoice_information')
{
    include_once(ROOT_PATH . 'includes/lib_clips.php');

    $invoice = get_invoice_info($user_id);

    $smarty->assign('invoice',        $invoice);
    $smarty->display('user_clips.dwt');
}
/*  我的积分 */
elseif ($action == 'integral')
{
    include_once(ROOT_PATH . 'includes/lib_clips.php');
    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

    $account_type = 'pay_points';

    /* 获取记录条数 */
    $sql = "SELECT COUNT(*) FROM " .$ecs->table('account_log').
        " WHERE user_id = '$user_id'" .
        " AND $account_type <> 0 ";
    $record_count = $db->getOne($sql);

    //分页函数
    $pager = get_pager('user.php', array('act' => $action), $record_count, $page);

    //获取积分
    $sql_integral = "SELECT pay_points FROM " .$ecs->table('users').
        " WHERE user_id = '$user_id'";
    $integral = $db->getOne($sql_integral);
    if (empty($integral))
    {
        $integral = 0;
    }

    //获取余额记录
    $account_log = array();
    $sql = "SELECT * FROM " . $ecs->table('account_log') .
        " WHERE user_id = '$user_id'" .
        " AND $account_type <> 0 " .
        " ORDER BY log_id DESC";
    $res = $GLOBALS['db']->selectLimit($sql, $pager['size'], $pager['start']);
    while ($row = $db->fetchRow($res))
    {
        $row['change_time'] = local_date('Y-m-d H:i:s', $row['change_time']);
        $row['type'] = $row[$account_type] > 0 ? 0 : 1;
        $row['frozen_money'] = price_format(abs($row['frozen_money']), false);
        $row['rank_points'] = abs($row['rank_points']);
        $row['pay_points'] = $row['pay_points'];
        $row['short_change_desc'] = sub_str($row['change_desc'], 60);
        $row['amount'] = $row[$account_type];
        $account_log[] = $row;
    }

    //模板赋值
    $smarty->assign('integral', $integral);
    $smarty->assign('account_log',    $account_log);
    $smarty->assign('pager',          $pager);
    $smarty->display('user_clips.dwt');
}
/*  我的礼品卡 */
elseif ($action == 'gift_card')
{
    include_once(ROOT_PATH . 'includes/lib_clips.php');

    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    $record_count = $db->getOne("SELECT COUNT(*) FROM " .$ecs->table('gift_card'). " WHERE member_id = '$user_id'");

    $pager = get_pager('user.php', array('act' => $action), $record_count, $page);
    $gift = get_user_gift_list($user_id, $pager['size'], $pager['start']);
    $smarty->assign('pager', $pager);
    $smarty->assign('gift', $gift);

    $smarty->display('user_clips.dwt');
}

/* 添加一个礼品卡 */
elseif ($action == 'act_add_gift')
{
    include_once(ROOT_PATH . 'includes/lib_transaction.php');

    $gift_sn = $_POST['gift_sn'];
    $gift_password = $_POST['gift_password'];
    if (add_gift($user_id, $gift_sn,$gift_password))
    {
        show_message($_LANG['add_gift_sucess'], $_LANG['back_up_page'], 'user.php?act=gift_card', 'info');
    }
    else
    {
        $err->show($_LANG['back_up_page'], 'user.php?act=gift_card');
    }
}

/* 合并礼品卡 */
elseif ($action == 'act_merge_gift')
{
    include_once(ROOT_PATH . 'includes/lib_transaction.php');

    $gift_sn = $_POST['gift_sn'];
    $merge_gift_sn = $_POST['merge_gift_sn'];
//    print_r($_POST);
    if (merge_gift($user_id, $gift_sn,$merge_gift_sn))
    {
        show_message($_LANG['merge_gift_sucess'], $_LANG['back_up_page'], 'user.php?act=gift_card', 'info');
    }
    else
    {
        $err->show($_LANG['back_up_page'], 'user.php?act=gift_card');
    }
}
/* 添加一张优惠券 */
elseif ($action == 'act_add_coupons')
{
    include_once(ROOT_PATH . 'includes/lib_transaction.php');

    $bonus_sn = $_POST['bonus_sn'];
    if (add_bonus($user_id, $bonus_sn))
    {
        show_message($_LANG['bonus_add_success'], $_LANG['back_up_page'], 'user.php?act=coupons', 'info');
    }
    else
    {
        $err->show($_LANG['back_up_page'], 'user.php?act=coupons');
    }
}
/*  我的优惠券 */
elseif ($action == 'coupons')
{
    include_once(ROOT_PATH . 'includes/lib_clips.php');

    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    $status = isset($_REQUEST['status']) ? $_REQUEST['status'] : 'avl';

    $day = getdate();
    $cur_date = local_mktime(23, 59, 59, $day['mon'], $day['mday'], $day['year']);

    $used_count = $db->getOne("SELECT COUNT(*) FROM " .$ecs->table('user_bonus'). " WHERE order_id > 0 and user_id = '$user_id'");
    $unavl_count = $db->getOne("SELECT COUNT(*) FROM " .$ecs->table('user_bonus'). " AS u ,".
        $ecs->table('bonus_type'). " AS b".
        " WHERE u.bonus_type_id = b.type_id AND u.use_end_datetime < $cur_date AND order_id = 0 AND u.user_id = '" .$user_id. "'");
    $avl_count = $db->getOne("SELECT COUNT(*) FROM " .$ecs->table('user_bonus'). " AS u ,".
        $ecs->table('bonus_type'). " AS b".
        " WHERE u.bonus_type_id = b.type_id AND u.use_end_datetime >= $cur_date AND order_id = 0 AND u.user_id = '" .$user_id. "'");

    $record_count = 0;
    switch ($status)
    {
        case 'used' :
            $record_count = $used_count;
            break;
        case 'unavl' :
            $record_count = $unavl_count;
            break;
        default:
            $record_count = $avl_count;
            break;
    }

    $pager = get_pager('user.php', array('act' => $action,'status' => $status), $record_count, $page);

    $bonus = get_user_bouns_lists($user_id, $pager['size'], $pager['start'], $status);
    $smarty->assign('status', $status);
    $smarty->assign('used_count', $used_count);
    $smarty->assign('unavl_count', $unavl_count);
    $smarty->assign('avl_count', $avl_count);
    $smarty->assign('pager', $pager);
    $smarty->assign('bonus', $bonus);
    $smarty->display('user_clips.dwt');
}
/*  我的关注 */
elseif ($action == 'follow')
{
    include_once(ROOT_PATH . 'includes/lib_clips.php');

    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

    $record_count = $db->getOne("SELECT COUNT(*) FROM " .$ecs->table('collect_goods').
        " WHERE user_id='$user_id' ORDER BY add_time DESC");

    $pager = get_pager('user.php', array('act' => $action), $record_count, $page, 8);
    $smarty->assign('pager', $pager);
    $smarty->assign('goods_list', get_collection_goods($user_id, $pager['size'], $pager['start']));

    $smarty->display('user_clips.dwt');
}
/* 添加我的留言 */
elseif ($action == 'act_add_message')
{
    include_once(ROOT_PATH . 'includes/lib_clips.php');

    $message = array(
        'user_id'     => $user_id,
        'user_name'   => $_SESSION['user_name'],
        'user_email'  => $_SESSION['email'],
        'msg_type'    => isset($_POST['msg_type']) ? intval($_POST['msg_type'])     : 0,
        'msg_title'   => isset($_POST['msg_title']) ? trim($_POST['msg_title'])     : '',
        'msg_content' => isset($_POST['msg_content']) ? trim($_POST['msg_content']) : '',
        'order_id'=>empty($_POST['order_id']) ? 0 : intval($_POST['order_id']),
        'upload'      => (isset($_FILES['message_img']['error']) && $_FILES['message_img']['error'] == 0) || (!isset($_FILES['message_img']['error']) && isset($_FILES['message_img']['tmp_name']) && $_FILES['message_img']['tmp_name'] != 'none')
         ? $_FILES['message_img'] : array()
     );

    if (add_message($message))
    {
        show_message($_LANG['add_message_success'], $_LANG['message_list_lnk'], 'user.php?act=message_list&order_id=' . $message['order_id'],'info');
    }
    else
    {
        $err->show($_LANG['message_list_lnk'], 'user.php?act=message_list');
    }
}

/* 标签云列表 */
elseif ($action == 'tag_list')
{
    include_once(ROOT_PATH . 'includes/lib_clips.php');

    $good_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

    $smarty->assign('tags',      get_user_tags($user_id));
    $smarty->assign('tags_from', 'user');
    $smarty->display('user_clips.dwt');
}

/* 删除标签云的处理 */
elseif ($action == 'act_del_tag')
{
    include_once(ROOT_PATH . 'includes/lib_clips.php');

    $tag_words = isset($_GET['tag_words']) ? trim($_GET['tag_words']) : '';
    delete_tag($tag_words, $user_id);

    ecs_header("Location: user.php?act=tag_list\n");
    exit;

}

/* 显示缺货登记列表 */
elseif ($action == 'booking_list')
{
    include_once(ROOT_PATH . 'includes/lib_clips.php');

    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

    /* 获取缺货登记的数量 */
    $sql = "SELECT COUNT(*) " .
            "FROM " .$ecs->table('booking_goods'). " AS bg, " .
                     $ecs->table('goods') . " AS g " .
            "WHERE bg.goods_id = g.goods_id AND user_id = '$user_id'";
    $record_count = $db->getOne($sql);
    $pager = get_pager('user.php', array('act' => $action), $record_count, $page);

    $smarty->assign('booking_list', get_booking_list($user_id, $pager['size'], $pager['start']));
    $smarty->assign('pager',        $pager);
    $smarty->display('user_clips.dwt');
}
/* 添加缺货登记页面 */
elseif ($action == 'add_booking')
{
    include_once(ROOT_PATH . 'includes/lib_clips.php');

    $goods_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($goods_id == 0)
    {
        show_message($_LANG['no_goods_id'], $_LANG['back_page_up'], '', 'error');
    }

    /* 根据规格属性获取货品规格信息 */
    $goods_attr = '';
    if ($_GET['spec'] != '')
    {
        $goods_attr_id = $_GET['spec'];

        $attr_list = array();
        $sql = "SELECT a.attr_name, g.attr_value " .
                "FROM " . $ecs->table('goods_attr') . " AS g, " .
                    $ecs->table('attribute') . " AS a " .
                "WHERE g.attr_id = a.attr_id " .
                "AND g.goods_attr_id " . db_create_in($goods_attr_id);
        $res = $db->query($sql);
        while ($row = $db->fetchRow($res))
        {
            $attr_list[] = $row['attr_name'] . ': ' . $row['attr_value'];
        }
        $goods_attr = join(chr(13) . chr(10), $attr_list);
    }
    $smarty->assign('goods_attr', $goods_attr);

    $smarty->assign('info', get_goodsinfo($goods_id));
    $smarty->display('user_clips.dwt');

}

/* 添加缺货登记的处理 */
elseif ($action == 'act_add_booking')
{
    include_once(ROOT_PATH . 'includes/lib_clips.php');

    $booking = array(
        'goods_id'     => isset($_POST['id'])      ? intval($_POST['id'])     : 0,
        'goods_amount' => isset($_POST['number'])  ? intval($_POST['number']) : 0,
        'desc'         => isset($_POST['desc'])    ? trim($_POST['desc'])     : '',
        'linkman'      => isset($_POST['linkman']) ? trim($_POST['linkman'])  : '',
        'email'        => isset($_POST['email'])   ? trim($_POST['email'])    : '',
        'tel'          => isset($_POST['tel'])     ? trim($_POST['tel'])      : '',
        'booking_id'   => isset($_POST['rec_id'])  ? intval($_POST['rec_id']) : 0
    );

    // 查看此商品是否已经登记过
    $rec_id = get_booking_rec($user_id, $booking['goods_id']);
    if ($rec_id > 0)
    {
        show_message($_LANG['booking_rec_exist'], $_LANG['back_page_up'], '', 'error');
    }

    if (add_booking($booking))
    {
        show_message($_LANG['booking_success'], $_LANG['back_booking_list'], 'user.php?act=booking_list',
        'info');
    }
    else
    {
        $err->show($_LANG['booking_list_lnk'], 'user.php?act=booking_list');
    }
}

/* 删除缺货登记 */
elseif ($action == 'act_del_booking')
{
    include_once(ROOT_PATH . 'includes/lib_clips.php');

    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($id == 0 || $user_id == 0)
    {
        ecs_header("Location: user.php?act=booking_list\n");
        exit;
    }

    $result = delete_booking($id, $user_id);
    if ($result)
    {
        ecs_header("Location: user.php?act=booking_list\n");
        exit;
    }
}

/* 确认收货 */
elseif ($action == 'affirm_received')
{
    include_once(ROOT_PATH . 'includes/lib_transaction.php');

    $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

    if (affirm_received($order_id, $user_id))
    {
        include_once(ROOT_PATH . 'includes/lib_order.php');
        $order = order_info($order_id);
        /* 计算并发放积分 */
        $integral = integral_to_give($order);

        log_account_change($order['user_id'], 0, 0, intval($integral['rank_points']), intval($integral['custom_points']), sprintf($_LANG['order_gift_integral'], $order['order_sn']),99,$order['integrals'],$order['order_sn']);

        ecs_header("Location: user.php?act=order_list\n");
        exit;
    }
    else
    {
        $err->show($_LANG['order_list_lnk'], 'user.php?act=order_list');
    }
}

/* 会员退款申请界面 */
elseif ($action == 'account_raply')
{
    $smarty->display('user_transaction.dwt');
}

/* 会员预付款界面 */
elseif ($action == 'account_deposit')
{
    include_once(ROOT_PATH . 'includes/lib_clips.php');

    $surplus_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $account    = get_surplus_info($surplus_id);

    $smarty->assign('payment', get_online_payment_list(false));
    $smarty->assign('order',   $account);
    $smarty->display('user_transaction.dwt');
}

/* 会员账目明细界面 */
elseif ($action == 'account_detail')
{
    include_once(ROOT_PATH . 'includes/lib_clips.php');

    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

    $account_type = 'user_money';

    /* 获取记录条数 */
    $sql = "SELECT COUNT(*) FROM " .$ecs->table('account_log').
           " WHERE user_id = '$user_id'" .
           " AND $account_type <> 0 ";
    $record_count = $db->getOne($sql);

    //分页函数
    $pager = get_pager('user.php', array('act' => $action), $record_count, $page);

    //获取剩余余额
    $surplus_amount = get_user_surplus($user_id);
    if (empty($surplus_amount))
    {
        $surplus_amount = 0;
    }

    //获取余额记录
    $account_log = array();
    $sql = "SELECT * FROM " . $ecs->table('account_log') .
           " WHERE user_id = '$user_id'" .
           " AND $account_type <> 0 " .
           " ORDER BY log_id DESC";
    $res = $GLOBALS['db']->selectLimit($sql, $pager['size'], $pager['start']);
    while ($row = $db->fetchRow($res))
    {
        $row['change_time'] = local_date($_CFG['date_format'], $row['change_time']);
        $row['type'] = $row[$account_type] > 0 ? $_LANG['account_inc'] : $_LANG['account_dec'];
        $row['user_money'] = price_format(abs($row['user_money']), false);
        $row['frozen_money'] = price_format(abs($row['frozen_money']), false);
        $row['rank_points'] = abs($row['rank_points']);
        $row['pay_points'] = abs($row['pay_points']);
        $row['short_change_desc'] = sub_str($row['change_desc'], 60);
        $row['amount'] = $row[$account_type];
        $account_log[] = $row;
    }

    //模板赋值
    $smarty->assign('surplus_amount', price_format($surplus_amount, false));
    $smarty->assign('account_log',    $account_log);
    $smarty->assign('pager',          $pager);
    $smarty->display('user_transaction.dwt');
}

/* 会员充值和提现申请记录 */
elseif ($action == 'account_log')
{
    include_once(ROOT_PATH . 'includes/lib_clips.php');
    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

    $account_type = 'user_money';

    /* 获取记录条数 */
    $sql = "SELECT COUNT(*) FROM " .$ecs->table('account_log').
        " WHERE user_id = '$user_id'" .
        " AND $account_type <> 0 ";
    $record_count = $db->getOne($sql);

    //分页函数
    $pager = get_pager('user.php', array('act' => $action), $record_count, $page);

    //获取剩余余额
    $surplus_amount = get_user_surplus($user_id);
    if (empty($surplus_amount))
    {
        $surplus_amount = 0;
    }

    //获取余额记录
    $account_log = array();
    $sql = "SELECT * FROM " . $ecs->table('account_log') .
        " WHERE user_id = '$user_id'" .
        " AND $account_type <> 0 " .
        " ORDER BY log_id DESC";
    $res = $GLOBALS['db']->selectLimit($sql, $pager['size'], $pager['start']);
    while ($row = $db->fetchRow($res))
    {
        $row['change_time'] = local_date('Y-m-d H:i:s', $row['change_time']);
        $row['type'] = $row[$account_type] > 0 ? 0 : 1;
        $row['frozen_money'] = price_format(abs($row['frozen_money']), false);
        $row['rank_points'] = abs($row['rank_points']);
        $row['pay_points'] = abs($row['pay_points']);
        $row['short_change_desc'] = sub_str($row['change_desc'], 60);
        $row['amount'] = $row[$account_type];
        $account_log[] = $row;
    }

    //模板赋值
    $smarty->assign('surplus_amount', price_format($surplus_amount, false));
    $smarty->assign('account_log',    $account_log);
    $smarty->assign('pager',          $pager);
    $smarty->display('user_transaction.dwt');
}

/* 对会员余额申请的处理 */
elseif ($action == 'act_account')
{
    include_once(ROOT_PATH . 'includes/lib_clips.php');
    include_once(ROOT_PATH . 'includes/lib_order.php');
    $amount = isset($_POST['amount']) ? floatval($_POST['amount']) : 0;
    if ($amount <= 0)
    {
        show_message($_LANG['amount_gt_zero']);
    }

    /* 变量初始化 */
    $surplus = array(
            'user_id'      => $user_id,
            'rec_id'       => !empty($_POST['rec_id'])      ? intval($_POST['rec_id'])       : 0,
            'process_type' => isset($_POST['surplus_type']) ? intval($_POST['surplus_type']) : 0,
            'payment_id'   => isset($_POST['payment_id'])   ? intval($_POST['payment_id'])   : 0,
            'user_note'    => isset($_POST['user_note'])    ? trim($_POST['user_note'])      : '',
            'amount'       => $amount
    );

    /* 退款申请的处理 */
    if ($surplus['process_type'] == 1)
    {
        /* 判断是否有足够的余额的进行退款的操作 */
        $sur_amount = get_user_surplus($user_id);
        if ($amount > $sur_amount)
        {
            $content = $_LANG['surplus_amount_error'];
            show_message($content, $_LANG['back_page_up'], '', 'info');
        }

        //插入会员账目明细
        $amount = '-'.$amount;
        $surplus['payment'] = '';
        $surplus['rec_id']  = insert_user_account($surplus, $amount);

        /* 如果成功提交 */
        if ($surplus['rec_id'] > 0)
        {
            $content = $_LANG['surplus_appl_submit'];
            show_message($content, $_LANG['back_account_log'], 'user.php?act=account_log', 'info');
        }
        else
        {
            $content = $_LANG['process_false'];
            show_message($content, $_LANG['back_page_up'], '', 'info');
        }
    }
    /* 如果是会员预付款，跳转到下一步，进行线上支付的操作 */
    else
    {
        if ($surplus['payment_id'] <= 0)
        {
            show_message($_LANG['select_payment_pls']);
        }

        include_once(ROOT_PATH .'includes/lib_payment.php');

        //获取支付方式名称
        $payment_info = array();
        $payment_info = payment_info($surplus['payment_id']);
        $surplus['payment'] = $payment_info['pay_name'];

        if ($surplus['rec_id'] > 0)
        {
            //更新会员账目明细
            $surplus['rec_id'] = update_user_account($surplus);
        }
        else
        {
            //插入会员账目明细
            $surplus['rec_id'] = insert_user_account($surplus, $amount);
        }

        //取得支付信息，生成支付代码
        $payment = unserialize_config($payment_info['pay_config']);

        //生成伪订单号, 不足的时候补0
        $order = array();
        $order['order_sn']       = $surplus['rec_id'];
        $order['user_name']      = $_SESSION['user_name'];
        $order['surplus_amount'] = $amount;

        //计算支付手续费用
        $payment_info['pay_fee'] = pay_fee($surplus['payment_id'], $order['surplus_amount'], 0);

        //计算此次预付款需要支付的总金额
        $order['order_amount']   = $amount + $payment_info['pay_fee'];

        //记录支付log
        $order['log_id'] = insert_pay_log($surplus['rec_id'], $order['order_amount'], $type=PAY_SURPLUS, 0);

        /* 调用相应的支付方式文件 */
        include_once(ROOT_PATH . 'includes/modules/payment/' . $payment_info['pay_code'] . '.php');

        /* 取得在线支付方式的支付按钮 */
        $pay_obj = new $payment_info['pay_code'];
        $payment_info['pay_button'] = $pay_obj->get_code($order, $payment);

        /* 模板赋值 */
        $smarty->assign('payment', $payment_info);
        $smarty->assign('pay_fee', price_format($payment_info['pay_fee'], false));
        $smarty->assign('amount',  price_format($amount, false));
        $smarty->assign('order',   $order);
        $smarty->display('user_transaction.dwt');
    }
}

/* 删除会员余额 */
elseif ($action == 'cancel')
{
    include_once(ROOT_PATH . 'includes/lib_clips.php');

    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($id == 0 || $user_id == 0)
    {
        ecs_header("Location: user.php?act=account_log\n");
        exit;
    }

    $result = del_user_account($id, $user_id);
    if ($result)
    {
        ecs_header("Location: user.php?act=account_log\n");
        exit;
    }
}

/* 会员通过帐目明细列表进行再付款的操作 */
elseif ($action == 'pay')
{
    include_once(ROOT_PATH . 'includes/lib_clips.php');
    include_once(ROOT_PATH . 'includes/lib_payment.php');
    include_once(ROOT_PATH . 'includes/lib_order.php');

    //变量初始化
    $surplus_id = isset($_GET['id'])  ? intval($_GET['id'])  : 0;
    $payment_id = isset($_GET['pid']) ? intval($_GET['pid']) : 0;

    if ($surplus_id == 0)
    {
        ecs_header("Location: user.php?act=account_log\n");
        exit;
    }

    //如果原来的支付方式已禁用或者已删除, 重新选择支付方式
    if ($payment_id == 0)
    {
        ecs_header("Location: user.php?act=account_deposit&id=".$surplus_id."\n");
        exit;
    }

    //获取单条会员帐目信息
    $order = array();
    $order = get_surplus_info($surplus_id);

    //支付方式的信息
    $payment_info = array();
    $payment_info = payment_info($payment_id);

    /* 如果当前支付方式没有被禁用，进行支付的操作 */
    if (!empty($payment_info))
    {
        //取得支付信息，生成支付代码
        $payment = unserialize_config($payment_info['pay_config']);

        //生成伪订单号
        $order['order_sn'] = $surplus_id;

        //获取需要支付的log_id
        $order['log_id'] = get_paylog_id($surplus_id, $pay_type = PAY_SURPLUS);

        $order['user_name']      = $_SESSION['user_name'];
        $order['surplus_amount'] = $order['amount'];

        //计算支付手续费用
        $payment_info['pay_fee'] = pay_fee($payment_id, $order['surplus_amount'], 0);

        //计算此次预付款需要支付的总金额
        $order['order_amount']   = $order['surplus_amount'] + $payment_info['pay_fee'];

        //如果支付费用改变了，也要相应的更改pay_log表的order_amount
        $order_amount = $db->getOne("SELECT order_amount FROM " .$ecs->table('pay_log')." WHERE log_id = '$order[log_id]'");
        if ($order_amount <> $order['order_amount'])
        {
            $db->query("UPDATE " .$ecs->table('pay_log').
                       " SET order_amount = '$order[order_amount]' WHERE log_id = '$order[log_id]'");
        }

        /* 调用相应的支付方式文件 */
        include_once(ROOT_PATH . 'includes/modules/payment/' . $payment_info['pay_code'] . '.php');

        /* 取得在线支付方式的支付按钮 */
        $pay_obj = new $payment_info['pay_code'];
        $payment_info['pay_button'] = $pay_obj->get_code($order, $payment);

        /* 模板赋值 */
        $smarty->assign('payment', $payment_info);
        $smarty->assign('order',   $order);
        $smarty->assign('pay_fee', price_format($payment_info['pay_fee'], false));
        $smarty->assign('amount',  price_format($order['surplus_amount'], false));
        $smarty->assign('action',  'act_account');
        $smarty->display('user_transaction.dwt');
    }
    /* 重新选择支付方式 */
    else
    {
        include_once(ROOT_PATH . 'includes/lib_clips.php');

        $smarty->assign('payment', get_online_payment_list());
        $smarty->assign('order',   $order);
        $smarty->assign('action',  'account_deposit');
        $smarty->display('user_transaction.dwt');
    }
}

/* 添加标签(ajax) */
elseif ($action == 'add_tag')
{
    include_once('includes/cls_json.php');
    include_once('includes/lib_clips.php');

    $result = array('error' => 0, 'message' => '', 'content' => '');
    $id     = isset($_POST['id']) ? intval($_POST['id']) : 0;
    $tag    = isset($_POST['tag']) ? json_str_iconv(trim($_POST['tag'])) : '';

    if ($user_id == 0)
    {
        /* 用户没有登录 */
        $result['error']   = 1;
        $result['message'] = $_LANG['tag_anonymous'];
    }
    else
    {
        add_tag($id, $tag); // 添加tag
        clear_cache_files('goods'); // 删除缓存

        /* 重新获得该商品的所有缓存 */
        $arr = get_tags($id);

        foreach ($arr AS $row)
        {
            $result['content'][] = array('word' => htmlspecialchars($row['tag_words']), 'count' => $row['tag_count']);
        }
    }

    $json = new JSON;

    echo $json->encode($result);
    exit;
}

/* 添加收藏商品(ajax) */
elseif ($action == 'collect')
{
    include_once(ROOT_PATH .'includes/cls_json.php');
    $json = new JSON();
    $result = array('error' => 0, 'message' => '');
    $goods_id = $_GET['id'];

    if (!isset($_SESSION['user_id']) || $_SESSION['user_id'] == 0)
    {
        $result['error'] = 1;
        $result['message'] = $_LANG['login_please'];
        die($json->encode($result));
    }
    else
    {
        /* 检查是否已经存在于用户的收藏夹 */
        $sql = "SELECT COUNT(*) FROM " .$GLOBALS['ecs']->table('collect_goods') .
            " WHERE user_id='$_SESSION[user_id]' AND goods_id = '$goods_id'";
        if ($GLOBALS['db']->GetOne($sql) > 0)
        {
            $result['error'] = 1;
            $result['message'] = $GLOBALS['_LANG']['collect_existed'];
            die($json->encode($result));
        }
        else
        {
            $time = gmtime();
            $sql = "INSERT INTO " .$GLOBALS['ecs']->table('collect_goods'). " (user_id, goods_id, add_time)" .
                    "VALUES ('$_SESSION[user_id]', '$goods_id', '$time')";

            if ($GLOBALS['db']->query($sql) === false)
            {
                $result['error'] = 1;
                $result['message'] = $GLOBALS['db']->errorMsg();
                die($json->encode($result));
            }
            else
            {
                clear_cache_files('goods'); // 删除缓存
                $result['error'] = 0;
                $result['message'] = $GLOBALS['_LANG']['collect_success'];
                die($json->encode($result));
            }
        }
    }
}

/* 删除留言 */
elseif ($action == 'del_msg')
{
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    $order_id = empty($_GET['order_id']) ? 0 : intval($_GET['order_id']);

    if ($id > 0)
    {
        $sql = 'SELECT user_id, message_img FROM ' .$ecs->table('feedback'). " WHERE msg_id = '$id' LIMIT 1";
        $row = $db->getRow($sql);
        if ($row && $row['user_id'] == $user_id)
        {
            /* 验证通过，删除留言，回复，及相应文件 */
            if ($row['message_img'])
            {
                @unlink(ROOT_PATH . DATA_DIR . '/feedbackimg/'. $row['message_img']);
            }
            $sql = "DELETE FROM " .$ecs->table('feedback'). " WHERE msg_id = '$id' OR parent_id = '$id'";
            $db->query($sql);
        }
    }
    ecs_header("Location: user.php?act=message_list&order_id=$order_id\n");
    exit;
}

/* 删除评论 */
elseif ($action == 'del_cmt')
{
    $id = isset($_GET['id']) ? intval($_GET['id']) : 0;
    if ($id > 0)
    {
        $sql = "DELETE FROM " .$ecs->table('comment'). " WHERE comment_id = '$id' AND user_id = '$user_id'";
        $db->query($sql);
    }
    ecs_header("Location: user.php?act=comment_list\n");
    exit;
}

/* 合并订单 */
elseif ($action == 'merge_order')
{
    include_once(ROOT_PATH .'includes/lib_transaction.php');
    include_once(ROOT_PATH .'includes/lib_order.php');
    $from_order = isset($_POST['from_order']) ? trim($_POST['from_order']) : '';
    $to_order   = isset($_POST['to_order']) ? trim($_POST['to_order']) : '';
    if (merge_user_order($from_order, $to_order, $user_id))
    {
        show_message($_LANG['merge_order_success'],$_LANG['order_list_lnk'],'user.php?act=order_list', 'info');
    }
    else
    {
        $err->show($_LANG['order_list_lnk']);
    }
}
/* 将指定订单中商品添加到购物车 */
elseif ($action == 'return_to_cart')
{
    include_once(ROOT_PATH .'includes/cls_json.php');
    include_once(ROOT_PATH .'includes/lib_transaction.php');
    $json = new JSON();

    $result = array('error' => 0, 'message' => '', 'content' => '');
    $order_id = isset($_POST['order_id']) ? intval($_POST['order_id']) : 0;
    if ($order_id == 0)
    {
        $result['error']   = 1;
        $result['message'] = $_LANG['order_id_empty'];
        die($json->encode($result));
    }

    if ($user_id == 0)
    {
        /* 用户没有登录 */
        $result['error']   = 1;
        $result['message'] = $_LANG['login_please'];
        die($json->encode($result));
    }

    /* 检查订单是否属于该用户 */
    $order_user = $db->getOne("SELECT user_id FROM " .$ecs->table('order_info'). " WHERE order_id = '$order_id'");
    if (empty($order_user))
    {
        $result['error'] = 1;
        $result['message'] = $_LANG['order_exist'];
        die($json->encode($result));
    }
    else
    {
        if ($order_user != $user_id)
        {
            $result['error'] = 1;
            $result['message'] = $_LANG['no_priv'];
            die($json->encode($result));
        }
    }

    $message = return_to_cart($order_id);

    if ($message === true)
    {
        $result['error'] = 0;
        $result['message'] = $_LANG['return_to_cart_success'];
        die($json->encode($result));
    }
    else
    {
        $result['error'] = 1;
        $result['message'] = $_LANG['order_exist'];
        die($json->encode($result));
    }

}

/* 编辑使用余额支付的处理 */
elseif ($action == 'act_edit_surplus')
{
    /* 检查是否登录 */
    if ($_SESSION['user_id'] <= 0)
    {
        ecs_header("Location: ./\n");
        exit;
    }

    /* 检查订单号 */
    $order_id = intval($_POST['order_id']);
    if ($order_id <= 0)
    {
        ecs_header("Location: ./\n");
        exit;
    }

    /* 检查余额 */
    $surplus = floatval($_POST['surplus']);
    if ($surplus <= 0)
    {
        $err->add($_LANG['error_surplus_invalid']);
        $err->show($_LANG['order_detail'], 'user.php?act=order_detail&order_id=' . $order_id);
    }

    include_once(ROOT_PATH . 'includes/lib_order.php');

    /* 取得订单 */
    $order = order_info($order_id);
    if (empty($order))
    {
        ecs_header("Location: ./\n");
        exit;
    }

    /* 检查订单用户跟当前用户是否一致 */
    if ($_SESSION['user_id'] != $order['user_id'])
    {
        ecs_header("Location: ./\n");
        exit;
    }

    /* 检查订单是否未付款，检查应付款金额是否大于0 */
    if ($order['pay_status'] != PS_UNPAYED || $order['order_amount'] <= 0)
    {
        $err->add($_LANG['error_order_is_paid']);
        $err->show($_LANG['order_detail'], 'user.php?act=order_detail&order_id=' . $order_id);
    }

    /* 计算应付款金额（减去支付费用） */
    $order['order_amount'] -= $order['pay_fee'];

    /* 余额是否超过了应付款金额，改为应付款金额 */
    if ($surplus > $order['order_amount'])
    {
        $surplus = $order['order_amount'];
    }

    /* 取得用户信息 */
    $user = user_info($_SESSION['user_id']);

    /* 用户帐户余额是否足够 */
    if ($surplus > $user['user_money'] + $user['credit_line'])
    {
        $err->add($_LANG['error_surplus_not_enough']);
        $err->show($_LANG['order_detail'], 'user.php?act=order_detail&order_id=' . $order_id);
    }

    /* 修改订单，重新计算支付费用 */
    $order['surplus'] += $surplus;
    $order['order_amount'] -= $surplus;
    if ($order['order_amount'] > 0)
    {
        $cod_fee = 0;
        if ($order['shipping_id'] > 0)
        {
            $regions  = array($order['country'], $order['province'], $order['city'], $order['district']);
            $shipping = shipping_area_info($order['shipping_id'], $regions);
            if ($shipping['support_cod'] == '1')
            {
                $cod_fee = $shipping['pay_fee'];
            }
        }

        $pay_fee = 0;
        if ($order['pay_id'] > 0)
        {
            $pay_fee = pay_fee($order['pay_id'], $order['order_amount'], $cod_fee);
        }

        $order['pay_fee'] = $pay_fee;
        $order['order_amount'] += $pay_fee;
    }

    /* 如果全部支付，设为已确认、已付款 */
    if ($order['order_amount'] == 0)
    {
        if ($order['order_status'] == OS_UNCONFIRMED)
        {
            $order['order_status'] = OS_CONFIRMED;
            $order['confirm_time'] = gmtime();
        }
        $order['pay_status'] = PS_PAYED;
        $order['pay_time'] = gmtime();
    }
    $order = addslashes_deep($order);
    update_order($order_id, $order);

    /* 更新用户余额 */
    $change_desc = sprintf($_LANG['pay_order_by_surplus'], $order['order_sn']);
    log_account_change($user['user_id'], (-1) * $surplus, 0, 0, 0, $change_desc);

    /* 跳转 */
    ecs_header('Location: user.php?act=order_detail&order_id=' . $order_id . "\n");
    exit;
}

/* 编辑使用余额支付的处理 */
elseif ($action == 'act_edit_payment')
{
    /* 检查是否登录 */
    if ($_SESSION['user_id'] <= 0)
    {
        ecs_header("Location: ./\n");
        exit;
    }

    /* 检查支付方式 */
    $pay_id = intval($_POST['pay_id']);
    if ($pay_id <= 0)
    {
        ecs_header("Location: ./\n");
        exit;
    }

    include_once(ROOT_PATH . 'includes/lib_order.php');
    $payment_info = payment_info($pay_id);
    if (empty($payment_info))
    {
        ecs_header("Location: ./\n");
        exit;
    }

    /* 检查订单号 */
    $order_id = intval($_POST['order_id']);
    if ($order_id <= 0)
    {
        ecs_header("Location: ./\n");
        exit;
    }

    /* 取得订单 */
    $order = order_info($order_id);
    $sql = 'SELECT log_id FROM ' . $ecs->table('pay_log') . " WHERE order_id='$order_id' AND order_amount='".$order['order_amount']."'";
    $logId = $db->getOne($sql);
    $order['log_id'] = $logId;
    if (empty($logId))
    {
        ecs_header("Location: ./\n");
        exit;
    }

    if (empty($order))
    {
        ecs_header("Location: ./\n");
        exit;
    }

    /* 检查订单用户跟当前用户是否一致 */
    if ($_SESSION['user_id'] != $order['user_id'])
    {
        ecs_header("Location: ./\n");
        exit;
    }

    /* 检查订单是否未付款和未发货 以及订单金额是否为0 和支付id是否为改变*/
    if ($order['pay_status'] != PS_UNPAYED || $order['shipping_status'] != SS_UNSHIPPED || $order['goods_amount'] <= 0)
    {
        ecs_header("Location: user.php?act=order_detail&order_id=$order_id\n");
        exit;
    }

    $order_amount = $order['order_amount'] - $order['pay_fee'];
    $pay_fee = pay_fee($pay_id, $order_amount);
    $order_amount += $pay_fee;

    $bankradios = '';
    if ($payment_info['pay_code'] == 'ebcolpay' && isset($_POST['bankradios']))
    {
        $bankradios = $_POST['bankradios'];
    }
    if ($payment_info['pay_code'] == 'olpay' && isset($_POST['bankradios']))
    {
        $bankradios = $_POST['bankradios'];
    }

    $sql = "UPDATE " . $ecs->table('order_info') .
           " SET pay_id='$pay_id', pay_name='$payment_info[pay_name]', pay_fee='$pay_fee', order_amount='$order_amount', inv_content='$bankradios'".
           " WHERE order_id = '$order_id'";
    $db->query($sql);

    //取得支付信息，生成支付代码
    $payment = unserialize_config($payment_info['pay_config']);
    /* 调用相应的支付方式文件 */
    include_once(ROOT_PATH . 'includes/modules/payment/' . $payment_info['pay_code'] . '.php');

    /* 取得在线支付方式的支付按钮 */
    $pay_obj = new $payment_info['pay_code'];
    $form = '';
    if ($payment_info['pay_code'] == 'olpay' || $payment_info['pay_code'] == 'ebcolpay')
    {
        $form = $pay_obj->redirect_pay($order, $payment, $bankradios, $bank_code_arr);
    }
    else
    {
        $form = $pay_obj->redirect_pay($order, $payment);
    }

    echo $form;
    /* 跳转 */
//    ecs_header("Location: user.php?act=order_detail&order_id=$order_id\n");
    exit;
}

/* 保存订单详情收货地址 */
elseif ($action == 'save_order_address')
{
    include_once(ROOT_PATH .'includes/lib_transaction.php');
    
    $address = array(
        'consignee' => isset($_POST['consignee']) ? compile_str(trim($_POST['consignee']))  : '',
        'email'     => isset($_POST['email'])     ? compile_str(trim($_POST['email']))      : '',
        'address'   => isset($_POST['address'])   ? compile_str(trim($_POST['address']))    : '',
        'zipcode'   => isset($_POST['zipcode'])   ? compile_str(make_semiangle(trim($_POST['zipcode']))) : '',
        'tel'       => isset($_POST['tel'])       ? compile_str(trim($_POST['tel']))        : '',
        'mobile'    => isset($_POST['mobile'])    ? compile_str(trim($_POST['mobile']))     : '',
        'sign_building' => isset($_POST['sign_building']) ? compile_str(trim($_POST['sign_building'])) : '',
        'best_time' => isset($_POST['best_time']) ? compile_str(trim($_POST['best_time']))  : '',
        'order_id'  => isset($_POST['order_id'])  ? intval($_POST['order_id']) : 0
        );
    if (save_order_address($address, $user_id))
    {
        ecs_header('Location: user.php?act=order_detail&order_id=' .$address['order_id']. "\n");
        exit;
    }
    else
    {
        $err->show($_LANG['order_list_lnk'], 'user.php?act=order_list');
    }
}

/* 我的红包列表 */
elseif ($action == 'bonus')
{
    include_once(ROOT_PATH .'includes/lib_transaction.php');

    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;
    $record_count = $db->getOne("SELECT COUNT(*) FROM " .$ecs->table('user_bonus'). " WHERE user_id = '$user_id'");

    $pager = get_pager('user.php', array('act' => $action), $record_count, $page);
    $bonus = get_user_bouns_list($user_id, $pager['size'], $pager['start']);

    $smarty->assign('pager', $pager);
    $smarty->assign('bonus', $bonus);
    $smarty->display('user_transaction.dwt');
}

/* 我的团购列表 */
elseif ($action == 'group_buy')
{
    include_once(ROOT_PATH .'includes/lib_transaction.php');

    //待议
    $smarty->display('user_transaction.dwt');
}

/* 团购订单详情 */
elseif ($action == 'group_buy_detail')
{
    include_once(ROOT_PATH .'includes/lib_transaction.php');

    //待议
    $smarty->display('user_transaction.dwt');
}

// 用户推荐页面
elseif ($action == 'affiliate')
{
    $goodsid = intval(isset($_REQUEST['goodsid']) ? $_REQUEST['goodsid'] : 0);
    if(empty($goodsid))
    {
        //我的推荐页面

        $page       = !empty($_REQUEST['page'])  && intval($_REQUEST['page'])  > 0 ? intval($_REQUEST['page'])  : 1;
        $size       = !empty($_CFG['page_size']) && intval($_CFG['page_size']) > 0 ? intval($_CFG['page_size']) : 10;

        empty($affiliate) && $affiliate = array();

        if(empty($affiliate['config']['separate_by']))
        {
            //推荐注册分成
            $affdb = array();
            $num = count($affiliate['item']);
            $up_uid = "'$user_id'";
            $all_uid = "'$user_id'";
            for ($i = 1 ; $i <=$num ;$i++)
            {
                $count = 0;
                if ($up_uid)
                {
                    $sql = "SELECT user_id FROM " . $ecs->table('users') . " WHERE parent_id IN($up_uid)";
                    $query = $db->query($sql);
                    $up_uid = '';
                    while ($rt = $db->fetch_array($query))
                    {
                        $up_uid .= $up_uid ? ",'$rt[user_id]'" : "'$rt[user_id]'";
                        if($i < $num)
                        {
                            $all_uid .= ", '$rt[user_id]'";
                        }
                        $count++;
                    }
                }
                $affdb[$i]['num'] = $count;
                $affdb[$i]['point'] = $affiliate['item'][$i-1]['level_point'];
                $affdb[$i]['money'] = $affiliate['item'][$i-1]['level_money'];
            }
            $smarty->assign('affdb', $affdb);

            $sqlcount = "SELECT count(*) FROM " . $ecs->table('order_info') . " o".
        " LEFT JOIN".$ecs->table('users')." u ON o.user_id = u.user_id".
        " LEFT JOIN " . $ecs->table('affiliate_log') . " a ON o.order_id = a.order_id" .
        " WHERE o.user_id > 0 AND (u.parent_id IN ($all_uid) AND o.is_separate = 0 OR a.user_id = '$user_id' AND o.is_separate > 0)";

            $sql = "SELECT o.*, a.log_id, a.user_id as suid,  a.user_name as auser, a.money, a.point, a.separate_type FROM " . $ecs->table('order_info') . " o".
                    " LEFT JOIN".$ecs->table('users')." u ON o.user_id = u.user_id".
                    " LEFT JOIN " . $ecs->table('affiliate_log') . " a ON o.order_id = a.order_id" .
        " WHERE o.user_id > 0 AND (u.parent_id IN ($all_uid) AND o.is_separate = 0 OR a.user_id = '$user_id' AND o.is_separate > 0)".
                    " ORDER BY order_id DESC" ;

            /*
                SQL解释：

                订单、用户、分成记录关联
                一个订单可能有多个分成记录

                1、订单有效 o.user_id > 0
                2、满足以下之一：
                    a.直接下线的未分成订单 u.parent_id IN ($all_uid) AND o.is_separate = 0
                        其中$all_uid为该ID及其下线(不包含最后一层下线)
                    b.全部已分成订单 a.user_id = '$user_id' AND o.is_separate > 0

            */

            $affiliate_intro = nl2br(sprintf($_LANG['affiliate_intro'][$affiliate['config']['separate_by']], $affiliate['config']['expire'], $_LANG['expire_unit'][$affiliate['config']['expire_unit']], $affiliate['config']['level_register_all'], $affiliate['config']['level_register_up'], $affiliate['config']['level_money_all'], $affiliate['config']['level_point_all']));
        }
        else
        {
            //推荐订单分成
            $sqlcount = "SELECT count(*) FROM " . $ecs->table('order_info') . " o".
                    " LEFT JOIN".$ecs->table('users')." u ON o.user_id = u.user_id".
                    " LEFT JOIN " . $ecs->table('affiliate_log') . " a ON o.order_id = a.order_id" .
                    " WHERE o.user_id > 0 AND (o.parent_id = '$user_id' AND o.is_separate = 0 OR a.user_id = '$user_id' AND o.is_separate > 0)";


            $sql = "SELECT o.*, a.log_id,a.user_id as suid, a.user_name as auser, a.money, a.point, a.separate_type,u.parent_id as up FROM " . $ecs->table('order_info') . " o".
                    " LEFT JOIN".$ecs->table('users')." u ON o.user_id = u.user_id".
                    " LEFT JOIN " . $ecs->table('affiliate_log') . " a ON o.order_id = a.order_id" .
                    " WHERE o.user_id > 0 AND (o.parent_id = '$user_id' AND o.is_separate = 0 OR a.user_id = '$user_id' AND o.is_separate > 0)" .
                    " ORDER BY order_id DESC" ;

            /*
                SQL解释：

                订单、用户、分成记录关联
                一个订单可能有多个分成记录

                1、订单有效 o.user_id > 0
                2、满足以下之一：
                    a.订单下线的未分成订单 o.parent_id = '$user_id' AND o.is_separate = 0
                    b.全部已分成订单 a.user_id = '$user_id' AND o.is_separate > 0

            */

            $affiliate_intro = nl2br(sprintf($_LANG['affiliate_intro'][$affiliate['config']['separate_by']], $affiliate['config']['expire'], $_LANG['expire_unit'][$affiliate['config']['expire_unit']], $affiliate['config']['level_money_all'], $affiliate['config']['level_point_all']));

        }

        $count = $db->getOne($sqlcount);

        $max_page = ($count> 0) ? ceil($count / $size) : 1;
        if ($page > $max_page)
        {
            $page = $max_page;
        }

        $res = $db->SelectLimit($sql, $size, ($page - 1) * $size);
        $logdb = array();
        while ($rt = $GLOBALS['db']->fetchRow($res))
        {
            if(!empty($rt['suid']))
            {
                //在affiliate_log有记录
                if($rt['separate_type'] == -1 || $rt['separate_type'] == -2)
                {
                    //已被撤销
                    $rt['is_separate'] = 3;
                }
            }
            $rt['order_sn'] = substr($rt['order_sn'], 0, strlen($rt['order_sn']) - 5) . "***" . substr($rt['order_sn'], -2, 2);
            $logdb[] = $rt;
        }

        $url_format = "user.php?act=affiliate&page=";

        $pager = array(
                    'page'  => $page,
                    'size'  => $size,
                    'sort'  => '',
                    'order' => '',
                    'record_count' => $count,
                    'page_count'   => $max_page,
                    'page_first'   => $url_format. '1',
                    'page_prev'    => $page > 1 ? $url_format.($page - 1) : "javascript:;",
                    'page_next'    => $page < $max_page ? $url_format.($page + 1) : "javascript:;",
                    'page_last'    => $url_format. $max_page,
                    'array'        => array()
                );
        for ($i = 1; $i <= $max_page; $i++)
        {
            $pager['array'][$i] = $i;
        }

        $smarty->assign('url_format', $url_format);
        $smarty->assign('pager', $pager);


        $smarty->assign('affiliate_intro', $affiliate_intro);
        $smarty->assign('affiliate_type', $affiliate['config']['separate_by']);

        $smarty->assign('logdb', $logdb);
    }
    else
    {
        //单个商品推荐
        $smarty->assign('userid', $user_id);
        $smarty->assign('goodsid', $goodsid);

        $types = array(1,2,3,4,5);
        $smarty->assign('types', $types);

        $goods = get_goods_info($goodsid);
        $shopurl = $ecs->url();
        $goods['goods_img'] = (strpos($goods['goods_img'], 'http://') === false && strpos($goods['goods_img'], 'https://') === false) ? $shopurl . $goods['goods_img'] : $goods['goods_img'];
        $goods['goods_thumb'] = (strpos($goods['goods_thumb'], 'http://') === false && strpos($goods['goods_thumb'], 'https://') === false) ? $shopurl . $goods['goods_thumb'] : $goods['goods_thumb'];
        $goods['shop_price'] = price_format($goods['shop_price']);

        $smarty->assign('goods', $goods);
    }

    $smarty->assign('shopname', $_CFG['shop_name']);
    $smarty->assign('userid', $user_id);
    $smarty->assign('shopurl', $ecs->url());
    $smarty->assign('logosrc', 'themes/' . $_CFG['template'] . '/images/logo.gif');

    $smarty->display('user_clips.dwt');
}

//首页邮件订阅ajax操做和验证操作
elseif ($action =='email_list')
{
    $job = $_GET['job'];

    if($job == 'add' || $job == 'del')
    {
        if(isset($_SESSION['last_email_query']))
        {
            if(time() - $_SESSION['last_email_query'] <= 30)
            {
                die($_LANG['order_query_toofast']);
            }
        }
        $_SESSION['last_email_query'] = time();
    }

    $email = trim($_GET['email']);
    $email = htmlspecialchars($email);

    if (!is_email($email))
    {
        $info = sprintf($_LANG['email_invalid'], $email);
        die($info);
    }
    $ck = $db->getRow("SELECT * FROM " . $ecs->table('email_list') . " WHERE email = '$email'");
    if ($job == 'add')
    {
        if (empty($ck))
        {
            $hash = substr(md5(time()), 1, 10);
            $sql = "INSERT INTO " . $ecs->table('email_list') . " (email, stat, hash) VALUES ('$email', 0, '$hash')";
            $db->query($sql);
            $info = $_LANG['email_check'];
            $url = $ecs->url() . "user.php?act=email_list&job=add_check&hash=$hash&email=$email";
            send_mail('', $email, $_LANG['check_mail'], sprintf($_LANG['check_mail_content'], $email, $_CFG['shop_name'], $url, $url, $_CFG['shop_name'], local_date('Y-m-d')), 1);
        }
        elseif ($ck['stat'] == 1)
        {
            $info = sprintf($_LANG['email_alreadyin_list'], $email);
        }
        else
        {
            $hash = substr(md5(time()),1 , 10);
            $sql = "UPDATE " . $ecs->table('email_list') . "SET hash = '$hash' WHERE email = '$email'";
            $db->query($sql);
            $info = $_LANG['email_re_check'];
            $url = $ecs->url() . "user.php?act=email_list&job=add_check&hash=$hash&email=$email";
            send_mail('', $email, $_LANG['check_mail'], sprintf($_LANG['check_mail_content'], $email, $_CFG['shop_name'], $url, $url, $_CFG['shop_name'], local_date('Y-m-d')), 1);
        }
        die($info);
    }
    elseif ($job == 'del')
    {
        if (empty($ck))
        {
            $info = sprintf($_LANG['email_notin_list'], $email);
        }
        elseif ($ck['stat'] == 1)
        {
            $hash = substr(md5(time()),1,10);
            $sql = "UPDATE " . $ecs->table('email_list') . "SET hash = '$hash' WHERE email = '$email'";
            $db->query($sql);
            $info = $_LANG['email_check'];
            $url = $ecs->url() . "user.php?act=email_list&job=del_check&hash=$hash&email=$email";
            send_mail('', $email, $_LANG['check_mail'], sprintf($_LANG['check_mail_content'], $email, $_CFG['shop_name'], $url, $url, $_CFG['shop_name'], local_date('Y-m-d')), 1);
        }
        else
        {
            $info = $_LANG['email_not_alive'];
        }
        die($info);
    }
    elseif ($job == 'add_check')
    {
        if (empty($ck))
        {
            $info = sprintf($_LANG['email_notin_list'], $email);
        }
        elseif ($ck['stat'] == 1)
        {
            $info = $_LANG['email_checked'];
        }
        else
        {
            if ($_GET['hash'] == $ck['hash'])
            {
                $sql = "UPDATE " . $ecs->table('email_list') . "SET stat = 1 WHERE email = '$email'";
                $db->query($sql);
                $info = $_LANG['email_checked'];
            }
            else
            {
                $info = $_LANG['hash_wrong'];
            }
        }
        show_message($info, $_LANG['back_home_lnk'], 'index.php');
    }
    elseif ($job == 'del_check')
    {
        if (empty($ck))
        {
            $info = sprintf($_LANG['email_invalid'], $email);
        }
        elseif ($ck['stat'] == 1)
        {
            if ($_GET['hash'] == $ck['hash'])
            {
                $sql = "DELETE FROM " . $ecs->table('email_list') . "WHERE email = '$email'";
                $db->query($sql);
                $info = $_LANG['email_canceled'];
            }
            else
            {
                $info = $_LANG['hash_wrong'];
            }
        }
        else
        {
            $info = $_LANG['email_not_alive'];
        }
        show_message($info, $_LANG['back_home_lnk'], 'index.php');
    }
}

/* ajax 发送验证邮件 */
elseif ($action == 'send_hash_mail')
{
    include_once(ROOT_PATH .'includes/cls_json.php');
    include_once(ROOT_PATH .'includes/lib_passport.php');
    $json = new JSON();

    $result = array('error' => 0, 'message' => '', 'content' => '');

    if ($user_id == 0)
    {
        /* 用户没有登录 */
        $result['error']   = 1;
        $result['message'] = $_LANG['login_please'];
        die($json->encode($result));
    }

    if (send_regiter_hash($user_id))
    {
        $result['message'] = $_LANG['validate_mail_ok'];
        die($json->encode($result));
    }
    else
    {
        $result['error'] = 1;
        $result['message'] = $GLOBALS['err']->last_message();
    }

    die($json->encode($result));
}
else if ($action == 'track_packages')
{
    include_once(ROOT_PATH . 'includes/lib_transaction.php');
    include_once(ROOT_PATH .'includes/lib_order.php');

    $page = isset($_REQUEST['page']) ? intval($_REQUEST['page']) : 1;

    $orders = array();

    $sql = "SELECT order_id,order_sn,invoice_no,shipping_id FROM " .$ecs->table('order_info').
            " WHERE user_id = '$user_id' AND shipping_status = '" . SS_SHIPPED . "'";
    $res = $db->query($sql);
    $record_count = 0;
    while ($item = $db->fetch_array($res))
    {
        $shipping   = get_shipping_object($item['shipping_id']);

        if (method_exists ($shipping, 'query'))
        {
            $query_link = $shipping->query($item['invoice_no']);
        }
        else
        {
            $query_link = $item['invoice_no'];
        }

        if ($query_link != $item['invoice_no'])
        {
            $item['query_link'] = $query_link;
            $orders[]  = $item;
            $record_count += 1;
        }
    }
    $pager  = get_pager('user.php', array('act' => $action), $record_count, $page);
    $smarty->assign('pager',  $pager);
    $smarty->assign('orders', $orders);
    $smarty->display('user_transaction.dwt');
}
else if ($action == 'order_query')
{
    $_GET['order_sn'] = trim(substr($_GET['order_sn'], 1));
    $order_sn = empty($_GET['order_sn']) ? '' : addslashes($_GET['order_sn']);
    include_once(ROOT_PATH .'includes/cls_json.php');
    $json = new JSON();

    $result = array('error'=>0, 'message'=>'', 'content'=>'');

    if(isset($_SESSION['last_order_query']))
    {
        if(time() - $_SESSION['last_order_query'] <= 10)
        {
            $result['error'] = 1;
            $result['message'] = $_LANG['order_query_toofast'];
            die($json->encode($result));
        }
    }
    $_SESSION['last_order_query'] = time();

    if (empty($order_sn))
    {
        $result['error'] = 1;
        $result['message'] = $_LANG['invalid_order_sn'];
        die($json->encode($result));
    }

    $sql = "SELECT order_id, order_status, shipping_status, pay_status, ".
           " shipping_time, shipping_id, invoice_no, user_id ".
           " FROM " . $ecs->table('order_info').
           " WHERE order_sn = '$order_sn' LIMIT 1";

    $row = $db->getRow($sql);
    if (empty($row))
    {
        $result['error'] = 1;
        $result['message'] = $_LANG['invalid_order_sn'];
        die($json->encode($result));
    }

    $order_query = array();
    $order_query['order_sn'] = $order_sn;
    $order_query['order_id'] = $row['order_id'];
    $order_query['order_status'] = $_LANG['os'][$row['order_status']] . ',' . $_LANG['ps'][$row['pay_status']] . ',' . $_LANG['ss'][$row['shipping_status']];

    if ($row['invoice_no'] && $row['shipping_id'] > 0)
    {
        $sql = "SELECT shipping_code FROM " . $ecs->table('shipping') . " WHERE shipping_id = '$row[shipping_id]'";
        $shipping_code = $db->getOne($sql);
        $plugin = ROOT_PATH . 'includes/modules/shipping/' . $shipping_code . '.php';
        if (file_exists($plugin))
        {
            include_once($plugin);
            $shipping = new $shipping_code;
            $order_query['invoice_no'] = $shipping->query((string)$row['invoice_no']);
        }
        else
        {
            $order_query['invoice_no'] = (string)$row['invoice_no'];
        }
    }

    $order_query['user_id'] = $row['user_id'];
    /* 如果是匿名用户显示发货时间 */
    if ($row['user_id'] == 0 && $row['shipping_time'] > 0)
    {
        $order_query['shipping_date'] = local_date($GLOBALS['_CFG']['date_format'], $row['shipping_time']);
    }
    $smarty->assign('order_query',    $order_query);
    $result['content'] = $smarty->fetch('library/order_query.lbi');
    die($json->encode($result));
}
elseif ($action == 'transform_points')
{
    $rule = array();
    if (!empty($_CFG['points_rule']))
    {
        $rule = unserialize($_CFG['points_rule']);
    }
    $cfg = array();
    if (!empty($_CFG['integrate_config']))
    {
        $cfg = unserialize($_CFG['integrate_config']);
        $_LANG['exchange_points'][0] = empty($cfg['uc_lang']['credits'][0][0])? $_LANG['exchange_points'][0] : $cfg['uc_lang']['credits'][0][0];
        $_LANG['exchange_points'][1] = empty($cfg['uc_lang']['credits'][1][0])? $_LANG['exchange_points'][1] : $cfg['uc_lang']['credits'][1][0];
    }
    $sql = "SELECT user_id, user_name, pay_points, rank_points FROM " . $ecs->table('users')  . " WHERE user_id='$user_id'";
    $row = $db->getRow($sql);
    if ($_CFG['integrate_code'] == 'ucenter')
    {
        $exchange_type = 'ucenter';
        $to_credits_options = array();
        $out_exchange_allow = array();
        foreach ($rule as $credit)
        {
            $out_exchange_allow[$credit['appiddesc'] . '|' . $credit['creditdesc'] . '|' . $credit['creditsrc']] = $credit['ratio'];
            if (!array_key_exists($credit['appiddesc']. '|' .$credit['creditdesc'], $to_credits_options))
            {
                $to_credits_options[$credit['appiddesc']. '|' .$credit['creditdesc']] = $credit['title'];
            }
        }
        $smarty->assign('selected_org', $rule[0]['creditsrc']);
        $smarty->assign('selected_dst', $rule[0]['appiddesc']. '|' .$rule[0]['creditdesc']);
        $smarty->assign('descreditunit', $rule[0]['unit']);
        $smarty->assign('orgcredittitle', $_LANG['exchange_points'][$rule[0]['creditsrc']]);
        $smarty->assign('descredittitle', $rule[0]['title']);
        $smarty->assign('descreditamount', round((1 / $rule[0]['ratio']), 2));
        $smarty->assign('to_credits_options', $to_credits_options);
        $smarty->assign('out_exchange_allow', $out_exchange_allow);
    }
    else
    {
        $exchange_type = 'other';

        $bbs_points_name = $user->get_points_name();
        $total_bbs_points = $user->get_points($row['user_name']);

        /* 论坛积分 */
        $bbs_points = array();
        foreach ($bbs_points_name as $key=>$val)
        {
            $bbs_points[$key] = array('title'=>$_LANG['bbs'] . $val['title'], 'value'=>$total_bbs_points[$key]);
        }

        /* 兑换规则 */
        $rule_list = array();
        foreach ($rule as $key=>$val)
        {
            $rule_key = substr($key, 0, 1);
            $bbs_key = substr($key, 1);
            $rule_list[$key]['rate'] = $val;
            switch ($rule_key)
            {
                case TO_P :
                    $rule_list[$key]['from'] = $_LANG['bbs'] . $bbs_points_name[$bbs_key]['title'];
                    $rule_list[$key]['to'] = $_LANG['pay_points'];
                    break;
                case TO_R :
                    $rule_list[$key]['from'] = $_LANG['bbs'] . $bbs_points_name[$bbs_key]['title'];
                    $rule_list[$key]['to'] = $_LANG['rank_points'];
                    break;
                case FROM_P :
                    $rule_list[$key]['from'] = $_LANG['pay_points'];$_LANG['bbs'] . $bbs_points_name[$bbs_key]['title'];
                    $rule_list[$key]['to'] =$_LANG['bbs'] . $bbs_points_name[$bbs_key]['title'];
                    break;
                case FROM_R :
                    $rule_list[$key]['from'] = $_LANG['rank_points'];
                    $rule_list[$key]['to'] = $_LANG['bbs'] . $bbs_points_name[$bbs_key]['title'];
                    break;
            }
        }
        $smarty->assign('bbs_points', $bbs_points);
        $smarty->assign('rule_list',  $rule_list);
    }
    $smarty->assign('shop_points', $row);
    $smarty->assign('exchange_type',     $exchange_type);
    $smarty->assign('action',     $action);
    $smarty->assign('lang',       $_LANG);
    $smarty->display('user_transaction.dwt');
}
elseif ($action == 'act_transform_points')
{
    $rule_index = empty($_POST['rule_index']) ? '' : trim($_POST['rule_index']);
    $num = empty($_POST['num']) ? 0 : intval($_POST['num']);


    if ($num <= 0 || $num != floor($num))
    {
        show_message($_LANG['invalid_points'], $_LANG['transform_points'], 'user.php?act=transform_points');
    }

    $num = floor($num); //格式化为整数

    $bbs_key = substr($rule_index, 1);
    $rule_key = substr($rule_index, 0, 1);

    $max_num = 0;

    /* 取出用户数据 */
    $sql = "SELECT user_name, user_id, pay_points, rank_points FROM " . $ecs->table('users') . " WHERE user_id='$user_id'";
    $row = $db->getRow($sql);
    $bbs_points = $user->get_points($row['user_name']);
    $points_name = $user->get_points_name();

    $rule = array();
    if ($_CFG['points_rule'])
    {
        $rule = unserialize($_CFG['points_rule']);
    }
    list($from, $to) = explode(':', $rule[$rule_index]);

    $max_points = 0;
    switch ($rule_key)
    {
        case TO_P :
            $max_points = $bbs_points[$bbs_key];
            break;
        case TO_R :
            $max_points = $bbs_points[$bbs_key];
            break;
        case FROM_P :
            $max_points = $row['pay_points'];
            break;
        case FROM_R :
            $max_points = $row['rank_points'];
    }

    /* 检查积分是否超过最大值 */
    if ($max_points <=0 || $num > $max_points)
    {
        show_message($_LANG['overflow_points'], $_LANG['transform_points'], 'user.php?act=transform_points' );
    }

    switch ($rule_key)
    {
        case TO_P :
            $result_points = floor($num * $to / $from);
            $user->set_points($row['user_name'], array($bbs_key=>0 - $num)); //调整论坛积分
            log_account_change($row['user_id'], 0, 0, 0, $result_points, $_LANG['transform_points'], ACT_OTHER);
            show_message(sprintf($_LANG['to_pay_points'],  $num, $points_name[$bbs_key]['title'], $result_points), $_LANG['transform_points'], 'user.php?act=transform_points');

        case TO_R :
            $result_points = floor($num * $to / $from);
            $user->set_points($row['user_name'], array($bbs_key=>0 - $num)); //调整论坛积分
            log_account_change($row['user_id'], 0, 0, $result_points, 0, $_LANG['transform_points'], ACT_OTHER);
            show_message(sprintf($_LANG['to_rank_points'], $num, $points_name[$bbs_key]['title'], $result_points), $_LANG['transform_points'], 'user.php?act=transform_points');

        case FROM_P :
            $result_points = floor($num * $to / $from);
            log_account_change($row['user_id'], 0, 0, 0, 0-$num, $_LANG['transform_points'], ACT_OTHER); //调整商城积分
            $user->set_points($row['user_name'], array($bbs_key=>$result_points)); //调整论坛积分
            show_message(sprintf($_LANG['from_pay_points'], $num, $result_points,  $points_name[$bbs_key]['title']), $_LANG['transform_points'], 'user.php?act=transform_points');

        case FROM_R :
            $result_points = floor($num * $to / $from);
            log_account_change($row['user_id'], 0, 0, 0-$num, 0, $_LANG['transform_points'], ACT_OTHER); //调整商城积分
            $user->set_points($row['user_name'], array($bbs_key=>$result_points)); //调整论坛积分
            show_message(sprintf($_LANG['from_rank_points'], $num, $result_points, $points_name[$bbs_key]['title']), $_LANG['transform_points'], 'user.php?act=transform_points');
    }
}
elseif ($action == 'act_transform_ucenter_points')
{
    $rule = array();
    if ($_CFG['points_rule'])
    {
        $rule = unserialize($_CFG['points_rule']);
    }
    $shop_points = array(0 => 'rank_points', 1 => 'pay_points');
    $sql = "SELECT user_id, user_name, pay_points, rank_points FROM " . $ecs->table('users')  . " WHERE user_id='$user_id'";
    $row = $db->getRow($sql);
    $exchange_amount = intval($_POST['amount']);
    $fromcredits = intval($_POST['fromcredits']);
    $tocredits = trim($_POST['tocredits']);
    $cfg = unserialize($_CFG['integrate_config']);
    if (!empty($cfg))
    {
        $_LANG['exchange_points'][0] = empty($cfg['uc_lang']['credits'][0][0])? $_LANG['exchange_points'][0] : $cfg['uc_lang']['credits'][0][0];
        $_LANG['exchange_points'][1] = empty($cfg['uc_lang']['credits'][1][0])? $_LANG['exchange_points'][1] : $cfg['uc_lang']['credits'][1][0];
    }
    list($appiddesc, $creditdesc) = explode('|', $tocredits);
    $ratio = 0;

    if ($exchange_amount <= 0)
    {
        show_message($_LANG['invalid_points'], $_LANG['transform_points'], 'user.php?act=transform_points');
    }
    if ($exchange_amount > $row[$shop_points[$fromcredits]])
    {
        show_message($_LANG['overflow_points'], $_LANG['transform_points'], 'user.php?act=transform_points');
    }
    foreach ($rule as $credit)
    {
        if ($credit['appiddesc'] == $appiddesc && $credit['creditdesc'] == $creditdesc && $credit['creditsrc'] == $fromcredits)
        {
            $ratio = $credit['ratio'];
            break;
        }
    }
    if ($ratio == 0)
    {
        show_message($_LANG['exchange_deny'], $_LANG['transform_points'], 'user.php?act=transform_points');
    }
    $netamount = floor($exchange_amount / $ratio);
    include_once(ROOT_PATH . './includes/lib_uc.php');
    $result = exchange_points($row['user_id'], $fromcredits, $creditdesc, $appiddesc, $netamount);
    if ($result === true)
    {
        $sql = "UPDATE " . $ecs->table('users') . " SET {$shop_points[$fromcredits]}={$shop_points[$fromcredits]}-'$exchange_amount' WHERE user_id='{$row['user_id']}'";
        $db->query($sql);
        $sql = "INSERT INTO " . $ecs->table('account_log') . "(user_id, {$shop_points[$fromcredits]}, change_time, change_desc, change_type)" . " VALUES ('{$row['user_id']}', '-$exchange_amount', '". gmtime() ."', '" . $cfg['uc_lang']['exchange'] . "', '98')";
        $db->query($sql);
        show_message(sprintf($_LANG['exchange_success'], $exchange_amount, $_LANG['exchange_points'][$fromcredits], $netamount, $credit['title']), $_LANG['transform_points'], 'user.php?act=transform_points');
    }
    else
    {
        show_message($_LANG['exchange_error_1'], $_LANG['transform_points'], 'user.php?act=transform_points');
    }
}
/* 清除商品浏览历史 */
elseif ($action == 'clear_history')
{
    setcookie('ECS[history]',   '', 1);
}

/* 免费领取会员 */
elseif ($action == 'ajax_free_members')
{
    include_once('includes/cls_json.php');

    $json = new JSON;
    $r['errorcode']   = 0;
    $r['msg'] = '领取成功！';
    $user_id = $_SESSION['user_id'];
    $time = gmtime();

    /*
     * 检查用户是否已经登录
     * 如果用户已经登录了则检查是否有默认的收货地址
     * 如果没有登录则跳转到登录和注册页面
     */
    if ($user_id <= 0)
    {
        $r['errorcode']   = 1;
        $r['msg'] = '用户未登录！';
        die($json->encode($r));
    }
    /*
     * 检查用户是否为会员身份
     */
    $is_members = check_user_members($user_id);
    if ($is_members)
    {
        $r['errorcode']   = 2;
        $r['msg'] = '您已经是会员，不能重复领取！';
        die($json->encode($r));
    }

    $has_get = check_free_members($user_id);
    if ($has_get)
    {
        $r['errorcode']   = 3;
        $r['msg'] = '您已经领取过会员，不能重复领取！';
        die($json->encode($r));
    }

    $members_deadline = gmtime() + (90 * 24 * 3600);

    $msql  = "UPDATE " .$GLOBALS['ecs']->table('users'). " SET is_members='1', members_deadline='$members_deadline'".
        " WHERE user_id='" . $user_id . "'";
    $result = $GLOBALS['db']->query($msql);
    if(!$result){
        $result['error'] = 4;
        $result['msg'] = '会员领取失败';
        die($json->encode($result));
    }
    else{
        $sql = "INSERT INTO " .$GLOBALS['ecs']->table('freemembers_log'). " (user_id, has_get, add_time)" .
            "VALUES ('$user_id', '1', '$time')";

        $GLOBALS['db']->query($sql);
    }

    die($json->encode($r));
}
/* 领取樱桃爷爷优惠券 */
elseif ($action == 'ajax_yt_yhq')
{
    include_once('includes/cls_json.php');

    $user_id = $_SESSION['user_id'];
    if ($user_id == 0)
    {
        $_SESSION['back_act'] = 'topic.php?topic_id=72';
        ecs_header("Location: ./user.php?act=login\n");
        exit;
    }

//    $bonus_type = json_str_iconv($_REQUEST['bonus_type']);
    $bonus_type=array(79);
    if ($bonus_type[0] == 0)
    {
        //提示错误参数
        $smarty->assign('error',1);
        show_message("参数错误","","","","","ytyhq");
    }
    $sql = "SELECT reg_time ".
        " FROM " . $GLOBALS['ecs'] ->table('users').
        " WHERE user_id = '$user_id' LIMIT 1";
    $reg_time = $GLOBALS['db']->getOne($sql);

    $sql = "SELECT * ".
        " FROM " . $GLOBALS['ecs'] ->table('bonus_type').
        " WHERE type_id = '$bonus_type[0]' LIMIT 1";
    $bonus = $GLOBALS['db']->getRow($sql);
    if ($reg_time > $send_end_date)
    {
        //优惠券过期
        $smarty->assign('error',1);
        show_message("活动已过期","","","","","ytyhq");
    }
    $sql = "SELECT count(*) FROM " . $ecs->table('user_bonus') .
        " WHERE user_id = '{$user_id}' AND bonus_type_id = '{$bonus_type[0]}'";
    if ($db->getOne($sql) > 0)
    {
        $smarty->assign('error',1);
        show_message("您已经领取过该优惠券","","","","","ytyhq");
    }
    else
    {
        $now = gmtime();
        foreach($bonus_type AS $value)
        {
            $sql = "INSERT INTO " . $ecs->table('user_bonus') .
                "(bonus_type_id, bonus_sn, user_id, used_time, order_id, emailed,binding_time,use_start_datetime,use_end_datetime) " .
                "VALUES ('$value', 0, '$user_id', 0, 0, " .BONUS_MAIL_FAIL. ",'$now','$bonus[use_start_date]','$bonus[use_end_date]')";
            $db->query($sql);
            $new_bonus_id = $db->insert_id();
            if($bonus['use_datetime'] == 1)
            {
                //按起止日期计算
                $use_effective_date = $now+$bonus['use_effective_date']*24 * 3600;
                $m_sql = "UPDATE " .$ecs->table('user_bonus'). " SET ".
                    "use_start_datetime  = '$now', ".
                    "use_end_datetime    = '$use_effective_date' ".
                    "WHERE bonus_id   = '$new_bonus_id'";
                $db->query($m_sql);
            }
        }


        $smarty->assign('error',0);
        show_message("恭喜您领取成功","","","","","ytyhq");
    }
}
/* 领取520活动优惠券 */
elseif ($action == 'ajax_hd_yhq')
{
    include_once('includes/cls_json.php');

    $json = new JSON;
    $user_id = $_SESSION['user_id'];
    if ($user_id == 0)
    {
        //上一页活动地址
        $_SESSION['back_act'] = 'topic.php?topic_id=76';
        $r['errorcode']   = 1;
        $r['msg'] = '用户未登录！';
        die($json->encode($r));
        exit;
    }

//    $bonus_type = json_str_iconv($_REQUEST['bonus_type']);
    $bonus_type=array(88);
    if ($bonus_type[0] == 0)
    {
        //提示错误参数
        $r['errorcode']   = 2;
        $r['msg'] = '参数错误';
        die($json->encode($r));
    }
    $sql = "SELECT reg_time ".
        " FROM " . $GLOBALS['ecs'] ->table('users').
        " WHERE user_id = '$user_id' LIMIT 1";
    $reg_time = $GLOBALS['db']->getOne($sql);

    $sql = "SELECT * ".
        " FROM " . $GLOBALS['ecs'] ->table('bonus_type').
        " WHERE type_id = '$bonus_type[0]' LIMIT 1";
    $bonus = $GLOBALS['db']->getRow($sql);
    if ($reg_time > $bonus['send_end_date'])
    {
        //优惠券过期
        $r['errorcode']   = 2;
        $r['msg'] = '活动已过期';
        die($json->encode($r));
    }
        $now = gmtime();
        foreach($bonus_type AS $value)
        {
            $sql = "INSERT INTO " . $ecs->table('user_bonus') .
                "(bonus_type_id, bonus_sn, user_id, used_time, order_id, emailed,binding_time,use_start_datetime,use_end_datetime) " .
                "VALUES ('$value', 0, '$user_id', 0, 0, " .BONUS_MAIL_FAIL. ",'$now','$bonus[use_start_date]','$bonus[use_end_date]')";
            $db->query($sql);
            $new_bonus_id = $db->insert_id();
            if($bonus['use_datetime'] == 1)
            {
                //按起止日期计算
                $use_effective_date = $now+$bonus['use_effective_date']*24 * 3600;
                $m_sql = "UPDATE " .$ecs->table('user_bonus'). " SET ".
                    "use_start_datetime  = '$now', ".
                    "use_end_datetime    = '$use_effective_date' ".
                    "WHERE bonus_id   = '$new_bonus_id'";
                $db->query($m_sql);
            }
        }

        $r['errorcode']   = 0;
        $r['msg'] = '恭喜您领取成功';
        die($json->encode($r));
//    }
}
/* 领取情人节活动优惠券 */
elseif ($action == 'ajax_Valentine')
{
    include_once('includes/cls_json.php');

    $json = new JSON;
    $user_id = $_SESSION['user_id'];
    if ($user_id == 0)
    {
        //上一页活动地址
        $_SESSION['back_act'] = 'topic.php?topic_id=151';
        $r['errorcode']   = 1;
        $r['msg'] = '用户未登录！';
        die($json->encode($r));
        exit;
    }

//    $bonus_type = json_str_iconv($_REQUEST['bonus_type']);
    $bonus_type=array(151);
    if ($bonus_type[0] == 0)
    {
        //提示错误参数
        $r['errorcode']   = 2;
        $r['msg'] = '参数错误';
        die($json->encode($r));
    }
    $sql = "SELECT reg_time ".
        " FROM " . $GLOBALS['ecs'] ->table('users').
        " WHERE user_id = '$user_id' LIMIT 1";
    $reg_time = $GLOBALS['db']->getOne($sql);

    $sql = "SELECT * ".
        " FROM " . $GLOBALS['ecs'] ->table('bonus_type').
        " WHERE type_id = '$bonus_type[0]' LIMIT 1";
    $bonus = $GLOBALS['db']->getRow($sql);
    if ($reg_time > $bonus['send_end_date'])
    {
        //优惠券过期
        $r['errorcode']   = 2;
        $r['msg'] = '活动已过期';
        die($json->encode($r));
    }
        $now = gmtime();
        foreach($bonus_type AS $value)
        {
            $sql = "INSERT INTO " . $ecs->table('user_bonus') .
                "(bonus_type_id, bonus_sn, user_id, used_time, order_id, emailed,binding_time,use_start_datetime,use_end_datetime) " .
                "VALUES ('$value', 0, '$user_id', 0, 0, " .BONUS_MAIL_FAIL. ",'$now','$bonus[use_start_date]','$bonus[use_end_date]')";
            $db->query($sql);
            $new_bonus_id = $db->insert_id();
            if($bonus['use_datetime'] == 1)
            {
                //按起止日期计算
                $use_effective_date = $now+$bonus['use_effective_date']*24 * 3600;
                $m_sql = "UPDATE " .$ecs->table('user_bonus'). " SET ".
                    "use_start_datetime  = '$now', ".
                    "use_end_datetime    = '$use_effective_date' ".
                    "WHERE bonus_id   = '$new_bonus_id'";
                $db->query($m_sql);
            }
        }

        $r['errorcode']   = 0;
        $r['msg'] = '恭喜您领取成功';
        die($json->encode($r));
//    }
}
/* carnival活动优惠券 */
elseif ($action == 'ajax_carnival_yhq')
{
    include_once('includes/cls_json.php');

    $json = new JSON;
    $user_id = $_SESSION['user_id'];
    if ($user_id == 0)
    {
        //上一页活动地址
        $_SESSION['back_act'] = 'topic.php?topic_id=80';
        $r['errorcode']   = 1;
        $r['msg'] = '用户未登录！';
        die($json->encode($r));
        exit;
    }

//    $bonus_type = json_str_iconv($_REQUEST['bonus_type']);
    $bonus_type=array(98);
    if ($bonus_type[0] == 0)
    {
        //提示错误参数
        $r['errorcode']   = 2;
        $r['msg'] = '参数错误';
        die($json->encode($r));
    }
    $sql = "SELECT reg_time ".
        " FROM " . $GLOBALS['ecs'] ->table('users').
        " WHERE user_id = '$user_id' LIMIT 1";
    $reg_time = $GLOBALS['db']->getOne($sql);

    $sql = "SELECT * ".
        " FROM " . $GLOBALS['ecs'] ->table('bonus_type').
        " WHERE type_id = '$bonus_type[0]' LIMIT 1";
    $bonus = $GLOBALS['db']->getRow($sql);
    if ($reg_time > $bonus['send_end_date'])
    {
        //优惠券过期
        $r['errorcode']   = 2;
        $r['msg'] = '活动已过期';
        die($json->encode($r));
    }
        $now = gmtime();
        foreach($bonus_type AS $value)
        {
            $sql = "INSERT INTO " . $ecs->table('user_bonus') .
                "(bonus_type_id, bonus_sn, user_id, used_time, order_id, emailed,binding_time,use_start_datetime,use_end_datetime) " .
                "VALUES ('$value', 0, '$user_id', 0, 0, " .BONUS_MAIL_FAIL. ",'$now','$bonus[use_start_date]','$bonus[use_end_date]')";
            $db->query($sql);
            $new_bonus_id = $db->insert_id();
            if($bonus['use_datetime'] == 1)
            {
                //按起止日期计算
                $use_effective_date = $now+$bonus['use_effective_date']*24 * 3600;
                $m_sql = "UPDATE " .$ecs->table('user_bonus'). " SET ".
                    "use_start_datetime  = '$now', ".
                    "use_end_datetime    = '$use_effective_date' ".
                    "WHERE bonus_id   = '$new_bonus_id'";
                $db->query($m_sql);
            }
        }

        $r['errorcode']   = 0;
        $r['msg'] = '恭喜您领取成功';
        die($json->encode($r));
//    }
}
/* recruitment活动优惠券 */
elseif ($action == 'ajax_recruitment_yhq')
{
    include_once('includes/cls_json.php');

    $json = new JSON;
    $user_id = $_SESSION['user_id'];
    if ($user_id == 0)
    {
        //上一页活动地址
        $_SESSION['back_act'] = 'topic.php?topic_id=91';
        $r['errorcode']   = 1;
        $r['msg'] = '用户未登录！';
        die($json->encode($r));
        exit;
    }

//    $bonus_type = json_str_iconv($_REQUEST['bonus_type']);
    $bonus_type=array(108,109,110,111,112,113,114);//108 109 110 111 112 113 114
//    $bonus_type=array(84,85,86);
    if ($bonus_type[0] == 0)
    {
        //提示错误参数
        $r['errorcode']   = 2;
        $r['msg'] = '参数错误';
        die($json->encode($r));
    }
    $sql = "SELECT reg_time ".
        " FROM " . $GLOBALS['ecs'] ->table('users').
        " WHERE user_id = '$user_id' LIMIT 1";
    $reg_time = $GLOBALS['db']->getOne($sql);
//    $send_start_date = gmtime();

    $send_start_date = local_mktime(12,00,00,6,15,2016);
//    $send_start_date1 = local_date($GLOBALS['_CFG']['time_format'], $send_start_date);
    if ($reg_time < $send_start_date)
    {
        //提示只允许新用户参加
        $r['errorcode']   = 2;
        $r['msg'] = '该活动只允许新用户参加！';
        die($json->encode($r));
    }
    $i =0;
    $now = gmtime();
    foreach($bonus_type AS $key=>$value)
    {
        if ($bonus_type[$key] == 0)
        {
            //提示错误参数
            $r['errorcode']   = 2;
            $r['msg'] = '参数错误';
            die($json->encode($r));
        }
        $sql = "SELECT send_end_date ".
            " FROM " . $GLOBALS['ecs'] ->table('bonus_type').
            " WHERE type_id = '$bonus_type[$key]' LIMIT 1";
        $send_end_date = $GLOBALS['db']->getOne($sql);
        if ($reg_time > $send_end_date)
        {
            //优惠券过期
            $r['errorcode']   = 2;
            $r['msg'] = '活动已过期';
            die($json->encode($r));
        }
        $sql = "SELECT count(*) FROM " . $ecs->table('user_bonus') .
            " WHERE user_id = '{$user_id}' AND bonus_type_id = '{$bonus_type[$key]}'";
        if ($db->getOne($sql) > 0)
        {
            continue;
        }
        else
        {
            $sql1 = "SELECT * FROM " . $GLOBALS['ecs']->table('bonus_type') .
                " WHERE type_id = '$value'";
            $bonus =  $GLOBALS['db']->getRow($sql1);
            $sql = "INSERT INTO " . $ecs->table('user_bonus') .
                "(bonus_type_id, bonus_sn, user_id, used_time, order_id, emailed,binding_time,use_start_datetime,use_end_datetime) " .
                "VALUES ('$value', 0, '$user_id', 0, 0, " .BONUS_MAIL_FAIL. ",'$now','$bonus[use_start_date]','$bonus[use_end_date]')";
            $db->query($sql);
            $new_bonus_id = $db->insert_id();
            if($bonus['use_datetime'] == 1)
            {
                //按起止日期计算
                $use_effective_date = $now+$bonus['use_effective_date']*24 * 3600;
                $m_sql = "UPDATE " .$GLOBALS['ecs']->table('user_bonus'). " SET ".
                    "use_start_datetime  = '$now', ".
                    "use_end_datetime    = '$use_effective_date' ".
                    "WHERE bonus_id   = '$new_bonus_id'";
                $GLOBALS['db']->query($m_sql);
            }
            $i++;
        }
    }
    if($i > 0)
    {
        $r['errorcode']   = 0;
        $r['msg'] = '恭喜您领取成功';
        die($json->encode($r));exit;
    }
    else
    {
        $r['errorcode']   = 2;
        $r['msg'] = '您已经领取过该优惠券';
        die($json->encode($r));exit;
    }
}
/* 泰国乳胶枕优惠券 */
elseif ($action == 'ajax_hd_rjz')
{
    include_once('includes/cls_json.php');

    $json = new JSON;
    $user_id = $_SESSION['user_id'];
    if ($user_id == 0)
    {
        //上一页活动地址
        $_SESSION['back_act'] = 'topic.php?topic_id=102';
        $r['errorcode']   = 1;
        $r['msg'] = '用户未登录！';
        die($json->encode($r));
        exit;
    }

//    $bonus_type = json_str_iconv($_REQUEST['bonus_type']);
    $bonus_type=array(129);
    if ($bonus_type[0] == 0)
    {
        //提示错误参数
        $r['errorcode']   = 2;
        $r['msg'] = '参数错误';
        die($json->encode($r));
    }
    $sql = "SELECT reg_time ".
        " FROM " . $GLOBALS['ecs'] ->table('users').
        " WHERE user_id = '$user_id' LIMIT 1";
    $reg_time = $GLOBALS['db']->getOne($sql);

    $sql = "SELECT * ".
        " FROM " . $GLOBALS['ecs'] ->table('bonus_type').
        " WHERE type_id = '$bonus_type[0]' LIMIT 1";
    $bonus = $GLOBALS['db']->getRow($sql);
    if ($reg_time > $bonus['send_end_date'])
    {
        //优惠券过期
        $r['errorcode']   = 2;
        $r['msg'] = '活动已过期';
        die($json->encode($r));
    }
    $now = gmtime();
    foreach($bonus_type AS $value)
    {
        $sql = "INSERT INTO " . $ecs->table('user_bonus') .
            "(bonus_type_id, bonus_sn, user_id, used_time, order_id, emailed,binding_time,use_start_datetime,use_end_datetime) " .
            "VALUES ('$value', 0, '$user_id', 0, 0, " .BONUS_MAIL_FAIL. ",'$now','$bonus[use_start_date]','$bonus[use_end_date]')";
        $db->query($sql);
        $new_bonus_id = $db->insert_id();
        if($bonus['use_datetime'] == 1)
        {
            //按起止日期计算
            $use_effective_date = $now+$bonus['use_effective_date']*24 * 3600;
            $m_sql = "UPDATE " .$ecs->table('user_bonus'). " SET ".
                "use_start_datetime  = '$now', ".
                "use_end_datetime    = '$use_effective_date' ".
                "WHERE bonus_id   = '$new_bonus_id'";
            $db->query($m_sql);
        }
    }

    $r['errorcode']   = 0;
    $r['msg'] = '恭喜您领取成功';
    die($json->encode($r));
//    }
}
/*七夕券 */
elseif ($action == 'ajax_hd_qx')
{
    include_once('includes/cls_json.php');

    $json = new JSON;
    $user_id = $_SESSION['user_id'];
    if ($user_id == 0)
    {
        //上一页活动地址
        $_SESSION['back_act'] = 'topic.php?topic_id=102';
        $r['errorcode']   = 1;
        $r['msg'] = '用户未登录！';
        die($json->encode($r));
        exit;
    }

    $att = json_str_iconv($_REQUEST['bonus_type']);
    $att = explode(',',$att);
    $bonus_type[0] = $att[0];
//    $bonus_type=array(129);
    if ($bonus_type[0] == 0)
    {
        //提示错误参数
        $r['errorcode']   = 2;
        $r['msg'] = '参数错误';
        die($json->encode($r));
    }
    $sql = "SELECT reg_time ".
        " FROM " . $GLOBALS['ecs'] ->table('users').
        " WHERE user_id = '$user_id' LIMIT 1";
    $reg_time = $GLOBALS['db']->getOne($sql);

    $sql = "SELECT * ".
        " FROM " . $GLOBALS['ecs'] ->table('bonus_type').
        " WHERE type_id = '$bonus_type[0]' LIMIT 1";
    $bonus = $GLOBALS['db']->getRow($sql);
    if ($reg_time > $bonus['send_end_date'])
    {
        //优惠券过期
        $r['errorcode']   = 2;
        $r['msg'] = '活动已过期';
        die($json->encode($r));
    }
    $now = gmtime();
    foreach($bonus_type AS $value)
    {
        $sql = "INSERT INTO " . $ecs->table('user_bonus') .
            "(bonus_type_id, bonus_sn, user_id, used_time, order_id, emailed,binding_time,use_start_datetime,use_end_datetime) " .
            "VALUES ('$value', 0, '$user_id', 0, 0, " .BONUS_MAIL_FAIL. ",'$now','$bonus[use_start_date]','$bonus[use_end_date]')";
        $db->query($sql);
        $new_bonus_id = $db->insert_id();
        if($bonus['use_datetime'] == 1)
        {
            //按起止日期计算
            $use_effective_date = $now+$bonus['use_effective_date']*24 * 3600;
            $m_sql = "UPDATE " .$ecs->table('user_bonus'). " SET ".
                "use_start_datetime  = '$now', ".
                "use_end_datetime    = '$use_effective_date' ".
                "WHERE bonus_id   = '$new_bonus_id'";
            $db->query($m_sql);
        }
    }

    $r['errorcode']   = 0;
    $r['msg'] = '恭喜您领取成功';
    die($json->encode($r));
//    }
}
/*五一券 */
elseif ($action == 'ajax_hd_wy')
{
    include_once('includes/cls_json.php');

    $json = new JSON;
    $user_id = $_SESSION['user_id'];
    if ($user_id == 0)
    {
        //上一页活动地址
        $_SESSION['back_act'] = 'topic.php?topic_id=157';
        $r['errorcode']   = 1;
        $r['msg'] = '用户未登录！';
        die($json->encode($r));
        exit;
    }

//    $bonus_type = json_str_iconv($_REQUEST['bonus_type']);
    $bonus_type=array(157);
    if ($bonus_type[0] == 0)
    {
        //提示错误参数
        $r['errorcode']   = 2;
        $r['msg'] = '参数错误';
        die($json->encode($r));
    }
    $sql = "SELECT reg_time ".
        " FROM " . $GLOBALS['ecs'] ->table('users').
        " WHERE user_id = '$user_id' LIMIT 1";
    $reg_time = $GLOBALS['db']->getOne($sql);

    $sql = "SELECT * ".
        " FROM " . $GLOBALS['ecs'] ->table('bonus_type').
        " WHERE type_id = '$bonus_type[0]' LIMIT 1";
    $bonus = $GLOBALS['db']->getRow($sql);
    if ($reg_time > $bonus['send_end_date'])
    {
        //优惠券过期
        $r['errorcode']   = 2;
        $r['msg'] = '活动已过期';
        die($json->encode($r));
    }
    $now = gmtime();
    foreach($bonus_type AS $value)
    {
        $sql = "INSERT INTO " . $ecs->table('user_bonus') .
            "(bonus_type_id, bonus_sn, user_id, used_time, order_id, emailed,binding_time,use_start_datetime,use_end_datetime) " .
            "VALUES ('$value', 0, '$user_id', 0, 0, " .BONUS_MAIL_FAIL. ",'$now','$bonus[use_start_date]','$bonus[use_end_date]')";
        $db->query($sql);
        $new_bonus_id = $db->insert_id();
        if($bonus['use_datetime'] == 1)
        {
            //按起止日期计算
            $use_effective_date = $now+$bonus['use_effective_date']*24 * 3600;
            $m_sql = "UPDATE " .$ecs->table('user_bonus'). " SET ".
                "use_start_datetime  = '$now', ".
                "use_end_datetime    = '$use_effective_date' ".
                "WHERE bonus_id   = '$new_bonus_id'";
            $db->query($m_sql);
        }
    }

    $r['errorcode']   = 0;
    $r['msg'] = '恭喜您领取成功';
    die($json->encode($r));
//    }
}

/* 洗衣服优惠券 */
elseif ($action == 'ajax_hd_xyf')
{
    include_once('includes/cls_json.php');

    $json = new JSON;
    $user_id = $_SESSION['user_id'];
    if ($user_id == 0)
    {
        //上一页活动地址
        $_SESSION['back_act'] = 'topic.php?topic_id=188';
        $r['errorcode']   = 1;
        $r['msg'] = '用户未登录！';
        die($json->encode($r));
        exit;
    }

//    $bonus_type = json_str_iconv($_REQUEST['bonus_type']);
    $bonus_type=array(165);
    if ($bonus_type[0] == 0)
    {
        //提示错误参数
        $r['errorcode']   = 2;
        $r['msg'] = '参数错误';
        die($json->encode($r));
    }
    $sql = "SELECT reg_time ".
        " FROM " . $GLOBALS['ecs'] ->table('users').
        " WHERE user_id = '$user_id' LIMIT 1";
    $reg_time = $GLOBALS['db']->getOne($sql);

    $sql = "SELECT * ".
        " FROM " . $GLOBALS['ecs'] ->table('bonus_type').
        " WHERE type_id = '$bonus_type[0]' LIMIT 1";
    $bonus = $GLOBALS['db']->getRow($sql);
    if ($reg_time > $bonus['send_end_date'])
    {
        //优惠券过期
        $r['errorcode']   = 2;
        $r['msg'] = '活动已过期';
        die($json->encode($r));
    }
    $now = gmtime();
    foreach($bonus_type AS $value)
    {
        $sql = "INSERT INTO " . $ecs->table('user_bonus') .
            "(bonus_type_id, bonus_sn, user_id, used_time, order_id, emailed,binding_time,use_start_datetime,use_end_datetime) " .
            "VALUES ('$value', 0, '$user_id', 0, 0, " .BONUS_MAIL_FAIL. ",'$now','$bonus[use_start_date]','$bonus[use_end_date]')";
        $db->query($sql);
        $new_bonus_id = $db->insert_id();
        if($bonus['use_datetime'] == 1)
        {
            //按起止日期计算
            $use_effective_date = $now+$bonus['use_effective_date']*24 * 3600;
            $m_sql = "UPDATE " .$ecs->table('user_bonus'). " SET ".
                "use_start_datetime  = '$now', ".
                "use_end_datetime    = '$use_effective_date' ".
                "WHERE bonus_id   = '$new_bonus_id'";
            $db->query($m_sql);
        }
    }

    $r['errorcode']   = 0;
    $r['msg'] = '恭喜您领取成功';
    die($json->encode($r));
//    }
}



//去支付
elseif($action == "order_pay")
{
    include_once(ROOT_PATH . 'includes/lib_transaction.php');
    include_once(ROOT_PATH . 'includes/lib_order.php');
    include_once(ROOT_PATH . 'includes/lib_clips.php');
    $order_id = isset($_GET['order_id']) ? intval($_GET['order_id']) : 0;

    /* 订单详情 */
    $order = get_order_detail($order_id, $user_id);

    if ($order === false)
    {
        $err->show($_LANG['back_home_lnk'], './');

        exit;
    }

    /* 订单商品 */
    $goods_list = order_goods($order_id);
    /* 未发货，未付款时允许更换支付方式 */
    if ($order['order_amount'] > 0 && $order['pay_status'] == PS_UNPAYED && $order['shipping_status'] == SS_UNSHIPPED)
    {
        $goods_ids = array();
        foreach ($goods_list as $goods)
        {
            $goods_ids[] = $goods['goods_id'];
        }
        $goods_ids = array_unique(array_filter($goods_ids));
        //获取可用的支付方式（如果有多个物品，要取多个物品公共的）- add by qihua on 20130816
        $payment_list = available_payment_list_by_goods($goods_ids, false, 0, true);
        $pay_ids=null;
        if($order['bonus_id'] > 0)
        {
            $bonus_payment_list=array();
            $bonus = bonus_info(intval($order['bonus_id']));
            if($bonus['pay_ids']!=null)
            {
                $pay_ids = explode(',',$bonus['pay_ids']);
            }
        }
        //过滤掉微信支付
        foreach ($payment_list as $key=>$val)
        {
            if ($val['pay_code'] == 'weixinpay')
            {
                unset($payment_list[$key]);
            }
            //过滤国美支付
            if ($order['order_amount'] < 300 && $val['pay_code'] == 'guomeipay')
            {
                unset($payment_list[$key]);
            }
            else
            {
                if($pay_ids != null)
                {
                    //过滤掉红包限制的支付
                    foreach($pay_ids AS $v)
                    {
                        if($val['pay_id'] == $v)
                        {
                            if(isset($payment_list[$key]))
                            {
                                $bonus_payment_list[]=$payment_list[$key];
                            }
                        }
                    }
                }
            }
        }
        //$payment_list = available_payment_list(false, 0, true);

        /* 过滤掉余额支付方式 */
        if(is_array($payment_list))
        {
            foreach ($payment_list as $key => $payment)
            {
                if ($payment['pay_code'] == 'balance')
                {
                    unset($payment_list[$key]);
                }
            }
        }
        if($order['bonus_id'] > 0)
        {

            $smarty->assign('payment_list', $bonus_payment_list);
        }
        else
        {
            $smarty->assign('payment_list', $payment_list);
        }
    }
    else
    {
        exit;
    }

    $goods_names = '';
    foreach ($goods_list as $goods)
    {
        $goods_ids[] = $goods['goods_id'];
        $goods_names .= $goods['goods_name'];
    }
    /* 取得区域名 */
    $sql = "SELECT concat(IFNULL(p.region_name, ''), " .
        "'  ', IFNULL(t.region_name, ''), '  ', IFNULL(d.region_name, '')) AS region " .
        "FROM " . $ecs->table('order_info') . " AS u " .
        "LEFT JOIN " . $ecs->table('region') . " AS p ON u.province = p.region_id " .
        "LEFT JOIN " . $ecs->table('region') . " AS t ON u.city = t.region_id " .
        "LEFT JOIN " . $ecs->table('region') . " AS d ON u.district = d.region_id " .
        "WHERE u.order_id = '$order[order_id]'";
    $region = $db->getOne($sql);
    if(!empty($region)){
        $order["address_name"] = $region.' '.$order["address"];
    }
    $smarty->assign('goods_names', $goods_names);
    $smarty->assign('order',      $order);
    $smarty->display('order_pay.dwt');
}
?>