<?php

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');

$action = isset($_REQUEST['act']) ? $_REQUEST['act'] : 'list';

switch ($action)
{
    /* 虚拟短信发送结果 */
    case 'list' :
        $smarty->assign('full_page',        1);
        $list = virresult_list();
        $smarty->assign('ur_here',      $_LANG['09_sms_virtualresult']);
        $smarty->assign('sendresult_list',   $list['results']);
        $smarty->assign('filter',       $list['filter']);
        $smarty->assign('tel', $_REQUEST['tel']);
        $smarty->assign('user_name', $_REQUEST['user_name']);
        $smarty->assign('status', $_REQUEST['status']);
        $smarty->assign('record_count', $list['record_count']);
        $smarty->assign('page_count',   $list['page_count']);

        /* 显示模板 */
        assign_query_info();
        $smarty->display('sms_send_virresult.htm');

        break;

    case 'query' :
        $list = virresult_list();

        $smarty->assign('sendresult_list',   $list['results']);
        $smarty->assign('filter',       $list['filter']);
        $smarty->assign('record_count', $list['record_count']);
        $smarty->assign('page_count',   $list['page_count']);
        make_json_result($smarty->fetch('sms_send_virresult.htm'), '', array('filter' => $list['filter'], 'page_count' => $list['page_count']));

        break;
}

function virresult_list()
{
    $result = get_filter();
    if ($result === false)
    {
        /* 过滤信息 */
        $filter['tel'] = empty($_REQUEST['tel']) ? '' : trim($_REQUEST['tel']);
        $filter['user_id'] = empty($_REQUEST['user_id']) ? '' : trim($_REQUEST['user_id']);
        $filter['user_name'] = empty($_REQUEST['user_name']) ? '' : trim($_REQUEST['user_name']);
        $filter['status'] = $_REQUEST['status'];

        $where = 'WHERE 1 ';
        if ($filter['tel'])
        {
            $where .= " AND tel LIKE '%" . mysql_like_quote($filter['tel']) . "%'";
        }
        if ($filter['user_name'])
        {
            $where .= " AND user_name LIKE '%" . mysql_like_quote($filter['user_name']) . "%'";
        }
        if ($filter['status'] != -1 && $filter['status'] != null)
        {
            $where .= " AND status = '$filter[status]'";
        }

        /* 分页大小 */

        $filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page']) <= 0) ? 1 : intval($_REQUEST['page']);

        if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0)
        {
            $filter['page_size'] = intval($_REQUEST['page_size']);
        }
        elseif (isset($_COOKIE['ECSCP']['page_size']) && intval($_COOKIE['ECSCP']['page_size']) > 0)
        {
            $filter['page_size'] = intval($_COOKIE['ECSCP']['page_size']);
        }
        else
        {
            $filter['page_size'] = 15;
        }
        /* 记录总数 */
        $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('sms_sendresult'). $where;

        $filter['record_count']   = $GLOBALS['db']->getOne($sql);
        $filter['page_count']     = $filter['record_count'] > 0 ? ceil($filter['record_count'] / $filter['page_size']) : 1;

        /* 查询 */
        $sql = "SELECT * FROM " . $GLOBALS['ecs']->table('sms_sendresult') .  $where .
            " LIMIT " . ($filter['page'] - 1) * $filter['page_size'] . ",$filter[page_size]";

        set_filter($filter, $sql);
    }
    else
    {
        $sql    = $result['sql'];
        $filter = $result['filter'];
    }

    $row = $GLOBALS['db']->getAll($sql);
    $arr = array('results' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}

?>