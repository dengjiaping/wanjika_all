<?php
  define ("PER_URL","http://211.136.93.20:8081/webpay/spBackPer.do");
  define ("MONTH_URL","http://211.136.93.20:8081/webpay/spBackMonth.do");
  define ("UMPAY_PAY_URL","http://211.136.93.20:8081/uniform/spUniformPay.do");
  define ("UMPAY_VIEW_URL","http://211.136.93.20:8081/webpay/spQueryUserRegState.do");
  define ("UMPAY_CANCEL_URL","http://211.136.93.20:8081/webpay/spCancelUserRegInfo.do");
  define ("UMPAY_AMOUNT_URL","http://211.136.93.20:8081/webpay/spDayTransBill.do");
?>
<?php
    function ssl_sign($data,$priv_key_file){
    if(!$priv_key_file){$priv_key_file ="./testMer.key.pem";}
    if(!File_exists($priv_key_file)){
        echo "key_file is not exists!\n";
        return FALSE;
    }
    //error_log($data,3,'/tmp/datastring');
    $fp = fopen($priv_key_file, "rb");

    $priv_key = fread($fp, 8192);

    @fclose($fp);
    $pkeyid = openssl_get_privatekey($priv_key);

    if(!is_resource($pkeyid)){echo "not a resource \n " ; return FALSE;}
    // compute signature

    @openssl_sign($data, $signature, $pkeyid);

    // free the key from memory
    @openssl_free_key($pkeyid);
    return base64_encode($signature);
  } 
  
   function ssl_verify($data,$signature,$cert_file){
    if(!$cert_file){$cert_file ="./testUmpay.cert.pem"; }
    if(!File_exists($cert_file)){
        return FALSE;
        echo "cert_file is not exists!\n";
    }
    echo $signature;
    $signature = base64_decode($signature);
    echo $signature;
    $fp = fopen($cert_file, "r");
    $cert = fread($fp, 8192);
    fclose($fp);
    //echo $data."<br>".$signature."<br>".$cert_file."<br>" ;//exit;
    $pubkeyid = openssl_get_publickey($cert);
    if(!is_resource($pubkeyid)){
        return FALSE;
    }
    $ok = openssl_verify($data,$signature,$pubkeyid);
    @openssl_free_key($pubkeyid);
    if ($ok == 1) {
        echo "sucessful!";
        return TRUE;
    } elseif ($ok == 0) {
        echo "fail!!!";
        return FALSE;
    } else {
        return FALSE;
    }
    return FALSE;
   }
?>
