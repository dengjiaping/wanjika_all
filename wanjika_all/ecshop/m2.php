<?php
class m2Base {
	public $m_arr;
	private $sign_arr; // 签名数组
	public $mem;
	private $access_token;
	function __construct() {
		include 'config.php';
		$this->m_arr = $m_arr;
		if (empty ( $this->m_arr ['api_id'] )) {
			$this->m_arr ['api_id'] = $this->m_arr ['api_server_code'];
		}

		if ($this->m_arr ['mode'] == 1) {
			if ($this->m_arr ['memcache_open']) {
				$this->mem = new Memcache ();
				$this->mem->connect ( $this->m_arr ['memcached_server'] );
				
			}
		}
	}

	/**
	 *
	 * @param json $post_data
	 *        	要发送的数据
	 * @param string $method        	
	 * @param string $forward        	
	 * @param string $error_url
	 *        	请求错误时跳转的url
	 * @return Ambigous <string, mixed>
	 */
	function get_url_data($api='T288',$post_data, $method = 'post', $forward = '', $error_url = '') {
		$url = $this->create_m2_url ($api, $post_data, $method, $forward, $error_url );
		$data = $this->get_stream_data ( $url, $post_data );
		//var_dump($data);die;
		if ($this->is_json ( $data )) {
			$data_result = json_decode ( $data, true );
			if (empty ( $data_result ['err_code'] )) {
				if($data_result['returncode'] == '00'){
					return $data_result ['data'];
				}else{

					return null;
				}
				
			} else {
				$result = '错误代码:' . $data_result ['err_code'] . ",错误详情:" . $data_result ['err_text'];
			}
		} else {
			if (! empty ( $forward )) {
				// 跳转类的请求
				header ( 'Location:' . $url );
				exit ();
			} else {
				$result = $data;
			}
		}

		return $result;
	}
	
	/**
	 * 加密函数
	 * 
	 * @return string
	 */
	function get_signature($api) {
		$sign_arr = array ();
		if (! empty ( $this->sign_arr ['nonce'] )) {
			$sign_arr ['nonce'] = $this->sign_arr ['nonce'];
		}
		
		if (! empty ( $this->sign_arr ['aid'] )) {
			$sign_arr ['aid'] = $this->sign_arr ['aid'];
		}
		
		if (! empty ( $this->m_arr ['key'] )) {
			$sign_arr ['key'] = $this->m_arr ['key'];
		}
		
		if (! empty ( $this->sign_arr ['api_id'][$api] )) {
			$sign_arr ['api_id'] = $this->sign_arr ['api_id'][$api];
		}
		
		if (! empty ( $this->sign_arr ['tid'] )) {
			$sign_arr ['tid'] = $this->sign_arr ['tid'];
		}
		
		if (! empty ( $this->sign_arr ['timestamp'] )) {
			$sign_arr ['timestamp'] = $this->sign_arr ['timestamp'];
		}
		// 有跳转请求
		if (! empty ( $this->sign_arr ['forward'] )) {
			$sign_arr ['forward'] = $this->sign_arr ['forward'];
		}
		if (! empty ( $this->sign_arr ['error_url'] )) {
			$sign_arr ['error_url'] = $this->sign_arr ['error_url'];
		}
		if (! empty ( $this->sign_arr ['data_sign'] )) {
			$sign_arr ['data_sign'] = $this->sign_arr ['data_sign'];
		}
		//var_dump($sign_arr);die;
		usort ( $sign_arr, 'nextpermu' );
		
		$sign_str = sha1 ( implode ( '', $sign_arr ) );
		//var_dump(implode ( '', $sign_arr ));echo'<br>';
		//var_dump($sign_str);die;
		$this->sign_arr = array ();
		
		return strtoupper ( $sign_str );
	}
	
	/**
	 * 对数据包进行签名
	 * 
	 * @param
	 *        	$data
	 */
	function data_signature($data) {
		return strtoupper ( sha1 ( $data ) );
	}
	
