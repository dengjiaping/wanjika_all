<?php

/**
 * ECSHOP mobile前台公共函数
 * ============================================================================
 * * 版权所有 2005-2012 上海商派网络科技有限公司，并保留所有权利。
 * 网站地址: http://www.ecshop.com；
 * ----------------------------------------------------------------------------
 * 这不是一个自由软件！您只能在不用于商业目的的前提下对程序代码进行修改和
 * 使用；不允许对程序代码以任何形式任何目的的再发布。
 * ============================================================================
 * $Author: testyang $
 * $Id: lib_main.php 15013 2008-10-23 09:31:42Z testyang $
*/

if (!defined('IN_ECS'))
{
    die('Hacking attempt');
}

/**
 * 对输出编码
 *
 * @access  public
 * @param   string   $str
 * @return  string
 */
function encode_output($str)
{
//    if (EC_CHARSET != 'utf-8')
//    {
//        $str = ecs_iconv(EC_CHARSET, 'utf-8', $str);
//    }
    return htmlspecialchars($str);
}

/**
 * wap分页函数
 *
 * @access      public
 * @param       int     $num        总记录数
 * @param       int     $perpage    每页记录数
 * @param       int     $curr_page  当前页数
 * @param       string  $mpurl      传入的连接地址
 * @param       string  $pvar       分页变量
 */
function get_wap_pager($num, $perpage, $curr_page, $mpurl,$pvar)
{
    $multipage = '';
    if($num > $perpage)
    {
        $page = 2;
        $offset = 1;
        $pages = ceil($num / $perpage);
        $all_pages = $pages;
        $tmp_page = $curr_page;
        $setp = strpos($mpurl, '?') === false ? "?" : '&amp;';
        if($curr_page > 1)
        {
            $multipage .= "<a href=\"$mpurl${setp}${pvar}=".($curr_page-1)."\">上一页</a>";
        }
        $multipage .= $curr_page."/".$pages;
        if(($curr_page++) < $pages)
        {
            $multipage .= "<a href=\"$mpurl${setp}${pvar}=".$curr_page++."\">下一页</a><br/>";
        }
        //$multipage .= $pages > $page ? " ... <a href=\"$mpurl&amp;$pvar=$pages\"> [$pages] &gt;&gt;</a>" : " 页/".$all_pages."页";
        //$url_array = explode("?" , $mpurl);
       // $field_str = "";
       // if (isset($url_array[1]))
       // {
          //  $filed_array = explode("&amp;" , $url_array[1]);
           // if (count($filed_array) > 0)
            //{
             //   foreach ($filed_array AS $data)
              //  {
               //     $value_array = explode("=" , $data);
                //    $field_str .= "<postfield name='".$value_array[0]."' value='".encode_output($value_array[1])."'/>\n";
               // }
           // }
      //  }
        //$multipage .= "跳转到第<input type='text' name='pageno' format='*N' size='4' value='' maxlength='2' emptyok='true' />页<anchor>[GO]<go href='{$url_array[0]}' method='get'>{$field_str}<postfield name='".$pvar."' value='$(pageno)'/></go></anchor>";
        //<postfield name='snid' value='".session_id()."'/>
    }
    return $multipage;
}

/**
 * 返回尾文件
 *
 * @return  string
 */
function get_footer()
{
    if ($_SESSION['user_id'] > 0)
    {
        $footer = "<br/><a href='user.php?act=user_center'>用户中心</a>|<a href='user.php?act=logout'>退出</a>|<a href='javascript:scroll(0,0)' hidefocus='true'>回到顶部</a><br/>Copyright 2009<br/>Powered by ECShop v2.7.2";
    }
    else
    {
        $footer = "<br/><a href='user.php?act=login'>登陆</a>|<a href='user.php?act=register'>免费注册</a>|<a href='javascript:scroll(0,0)' hidefocus='true'>回到顶部</a><br/>Copyright 2009<br/>Powered by ECShop v2.7.2";
    }

    return $footer;
}

