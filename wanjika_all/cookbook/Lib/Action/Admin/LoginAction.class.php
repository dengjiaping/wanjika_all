<?php

class LoginAction extends Action {	
	public function index(){
	
		if($this->ispost()){			
					
				if(session('code') != md5(htmlspecialchars(addslashes($_POST['code']),ENT_QUOTES))){
					$this->error('验证码错误');
				}				
				$username=htmlspecialchars(addslashes($_POST['username']),ENT_QUOTES);			
				$pwd=MD5(htmlspecialchars(addslashes($_POST['pwd']),ENT_QUOTES));
				if(inject_check($username)){			
					$this->ajaxReturn(0,'非法提交！',0);
				}
				$admin=M('admin')->where(array('user'=>$username,'pwd'=>$pwd,'status'=>'1'))->find();
				if($admin){				
					session('adminuser',$admin['user'],3600);
					session('author',$admin['author'],3600);				
					$this->success('登陆成功',U('/Admin/Index/index'));	
				}
				elseif($username==C('webuser') && $pwd==MD5(C('webuserpass'))){				
					session('adminuser',C('webuser'),3600);
					session('author',C('webname'),3600);					
					$this->success('登陆成功',U('/Admin/Index/index'));			
				}else{
					$this->error('登陆失败,账号密码错误');
				}
		}else{
			header("Content-Type:text/html;charset=utf-8");

		
		
			if(strstr($_SERVER["HTTP_USER_AGENT"], "MSIE 6.0") )  
				{
					echo "为了保证后台功能的完整请用IE8以上浏览器访问后台";
					die;
				}			
			if(session('adminuser')!==C('webuser'))			
			{	
				$this->display();
			}
			else{
				$this->success('你已登录，正在进入系统...',U('/Admin/Index/index'));
			}
		}
		
	}
	
	public function logout(){
	
		session(null);
		$this->success('退出成功...');
		
	}
}