<?php

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
if ($_REQUEST['act'] == 'list')
{
    $smarty->assign('full_page',        1);
    $list = task_list();
    $smarty->assign('ur_here',      $_LANG['17_failetask_list']);
    $smarty->assign('task_list',   $list['tasks']);
    $smarty->assign('filter',       $list['filter']);
    $smarty->assign('record_count', $list['record_count']);
    $smarty->assign('page_count',   $list['page_count']);

    /* 显示模板 */
    assign_query_info();
    $smarty->display('failetask_list.htm');
}


/*------------------------------------------------------ */
//-- 排序、分页、查询
/*------------------------------------------------------ */
elseif ($_REQUEST['act'] == 'query')
{
    $list = task_list();

    $smarty->assign('task_list',   $list['tasks']);
    $smarty->assign('filter',       $list['filter']);
    $smarty->assign('record_count', $list['record_count']);
    $smarty->assign('page_count',   $list['page_count']);
    make_json_result($smarty->fetch('failetask_list.htm'), '', array('filter' => $list['filter'], 'page_count' => $list['page_count']));
}

function task_list()
{
    $result = get_filter();
    if ($result === false)
    {
        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 't.addtime' : trim($_REQUEST['sort_by']);
        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);

        $where = 'WHERE (t.task_status = 12 or t.task_status = 14 or t.sft_status = 12 or t.sft_status = 22 or t.sft_status = 32) AND t.task_status <> 13 ';

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
        $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('kjt_task'). " AS t inner join".
            $GLOBALS['ecs']->table('order_info') . " AS o on t.order_no = o.order_sn ". $where;

        $filter['record_count']   = $GLOBALS['db']->getOne($sql);
        $filter['page_count']     = $filter['record_count'] > 0 ? ceil($filter['record_count'] / $filter['page_size']) : 1;

        /* 查询 */
        $sql = "SELECT t.*,o.order_id FROM " . $GLOBALS['ecs']->table('kjt_task') . " AS t inner join".
            $GLOBALS['ecs']->table('order_info') . " AS o on t.order_no = o.order_sn " .  $where .
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
        switch($row[$key]['task_status'])
        {
            case CREATE_ORDER_SUCC :
                $row[$key]['task_status'] = '跨境通下单成功';
                $row[$key]['return_desc'] = 'SUCCESS';
                break;

            case CREATE_ORDER_FAL :
                $row[$key]['task_status'] = '跨境通下单失败';
                break;

            case DELETE_ORDER_SUCC :
                $row[$key]['task_status'] = '订单作废成功';
                break;
        }
        switch($row[$key]['sft_status'])
        {
            case BILLING_SUCC :
                $row[$key]['sft_status'] = '代扣成功';
                break;

            case BILLING_FAL :
                $row[$key]['sft_status'] = '代扣失败';
                break;

            case DECLARE_SUCC :
                $row[$key]['sft_status'] = '支付申报成功';
                break;

            case DECLARE_FAL :
                $row[$key]['sft_status'] = '支付申报失败';
                break;

            case DECLARE_NOTICE_SUCC :
                $row[$key]['sft_status'] = '支付申报通知成功';
                $row[$key]['billing_desc'] = 'SUCCESS';
                break;

            case DECLARE_NOTICE_FAL :
                $row[$key]['sft_status'] = '支付申报通知失败';
                break;
        }
    }
    $arr = array('tasks' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}

?>
