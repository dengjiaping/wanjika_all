<?php
/**
 * Created by PhpStorm.
 * User: qihua
 * Date: 14-4-29
 * Time: 下午1:52
 */

class cls_logs {

    public function cls_logs()
    {
        //nothing
    }
    public static function add_logs($file, $content)
    {
        $res = file_put_contents($file, $content, FILE_APPEND);

        return $res > 0 ? true : false;
    }

    public static function add_payment_respond_log($content)
    {
        $file = "/web/payment_respond_log/" . date('Y-m-d') . '.log';

        return self::add_logs($file, $content);
    }
}