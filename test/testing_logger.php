<?php
class TestingLogger {

	var $messages = array();

	function info($message){
		$this->messages[] = "[info] $message";
	}

	function debug($message){
		$this->messages[] = "[debug] $message";
	}

	function flush(){
		// do nothing
	}
}
