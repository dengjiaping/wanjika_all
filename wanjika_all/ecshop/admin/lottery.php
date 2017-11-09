<?php

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
if ($_REQUEST['act'] == 'list')
{
    $smarty->assign('full_page',        1);
    $list = lottery_list();
    $smarty->assign('ur_here',      $_LANG['01_lottery_list']);
    $smarty->assign('lottery_list',   $list['lotteries']);
    $smarty->assign('filter',       $list['filter']);
    $smarty->assign('order_sn', $_REQUEST['order_sn']);
    $smarty->assign('user_name', $_REQUEST['user_name']);
    $smarty->assign('reward_id', $_REQUEST['reward_id']);
    $smarty->assign('record_count', $list['record_count']);
    $smarty->assign('page_count',   $list['page_count']);

    /* 显示模板 */
    assign_query_info();
    $smarty->display('lottery_list.htm');
}


/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    $list = lottery_list();

    $smarty->assign('lottery_list',   $list['lotteries']);
    $smarty->assign('filter',       $list['filter']);
    $smarty->assign('record_count', $list['record_count']);
    $smarty->assign('page_count',   $list['page_count']);
    make_json_result($smarty->fetch('lottery_list.htm'), '', array('filter' => $list['filter'], 'page_count' => $list['page_count']));
}

function lottery_list()
{
    $result = get_filter();
    if ($result === false)
    {
        /* 过滤信息 */
        $filter['order_sn'] = empty($_REQUEST['order_sn']) ? '' : trim($_REQUEST['order_sn']);
        $filter['user_id'] = empty($_REQUEST['user_id']) ? '' : trim($_REQUEST['user_id']);
        $filter['user_name'] = empty($_REQUEST['user_name']) ? '' : trim($_REQUEST['user_name']);
        $filter['reward_id'] = $_REQUEST['reward_id'];
        $filter['reward_name'] = empty($_REQUEST['reward_name']) ? '' : trim($_REQUEST['reward_name']);

        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'add_time' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $where = 'WHERE 1 ';
        if ($filter['order_sn'])
        {
            $where .= " AND order_sn LIKE '%" . mysql_like_quote($filter['order_sn']) . "%'";
        }
        if ($filter['user_name'])
        {
            $where .= " AND user_name LIKE '%" . mysql_like_quote($filter['user_name']) . "%'";
        }
        if ($filter['reward_id'] != -1 && $filter['reward_id'] != null)
        {
            $where .= " AND reward_id = '$filter[reward_id]'";
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
        $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('card_lottery'). $where;

        $filter['record_count']   = $GLOBALS['db']->getOne($sql);
        $filter['page_count']     = $filter['record_count'] > 0 ? ceil($filter['record_count'] / $filter['page_size']) : 1;

        /* 查询 */
        $sql = "SELECT * FROM " . $GLOBALS['ecs']->table('card_lottery') .  $where .
            " ORDER BY $filter[sort_by] $filter[sort_order] ".
            " LIMIT " . ($filter['page'] - 1) * $filter['page_size'] . ",$filter[page_size]";

        set_filter($filter, $sql);
    }
    else
    {
        $sql    = $result['sql'];
        $filter = $result['filter'];
    }

    $row = $GLOBALS['db']->getAll($sql);

    /* 格式话数据 */
    foreach ($row AS $key => $value)
    {
        $row[$key]['short_order_time'] = local_date('m-d H:i', $value['add_time']);
    }
    $arr = array('lotteries' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}

?>
