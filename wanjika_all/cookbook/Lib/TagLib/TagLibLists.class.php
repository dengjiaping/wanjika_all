<?php

 class TagLibLists extends TagLib{

    protected $tags = array(
		 'pcwap'=>array('attr'=>'pic,top,rec,num,paixu,cid'),
		 'cate'=>array('attr'=>'id'),
         'search' => array('attr' => 'num,paixu','status' =>1),
         'taglist' => array('attr' => 'num,paixu'),
         'newpost' => array('attr' => 'num','status'=>1),
         'links' => array('attr' => 'num,cate','isshow'=>1),
         'adlist' => array('attr' => 'id,cate','isshow'=>1),
		);
		public function _cate ($attr,$content){
		   $attr = $this->parseXmlAttr($attr);	 
		   
			if($attr['id']){
				$cid='where(array('.'\'id\''.'=>array('.'IN'.',array('.$attr['id'].')))'.')->';
			}		 
		 
		   $str='<?php ';      
		   $str .= '$cates=M("cate")->'.$cid.'order("sort ASC")->select();';
		   $str .= 'foreach ($cates as $i=>$cate):';	
		   $str .= '$cate["url"]=U(\'/\'.$cate[\'url\']);';
		   $str .= '$cate["i"]=$i+1;';
		   $str .= '$cate["name"]=$cate["catename"]; ?>';
		   $str .= $content;
		   $str .='<?php endforeach ?>'; 
		   return $str;
       }
	
	   public function _pcwap ($attr,$content){
		   $attr = $this->parseXmlAttr($attr);	 
		   $limit=$attr['num'];
			if($attr['cid']){
				$cid='where(array('.'\'cid\''.'=>array('.'IN'.',array('.$attr['cid'].')))'.')->';
			}
		   if($attr['pic']=='0' || $attr['pic']=='1'){
			 $ispic='\'ispic\'=>'.$attr['pic'].',';
		   }
		   if($attr['top']=='0' || $attr['top']=='1' ){
				$istop='\'istop\'=>'.$attr['top'].',';
		   }
		   if($attr['rec']=='0' || $attr['rec']=='1'){
		   $isrec='\'isrec\'=>'.$attr['rec'].',';
		   }
		   if($attr['paixu']){
			 $paixu=$attr['paixu'];
		   }else{
			 $paixu='id desc';
		   }
		   $str='<?php ';      
		   $str .= '$pcwap=M("info")->'.$cid.'where(array(\'isshow\'=>1,'.$istop.$isrec.$ispic.'))->order('.'"'.$paixu.'")->limit('.$limit.')->select();';
		   $str .= 'foreach ($pcwap as $s=>$pcwap):';		
		   $str .= '$cate=M(\'cate\')->find($pcwap["cid"]);';
		   $str .= '$pcwap["catename"]=$cate[\'catename\'];';
		   $str .= '$pcwap["i"]=$s+1;';
		   $str .= '$pcwap["cateurl"]=U(\'/\'.$cate[\'url\']);';
		   $str .= '$pcwap[\'url\']=U("/".$pcwap["id"]);'; 
		   $str .= '$tags=M(\'tags\')->where(array("pid"=>$pcwap[\'id\']))->select(); foreach($tags as $va){ ';
		   $str .= '$pcwap[\'tags\'].="<a href=".U(\'tag/\'.$va[url]).">".$va[tags]."</a>"; } ?>'; 
		   $str .= $content;
		   $str .='<?php endforeach ?>'; 
		   return $str;
       }
	  
	    public function _search ($attr,$content){
		   $attr = $this->parseXmlAttr($attr);	 
		   $limit=$attr['num'];
		   if($attr['paixu']){
			$paixu=$attr['paixu'];
		   }else{
			$paixu='id desc';
		   }
		   $str='<?php ';      
		   $str .= '$searchs=M("search")->where(array(\'status\'=>1))->order('.'"'.$paixu.'"'.')->limit('.$limit.')->select();';
		   $str .= 'foreach ($searchs as $search):';		 
		   $str .= '$search["url"]=U("/key/".$search["url"]);?>'; 
		   $str .= $content;
		   $str .='<?php endforeach ?>'; 
		   return $str;
       }
	   
	   
	    public function _taglist ($attr,$content){
		   $attr = $this->parseXmlAttr($attr);	 
		   $limit=$attr['num'];
		   if($attr['paixu']){
			$paixu=$attr['paixu'];
		   }else{
			$paixu='id desc';
		   }
		   $str='<?php ';      
		   $str .= '$tagall=M("tags")->order('.'"'.$paixu.'"'.')->limit('.$limit.')->getField("url,tags",true);';	 	 		
		   $str .= 'foreach($tagall as $key => $value):
					$taglist=Array(\'url\'=>$key,\'tag\'=>$value);					
					';		  	   
		   $str .= '$taglist["i"]=$key+1;'; 
		   $str .= '$taglist["url"]=U("/tag/".$taglist["url"]);?>'; 
		   $str .= $content;
		   $str .='<?php endforeach ?>'; 
		   return $str;
       }
	   
	    public function _newpost ($attr,$content){
		   $attr = $this->parseXmlAttr($attr);	 
		   $limit=$attr['num']; 	   
		   $str='<?php ';      
		   $str .= '$newrevs=M("book")->where(\'status=1\')->order("id desc")->limit('.$limit.')->select();';
		   $str .= 'foreach ($newrevs as $newpost):';		
		   $str .= '$info=M(\'info\')->find($newpost[\'infoid\']);';
		   $str .= '$newpost[\'title\']=$info[\'name\'];'; 
		   $str .= '$newpost[\'pic\']=$info[\'pic\'];'; 
		   $str .= '$newpost[\'desc\']=$info[\'desc\'];'; 
		   $str .= '$newpost[\'url\']=U(\'/\'.$info[\'id\']);?>'; 
		   $str .= $content;
		   $str .='<?php endforeach ?>'; 
		   return $str;
		}
	     public function _links ($attr,$content){
		   $attr = $this->parseXmlAttr($attr);	 
		   $limit=$attr['num']; 
		   $str='<?php '; 
		   if($attr['id']){
				$id='where(array('.'\'id\''.'=>array('.'IN'.',array('.$attr['id'].')))'.')->';
			}
			if($attr['cate']){
				$cate='where(array('.'\'cate\''.'=>'.$attr['cate'].'))->';
			}
		   $str .= '$link=M("links")->'.$id.$cate.'where(\'isshow=1\')->order("sort desc")->limit('.$limit.')->select();';
		   $str .= 'foreach ($link as $links):';
		   $str .= '?>';	 
		   $str .= $content;
		   $str .='<?php endforeach ?>'; 
		   return $str;
        }
	    public function _adlist ($attr,$content){
		   $attr = $this->parseXmlAttr($attr);	 
		   $str='<?php '; 
			if($attr['id']){
				$id='where(array('.'\'id\''.'=>array('.'IN'.',array('.$attr['id'].')))'.')->';
			}
			if($attr['cate']){
				$cate='where(array('.'\'cate\''.'=>'.$attr['cate'].'))->';
			}
		   $str .= '$adlists=M("ads")->'.$id.$cate.'where(\'isshow=1\')->order("id desc")->select();';
		   $str .= 'foreach ($adlists as $adlist):';
		   $str .= '?>';	 
		   $str .= $content;
		   $str .='<?php endforeach ?>'; 
		   return $str;
       }
	   
 }
 ?>
