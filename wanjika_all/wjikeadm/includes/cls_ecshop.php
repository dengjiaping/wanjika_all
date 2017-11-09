<?php

/**
 * ECSHOP ������
 * ============================================================================
 * ��Ȩ���� 2005-2010 �Ϻ���������Ƽ����޹�˾������������Ȩ����
 * ��վ��ַ: http://www.ecshop.com��
 * ----------------------------------------------------------------------------
 * �ⲻ��һ��������������ֻ���ڲ�������ҵĿ�ĵ�ǰ���¶Գ����������޸ĺ�
 * ʹ�ã��������Գ���������κ���ʽ�κ�Ŀ�ĵ��ٷ�����
 * ============================================================================
 * $Author: liuhui $
 * $Id: cls_ecshop.php 17171 2010-06-04 06:14:00Z liuhui $
*/

if (!defined('IN_ECS'))
{
    die('Hacking attempt');
}

define('APPNAME', 'ECSHOP');
define('VERSION', 'v2.7.1');
define('RELEASE', '20100604');

class ECS
{
    var $db_name = '';
    var $prefix  = 'ecs_';

    /**
     * ���캯��
     *
     * @access  public
     * @param   string      $ver        �汾��
     *
     * @return  void
     */
    function ECS($db_name, $prefix)
    {
        $this->db_name = $db_name;
        $this->prefix  = $prefix;
    }

    /**
     * ��ָ���ı�������ǰ׺�󷵻�
     *
     * @access  public
     * @param   string      $str        ����
     *
     * @return  string
     */
    function table($str)
    {
        return '`' . $this->db_name . '`.`' . $this->prefix . $str . '`';
    }

    /**
     * ECSHOP ������뷽��;
     *
     * @access  public
     * @param   string      $pass       ��Ҫ�����ԭʼ����
     *
     * @return  string
     */
    function compile_password($pass)
    {
        return md5($pass);
    }

    /**
     * ȡ�õ�ǰ������
     *
     * @access  public
     *
     * @return  string      ��ǰ������
     */
    function get_domain()
    {
        /* Э�� */
        $protocol = $this->http();

        /* ������IP��ַ */
        if (isset($_SERVER['HTTP_X_FORWARDED_HOST']))
        {
            $host = $_SERVER['HTTP_X_FORWARDED_HOST'];
        }
        elseif (isset($_SERVER['HTTP_HOST']))
        {
            $host = $_SERVER['HTTP_HOST'];
        }
        else
        {
            /* �˿� */
            if (isset($_SERVER['SERVER_PORT']))
            {
                $port = ':' . $_SERVER['SERVER_PORT'];

                if ((':80' == $port && 'http://' == $protocol) || (':443' == $port && 'https://' == $protocol))
                {
                    $port = '';
                }
            }
            else
            {
                $port = '';
            }

            if (isset($_SERVER['SERVER_NAME']))
            {
                $host = $_SERVER['SERVER_NAME'] . $port;
            }
            elseif (isset($_SERVER['SERVER_ADDR']))
            {
                $host = $_SERVER['SERVER_ADDR'] . $port;
            }
        }

        return $protocol . $host;
    }

    /**
     * ��� ECSHOP ��ǰ������ URL ��ַ
     *
     * @access  public
     *
     * @return  void
     */
    function url()
    {
        $curr = strpos(PHP_SELF, ADMIN_PATH . '/') !== false ?
                preg_replace('/(.*)(' . ADMIN_PATH . ')(\/?)(.)*/i', '\1', dirname(PHP_SELF)) :
                dirname(PHP_SELF);

        $root = str_replace('\\', '/', $curr);

        if (substr($root, -1) != '/')
        {
            $root .= '/';
        }

        return $this->get_domain() . $root;
    }

    /**
     * ��� ECSHOP ��ǰ������ HTTP Э�鷽ʽ
     *
     * @access  public
     *
     * @return  void
     */
    function http()
    {
        return (isset($_SERVER['HTTPS']) && (strtolower($_SERVER['HTTPS']) != 'off')) ? 'https://' : 'http://';
    }

    /**
     * �������Ŀ¼��·��
     *
     * @param int $sid
     *
     * @return string ·��
     */
    function data_dir($sid = 0)
    {
        if (empty($sid))
        {
            $s = 'data';
        }
        else
        {
            $s = 'user_files/';
            $s .= ceil($sid / 3000) . '/';
            $s .= $sid % 3000;
        }
        return $s;
    }

    /**
     * ���ͼƬ��Ŀ¼·��
     *
     * @param int $sid
     *
     * @return string ·��
     */
    function image_dir($sid = 0)
    {
        if (empty($sid))
        {
            $s = 'images';
        }
        else
        {
            $s = 'user_files/';
            $s .= ceil($sid / 3000) . '/';
            $s .= ($sid % 3000) . '/';
            $s .= 'images';
        }
        return $s;
    }

}

?>