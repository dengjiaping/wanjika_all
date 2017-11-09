<?php

class DelcaheAction extends CommAction {

public function rmdirr($dirname) {
	  if (!file_exists($dirname)) {
	   return false;
	  }
	  if (is_file($dirname) || is_link($dirname)) {
	   return unlink($dirname);
	  }
	  $dir = dir($dirname);
	  if($dir){
	   while (false !== $entry = $dir->read()) {
		if ($entry == '.' || $entry == '..') {
		 continue;
		}
		//递归
		$this->rmdirr($dirname . DIRECTORY_SEPARATOR . $entry);
	   }
	  }
	  $dir->close();
	  return rmdir($dirname);
	 }
   
 public function del_cache() {

	
 
 header("Content-type: text/html; charset=utf-8");
  //清文件缓存
  $dirs = array(APP_PATH.'Runtime/');
  @mkdir('Runtime',0777,true);
  //清理缓存
  foreach($dirs as $value) {
   $this->rmdirr($value);
  }

  $this->success('系统缓存清除成功',U('/Admin/Index/home'));
  //echo '<div style="color:red;">系统缓存清除成功！</div>';   
 } 


 }