<?php

class AdsAction extends CommAction {

	public function index(){
		
		if($this->ispost()){
			$con=M('ads')->add($this->_post());
			if($con>0){
				$this->success('添加成功');
			}else{
				$this->error('添加失败');
			}
		}else{
		$this->adlist=M('ads')->select();
		$this->display();
		}
	}
	public function eidt(){
		
		if($this->ispost()){
			$con=M('ads')->save($_POST);
			if($con>0){
				$this->success('修改成功',U('/Admin/Ads/index'));
			}else{
				$this->error('修改失败');
			}
		}else{
		$this->ads=M('ads')->find($_GET['id']);
		$this->display();
		}
	}
	public function del(){
		$con=M('ads')->delete($_GET['id']);
		if($con>0){
			$this->success('删除成功');
		}else{
			$this->error('删除失败');
		}
	}

}