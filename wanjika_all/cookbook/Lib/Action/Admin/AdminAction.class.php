<?php

class AdminAction extends CommAction {
	public function _initialize(){
	
		if(session('adminuser')!=C('webuser')){
		
			$this->error('你没有权限',U('/Admin/Index/home'));
		}
	}
	
	public function index(){
		
		if($this->ispost()){
			if(empty($_POST['user']) || empty($_POST['pwd'])){
				$this->error('用户名密码不能为空');
			}
			$_POST['pwd']=MD5($_POST['pwd']);
			$_POST['status']=1;
			if(M('admin')->data($_POST)->add($_POST)){
				$this->success('添加成功');
			}else{
				$this->error('添加失败');
			}
		}else{
		
		$this->admin=M('admin')->order("id desc")->select();
		$this->display();
		}
	}

	
	public function edit(){
		if($this->ispost()){
			if(empty($_POST['user']) || empty($_POST['pwd'])){
					$this->error('用户名密码不能为空');
			}
			$_POST['pwd']=MD5($_POST['pwd']);
			if(M('admin')->data($_POST)->save($_POST)){
				$this->success('修改成功',U('/Admin/Admin/index'));
			}else{
				$this->error('修改失败');
			}
		}else{
		$this->admin=M('admin')->find($_GET['id']);
		$this->display();
		}
	}
	public function del(){
	if(M('admin')->delete($_GET['id'])){
		$this->success('删除成功');
	}else{
		$this->success('删除失败');
	}
		
	}
	
	
		
}