	/**
	 *
	 * @param json $post_data        	
	 * @param string $method        	
	 * @param string $forward        	
	 * @param string $error_url        	
	 * @return string
	 */
	function create_m2_url($api,$post_data, $method = 'POST', $forward = '', $error_url = '') {
		if ($this->m_arr ['mode'] == '0') {
			$this->sign_arr = $this->m_arr;
			$this->sign_arr ['timestamp'] = $this->java_timestamp();
			// 有跳转请求
			if (! empty ( $forward )) {
				$this->sign_arr ['forward'] = $forward;
			}
			if (! empty ( $error_url )) {
				$this->sign_arr ['error_url'] = $error_url;
			}			
			if ($this->m_arr ['is_data_sign'] == 1) {
				$this->sign_arr ['data_sign'] = $this->data_signature ( $post_data );
			}
			
			$creat_url_arr = $this->sign_arr;
			
			if (strtoupper ( $method ) == 'GET') {
				$creat_url_arr ['data'] = $post_data;
				$creat_url_arr ['method'] = $method;
			}
			$sign_str = $this->get_signature ($api);
			$url = $this->create_url ( $api,$this->m_arr ['url'], $creat_url_arr, $sign_str );
		} elseif ($this->m_arr ['mode'] == 1) {
			$access_token = $this->is_access_token ();			
			// 判断access_token是否失效
			if ($access_token && ! $this->do_filter_apiid ( $access_token ['lost_api_ids'] )) {
				// 未失效，直接使用
				$creat_url_arr = array (
						'aid' => $this->m_arr ['aid'],
						'api_id' => $this->m_arr ['api_id'],
						'access_token' => $access_token ['access_token'] 
				);
				// 有跳转请求时
				if (! empty ( $forward )) {
					$creat_url_arr ['forward'] = $forward;
				}
				if (! empty ( $error_url )) {
					$creat_url_arr ['error_url'] = $error_url;
				}
				// GET请求时
				if (strtoupper ( $method ) == 'GET') {
					$creat_url_arr ['data'] = $post_data;
					$creat_url_arr ['method'] = $method;
				}
				
				$url = $this->create_url ($api, $this->m_arr ['url_ac'], $creat_url_arr );
			} else {
				$token = $this->get_access_token ($api);
				
				if ($token && ! $this->do_filter_apiid ( $token ['lost_api_ids'] )) {
					$creat_url_arr = array (
							'aid' => $this->m_arr ['aid'],
							'api_id' => $this->m_arr ['api_id'],
							'access_token' => $token ['access_token'] 
					);
					// 有跳转请求时
					if (! empty ( $forward )) {
						$creat_url_arr ['forward'] = $forward;
					}
					if (! empty ( $error_url )) {
						$creat_url_arr ['error_url'] = $error_url;
					}
					// GET请求时
					if (strtoupper ( $method ) == 'GET') {
						$creat_url_arr ['data'] = $post_data;
						$creat_url_arr ['method'] = $method;
					}
					//var_dump($this->m_arr ['mode']);die;
					$url = $this->create_url ($api, $this->m_arr ['url_ac'], $creat_url_arr );
					
				} else {
					// 如果没有激活token
					$this->m_arr ['mode'] = 0;
					$url = $this->create_m2_url ( $post_data );
				}
			}
		}
		
		return $url;
	}
	function is_access_token() {
		date_default_timezone_set ( 'PRC' );
		if ($this->m_arr ['memcache_open']) {
			$access_token = $this->mem->get ( 'access_token' );
		} else {
			$access_token = $this->access_token;
		}
		$now_time = time ();
		$start_time = strtotime ( $access_token ['start_time'] );
		if ($now_time - $start_time < $access_token ['expire']) {
			return $access_token;
		} else {
			return false;
		}
	}
	
	/**
	 * 判断api_id是否在忽略的数组集里
	 * 
	 * @param array $lost_api_ids        	
	 * @return boolean
	 */
	function do_filter_apiid($lost_api_ids) {
		if (in_array ( $this->m_arr ['api_id'], $lost_api_ids )) {
			return true;
		} else {
			return false;
		}
	}
	
