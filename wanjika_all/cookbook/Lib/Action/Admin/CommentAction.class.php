<?php

class CommentAction extends CommAction {

	public function index(){
		if($this->ispost()){
		
			if($_POST['del']==1){
				$i=0;
				while($i<=count($_POST['id'])){
					$arr=M('book')->where(array('id'=>$_POST['id'][$i]))->delete();					
					$i++;
				}
				$this->success('删除成功');
			}
			if($_POST['del']==2){
				$i=0;
				while($i<=count($_POST['id'])){
					$arr=M('book')->where(array('id'=>$_POST['id'][$i]))->setField('status','1');					
					$i++;
				}
				$this->success('审核成功');
			}
			
		}else{
		import('Class.Page',LIB_PATH);// 导入分页类		
		$count      = M('book')->count();// 查询满足要求的总记录数
		$Page       = new Page($count,25);// 实例化分页类 传入总记录数和每页显示的记录数
		$Page->url =GROUP_NAME.'/Comment/index/p/';		
		$this->page = $Page->show();	
		$book=$book =M('book')->order('id DESC')->limit($Page->firstRow.','.$Page->listRows)->select();	
		
		foreach ($book as $k=>$v){
		
			$info=M('info')->find($v['infoid']);
			$book[$k]['infoname']=$info['name'];
			
		}
	
		$this->book=$book;
			
		
		$this->display();
		}
	}
	public function edit(){
		
		if($this->ispost()){
			$data['reply']=$_POST['reply'];
			$data['id']=$_POST['id'];
			$data['status']=1;
			$con=M('book')->where("id=$data[id]")->data($data)->save();		
			send_mail($_POST['email'],C('email'),C('webname'),$_POST['reply']);
			
			$this->success('回复成功',U('/Admin/Comment/index'));
			
		}else{
		$this->book=M('book')->find($_GET['id']);
		
		$this->display();
		}
	}
	public function del(){
		$con=M('book')->delete($_GET['id']);
		if($con>0){
			$this->success('删除成功');
		}else{
			$this->error('删除失败');
		}
	}

}