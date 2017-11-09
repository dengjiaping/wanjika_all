<?php

/**
 * ECSHOP 订单管理
 * ============================================================================
 * 版权所有 2005-2010 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: yehuaixiao $
 * $Id: order.php 17219 2011-01-27 10:49:19Z yehuaixiao $
 */

define('IN_ECS', true);

require(dirname(__FILE__) . '/includes/init.php');
require_once(ROOT_PATH . 'includes/lib_order.php');
require_once(ROOT_PATH . 'includes/lib_goods.php');

/*------------------------------------------------------ */
//-- 订单列表
/*------------------------------------------------------ */
if ($_REQUEST['act'] == 'list')
{
    /* 检查权限 */
    //admin_priv('order_view');

    /* 模板赋值 */
    $smarty->assign('ur_here', $_LANG['21_data_capture']);
    $smarty->assign('action_link', array('href' => 'capture_jderji.php?download=下载', 'text' => '下载数据'));

/*    $smarty->assign('status_list', $_LANG['cs']);   // 订单状态

    $smarty->assign('os_unconfirmed',   OS_UNCONFIRMED);
    $smarty->assign('cs_await_pay',     CS_AWAIT_PAY);
    $smarty->assign('cs_await_ship',    CS_AWAIT_SHIP);*/
    //后台页面样式
    $smarty->assign('full_page',        1);
    //获取信息列表
    $list = capture_list();
    $smarty->assign('capture_list', $list);
    $smarty->assign('record_count', $order_list['record_count']);
    $smarty->assign('page_count',   $order_list['page_count']);
    $smarty->assign('sort_order_time', '<img src="images/sort_desc.gif">');

    /* 显示模板 */
    assign_query_info();
    $smarty->display('capture_jderji.htm');
}
elseif ($_REQUEST['download']=='下载') {
        // 导出到文本;
    $jd = array();
    for($i = 1;$i < 4;$i++){
        $str = '';
        $url = 'http://list.jd.com/list.html?cat=652%2C828%2C842&page='.$i;  //这儿填页面地址
        $info=file_get_contents($url);
        $ul_pattern = "/<ul class=\"list-h\">(.*)<\/ul>/";
        preg_match($ul_pattern,$info,$ulcontent);

        $li_pattern = "/<li[^>]+>(.*?)<\/li>/";
        preg_match_all($li_pattern,$ulcontent[1],$licontent);

        foreach ($licontent[0] as $value) {
            $skuu = array();
            $sku_pattern =  "/<li index=\".*\" sku=\"(.*)\" selfservice=\".*\">/i";
            preg_match_all($sku_pattern,$value,$skuid);
            $skuu['skuid'] = $skuid[1][0];

            $name_pattern =  "/<img width=\"220\" height=\"220\" alt=\"(.*?)\"/i";
            preg_match_all($name_pattern,$value,$name);
            $skuu['name'] = $name[1][0];

            $href_pattern =  "/<a [^>]+href=\"(.*?)\" [^>]+>/i";
            preg_match_all($href_pattern,$value,$href);
            $skuu['href'] = $href[1][0];

            $jd[$skuu['skuid']] = $skuu;

            $str .= 'J_'.$skuu['skuid'].',';
        }

        $url1 = 'http://p.3.cn/prices/mgets?skuIds='.$str;
        $pricejd = file_get_contents($url1);

        $priceobj = json_decode($pricejd);
        foreach($priceobj as $obj) {
            $strr = $obj->id;
            $skui = str_replace('J_','',$strr);

            $jd[$skui]['price'] = $obj->p;
        }
    }
    $file = date('YmdHi').".csv";

    $str1 = "排名\t商品名\t链接\t商品ID\t价格";
    $i = 1;
    foreach ($jd as $key=>$value) {
        $value['name'] = str_replace("\t",'',$value['name']);
        $str1 .= "\n{$i}\t{$value['name']}\t{$value['href']}\t{$value['skuid']}\t{$value['price']}";
        $i++;
    }

    header ( 'Content-Description: File Transfer' );
    header ( 'Content-Type: application/vnd.ms-excel ; charset=UTF-16LE' );
    header ( 'Content-Disposition: attachment; filename=' . $file );
// header ( 'Content-Transfer-Encoding: binary' );
    header ( 'Expires: 0' );
    header ( 'Cache-Control: must-revalidate, post-check=0, pre-check=0' );
    header ( 'Pragma: public' );
//header ( 'Content-Length: ' . strlen ( $str_down ) );

//添加BOM，保证csv能够显示utf8的字符
    echo(chr(255).chr(254));
    echo(mb_convert_encoding($str1,"UTF-16LE","UTF-8"));
}
function capture_list()
{

    $jd = array();
    for($i = 1;$i < 4;$i++){
        $str = '';
        $url = 'http://list.jd.com/list.html?cat=652%2C828%2C842&page='.$i;  //这儿填页面地址
        $info=file_get_contents($url);
        $ul_pattern = "/<ul class=\"list-h\">(.*)<\/ul>/";
        preg_match($ul_pattern,$info,$ulcontent);

        $li_pattern = "/<li[^>]+>(.*?)<\/li>/";
        preg_match_all($li_pattern,$ulcontent[1],$licontent);

        foreach ($licontent[0] as $value) {
            $skuu = array();
            $sku_pattern =  "/<li index=\".*\" sku=\"(.*)\" selfservice=\".*\">/i";
            preg_match_all($sku_pattern,$value,$skuid);
            $skuu['skuid'] = $skuid[1][0];

            $name_pattern =  "/<img width=\"220\" height=\"220\" alt=\"(.*?)\"/i";
            preg_match_all($name_pattern,$value,$name);
            $skuu['name'] = $name[1][0];

            $href_pattern =  "/<a [^>]+href=\"(.*?)\" [^>]+>/i";
            preg_match_all($href_pattern,$value,$href);
            $skuu['href'] = $href[1][0];

            $jd[$skuu['skuid']] = $skuu;

            $str .= 'J_'.$skuu['skuid'].',';
        }

        $url1 = 'http://p.3.cn/prices/mgets?skuIds='.$str;
        $pricejd = file_get_contents($url1);

        $priceobj = json_decode($pricejd);
        foreach($priceobj as $obj) {
            $strr = $obj->id;
            $skui = str_replace('J_','',$strr);

            $jd[$skui]['price'] = $obj->p;
        }
    }
    $file = date('YmdHi').".csv";
    $str1 = "排名\t商品名\t链接\t商品ID\t价格";
    $i = 1;
    foreach ($jd as $key=>$value) {
        $jd[$key]['rank'] = $i;
        $value['name'] = str_replace("\t",'',$value['name']);
        $str1 .= "\n{$value['rank']}\t{$value['name']}\t{$value['href']}\t{$value['skuid']}\t{$value['price']}";
        $i++;

    }
    return $jd;

//    $result = get_filter();
//    if ($result === false)
//    {
//        /* 过滤信息 */
//        $filter['order_sn'] = empty($_REQUEST['order_sn']) ? '' : trim($_REQUEST['order_sn']);
//        if (!empty($_GET['is_ajax']) && $_GET['is_ajax'] == 1)
//        {
//            $_REQUEST['consignee'] = json_str_iconv($_REQUEST['consignee']);
//            //$_REQUEST['address'] = json_str_iconv($_REQUEST['address']);
//        }
//        $filter['consignee'] = empty($_REQUEST['consignee']) ? '' : trim($_REQUEST['consignee']);
//        $filter['email'] = empty($_REQUEST['email']) ? '' : trim($_REQUEST['email']);
//        $filter['address'] = empty($_REQUEST['address']) ? '' : trim($_REQUEST['address']);
//        $filter['zipcode'] = empty($_REQUEST['zipcode']) ? '' : trim($_REQUEST['zipcode']);
//        $filter['tel'] = empty($_REQUEST['tel']) ? '' : trim($_REQUEST['tel']);
//        $filter['mobile'] = empty($_REQUEST['mobile']) ? 0 : intval($_REQUEST['mobile']);
//        $filter['country'] = empty($_REQUEST['country']) ? 0 : intval($_REQUEST['country']);
//        $filter['province'] = empty($_REQUEST['province']) ? 0 : intval($_REQUEST['province']);
//        $filter['city'] = empty($_REQUEST['city']) ? 0 : intval($_REQUEST['city']);
//        $filter['district'] = empty($_REQUEST['district']) ? 0 : intval($_REQUEST['district']);
//        $filter['shipping_id'] = empty($_REQUEST['shipping_id']) ? 0 : intval($_REQUEST['shipping_id']);
//        $filter['pay_id'] = empty($_REQUEST['pay_id']) ? 0 : intval($_REQUEST['pay_id']);
//        $filter['order_status'] = isset($_REQUEST['order_status']) ? intval($_REQUEST['order_status']) : -1;
//        $filter['shipping_status'] = isset($_REQUEST['shipping_status']) ? intval($_REQUEST['shipping_status']) : -1;
//        $filter['pay_status'] = isset($_REQUEST['pay_status']) ? intval($_REQUEST['pay_status']) : -1;
//        $filter['user_id'] = empty($_REQUEST['user_id']) ? 0 : intval($_REQUEST['user_id']);
//        $filter['user_name'] = empty($_REQUEST['user_name']) ? '' : trim($_REQUEST['user_name']);
//        $filter['composite_status'] = isset($_REQUEST['composite_status']) ? intval($_REQUEST['composite_status']) : -1;
//        $filter['group_buy_id'] = isset($_REQUEST['group_buy_id']) ? intval($_REQUEST['group_buy_id']) : 0;
//        $filter['user_rank'] = empty($_REQUEST['user_rank']) ? 0 : intval($_REQUEST['user_rank']);
//
//        $filter['sort_by'] = empty($_REQUEST['sort_by']) ? 'add_time' : trim($_REQUEST['sort_by']);
//        $filter['sort_order'] = empty($_REQUEST['sort_order']) ? 'DESC' : trim($_REQUEST['sort_order']);
//
//        $filter['start_time'] = empty($_REQUEST['start_time']) ? '' : (strpos($_REQUEST['start_time'], '-') > 0 ?  local_strtotime($_REQUEST['start_time']) : $_REQUEST['start_time']);
//        $filter['end_time'] = empty($_REQUEST['end_time']) ? '' : (strpos($_REQUEST['end_time'], '-') > 0 ?  local_strtotime($_REQUEST['end_time']) : $_REQUEST['end_time']);
//
//        if (isset($_REQUEST['referer']))
//        {
//            $filter['referer'] = $_REQUEST['referer'];
//        }
//
//        $where = 'WHERE 1 ';
//        if ($filter['order_sn'])
//        {
//            $where .= " AND o.order_sn LIKE '%" . mysql_like_quote($filter['order_sn']) . "%'";
//        }
//        if ($filter['consignee'])
//        {
//            $where .= " AND o.consignee LIKE '%" . mysql_like_quote($filter['consignee']) . "%'";
//        }
//        if ($filter['email'])
//        {
//            $where .= " AND o.email LIKE '%" . mysql_like_quote($filter['email']) . "%'";
//        }
//        if ($filter['address'])
//        {
//            $where .= " AND o.address LIKE '%" . mysql_like_quote($filter['address']) . "%'";
//        }
//        if ($filter['zipcode'])
//        {
//            $where .= " AND o.zipcode LIKE '%" . mysql_like_quote($filter['zipcode']) . "%'";
//        }
//        if ($filter['tel'])
//        {
//            $where .= " AND o.tel LIKE '%" . mysql_like_quote($filter['tel']) . "%'";
//        }
//        if ($filter['mobile'])
//        {
//            $where .= " AND o.mobile LIKE '%" .mysql_like_quote($filter['mobile']) . "%'";
//        }
//        if ($filter['country'])
//        {
//            $where .= " AND o.country = '$filter[country]'";
//        }
//        if ($filter['province'])
//        {
//            $where .= " AND o.province = '$filter[province]'";
//        }
//        if ($filter['city'])
//        {
//            $where .= " AND o.city = '$filter[city]'";
//        }
//        if ($filter['district'])
//        {
//            $where .= " AND o.district = '$filter[district]'";
//        }
//        if ($filter['shipping_id'])
//        {
//            $where .= " AND o.shipping_id  = '$filter[shipping_id]'";
//        }
//        if ($filter['pay_id'])
//        {
//            $where .= " AND o.pay_id  = '$filter[pay_id]'";
//        }
//        if ($filter['order_status'] != -1)
//        {
//            $where .= " AND o.order_status  = '$filter[order_status]'";
//        }
//        if ($filter['shipping_status'] != -1)
//        {
//            $where .= " AND o.shipping_status = '$filter[shipping_status]'";
//        }
//        if ($filter['pay_status'] != -1)
//        {
//            $where .= " AND o.pay_status = '$filter[pay_status]'";
//        }
//        if ($filter['user_id'])
//        {
//            $where .= " AND o.user_id = '$filter[user_id]'";
//        }
//        if ($filter['user_name'])
//        {
//            $where .= " AND u.user_name LIKE '%" . mysql_like_quote($filter['user_name']) . "%'";
//        }
//        if ($filter['start_time'])
//        {
//            $where .= " AND o.add_time >= '$filter[start_time]'";
//        }
//        if ($filter['end_time'])
//        {
//            $where .= " AND o.add_time <= '$filter[end_time]'";
//        }
//        if ($filter['user_rank'])
//        {
//            $where .= " AND u.user_rank = '$filter[user_rank]'";
//        }
//
//        //综合状态
//        switch($filter['composite_status'])
//        {
//            case CS_AWAIT_PAY :
//                $where .= order_query_sql('await_pay');
//                break;
//
//            case CS_AWAIT_SHIP :
//                $where .= order_query_sql('await_ship');
//                break;
//
//            case CS_FINISHED :
//                $where .= order_query_sql('finished');
//                break;
//
//            case PS_PAYING :
//                if ($filter['composite_status'] != -1)
//                {
//                    $where .= " AND o.pay_status = '$filter[composite_status]' ";
//                }
//                break;
//            case OS_SHIPPED_PART :
//                if ($filter['composite_status'] != -1)
//                {
//                    $where .= " AND o.shipping_status  = '$filter[composite_status]'-2 ";
//                }
//                break;
//            default:
//                if ($filter['composite_status'] != -1)
//                {
//                    $where .= " AND o.order_status = '$filter[composite_status]' ";
//                }
//        }
//
//        /* 团购订单 */
//        if ($filter['group_buy_id'])
//        {
//            $where .= " AND o.extension_code = 'group_buy' AND o.extension_id = '$filter[group_buy_id]' ";
//        }
//
//        if (isset($filter['referer']) && $filter['referer'] != -1)
//        {
//            $referer_list = get_referers();
//            $referer = $referer_list[$filter['referer']];
//            $where .= ' AND o.referer ="' . $referer . '"';
//        }
//
//        /* 如果管理员属于某个办事处，只列出这个办事处管辖的订单 */
//        $sql = "SELECT agency_id FROM " . $GLOBALS['ecs']->table('admin_user') . " WHERE user_id = '$_SESSION[admin_id]'";
//        $agency_id = $GLOBALS['db']->getOne($sql);
//        if ($agency_id > 0)
//        {
//            $where .= " AND o.agency_id = '$agency_id' ";
//        }
//
//        /* 分页大小 */
//
//        $filter['page'] = empty($_REQUEST['page']) || (intval($_REQUEST['page']) <= 0) ? 1 : intval($_REQUEST['page']);
//
//        if (isset($_REQUEST['page_size']) && intval($_REQUEST['page_size']) > 0)
//        {
//            $filter['page_size'] = intval($_REQUEST['page_size']);
//        }
//        elseif (isset($_COOKIE['ECSCP']['page_size']) && intval($_COOKIE['ECSCP']['page_size']) > 0)
//        {
//            $filter['page_size'] = intval($_COOKIE['ECSCP']['page_size']);
//        }
//        else
//        {
//            $filter['page_size'] = 15;
//        }
//        if ($_REQUEST['download']=='下载') {
//            ini_set ('memory_limit', '1024M');
//            $filter['page_size'] = 500000;
//        }
//        /* 记录总数 */
//        if ($filter['user_name'] || $filter['user_rank'])
//        {
//            $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('order_info') . " AS o inner join".
//                $GLOBALS['ecs']->table('users') . " AS u on o.user_id = u.user_id " . $where;
//        }
//        else
//        {
//            $sql = "SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('order_info') . " AS o ". $where;
//        }
//
//        $filter['record_count']   = $GLOBALS['db']->getOne($sql);
//        $filter['page_count']     = $filter['record_count'] > 0 ? ceil($filter['record_count'] / $filter['page_size']) : 1;
//
//        /* 查询 */
//        $sql = "SELECT o.order_id, o.order_sn, o.country, o.province, o.city, o.district, o.add_time, o.address, o.mobile,o.order_status, o.shipping_status, o.order_amount, o.money_paid," .
//            "o.pay_status, o.consignee, o.address, o.email, o.tel, o.extension_code, o.extension_id, o.pay_name, g.goods_number, g.goods_price, " .
//            "o.discount,o.tax,o.shipping_fee,o.insure_fee,o.pay_fee,o.pack_fee,o.card_fee,o.best_time," .
//            "(" . order_amount_field('o.') . ") AS total_fee, " .
//            "IFNULL(u.user_name, '" .$GLOBALS['_LANG']['anonymous']. "') AS buyer, g.goods_name ".
//            " FROM " . $GLOBALS['ecs']->table('order_info') . " AS o " .
//            " LEFT JOIN " .$GLOBALS['ecs']->table('users'). " AS u ON u.user_id=o.user_id " .
//            " LEFT JOIN " . $GLOBALS['ecs']->table('order_goods') . " AS g ON o.order_id=g.order_id " . $where .
//            " ORDER BY $filter[sort_by] $filter[sort_order] ".
//            " LIMIT " . ($filter['page'] - 1) * $filter['page_size'] . ",$filter[page_size]";
//
//        foreach (array('order_sn', 'consignee', 'email', 'address', 'zipcode', 'tel', 'user_name') AS $val)
//        {
//            $filter[$val] = stripslashes($filter[$val]);
//        }
//        set_filter($filter, $sql);
//    }
//    else
//    {
//        $sql    = $result['sql'];
//        $filter = $result['filter'];
//    }
//
//    $row = $GLOBALS['db']->getAll($sql);
//
//    /* 格式话数据 */
//    foreach ($row AS $key => $value)
//    {
//        $row[$key]['formated_order_amount'] = price_format($value['order_amount']);
//        $row[$key]['formated_money_paid'] = price_format($value['money_paid']);
//        $row[$key]['formated_total_fee'] = price_format($value['total_fee']);
//        $row[$key]['formated_discount'] = price_format($value['discount']);
//        $row[$key]['formated_tax'] = price_format($value['tax']);
//        $row[$key]['formated_shipping_fee'] = price_format($value['shipping_fee']);
//        $row[$key]['formated_insure_fee'] = price_format($value['insure_fee']);
//        $row[$key]['formated_pay_fee'] = price_format($value['pay_fee']);
//        $row[$key]['formated_pack_fee'] = price_format($value['pack_fee']);
//        $row[$key]['formated_card_fee'] = price_format($value['card_fee']);
//        $row[$key]['short_order_time'] = local_date('m-d H:i', $value['add_time']);
//        if ($value['order_status'] == OS_INVALID || $value['order_status'] == OS_CANCELED)
//        {
//            /* 如果该订单为无效或取消则显示删除链接 */
//            $row[$key]['can_remove'] = 1;
//        }
//        else
//        {
//            $row[$key]['can_remove'] = 0;
//        }
//    }
//    $arr = array('orders' => $row, 'filter' => $filter, 'page_count' => $filter['page_count'], 'record_count' => $filter['record_count']);
//
//    return $arr;
}

/**
 * 获取站点根目录网址
 *
 * @access  private
 * @return  Bool
 */
/*function get_site_root_url()
{
    return 'http://' . $_SERVER['HTTP_HOST'] . str_replace('/' . ADMIN_PATH . '/order.php', '', PHP_SELF);

}*/
?>