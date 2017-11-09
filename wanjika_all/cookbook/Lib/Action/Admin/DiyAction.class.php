<?php

class DiyAction extends CommAction {
    public function index(){
		
		$diy=M('diy')->where("pid=0")->order('cid ASC')->select();
		foreach($diy as $k=>$v){
			$cate=M('cate')->find($v['cid']);
			$diy[$k]['catename']=$cate['catename'];
		}
			$this->diy=$diy;
			import('Class.Cate',LIB_PATH);
			$cate = M('cate')->order('sort ASC')->select();
			$cate = Cate::catetree($cate);	
			$this->assign('cate',$cate);
			$this->display();
		}

	public function adddiy(){

		$cid=I('post.cid');
		if($cid=='0'){
			$this->error('请选择一个分类');
		}
		$count =	count($_POST['key']);
		for($i=0;$i<$count;$i++){
			$date['cid']=$cid;
			$date['key']=$_POST['key'][$i];
			$date['pid']=0;
			
			M('diy')->add($date);
		}
		$this->success('添加成功');
	}
	public function edit(){	
		if($this->ispost()){
		$con=M('diy')->save($_POST);
		if($con>0){
			$this->success('修改成功',U('/Admin/Diy/index'));
		}else{
			$this->error('修改失败');
		}
		}else{
		$id=I('get.id');	
		$this->diy=M('diy')->find($id);
		import('Class.Cate',LIB_PATH);
		$cate = M('cate')->order('sort ASC')->select();
		$cate = Cate::catetree($cate);	
		$this->assign('cate',$cate);

		$this->display();
		}
		
	}
	public function del(){
	
		if(M('diy')->delete($_GET['id'])){
			$this->success('删除成功');
		}else{
			$this->error('删除失败');
		}
	}
}