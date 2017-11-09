<?php

/**
 * 万集客会员狂欢节,全场满150减50,超值秒杀1元起活动领取红包页面
 *
 * @author qihua
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
if ((DEBUG_MODE & 2) != 2)
{
    $smarty->caching = true;
}
if ($_SESSION['user_id'] > 0)
{
    $smarty->assign('user_name', $_SESSION['user_name']);
}

if ($_REQUEST['act'] == 'getbonus')
{
    include_once(dirname(__FILE__) . '/../includes/cls_json.php');

    $json = new JSON;
    $r['errorcode']   = 0;
    $r['msg'] = '领取成功';
    $user_id = $_SESSION['user_id'];
    if ($user_id == 0)
    {
        $_SESSION['back_act'] = 'carnival.php';
        $r['errorcode']   = 2;
        $r['msg'] = '未登录';
        die($json->encode($r));
    }

//    $bonus_type = json_str_iconv($_REQUEST['bonus_type']);
    $bonus_type=array(98);
    if ($bonus_type[0] == 0)
    {
        //提示错误参数
        $r['errorcode']   = 1;
        $r['msg'] = '参数错误';
        die($json->encode($r));
    }
    $sql = "SELECT reg_time ".
        " FROM " . $GLOBALS['ecs'] ->table('users').
        " WHERE user_id = '$user_id' LIMIT 1";
    $reg_time = $GLOBALS['db']->getOne($sql);

    $sql = "SELECT send_start_date,send_end_date,is_limit ".
        " FROM " . $GLOBALS['ecs'] ->table('bonus_type').
        " WHERE type_id = '$bonus_type[0]' LIMIT 1";
    $res = $GLOBALS['db']->getRow($sql);
//    if ($reg_time < $res['send_start_date'])
//    {
//        //提示只允许新用户参加
//        $r['errorcode']   = 1;
//        $r['msg'] = '该活动只允许新用户参加！';
//        die($json->encode($r));
//    }
    if ($reg_time > $res['send_end_date'])
    {
        //优惠券过期
        $r['errorcode']   = 1;
        $r['msg'] = '活动已过期';
        die($json->encode($r));
    }
    $sql = "SELECT count(*) FROM " . $ecs->table('user_bonus') .
        " WHERE user_id = '{$user_id}' AND bonus_type_id = '{$bonus_type[0]}'";
//    if ($db->getOne($sql) > 0 && $res['is_limit'] == 1)
//    {
//        $r['errorcode']   = 1;
//        $r['msg'] = '您已经领取过该优惠券';
//        die($json->encode($r));
//    }
//    else
//    {
        $now = gmtime();
        foreach($bonus_type AS $value)
        {
            $sql = "INSERT INTO " . $ecs->table('user_bonus') .
                "(bonus_type_id, bonus_sn, user_id, used_time, order_id, emailed,binding_time) " .
                "VALUES ('$value', 0, '$user_id', 0, 0, " .BONUS_MAIL_FAIL. ",'$now')";
            $db->query($sql);
        }
//    }



        $r['errorcode']   = 0;
        $r['msg'] = '恭喜您领取成功';
        $r['is_true'] = '1';
        die($json->encode($r));
}
$smarty->display('carnival.html');

?>