<?php

class IndexAction extends CommAction {
    public function index(){
        function GetOpenid()
        {
            //通过code获得openid
            if (!isset($_GET['code'])){
                //触发微信返回code码
//                $baseUrl = urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?".'s=/'.wget_request().'.html');
//                $url = WCreateOauthUrlForCode($baseUrl);
//                Header("Location: $url");
//                exit();
                $baseUrl = urlencode('http://'.$_SERVER['HTTP_HOST'].$_SERVER['PHP_SELF']."?".wget_request());
                $url = WCreateOauthUrlForCode($baseUrl);
                Header("Location: $url");
                exit();
            } else {
                //获取code码，以获取openid
                $code = $_GET['code'];
                $userinfo = wgetUserinfoFromMp($code);
                return $userinfo;
            }
        }
        $info=GetOpenid();
//        $info = array("openid"=>"888","nickname"=>"胡先生");
        $_SESSION['openid'] = $info['openid'];
        $_SESSION['nickname'] = $info['nickname'];
		
		$this->display(':index');
	}
}