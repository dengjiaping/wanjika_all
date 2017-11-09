<?php
//验证码
class CodeAction extends Action {

	public function index(){
		
			header("content-type:image/png");
			 import('Class.Image',LIB_PATH);			 ob_end_clean();
			 Image::buildImageVerify(4,1,'png',50,28,'code');
			 //Image::buildImageVerify();
			
	}
	
}