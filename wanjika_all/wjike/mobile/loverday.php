<?php

/**
 * 情人节活动领取红包页面
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
        $_SESSION['back_act'] = 'loverday.php';
        $r['errorcode']   = 2;
        $r['msg'] = '未登录';
        die($json->encode($r));
    }

//    $bonus_type = json_str_iconv($_REQUEST['bonus_type']);
    $bonus_type=array(151);
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

    $sql = "SELECT send_start_date ".
        " FROM " . $GLOBALS['ecs'] ->table('bonus_type').
        " WHERE type_id = '$bonus_type[0]' LIMIT 1";
    $send_start_date = $GLOBALS['db']->getOne($sql);
//    if ($reg_time < $send_start_date)
//    {
//        //提示只允许新用户参加
//        $r['errorcode']   = 1;
//        $r['msg'] = '该活动只允许新用户参加！';
//        die($json->encode($r));
//    }
    $sql = "SELECT send_end_date ".
        " FROM " . $GLOBALS['ecs'] ->table('bonus_type').
        " WHERE type_id = '$bonus_type[0]' LIMIT 1";
    $send_end_date = $GLOBALS['db']->getOne($sql);
    if ($reg_time > $send_end_date)
    {
        //优惠券过期
        $r['errorcode']   = 1;
        $r['msg'] = '活动已过期';
        die($json->encode($r));
    }
        $now = gmtime();
        foreach($bonus_type AS $value)
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
        }


        $r['errorcode']   = 0;
        $r['msg'] = '恭喜您领取成功';
        $r['is_true'] = '1';
        die($json->encode($r));
}
$smarty->display('loverday.html');

?>