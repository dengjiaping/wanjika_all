<?php


class SearchAction extends CommAction {
	
	public function index(){
		
		if(!cookie('diywap_time')){
		cookie('time',time(),'expire=3600&prefix=pcwapsearch_');
		}
		
		
		$url=trim(htmlspecialchars(addslashes(I('get.url')),ENT_QUOTES));		
		if($url){
			$keword=M('search')->where(array('url'=>$url))->find();
			$key=$keword['key'];
		}else{
			$key=trim(htmlspecialchars(addslashes(I('get.key')),ENT_QUOTES));
		}
			$surl=M('search')->where(array('key'=>$key))->find();
		if($surl){
			$url=$surl['url'];
		}else{
			$pin=get_pinyin($key);
			$url=$pin[0];
		}
		if(inject_check($key)){
			$this->ajaxReturn(0,'非法提交！',0);
		}
		if(inject_check($url)){
			$this->ajaxReturn(0,'非法提交！',0);
		}
		if(empty($key)){
			$this->ajaxReturn(0,'非法提交！',0);
		}
		
		$map['content'] = array('like',"%$key%");
		
		import("Class.Page",LIB_PATH);
		$count=M('info')->where($map)->where('isshow=1')->count();	
		$Page= new Page($count,10);	
		$keylist=M('info')->where($map)->where('isshow=1')->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();			
	
		$Page->url ='key/'.$url;
		$show= $Page->show();
		$keys=M('search')->where(array('key'=>$key))->find();
			foreach($keylist as $ke=>$cname){							
				$ccate=M('cate')->find($cname['cid']);				
				$keylist[$ke]['catename']=$ccate['catename'];		
				$keylist[$ke]['cateurl']=U('/'.$ccate['url']);	
				$keylist[$ke]['url']=U('/'.$cname['id']);	
				$tags=M('tags')->where(array('pid'=>$cname['id']))->field('tags')->select();				
				foreach($tags as $v){
					$keylist[$ke]['tags'].="<a href=\"".U('/tag/'.$v['url'])."\">".$v['tags']."</a>";
				}			
			}	
		if($keys){
			if(date('ymd',cookie('pcwapsearch_time'))!=date('ymd',time())){
			M('search')->where(array('id'=>$keys['id']))->setInc('hits',1);
			}
		}else{
			$inurl=get_pinyin($key);
			$data['key']=$key;
			$data['url']=$inurl[0];
			$data['hits']=1;
			$data['time']=time();
			M('search')->add($data);
		}		
		$this->assign('page',$show);	
		$this->list=$keylist;
		$this->title=$key;
		$this->display(':search');
	
	}
	
}
?>