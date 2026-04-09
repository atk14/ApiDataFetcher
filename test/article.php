<?php
class Article {

	protected $id;
	protected $title;

	function __construct($id, $title = "Some_Nice_Title"){
		$this->id = $id;
	}

	function getId(){
		return $this->id;
	}

	function getTitle(){
		return $title;
	}

	function __toString(){
		return $this->getTitle();
	}
}
