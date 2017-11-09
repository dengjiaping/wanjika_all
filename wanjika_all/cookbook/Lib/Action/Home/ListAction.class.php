<?php
class ListAction extends CommAction {
    public function index(){

        $cookbook=M('cookbook')->where(1)->select();
			$url=htmlspecialchars(addslashes(I('get.url')),ENT_QUOTES);

			if(inject_check($url)){
				$this->error('非法操作');
			}
            $openid = $_SESSION['openid'];
			$tem=M('cate')->where(array('url'=>$url))->find();

			if(empty($tem)){
				header('HTTP/1.1 404 Not Found');
				header('status: 404 Not Found');
				Header("Location: ".C('domain'));
			}
			if($tem['outurl']){
				Header("HTTP/1.1 301 Moved Permanently");
				Header("Location: ". $tem['outurl']);
			}
			$id=$tem['id'];

			if($tem['pid']=='0'){

				$daohan='<a href="'.U('/'.$tem['url']).'" >'.$tem['catename'].'</a>';
			}else{

				$Upcate=M('cate')->find($tem['pid']);

				$daohan='<a href="'.U('/'.$Upcate['url']).'" >'.$Upcate['catename'].'</a> > <a href="'.U('/'.$tem['url']).'" >'.$tem['catename'].'</a>';
				}

			import("Class.Page",LIB_PATH);



				$cids=M('cate')->where("pid=$tem[id]")->getField('id',true);

				if($cids){
				$allid=implode(',',$cids).','.$id;
				}else{
					$allid=$id;
				}

        $is_draft = 0;
        if($url == 'wodecaogao')
        {
            $is_draft = 1;
        }
        $count= M('cookbook')->where(array('userid'=>$openid,'is_draft'=>$is_draft))->count();

        $Page= new Page($count,25);

        $Page->url =$url;

        $this->page = $Page->show();


        $cookbook =M('cookbook')->order('id DESC')->where(array('userid'=>$openid,'is_draft'=>$is_draft))->limit($Page->firstRow.','.$Page->listRows)->select();
        foreach($cookbook as $ke=>$book){
            $cookbook[$ke]['url']=U('/'.$book['id']);
        }
//			$list=M('info')->where(array('isshow'=>1))->where("cid IN ($allid)")->order('id desc')->limit($Page->firstRow.','.$Page->listRows)->select();
//			foreach($list as $ke=>$cname){
//				$ccate=M('cate')->find($cname['cid']);
//				$list[$ke]['catename']=$ccate['catename'];
//				$list[$ke]['cateurl']=U('/'.$ccate['url']);
//				$list[$ke]['url']=U('/'.$cname['id']);
//				$tags=M('tags')->where(array('pid'=>$cname['id']))->field('tags,url')->select();
//				foreach($tags as $v){
//					$list[$ke]['tag'].="<a href=".U('/tag/'.$v['url']).">".$v['tags']."</a>";
//				}
//				$list[$ke]['diy']=M('diy')->where(array('pid'=>$cname['id']))->select();
//			}


//        $Page->url =$url;
//			$show= $Page->show();
//			$this->assign('page',$show);
			
			
//				$catesub=M('cate')->where(array('pid'=>$tem[id]))->order('sort DESC')->select();
//			if(empty($catesub)){
//				$catesub=M('cate')->where(array('catetype'=>$tem['catetype'],'pid'=>$tem['pid']))->order('sort DESC')->select();
//			}
//				foreach ($catesub as $key=>$dd){
//					$catesub[$key]['url']=U('/'.$dd['url']);
//				}
		
//			$this->allrev=$allrev;
//			$this->allhits=$allhits;
			$this->daohan=$daohan;
//			$this->catesub=$catesub;
//			$this->list=$list;
			$this->title=$tem['catename'];
			$this->p=$_GET['p'];
			$this->seotitle=$tem['catetitle'];
			$this->key=$tem['catekey'];
			$this->desc=$tem['catedesc'];
			$this->catelogo=$tem['catelogo'];
			$this->content=$tem['content'];
			$this->url=U('/'.$tem['url']);
			$this->cate=$tem;
            $this->cookbook=$cookbook;
			unset($list);
			
			if($tem['catetemp']){
				$this->display(':'.$tem['catetemp']);	
				exit;
			}
			if($tem['catetype']=='2'){
				$this->display(':pic_list');
			}			
			if($tem['catetype']=='1'){
				$this->display(':text_list');
			}
			if($tem['catetype']=='3'){
				$this->display(':page');
			}
			
	}

}