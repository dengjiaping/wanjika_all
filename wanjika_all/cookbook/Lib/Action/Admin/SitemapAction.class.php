<?php

class SitemapAction extends CommAction {

	
	public function index(){
	

		if($this->ispost()){
			
				
			
		$wapqian=array_keys(C('APP_SUB_DOMAIN_RULES'));		
			preg_match('/[\w][\w-]*\.(?:com\.cn|com|cn|co|net|org|gov|cc|biz|info)(\/|$)/isU', $_SERVER['HTTP_HOST'], $domain);
			$domianhost=rtrim($domain[0], '/');			
		$wapurl='http://'.$wapqian[0].'.'.$domianhost;
		
		$info=M('info')->field('id')->order('id desc')->select();
		$cate=M('cate')->field('id,catename,catelogo,url')->order('id desc')->select();		
		$tags=M('tags')->field('id,tags,url')->order('id desc')->select();		
		if(!$_POST['so']){
		if($_POST['ismobil'] && $_POST['baidu']){
		$sitemap='<?xml version="1.0" encoding="UTF-8" ?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9" xmlns:mobile="http://www.baidu.com/schemas/sitemap-mobile/1/" > ';
		}elseif($_POST['ismobil'] && $_POST['google']){
			$sitemap='<?xml version="1.0" encoding="UTF-8" ?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"  xmlns:mobile="http://www.google.com/schemas/sitemap-mobile/1.0"> ';
		}else{
			$sitemap='<?xml version="1.0" encoding="UTF-8" ?><urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9"> ';
		}
		
		if($_POST['ismobil']){
		$sitemap.='<url><loc>'.$wapurl.'</loc><mobile:mobile type="mobile"/><lastmod>'.date('Y-m-d').'</lastmod><changefreq>daily</changefreq><priority>1.0</priority></url>';
		$sitemap.='<url><loc>'.C('domain').'</loc><lastmod>'.date('Y-m-d').'</lastmod><changefreq>daily</changefreq><priority>1.0</priority></url>';
		}else{
		$sitemap.='<url><loc>'.C('domain').'</loc><lastmod>'.date('Y-m-d').'</lastmod><changefreq>daily</changefreq><priority>1.0</priority></url>';
		}
		}
			if($_POST['so']){
			$sitemap.=C('domain').'	'.$wapurl."\r\n";
			}
			foreach($cate as $c){
				if($_POST['so']){
					$sitemap.=C('domain').U('/'.$c['url']).'	'.$wapurl.U('/'.$c['url'])."\r\n";
				}else{
				$sitemap.='<url>';
				$sitemap.='<loc>'.C('domain').U('/'.$c['url']).'</loc>';
				$sitemap.='<lastmod>'.date('Y-m-d').'</lastmod>';
			
				$sitemap.='<changefreq>daily</changefreq>';				
				$sitemap.='<priority>0.9</priority>';			
				$sitemap.='</url>';
					if($_POST['ismobil']){
				$sitemap.='<url>';
				$sitemap.='<loc>'.$wapurl.U('/'.$c['url']).'</loc>';	
				$sitemap.='<lastmod>'.date('Y-m-d').'</lastmod>';				
				$sitemap.='<mobile:mobile type="mobile"/>';				
				$sitemap.='<changefreq>daily</changefreq>';				
				$sitemap.='<priority>0.9</priority>';				
				$sitemap.='</url>';
				}
				}
			}
				
			foreach($info as $in){
				if($_POST['so']){
					$sitemap.=C('domain').U('/'.$in['id']).'	'.$wapurl.U('/'.$in['id'])."\r\n";
				}else{
				$sitemap.='<url>';
				$sitemap.='<loc>'.C('domain').U('/'.$in['id']).'</loc>';
				$sitemap.='<lastmod>'.date('Y-m-d').'</lastmod>';
			
				$sitemap.='<changefreq>daily</changefreq>';
				if($in['istop']){
				$sitemap.='<priority>0.8</priority>';
				}else{
					$sitemap.='<priority>0.5</priority>';	
				}
				$sitemap.='</url>';
				
				if($_POST['ismobil']){
				$sitemap.='<url>';
			
				$sitemap.='<loc>'.$wapurl.U('/'.$in['id']).'</loc>';	
				$sitemap.='<lastmod>'.date('Y-m-d').'</lastmod>';				
				$sitemap.='<mobile:mobile type="mobile"/>';				
				$sitemap.='<changefreq>daily</changefreq>';				
				$sitemap.='<priority>0.8</priority>';				
				$sitemap.='</url>';
				}
				}
			}
			foreach($tags as $tag){
				if($_POST['so']){
					$sitemap.=C('domain').U('/tag/'.$tag['url']).'	'.$wapurl.U('/tag/'.$tag['url'])."\r\n";
				}else{
				$sitemap.='<url>';
				$sitemap.='<loc>'.C('domain').U('/tag/'.$tag['url']).'</loc>';
					$sitemap.='<lastmod>'.date('Y-m-d').'</lastmod>';
				$sitemap.='<changefreq>daily</changefreq>';				
					$sitemap.='<priority>0.5</priority>';				
				$sitemap.='</url>';		
				if($_POST['ismobil']){
				$sitemap.='<url>';
				$sitemap.='<loc>'.$wapurl.U('/tag/'.$tag['url']).'</loc>';
				$sitemap.='<lastmod>'.date('Y-m-d').'</lastmod>';				
				$sitemap.='<mobile:mobile type="mobile"/>';				
				$sitemap.='<changefreq>daily</changefreq>';				
				$sitemap.='<priority>0.5</priority>';				
				$sitemap.='</url>';
				}
				}
			}
			
			
		if(!$_POST['so']){
		$sitemap.='</urlset>';
		}
		if($_POST['ismobil'] && $_POST['baidu'] ){
		$confpath =APP_PATH.'/baidu-sitemap.xml';
		$webconfig = @fopen($confpath,w);
		if($webconfig){
			$fwrite=fwrite($webconfig,$sitemap);
			
			$this->success('生成在根目录下：'.$confpath);
		}else{
			$this->error('网站根目录没有写入权限！');
		}
		}
		elseif($_POST['ismobil'] && $_POST['google']){
		$confpath =APP_PATH.'/google-sitemap.xml';
		$webconfig = @fopen($confpath,w);
		if($webconfig){
			$fwrite=fwrite($webconfig,$sitemap);
			
			$this->success('生成在根目录下：'.$confpath);
		}else{
			$this->error('网站根目录没有写入权限！');
		}
		}elseif($_POST['ismobil'] && $_POST['so']){
		$confpath =APP_PATH.'/360sitemap.txt';
		$webconfig = @fopen($confpath,w);
		if($webconfig){
			$fwrite=fwrite($webconfig,$sitemap);
			
			$this->success('生成在根目录下：'.$confpath);
		}else{
			$this->error('网站根目录没有写入权限！');
		}
		}else{
		$confpath =APP_PATH.'/sitemap.xml';
		$webconfig = @fopen($confpath,w);
		if($webconfig){
			$fwrite=fwrite($webconfig,$sitemap);
			
			$this->success('生成在根目录下：'.$confpath);
		}else{
			$this->error('网站根目录没有写入权限！');
		}
		}
		
		
	}
	else{
		$this->display();
	}
	
		
}
}