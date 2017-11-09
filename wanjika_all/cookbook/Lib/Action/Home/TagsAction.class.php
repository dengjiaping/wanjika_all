<?php

class TagsAction extends CommAction {
    public function index(){
		$tag=trim(htmlspecialchars(addslashes(I('get.url')),ENT_QUOTES));
	
		if(inject_check($tag)){
			$this->error('非法操作');
		}
		$tagtitle=M('tags')->where(array('url'=>$tag))->find();
		$pid=M('tags')->where(array('url'=>$tag))->getField('pid',true);
		M('tags')->where(array('tags'=>$tag))->setInc('hits',1);
		$map['id']  = array('in',$pid);
		$map['isshow']  = 1;
		$count=M('info')->where($map)->count();
		import("Class.Page",LIB_PATH);	
		$Page= new Page($count,10);	
				
		$taglist=M('info')->where($map)->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();	
		foreach($taglist as $ke=>$cname){							
				$ccate=M('cate')->find($cname['cid']);				
				$taglist[$ke]['catename']=$ccate['catename'];		
				$taglist[$ke]['cateurl']=U('/'.$ccate['url']);	
				$taglist[$ke]['url']=U('/'.$cname['id']);	
				$tags=M('tags')->where(array('pid'=>$cname['id']))->field('tags')->select();				
				foreach($tags as $v){
					$taglist[$ke]['tags'].="<a href=\"".U('/tags.php?tag='.$v['tags'])."\">".$v['tags']."</a>";
				}			
			}	
			
			$a=M("tags")->getField("url,tags",true);
				
				foreach($a as $key => $value) {
					$b=Array('url'=>$key,'tag'=>$value);
					
				}
			
			
			
			$Page->url='tag/'.$tag;
			$show= $Page->show();			
			$this->assign('page',$show);
			$this->assign('list',$taglist);
			$this->title=$tagtitle['tags'];
			$this->display(':tags');
	}
}