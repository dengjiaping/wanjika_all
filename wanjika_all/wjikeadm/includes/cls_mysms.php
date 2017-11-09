<?php

if (!defined('IN_ECS'))
{
    die('Hacking attempt');
}

/* 短信模块主类 */
class mysms
{
    var $api_urls   = array(
        'sdkapt'              =>      'http://sdk2.entinfo.cn:8061/webservice.asmx/mdsmssend',
        'hejia'              =>      'http://www.qymas.com/smsSendServlet.htm'
    );

    var $sdkapt_result   = array(
        '2'              =>      '帐号/密码不正确',
        '4'              =>      '余额不足',
        '6'             =>      '参数有误',
        '7'              =>      '权限受限',
        '12'              =>      '序列号状态错误',
        '20'              =>      '相同手机号，相同内容重复提交'
    );

    var $hejia_result   = array(
        '0'              =>      '发送成功',
        '1'              =>      '非法登录（如登录名、口令出错、登录名与口令不符等）',
        '3'              =>      '余额不足',
        '5'              =>      '参数错误',
        '9'              =>      '提交失败',
        '10'              =>      '目标号码数量超过限定大小（短信平台规定目标号码的数量最多为100个）',
        '11'        =>      '短信内容包含违禁词'
    );
    
    var $result  = array('status'=>true, 'api_errors' => '', 'server_errors' => '');

    var $wronglist          = array();
    var $numlist          = array();

    function send($phones,$content,$sendtype,$signtype)
    {
        $phonelist = $this->get_numstr($phones);
        if(empty($phonelist))
        {
            $this->result['status'] = false;
            $this->result['server_errors'] = '发送的手机号码为空';
            return $this->result;
        }

        $sms_url = $this->get_url($sendtype);
        if (!$sms_url)
        {
            $this->result['status'] = false;
            $this->result['server_errors'] = '请求调用接口名称错误';
            return $this->result;
        }
        $this->sendsms($phonelist,$sms_url,$content,$sendtype,$signtype);

//        if(!empty($this->wronglist)){
//            $msg = '!!!'.$this->get_wrongnum($this->wronglist).'号码发送失败。';
//            $result .= $msg;
//        }

        return $this->result;
    }

    function get_numstr($phones)
    {
        $phonearr = explode(',',$phones);
        foreach($phonearr as $phone)
        {
            if(!$this->is_moblie($phone)){
                array_push($this->wronglist,$phone);
                continue;
            }
            array_push($this->numlist,$phone);
        }
        $phone_str = implode(',',$this->numlist);

        return $phone_str;
    }

    function get_wrongnum($phones)
    {
        if (empty($phones))
        {
            return false;
        }
        $phone_str = implode(',',$phones);

        return $phone_str;
    }

    function sendsms($phonelist,$sms_url,$content,$sendtype,$signtype)
    {
        if($sendtype == 'hejia'){
//            header("content-type:text/html;charset=GBK");
//            $content = mb_convert_encoding($content,"GBK", "UTF-8");
//            $ch = curl_init();
            $username = 'wanjk';
            $pwd = strtoupper(md5('wanjika001'));
//            curl_setopt($ch, CURLOPT_URL, $sms_url);
//            curl_setopt($ch, CURLOPT_POSTFIELDS, "command=sendSMSMD5&username=$username&pwd=$pwd&mobiles=$phonelist&content=$content&incode=ISO-8859-1&outcode=GBK");
//            curl_setopt($ch, CURLOPT_POST, true);
//            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
//            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
//            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
//            $data = curl_exec($ch);
//            $result = $this->get_result(trim($data),$sendtype);
//            curl_close($ch);
            header("content-type:text/html;charset=GBK");
            $content = mb_convert_encoding($content,"GBK", "UTF-8");
            $url="http://www.qymas.com/smsSendServlet.htm?command=sendSMSMD5&username=$username&pwd=$pwd&mobiles=$phonelist&content=".$content."&incode=ISO-8859-1&outcode=GBK";
            $data=file_get_contents($url);
            $this->get_result(trim($data),$sendtype);
        }
        else if($sendtype == 'sdkapt'){
            //$content = mb_convert_encoding($content,"gb2312", "UTF-8");
            $ch = curl_init();
            $sn = 'SDK-APT-010-00090';
            $pwd = strtoupper(md5($sn.'b2#39c-8'));
            $ext = '';
            if($signtype == 1){
                $content .= '【万集客】';
            }
            else if ($signtype == 2){
                $ext = 1;
                $content .= '【万集卡】';
            }
            else if ($signtype == 3)
            {
                $ext = 3;
                $content .= '【12580商城】';
            }
            else
            {
                $ext = 2;
                $content .= '【C4U】';
            }
            curl_setopt($ch, CURLOPT_URL, $sms_url);
            curl_setopt($ch, CURLOPT_POSTFIELDS, "sn=$sn&pwd=$pwd&mobile=$phonelist&content=$content&ext=$ext&stime=&rrid=&msgfmt=");
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
            curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
            $data = curl_exec($ch);
            curl_close($ch);
            $this->get_result(trim($data),$sendtype);
        }
        else{
            $this->result['status'] = false;
            $this->result['server_errors'] = '请求调用接口名称错误';
            return;
        }
    }

