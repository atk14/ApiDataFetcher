<?php
class Stringerer {

	protected $string;

	function __construct($string){
		$this->string = (string)$string;
	}

	function __toString(){
		return $this->string;
	}
}
