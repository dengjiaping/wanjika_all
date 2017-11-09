<?php

class CateAction extends CommAction {

	
	public function index(){
	
		if($this->ispost()){
				
					
				  $id = $_POST['id'];
				
				if(is_array($id)){
					$where = 'id in('.implode(',',$id).')';
				}else{
					$where = 'id='.$id;
				}
				 $list = M('cate')->where($where)->delete();
				 if($list!==false) {
						$this->success("成功删除{$list}条！");
					}else{
						$this->error('删除失败！');
					}
		
		}else{
		
		$cate = M('cate')->where("pid='0'")->order('sort ASC')->select();
		
		foreach($cate as $k=>$v){
			
			$cate[$k]['sub']=M('cate')->where(array('pid'=>$v['id']))->order('sort ASC')->select();
		
		
		}
		
		$this->assign('cate',$cate);	
		
		$this->display();
		}
			
	
		
	}
  public function addcate(){
		
		if($this->ispost()){
			
				if(empty($_POST['catename'])){
					$this->error('分类名不能为空');
				}
				if($_POST['content']){
				$_POST['content']=stripslashes($_POST['content']);
				}
				if(empty($_POST['url'])){
				  $url=get_pinyin($_POST['catename']);
				  $_POST['url']=$url[0];
				}
			
				$con = M('cate')->add($_POST);				
				if($con>0){
					$this->success('添加成功',U('/Admin/Cate/index'));
				}else{
					$this->error('添加失败');
				}
				
		
		}else{
			$this->pid=M('cate')->where("pid='0'")->select();
			$this->display();
		}
  
  }
  public function edit(){		
		if($this->ispost()){
			
				if(empty($_POST['url'])){
				  $url=get_pinyin($_POST['catename']);
				  $_POST['url']=$url[0];
				}
				$cid=$_POST['id'];
			
				$youghji=M('cate')->where(array('pid'=>$cid))->find();			
				$cateold=M('cate')->find($cid);			
				
				if($youghji && $_POST['pid']!=$cateold['pid']){
					$this->error('你必须先把当前分类下的二级分类移走');
				}
				
				if($_POST['content']){
				$_POST['content']=stripslashes($_POST['content']);
				}
				if($_POST['catetype']=='1'){
					$ispic='0';
				}else{
					$ispic='1';
				}
				$editinfo['temp']=$_POST['infotemp'];
				$editinfo['ispic']=$ispic;
				$in=M('info')->where(array('cid'=>$_POST['id']))->setField($editinfo);
				
				$edit= M('cate')->save($_POST);
				
				if($edit || $in){
					$this->success('修改成功',U('/Admin/Cate/index'));
				}else{
					$this->error('修改失败');
				}
		}else{		
		$id=$_GET['id'];
		$cate=M('cate')->find($id);
		;
		$catelist = M('cate')->where('pid="0"')->select();
		$this->assign('catelist',$catelist);
		
		$this->assign('cate',$cate);
		$this->display();
		}
  }
  
  public function sortcate(){
	$db = M('cate');
	foreach ($_POST as $id=>$sort){
		$db->where(array( 'id'=>$id))->setField('sort',$sort);		
	}
	$this->success('设置成功');
  }
  public function delete(){
		
		$cate=M('cate')->where(array('pid'=>$_GET['id']))->find();		
		if($cate){
			$this->error('删除失败,请先删除分类下的子分类');
		}else{
			$delcate=M('cate')->delete($_GET['id']);
			$con=M('info')->where(array('cid'=>$_GET['id']))->delete();			
			if($con>0 || $delcate>0){
			$this->success('分类删除成功,同时共删除该分类下的'.$con.'文章他和'.$diy.'条自定义字段');
			}else{
			$this->error('删除失败');
			}
		}
  
		
		
  }

}