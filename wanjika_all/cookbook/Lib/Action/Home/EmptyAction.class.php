<?php 

class EmptyAction extends Action{

	public function index(){
				header('HTTP/1.1 404 Not Found'); 
				header('status: 404 Not Found');
				Header("Location: ".C('domain'));
	  }
	    Public function _empty () {
				header('HTTP/1.1 404 Not Found'); 
				header('status: 404 Not Found');
				Header("Location: ".C('domain'));
    }

}

?>