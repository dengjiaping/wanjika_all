<?php 

class EmptyAction extends Action{

	public function index(){
				header('HTTP/1.1 404 Not Found'); 
				$this->display(":404");
	  }
	    Public function _empty () {
				header('HTTP/1.1 404 Not Found'); 
				$this->display(":404");
    }

}

?>