    /**
     * 返回指定键名的URL
     *
     * @access  public
     * @param   string      $key        URL的名字，即数组的键名
     * @return  string or boolean       如果由形参指定的键名对应的URL值存在就返回该URL，否则返回false。
     */
    function get_url($key)
    {
        $url = $this->api_urls[$key];

        if (empty($url))
        {
            $this->result['status'] = false;
            $this->result['server_errors'] = '请求调用接口名称错误';
            return $this->result;
        }

        return $url;
    }

    function get_wronglist()
    {
        $wronglist = $this->wronglist;

        if (empty($wronglist))
        {
            return false;
        }

        return $wronglist;
    }

    /**
     * 检测手机号码是否正确
     *
     */
    function is_moblie($moblie)
    {
        return  preg_match("/^0?1((3|7|8)[0-9]|5[0-35-9]|4[57])\d{8}$/", $moblie);
    }

    function get_result($data,$sendtype)
    {
        if($sendtype == 'hejia'){
            $result = $this->hejia_result[$data];
        }
        else if($sendtype == 'sdkapt'){
            $data=str_replace("<string xmlns=\"http://tempuri.org/\">","",$data);
            $data=str_replace("</string>","",$data);
            $array=explode("-",$data);
            if(count($array) == 2)
            {
                $result = '发送成功';
            }
            else{
                $result = $this->sdkapt_result[$array[2]];
            }
        }
        else{
            $this->result['status'] = false;
            $this->result['server_errors'] = '请求调用接口名称错误';
            return;
        }

        if (empty($result))
        {
            $this->result['status'] = false;
            $this->result['api_errors'] = '接口调用返回值为:'.$data;
            return;
        }
        else{
            if($result != '发送成功'){
                $this->result['status'] = false;
                $this->result['api_errors'] = $result.',发送失败返回值为:-'.$array[2];
                return;
            }
        }
    }

    function sendEmail($receiver,$sumcount,$errorcount,$time) {
        $ch = curl_init();

        curl_setopt($ch, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER,1);
        curl_setopt($ch, CURLOPT_CUSTOMREQUEST, 'POST');
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);
        curl_setopt($ch, CURLOPT_URL, 'https://sendcloud.sohu.com/webapi/mail.send.json');
        curl_setopt($ch, CURLOPT_POSTFIELDS,
            array(
                'subject' => '短信发送结果',
                'html' => '您于'.$time.'进行了营销短信发送的操作，总发送号码个数为:'.$sumcount.'个，失败个数为:'.$errorcount.'个。',
                'from' => 'notreply@wanjike.com',
                'fromname' => '万集客',
                'to' => $receiver,
                'api_user' => 'postmaster@wjike.sendcloud.org',
                'api_key' => 'lasiDkTy'));

        $result = curl_exec($ch);

        if($result === false) //请求失败
        {
            $msg =  'last error : ' . curl_error($ch);
            return $msg;
        }

        curl_close($ch);
        return $result;
    }
}

?>