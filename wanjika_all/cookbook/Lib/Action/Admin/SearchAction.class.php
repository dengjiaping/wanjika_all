<?php


class SearchAction extends CommAction {
	
	public function index(){
		import("Class.Page",LIB_PATH);
			$count=M('search')->count();
			$Page= new Page($count,25);										
		
		$key=M('search')->limit($Page->firstRow.','.$Page->listRows)->select();
			$show= $Page->show();			

			$this->assign('page',$show);
		$this->key=$key;
		$this->display();
	}
	public function edit(){
		
	
		if($_POST['del']==2){
		$i=0;
		while($i<=count($_POST['id'])){
					M('search')->where(array('id'=>$_POST['id'][$i]))->setField('status','1');					
					$i++;
		}
		$this->success('修改成功');
		
		}
		if($_POST['del']==3){
		$i=0;
		while($i<=count($_POST['id'])){
					M('search')->where(array('id'=>$_POST['id'][$i]))->setField('status','0');					
					$i++;
		}
		
		$this->success('修改成功');
		
		}
		if($_POST['del']==1){
		$i=0;
		while($i<=count($_POST['id'])){
					M('search')->where(array('id'=>$_POST['id'][$i]))->delete();					
					$i++;
		}
		$this->success('删除成功');
		
		}
	}
}
?>