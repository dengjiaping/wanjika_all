<?php 
class WeixinAction extends CommAction{
	public function index(){
			
			$huifu=M('weixin_gz')->find(1);
			if($huifu['leixin']==2){
				$cate=M('cate')->find($huifu['cid']);
				$cids=M('cate')->where("pid=$cate[id]")->getField('id',true);
				if($cids){
				$allid=implode(',',$cids).','.$cate['id'];
				}else{
					$allid=$cate['id'];
				}
				$piclist=M('info')->where("cid IN ($allid)")->order($huifu['sort'])->limit($huifu['num'])->select();
				
			}
			$this->cate=M('cate')->select();
			$this->piclist=$piclist;
			$this->huifu=$huifu;
			$this->display();
	
	}
	public function gzhuifu(){
	
		if(M('weixin_gz')->save($_POST)){
			$this->success('设置成功');
		}else{
			$this->error('设置失败');
		}
	}
	
	public function keyhf(){
		$huifu=M('weixin_huifu')->select();
		foreach($huifu as $k=>$v){
		
			$c=M('cate')->find($v['cid']);
			$huifu[$k]['catename']=$c['catename'];
		}
		$this->huifu=$huifu;
		$this->cate=M('cate')->select();
		$this->display();
	}


	public function key(){
		
		if(M('weixin_huifu')->where(array('key'=>$_POST['key']))->find()){
			$this->error('关键词已存在');
		}
		if(M('weixin_huifu')->add(I('post.'))){
			$this->success('添加成功');
		}else{
			$this->error('添加失败');
		}
	}
	
	public function eidtkey(){
		if($this->ispost()){
			if($_POST['leixin']=='2'){
				$_POST['text']='';
			}
			if(M('weixin_huifu')->save($_POST)){
				$this->success('修改成功',U('/Admin/Weixin/keyhf'));
			}else{
				$this->error('修改失败内容没有被改变');
			}
		}else{
		$this->cate=M('cate')->select();
		$this->v=M('weixin_huifu')->find(I('get.id'));
		$this->display();
		}
	}
	public function delkey(){
		if(M('weixin_huifu')->delete(I('get.id'))){
		
			$this->success('删除成功');
		}else{
			$this->error('删除失败');
		}
	}
	
	public function setappid(){
		if(M('weixin_appid')->save(I('post.'))){
			$this->success('修改成功');
		}else{
			$this->error('修改失败，内容没有改变');
		}
	}
	public function menu(){
		
		if($this->ispost()){
		if(I('post.pid')==0){
		$con=count(M('weixin_menu')->where('pid=0')->select());
		if($con==3){
			$this->error('最多只能添加3个主菜单');
		}
		}		
		if(M('weixin_menu')->add(I('post.'))){
			$this->success('添加成功',U('/Admin/Weixin/menu'));
		}else{
			$this->error('添加失败');
		}
		}else{		
			$this->appid=M('weixin_appid')->find();	
			$this->menu=M('weixin_menu')->where('pid=0')->select();
			$menulist=M('weixin_menu')->where('pid=0')->order('sort ASC')->select();
			foreach($menulist as $k=>$v){
				$er=M('weixin_menu')->where(array('pid'=>$v['id']))->order('sort ASC')->select();
				$menulist[$k]['sub']=$er;
			}
			$this->menulist=$menulist;
			$this->keys=M('weixin_huifu')->select();
			$this->display();
		}
	}
	public function sortcate(){
		$db = M('weixin_menu');
		
		foreach ($_POST as $id=>$sorts){
			$db->where(array( 'id'=>$id))->setField('sort',$sorts);		
		}
	$this->success('排序已改变');
	}
	

	public function delmenu(){
		if(M('weixin_menu')->where(array('pid'=>$_GET['id']))->find()){
			$this->error('请先删除二级菜单');
		}
		if(M('weixin_menu')->delete(I('get.id'))){
			$this->success('删除成功');
		}else{
			$this->error('删除失败');
		}
	}
	
}