	/**
	 * 获取token
	 * 
	 * @return boolean mixed
	 */
	function get_access_token($api) {
		$this->sign_arr = array (
				'aid' => $this->m_arr ['aid'],
				'nonce' => $this->m_arr ['nonce'],
				'timestamp' => $this->java_timestamp () 
		);
		$creat_url_arr = $this->sign_arr;
		$sign_str = $this->get_signature ($api);		
		$url = $this->create_url ($api, $this->m_arr ['url_ac_token'], $creat_url_arr, $sign_str );
		$data = $this->get_stream_data ( $url );		
		$data_arr = json_decode ( $data, true );
		if (isset ( $data_arr ['err_code'] )) {
			$this->m_arr ['max_token'] -= 1;
			if ($this->m_arr ['max_token'] >= 0) {
				$this->get_access_token ($api);
			} else {
				return false;
			}
		} else {
			if ($this->m_arr ['memcache_open']) {
				$this->mem->add ( 'access_token', $data_arr );
			}
			$this->access_token = $data_arr;
			return $data_arr;
		}
	}
	
	/**
	 * 根据给定的参数组合成url
	 * 
	 * @param str $url
	 *        	网关地址
	 * @param arr $creat_url_arr
	 *        	需要的参数数组
	 * @param string $sign_str
	 *        	加密串
	 * @return string
	 */
	function create_url($api,$url, $creat_url_arr, $sign_str = '') {
		$str = '';
		if (! empty ( $creat_url_arr ['aid'] )) {
			$str .= "aid=" . $creat_url_arr ['aid'];
		}
		
		if (! empty ( $creat_url_arr ['nonce'] )) {
			$str .= "&nonce=" . $creat_url_arr ['nonce'];
		}
		
		if (! empty ( $creat_url_arr ['api_id'][$api] )) {
			$str .= "&api_id=" . $creat_url_arr ['api_id'][$api];
		}
		
		if (! empty ( $creat_url_arr ['tid'] )) {
			$str .= "&tid=" . $creat_url_arr ['tid'];
		}
		
		if (! empty ( $sign_str )) {
			$str .= "&signature=" . $sign_str;
		}
		
		if (! empty ( $creat_url_arr ['debug'] )) {
			$str .= "&debug=" . $creat_url_arr ['debug'];
		}
		
		if (! empty ( $creat_url_arr ['access_token'] )) {
			$str .= "&access_token=" . $creat_url_arr ['access_token'];
		}
		
		if (! empty ( $creat_url_arr ['timestamp'] )) {
			$str .= '&timestamp=' . $creat_url_arr ['timestamp'];
		}
		
		// 有跳转请求
		if (! empty ( $creat_url_arr ['forward'] )) {
			$str .= '&forward=' . urlencode ( $creat_url_arr ['forward'] );
		}
		if (! empty ( $creat_url_arr ['error_url'] )) {
			$str .= '&error_url=' . urlencode ( $creat_url_arr ['error_url'] );
		}
		if (! empty ( $creat_url_arr ['data_sign'] )) {
			$str .= '&data_sign=' . $creat_url_arr ['data_sign'];
		}
		if (! empty ( $creat_url_arr ['data'] )) {
			$str .= '&data=' . urlencode ( $creat_url_arr ['data'] );
		}
		
		$url = $url . "?" . $str;
		
		return $url;
	}
	
	// 获取远程数据
	function get_stream_data($url, $post_data = '') {
		$params = array (
				'http' => array (
						'method' => 'POST',
						'header' => "Content-Type: multipart/form-data\r\n",
						'content' => $post_data 
				) 
		);
		 
		$ctx = stream_context_create ( $params );
		$fp = fopen ( $url, 'rb', false, $ctx );
		
		$data = stream_get_contents ( $fp );
		
		return $data;
	}
	function java_timestamp() {
		return time () * 1000;
	}
	function is_json($string) {
	if(version_compare(PHP_VERSION,'5.3.0','ge')){
			json_decode ( $string );		
			return (json_last_error () == JSON_ERROR_NONE);
		}else{
			return is_null(json_decode($string));
		}
	}
}

/*
 * 对数组进行自定义排序
 */
function nextpermu($a, $b) {
	if (strcmp ( $a, $b ) == 0)
		return 0;
	return strcmp ( $a, $b ) > 0 ? 1 : - 1;
}


