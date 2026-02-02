<?php
namespace ApiDataFetcher;

class InvalidContentException extends Exception {

	private $content;

	function __construct($message,$content){
		$this->content = $content;
		parent::__construct($message);
	}

	function getContent(){
		return (string)$this->content;
	}
}
