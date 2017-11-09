<?php

/**
 * 获取签名数据
 * @param array $params
 * @return string
 */
function get_dstbdatasign($bonuse_key, $params) {
	
	$sign = "";
	if (isset($params ['merchno']) && !is_null($params ['merchno']) && $params ['merchno'] != '') {
		$sign .= "merchno=" . $params ['merchno'];
	}
	if (isset($params ['mediumno']) && !is_null($params ['mediumno']) && $params ['mediumno'] != '') {
		$sign .= "&mediumno=" . $params ['mediumno'];
	}
	if (isset($params ['cardno']) && !is_null($params ['cardno']) && $params ['cardno'] != '') {
		$sign .= "&cardno=" . $params ['cardno'];
	}
	if (isset($params ['usertype']) && !is_null($params ['usertype']) && $params ['usertype'] != '') {
		$sign .= "&usertype=" . $params ['usertype'];
	}
	if (isset($params ['dsorderid']) && !is_null($params ['dsorderid']) && $params ['dsorderid'] != '') {
		$sign .= "&dsorderid=" . $params ['dsorderid'];
	}
	if (isset($params ['amount']) && !is_null($params ['amount']) && $params ['amount'] != '') {
		$sign .= "&amount=" . $params ['amount'];
	}
	if (isset($params ['dsyburl']) && !is_null($params ['dsyburl']) && $params ['dsyburl'] != '') {
		$sign .= "&dsyburl=" . $params ['dsyburl'];
	}
	if (isset($params ['dstburl']) && !is_null($params ['dstburl']) && $params ['dstburl'] != '') {
		$sign .= "&dstburl=" . $params ['dstburl'];
	}
	if (isset($params ['orderurl']) && !is_null($params ['orderurl']) && $params ['orderurl'] != '') {
		$sign .= "&orderurl=" . $params ['orderurl'];
	}
	if (isset($params ['currency']) && !is_null($params ['currency']) && $params ['currency'] != '') {
		$sign .= "&currency=" . $params ['currency'];
	}
	if (isset($params ['productdesc']) && !is_null($params ['productdesc']) && $params ['productdesc'] != '') {
		$sign .= "&productdesc=" . $params ['productdesc'];
	}
	if (isset($params ['ebcbankid']) && !is_null($params ['ebcbankid']) && $params ['ebcbankid'] != '') {
		$sign .= "&ebcbankid=" . $params ['ebcbankid'];
	}
	if (isset($params ['bankcard']) && !is_null($params ['bankcard']) && $params ['bankcard'] != '') {
		$sign .= "&bankcard=" . $params ['bankcard'];
	}
	if (isset($params ['username']) && !is_null($params ['username']) && $params ['username'] != '') {
		$sign .= "&username=" . $params ['username'];
	}
	if (isset($params ['userbankcustom']) && !is_null($params ['userbankcustom']) && $params ['userbankcustom'] != '') {
		$sign .= "&userbankcustom=" . $params ['userbankcustom'];
	}
	if (isset($params ['address']) && !is_null($params ['address']) && $params ['address'] != '') {
		$sign .= "&address=" . $params ['address'];
	}
	if (isset($params ['cardtype']) && !is_null($params ['cardtype']) && $params ['cardtype'] != '') {
		$sign .= "&cardtype=" . $params ['cardtype'];
	}
	if (isset($params ['flag']) && !is_null($params ['flag']) && $params ['flag'] != '') {
		$sign .= "&flag=" . $params ['flag'];
	}
	//var_dump($sign);die;
	$des = new DES_JAVA ( $bonuse_key );
	
	$dstbdatasign = $des->encrypt ( $sign );
	
	return $dstbdatasign;
}

/**
 * 获取m2传输需要的数据
 * @param array $params
 * @return json
 */
function get_params($bonuse_key, $params) {
	
	$dstbdatasign = get_dstbdatasign ($bonuse_key, $params );
	
	$params ['dstbdatasign'] = $dstbdatasign;
	
	return json_encode ( $params );
}






/**
 * @param array $params
 * @param str $dstbdatasign
 * @return boolean
 */
function verify_sign($bonuse_key, $params, $return_sign){
	$sign = "";
	if (isset($params ['dstbdata']) && !is_null($params ['dstbdata']) && $params ['dstbdata'] != '') {
		$sign = $params ['dstbdata'];
	}
	
	$des = new DES_JAVA ( $bonuse_key );
	
	$dstbdatasign = $des->encrypt ( $sign );

	if($dstbdatasign == $return_sign){
		return true;
	}else{
		return false;
	}

}


/*
 * des加密类
 */
class DES_JAVA {
	var $key;
	function DES_JAVA($key) {
		$this->key = $key;
	}
	function encrypt($encrypt) {
		$encrypt = $this->pkcs5_pad ( $encrypt );
		$iv = mcrypt_create_iv ( mcrypt_get_iv_size ( MCRYPT_DES, MCRYPT_MODE_ECB ), MCRYPT_RAND );
		$passcrypt = mcrypt_encrypt ( MCRYPT_DES, $this->key, $encrypt, MCRYPT_MODE_ECB, $iv );
		return strtoupper ( bin2hex ( $passcrypt ) );
	}
	function decrypt($decrypt) {
		// $decoded = base64_decode($decrypt);
		$decoded = pack ( "H*", $decrypt );
		$iv = mcrypt_create_iv ( mcrypt_get_iv_size ( MCRYPT_DES, MCRYPT_MODE_ECB ), MCRYPT_RAND );
		$decrypted = mcrypt_decrypt ( MCRYPT_DES, $this->key, $decoded, MCRYPT_MODE_ECB, $iv );
		return $this->pkcs5_unpad ( $decrypted );
	}
	function pkcs5_unpad($text) {
		$pad = ord ( $text {strlen ( $text ) - 1} );
		
		if ($pad > strlen ( $text ))
			return $text;
		if (strspn ( $text, chr ( $pad ), strlen ( $text ) - $pad ) != $pad)
			return $text;
		return substr ( $text, 0, - 1 * $pad );
	}
	function pkcs5_pad($text) {
		$len = strlen ( $text );
		$mod = $len % 8;
		$pad = 8 - $mod;
		return $text . str_repeat ( chr ( $pad ), $pad );
	}
}

    
    
    
    
    
    