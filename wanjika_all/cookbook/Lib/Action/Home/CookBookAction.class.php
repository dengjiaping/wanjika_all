<?php

class CookBookAction extends CommAction {
    public function index(){
//        include_once(APP_PATH . '/wechat/WxPay.JsApiPay.php');
//        $tools = new JsApiPay();
//        $openId = $tools->GetOpenid();
		$this->display(':cookbook');
	}
}