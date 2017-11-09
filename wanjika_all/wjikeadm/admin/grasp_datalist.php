<?php

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
if ($_REQUEST['act'] == 'list')
{
    if ($_REQUEST['download']=='下载') {
        // 导出到文本;
        $file_name = "datalist_". date ( "Ymd" ) . ".csv";
        $str_down = "ID\t商品名称\t商品价格\t商品排名\t商品链接\t商品排序类型\t抓取关键字\t抓取时间";

        $list = get_list();
        while ( list ( $key, $val ) = each ( $list['lists'] ) ) {
            $sorttype = ($val['sorttype'] == '1' ? '相关度' : '销量');
            $str_down .= "\n" . $val ['id']  . "\t". $val['goods_name'] . "\t" . $val['goods_price'] . "\t"  . $val['rank'] . "\t" . $val['goods_link']. "\t" . $sorttype. "\t" . $val['grasp_keywords']. "\t" . $val['addtime'];
        }

        header ( 'Content-Description: File Transfer' );
        header ( 'Content-Type: application/vnd.ms-excel ; charset=UTF-16LE' );
        header ( 'Content-Disposition: attachment; filename=' . $file_name );
        // header ( 'Content-Transfer-Encoding: binary' );
        header ( 'Expires: 0' );
        header ( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
        header ( 'Pragma: public' );
        //header ( 'Content-Length: ' . strlen ( $str_down ) );
        ob_clean ();
        flush ();

        //添加BOM，保证csv能够显示utf8的字符
        echo(chr(255).chr(254));
        echo(mb_convert_encoding($str_down,"UTF-16LE","UTF-8"));

        exit;
        // header("Location: downtest.php");
    }
    $smarty->assign('ur_here',      $_LANG['02_data_list']);
    $smarty->assign('full_page',    1);

    $data_list = get_list();

    $smarty->assign('data_list',   $data_list['lists']);
    $smarty->assign('filter',       $data_list['filter']);
    $smarty->assign('goodsname', $_REQUEST['goodsname']);
    $smarty->assign('keywords', $_REQUEST['keywords']);
    $smarty->assign('start_time', $_REQUEST['start_time']);
    $smarty->assign('end_time', $_REQUEST['end_time']);
    $smarty->assign('record_count', $data_list['record_count']);
    $smarty->assign('page_count',   $data_list['page_count']);

    assign_query_info();
    $smarty->display('data_list.htm');
}

else if ($_REQUEST['act'] == 'query')
{
    $data_list = get_list();

    $smarty->assign('data_list',   $data_list['lists']);
    $smarty->assign('filter',       $data_list['filter']);
    $smarty->assign('record_count', $data_list['record_count']);
    $smarty->assign('page_count',   $data_list['page_count']);

    make_json_result($smarty->fetch('data_list.htm'), '',
        array('filter' => $data_list['filter'], 'page_count' => $data_list['page_count']));
}

function get_list()
{
    $result = get_filter();
    if ($result === false)
    {
        /* 过滤信息 */
        $filter['goodsname'] = empty($_REQUEST['goodsname']) ? '' : trim($_REQUEST['goodsname']);
        $filter['keywords'] = empty($_REQUEST['keywords']) ? '' : trim($_REQUEST['keywords']);
        $filter['start_time'] = empty($_REQUEST['start_time']) ? '' : (strpos($_REQUEST['start_time'], '-') > 0 ?  local_strtotime($_REQUEST['start_time']) : $_REQUEST['start_time']);
        $filter['end_time'] = empty($_REQUEST['end_time']) ? '' : (strpos($_REQUEST['end_time'], '-') > 0 ?  local_strtotime($_REQUEST['end_time']) : $_REQUEST['end_time']);

        $where = 'WHERE 1 ';
        if ($filter['goodsname'])
        {
            $where .= " AND goods_name LIKE '%" . mysql_like_quote($filter['goodsname']) . "%'";
        }
        if ($filter['keywords'])
        {
            $where .= " AND grasp_keywords LIKE '%" . mysql_like_quote($filter['keywords']) . "%'";
        }
        if ($filter['start_time'])
        {
            $where .= " AND addtime >= '$filter[start_time]'";
        }
        if ($filter['end_time'])
        {
            $where .= " AND addtime <= '$filter[end_time]'";
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
        $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('data_jdgrasp'). $where;

        $filter['record_count']   = $GLOBALS['db']->getOne($sql);
        $filter['page_count']     = $filter['record_count'] > 0 ? ceil($filter['record_count'] / $filter['page_size']) : 1;

        /* 查询 */
        $sql = "SELECT * FROM " . $GLOBALS['ecs']->table('data_jdgrasp') .  $where .
            " ORDER BY addtime desc ".
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
        //ecshop服务器和本地时间差修正显示
        $row[$key]['addtime'] = local_date('Y-m-d H:i', $value['addtime']);
    }

    $arr = array('lists' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);

    return $arr;
}
?>
