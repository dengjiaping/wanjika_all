<?php


class charge
{
    var $api_urls   = array(
        'beginfill' => 'http://soa.cxesales.com/cz/commsl_beginFill.action',
        'findfill' => 'http://soa.cxesales.com/cz/commsl_findFill.action',
        'querybalance' => 'http://soa.cxesales.com/cz/commsl_queryBalance.action'
    );

    var $oid_biz  = '201001';

    var $oid_reguser  = '880078';

    var $md5key  = 'finndin985jdafy12';

    var $result  = array('status'=>true, 'api_errors' => '', 'server_errors' => '', 'leftmoney' => 0);

    function beginfill($jno_cli,$phone,$price)
    {
        if(empty($phone))
        {
            $this->result['status'] = false;
            $this->result['server_errors'] = '充值的手机号码为空';
            return $this->result;
        }
        if(empty($price))
        {
            $this->result['status'] = false;
            $this->result['server_errors'] = '充值的价格为空';
            return $this->result;
        }
        $url = $this->get_url('beginfill');
        $str = $this->oid_biz.$jno_cli.$this->oid_reguser.$phone.$price.$this->md5key;
        $sign = md5($str);
        $xml_beginfill = "<?xml version='1.0' encoding='UTF-8'?><root><oid_biz>$this->oid_biz</oid_biz><jno_cli>$jno_cli</jno_cli><oid_reguser>$this->oid_reguser</oid_reguser><uid_cli>$phone</uid_cli><price>$price</price><sign>$sign</sign><type></type><province></province><city></city></root>";

        $ch = curl_init();
        $header[] = "Content-type: text/xml";//定义content-type为xml
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $xml_beginfill);
        $response = curl_exec($ch);
        if(curl_errno($ch))
        {
            $this->result['status'] = false;
            $this->result['server_errors'] = curl_error($ch);
            return $this->result;
        }
        $xmlData= simplexml_load_string($response);
        curl_close($ch);
        $r = (array)$xmlData;
        if($r["retcode"] === "000000"){
            $this->result['status'] = true;
            $this->result['leftmoney'] = $r["leftmoney"];
        }
        else{
            $this->result['status'] = false;
            $this->result['server_errors'] = $r["retcode"];
        }
        $this->record_log($r,$jno_cli,$phone);
        return $this->result;
    }

    function findfill($jno_cli)
    {
        //todo
    }

    function querybalance()
    {
        //todo
    }

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

    function record_log($result,$jno_cli,$phone) {
        //$file_name = ROOT_PATH . 'includes/'.date("Ymd").".log";
        $file_name = "/web/logs/call_interface_result_".date("Ymd").".log";
        $logStr = "\n[".date("Y-m-d H:i:s")."][".$result['retcode']."][".$phone."][".$jno_cli."]";
        file_put_contents($file_name,$logStr,FILE_APPEND);
    }
}

?>