function goods_extensioncode($good_id)
{
    if($good_id > 0){
        $sql = "SELECT extension_code FROM " . $GLOBALS['ecs']->table('goods') .
            " WHERE goods_id = " . $good_id;
        $res = $GLOBALS['db']->getOne($sql);

        return $res;
    }
    return '';
}

function get_mobile_orderstatus($order_status, $pay_status, $shipping_status, $is_list = true)
{
    $str='';

    if($is_list){
        if($order_status == 2){
            $str = "<span style='float: right;margin-right: 10px;color: #bdbdbd;font-size: 12px;'>已取消</span>";
        }
        elseif($order_status == 4){
            $str = "<span style='float: right;margin-right: 10px;font-size: 12px;'>退货</span>";
        }
        elseif($pay_status == 3){
            $str = "<span style='float: right;margin-right: 10px;font-size: 12px;'>部分退款</span>";
        }
        elseif($pay_status == 4){
            $str = "<span style='float: right;margin-right: 10px;font-size: 12px;'>已退款</span>";
        }
        elseif($pay_status == 0){
            $str = "<span style='float: right;margin-right: 10px;color: #50318f;font-size: 12px;'>待付款</span>";
        }
        elseif($pay_status == 2 &&  $shipping_status!=2){
            $str = "<span style='float: right;margin-right: 10px;color: #f9b002;font-size: 12px;'>待收货</span>";
        }
        elseif($pay_status == 2 && $shipping_status == 2){
            $str = "<span style='float: right;margin-right: 10px;color: #45ba9f;font-size: 12px;'>交易完成</span>";
        }
    }
    else{
        if($order_status == 2){
            $str = "<span style='color: #bdbdbd;'>已取消</span>";
        }
        elseif($order_status == 4){
            $str = "<span>退货</span>";
        }
        elseif($pay_status == 3){
            $str = "<span>部分退款</span>";
        }
        elseif($pay_status == 4){
            $str = "<span>已退款</span>";
        }
        elseif($pay_status == 0){
            $str = "<span style='color: #50318f;'>待付款</span>";
        }
        elseif($pay_status == 2 && $shipping_status <= 1){
            $str = "<span style='color: #f9b002;'>待收货</span>";
        }
        elseif($pay_status == 2 && $shipping_status == 2){
            $str = "<span style='color: #45ba9f;'>交易完成</span>";
        }
    }
    return $str;
}

function is_binding($openid)
{
    if(!empty($openid)){
        $sql="SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('weixin_user') .  " WHERE openid='$openid' ";
        $res = $GLOBALS['db']->getOne($sql);

        if($res > 0){
            return true;
        }
    }

    return  false;
}

function is_user_binding($user_name)
{
    if(!empty($user_name)){
        $sql="SELECT COUNT(*) FROM " . $GLOBALS['ecs']->table('weixin_user') .  " WHERE user_name='$user_name' ";
        $res = $GLOBALS['db']->getOne($sql);

        if($res > 0){
            return true;
        }
    }

    return  false;
}

function binding_weixin($openid,$user_id,$user_name)
{
    $sql = "INSERT INTO " .$GLOBALS['ecs']->table('weixin_user'). " (openid,user_id,user_name)" .
        " VALUES ('$openid', '$user_id', '$user_name')";
    return $GLOBALS['db']->query($sql);
}

function get_binding_user($openid)
{
    if(!empty($openid)){
        $sql = "SELECT user_name FROM " . $GLOBALS['ecs']->table('weixin_user') .
            " WHERE openid = '$openid'";
        $res = $GLOBALS['db']->getOne($sql);

        return $res;
    }
    return '';
}

function delete_binding($openid)
{
    $sql = "DELETE FROM " . $GLOBALS['ecs']->table('weixin_user') . " WHERE openid='" . $openid . "'";

    return $GLOBALS['db']->query($sql);
}

?>