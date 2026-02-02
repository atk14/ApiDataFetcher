<?php
namespace ApiDataFetcher;

class NoContentException extends InvalidContentException {

	function __construct($message,$content = ""){
		parent::__construct($message,$content);
	}
}
