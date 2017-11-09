<?php

class TagsAction extends CommAction {
    public function index(){
		if($this->ispost()){
			
			if($_POST['del']==1){
				$i=0;
				while($i<=count($_POST['id'])){
					$arr=M('tags')->delete($_POST['id'][$i]);					
					$i++;
				}
				$this->success('删除成功');
			}
			
			}else{
		$count=M('tags')->count();
		import('Class.Page',LIB_PATH);// 导入分页类		
		$count      = M('tags')->count();// 查询满足要求的总记录数
		$Page       = new Page($count,25);// 实例化分页类 传入总记录数和每页显示的记录数		
		$Page->url =GROUP_NAME.'/Tags/index/p/';
		$this->page = $Page->show();	
		$this->tags=M('tags')->order('id DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
	
		$this->display();
	}
	
	}
	public function edit(){
	
		if($this->ispost()){
			$url=get_pinyin($_POST['tags']);
			$_POST['url']=$url[0];
			if(M('tags')->save($_POST)){
				$this->success('修改成功',U('/Admin/Tags/index'));
			}else{
				$this->error('修改失败');
			}
		}else{
		$this->tags=M('tags')->find($_GET['id']);
		$this->display();
		}
	
	}
}