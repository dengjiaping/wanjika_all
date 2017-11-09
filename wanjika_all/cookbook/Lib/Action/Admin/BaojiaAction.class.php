<?php

class BaojiaAction extends CommAction {

	public function index(){
		import('Class.Page',LIB_PATH);
		$count= M('xuqiu')->count();
		$Page= new Page($count,1);
		$Page->url =GROUP_NAME.'/Baojia/index/p/';
		$this->page = $Page->show();
		$info =M('xuqiu')->order('id DESC')->limit($Page->firstRow.','.$Page->listRows)->select();
		$this->info=$info;
		$this->display();
	}
	

}