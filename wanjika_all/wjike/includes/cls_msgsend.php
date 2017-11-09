<?php

if (!defined('IN_ECS'))
{
    die('Hacking attempt');
}

/* 短信模块主类 */
class msgsend
{
    var $numlist          = array();

    function send($phones,$content,$source = 'unknown')
    {
        $array = $this->get_numstr($phones);
        $phonelist = $array['list'];
        $phonecount = $array['count'];
        if(empty($phonelist))
        {
            return false;
        }

        $sms_url = 'http://61.135.198.131:8023/MWGate/wmgw.asmx/MongateSendSubmit';
        $r = $this->sendsms($phonelist,$sms_url,$content,$phonecount,$source);

        return $r;
    }

    function get_numstr($phones)
    {
        $phonearr = explode(',',$phones);
        foreach($phonearr as $phone)
        {
            if(!$this->is_moblie($phone)){
                continue;
            }
            array_push($this->numlist,$phone);
        }
        $phone_str = implode(',',$this->numlist);
        $r = array();
        $r['list'] = $phone_str;
        $r['count'] = count($this->numlist);

        return $r;
    }

    function sendsms($phonelist,$sms_url,$content,$phonecount,$source = 'unknown')
    {
        $ch = curl_init();
        $userId = 'J71026';
        $password = '456123';

        curl_setopt($ch, CURLOPT_URL, $sms_url);
        curl_setopt($ch, CURLOPT_POSTFIELDS, "userId=$userId&password=$password&pszMobis=$phonelist&pszMsg=$content&iMobiCount=$phonecount");
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        $data = curl_exec($ch);
        $data=str_replace("<?xml version=\"1.0\" encoding=\"utf-8\"?>","",$data);
        $data=str_replace("<string xmlns=\"http://tempuri.org/\">","",$data);
        $data=str_replace("</string>","",$data);

        curl_close($ch);
        if(!is_local()){
            $time = local_date("Y-m-d H:i:s", gmtime());
            $file_name = "/web/msglog/msglog_".date("Ymd").".log";
            file_put_contents($file_name,"\n".$phonelist."||".$source."||".real_ip()."||".$time,FILE_APPEND);
        }
        $r = array();
        $r['status'] = false;
        $r['code'] = $data;
        if(strlen($data) > 8){
            $r['status'] = true;
        }

        return $r;
    }

    /**
     * 检测手机号码是否正确
     *
     */
    function is_moblie($moblie)
    {
        return  preg_match("/^0?1((3|7|8)[0-9]|5[0-35-9]|4[57])\d{8}$/", $moblie);
    }
}

?>