<?php

class BookAction extends CommAction {
	
	public function index(){
	
	
		$data['infoid']=htmlspecialchars(addslashes($_POST['infoid']),ENT_QUOTES);
		$data['name']=htmlspecialchars(addslashes($_POST['name']),ENT_QUOTES);
		$data['tel']=htmlspecialchars(addslashes($_POST['tel']),ENT_QUOTES);
		$data['email']=htmlspecialchars(addslashes($_POST['email']),ENT_QUOTES);
		$data['content']=htmlspecialchars(addslashes($_POST['content']),ENT_QUOTES);
		
		if(inject_check($data['infoid'])){
			
			$this->ajaxReturn(0,'非法提交！',0);
		}
		if(inject_check($data['name'])){
			
				$this->ajaxReturn(0,'非法提交！',0);
		}
		if(inject_check($data['tel'])){
			
				$this->ajaxReturn(0,'非法提交！',0);
		}
		if(inject_check($data['email'])){
			
			$this->ajaxReturn(0,'非法提交！',0);
		}
		if(inject_check($data['content'])){
			
				$this->ajaxReturn(0,'非法提交！',0);
		}
		
		$info=M('info')->find($data['infoid']);
		if($info['isrev']=='0'){
			$this->ajaxReturn(0,'评论已被关闭',0);
		}
		if(session('infoid')==$data['infoid']){		
			
			$this->ajaxReturn(0,'你的已经提交过，不用重复提交！',0);
			
		}else{
			
			if($data['name']==false){
		
				$this->ajaxReturn(0,'称呼不能为空！',0);
			}
			if($data['infoid']==false){
		
				$this->ajaxReturn(0,'内容id不能为空',0);
			}			
			if(checkEmail($data['email'])==false){
			
				$this->ajaxReturn(0,'不是合法的邮箱！',0);
			}
			if($data['content']==false){			
				$this->ajaxReturn(0,'内容不能为空！',0);
			}
			$data['time']=time();
		
			$book=M('book')->data($data)->filter('strip_tags')->add();
			if($book>0){		
				session('infoid',$data['infoid'],10);
				M('info')->where(array('id'=>$data[infoid]))->setInc('revs',1);
				send_mail(C('email'),$data['email'],$data['name'],'联系人:'.$data['name'].'电话：'.$data['tel'].'内容：'.$data['content'].'时间：'.date('y-m-d h:i:s',time()));
				
				$this->ajaxReturn(1,'评论发布成功',1);
			}else{
				$this->ajaxReturn(0,'评论发布失败',0);
			}
		}
		
	}


}