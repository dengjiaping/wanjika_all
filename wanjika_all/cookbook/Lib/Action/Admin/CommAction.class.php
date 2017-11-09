<?php

class CommAction extends Action {
	public function _initialize(){				if(!$_SESSION['adminuser']){			$this->error('非法操作',U('/Admin/Login/index'));		}		$this->webuser=C('webuser');		$this->adminuser=session('adminuser');	
	}

	
}