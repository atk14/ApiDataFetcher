<?php
namespace ApiDataFetcher;

class HttpException extends Exception {

	private $status_code;
	private $content;
	private $headers;

	function __construct($message,$status_code,$content,$headers){
		$this->status_code = $status_code;
		$this->content = $content;
		$this->headers = $headers;
		parent::__construct($message);
	}

	function getStatusCode(){
		return $this->status_code;
	}

	function getContent(){
		return (string)$this->content;
	}

	function getHeaders($options = []){
		$options += [
			"as_hash" => false,
			"lowerize_keys" => false
		];

		$out = $this->headers;

		if($options["as_hash"]){
			$headers = explode("\n",$out);
			$out = array();
			foreach($headers as $h){
				if(preg_match("/^([^ ]+):(.*)/",trim($h),$matches)){
					$key = $options["lowerize_keys"] ? strtolower($matches[1]) : $matches[1];
					$out[$key] = trim($matches[2]);
				}
			}
		}

		return $out;
	}
}
