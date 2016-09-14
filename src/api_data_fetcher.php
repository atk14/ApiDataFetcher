<?php
/**
 * Zakladni cast URL datoveho API
 */
if(!defined("API_DATA_FETCHER_BASE_URL")) {
	define("API_DATA_FETCHER_BASE_URL","http://skelet.atk14.net/site/api/");
}

/**
 * Trida zajistuje stahovani a dekodovani dat z datoveho API
 *
 * $apd = new ApiDataFetcher(array(
 *	"additional_headers" => array("Cookie: ".$request->getHeader("Cookie"))
 * ));
 * $data = $apd->get("articles/detail",array("id" => 123));
 */
class ApiDataFetcher{
	var $logger;
	var $request;
	var $response;
	var $lang;
	var $base_url;

	var $errors;
	var $url;
	var $status_code;
	var $additional_headers;

	protected static $QueriesExecuted = array();

	/**
	 * $adf = new ApiDataFetcher("https://service.activa.cz/api/");
	 */
	function __construct($url_or_options = null,$options = array()){
		global $ATK14_GLOBAL;

		if(is_array($url_or_options)){
			$options = $url_or_options;
			$url_or_options = null;
		}

		$url = $url_or_options ? $url_or_options : API_DATA_FETCHER_BASE_URL;

		$options += array(
			"logger" => $ATK14_GLOBAL->getLogger(),
			"request" => $GLOBALS["HTTP_REQUEST"],
			"response" => $GLOBALS["HTTP_RESPONSE"],
			"lang" => $ATK14_GLOBAL->getLang(),
			"url" => $url,
			"cache_storage" => new CacheFileStorage(),
			"additional_headers" => array(), // array("X-Forwarded-For: 127.0.0.1","X-Logged-User-Id: 123")
		);

		// "X-Forwarded-For: office.snapps.eu" -> array("X-Forwarded-For: office.snapps.eu")
		if(!is_array($options["additional_headers"])){
			$options["additional_headers"] = $options["additional_headers"] ? array($options["additional_headers"]) : array();
		}

		$this->logger = $options["logger"];
		$this->request = $options["request"];
		$this->response = $options["response"];
		$this->lang = $options["lang"];
		$this->base_url = $options["url"];
		$this->cache_storage = $options["cache_storage"];
		$this->additional_headers = $options["additional_headers"];
	}

	/**
	 * @return string[]
	 */
	function getErrors(){ return $this->errors; }

	/**
	 *	$api_data_fetcher->get("articles/detail",array("id" => 123));
	 *
	 *	$api_data_fetcher->get("articles/index",array("offset" => 10));
	 *	$api_data_fetcher->get("articles",array("offset" => 10)); // stejne jako "articles/index"
	 *
	 *	$api_data_fetcher->get("articles/index",array("offset" => 10),array("cache" => 60));
	 */
	function get($action,$params = array(),$options = array()){
		$options["method"] = "GET";
		return $this->_doRequest($action,$params,$options);
	}

	/**
	 *	$api_data_fetcher->post("articles/create_new",array(
	 *		"title" => "Very Nice Article"
	 *	));
	 */
	function post($action,$params = array(),$options = array()){
		$options["method"] = "POST";
		return $this->_doRequest($action,$params,$options);
	}

	/**
	 *	$api_data_fetcher->postFile("images/create_new","/path/to/file.jpg");
	 *	$api_data_fetcher->postFile("images/create_new","/path/to/file.jpg",array("title" => "Image of Earth", "description" => "..."));
	 */
	function postFile($action,$file,$params = array(),$options = array()){
		$options += array(
			"name" => null,
			"postname" => "file",
			"mime_type" => null, // "application/octet-stream"
		);

		if(!$options["name"]){
			$options["name"] = preg_replace('/^.*\//','',$file); // "/params/to/image.jpg" -> "image.jpg"
		}

		if(is_null($options["mime_type"])){
			$options["mime_type"] = Files::DetermineFileType($file);
		}

		return $this->_doRequest($action,$params,array(
			"method" => "POST",
			"file" => $options,
		));

		$url = "$this->base_url$this->lang/$action/?".$this->_joinParams($params);

		$uf = new UrlFetcher($url,array(
		));
		$uf->post(Files::GetFileContent($file),array("content_type" => $options["mime_type"]));
		
		// TODO: finish it!
	}

	/**
	 *	$this->_doRequest("products/detail",array("catalog_id" => "1234/3345566"));
	 */
	function _doRequest($action,$params,$options){
		$params += array(
			"format" => "json"
		);

		$options += array(
			"cache" => 0,
			"acceptable_error_codes" => array(),
			"file" => array(),
		);

		$this->errors = array();
		$this->data = null;

		$timer = new StopWatch();
		$timer->start();

		$url = "$this->base_url$this->lang/$action/";
		if($options["method"]!="POST" || $options["file"]){
			$url .= "?".$this->_joinParams($params);
		}
		$this->url = $url;

		if($options["cache"]>0 && ($ar = $this->_readCache($url,$options["cache"]))){
			$this->data = $ar["data"];
			$this->status_code = $ar["status_code"];
			$this->errors = $ar["errors"];
			return $this->data;
		}

		// Pro stahovani dat se pouziva UrlFetcher - soucast ATK14
		$headers = $this->additional_headers;
		//if($options["file"]){
		//	$headers["X-FileName"] = $options["name"];
		//}
		$u = new UrlFetcher($url,array(
			"additional_headers" => $headers
		));

		if($options["file"]){
			$content = Files::GetFileContent($options["file"]["name"]);
			$u->post($content,array(
				"content_type" => $options["file"]["mime_type"],
				"additional_headers" => array(
					sprintf('Content-Disposition: attachment; filename="%s"',rawurlencode($options["file"]["name"]))
				)
			));

		}elseif($options["method"]=="POST"){
			// Content-Type: application/x-www-form-urlencoded
			// Content-Length: 244

			$u->post($params);
		}else{
			$u->fetchContent();
		}
		$this->status_code = $u->getStatusCode();

		$timer->stop();

		ApiDataFetcher::$QueriesExecuted[] = array(
			"method" => $options["method"],
			"url" => $this->url,
			"params" => $params,
			"duration" => $timer->getResult()
		);

		$this->status_code = $u->getStatusCode();

		// TODO: vyresit zalogovani parametru POSTem

		$content = $u->getContent();
		if(!$content){
			throw new Exception("No content on $url (HTTP $this->status_code)");
		}

		$d = json_decode($u->getContent(),true);
		if(is_null($d)){
			error_log("ApiDataFetcher:
URL: $url
response code: ".$u->getStatusCode()."
invalid json:\n".$u->getContent()
			);
			throw new Exception("json_decode() failed on $url (HTTP $this->status_code)");
		}

		if(preg_match('/^2/',$this->status_code)){
			$this->data = $d;
			$valid_response = true;
		}else{
			$this->errors = $d;
			$valid_response = false;
		}

		if(!$valid_response && !in_array($this->status_code,$options["acceptable_error_codes"])){
			$this->logger->info(
				"ApiDataFetcher: $options[method] $url (HTTP $this->status_code, $timer)\n".
				($options["method"] == "POST" ? "params: ".print_r($params,true)."\n" : "").
				"error: ".join(" | ",$this->errors)."\n".
				"requested URL: ".$this->request->getUrl()
			);
			$this->logger->flush();
			throw new Exception("HTTP status code $this->status_code (".join(" | ",$this->errors)."), url: $url");
		}else{
			$this->logger->debug(
				"ApiDataFetcher: $options[method] $url (HTTP $this->status_code, $timer)".
				($options["method"] == "POST" ? "\nparams: ".print_r($params,true)."\n" : "")
			);
			if($options["cache"]>0){
				$this->_writeCache($url,$this->status_code,$this->data,$this->errors);
			}
		}

		return $this->data;
	}

	/**
	 * Vrati posledni URL posledniho pozadavku
	 */
	function getUrl(){
		return $this->url;
	}

	/**
	 * Vrati status code posledniho pozadavku
	 */
	function getStatusCode(){
		return $this->status_code;
	}

	function _joinParams($params){
		$out = array();
		foreach($params as $key => $value){
			if(is_object($value)){ $value = $value->getId(); }
			$out[] = urlencode($key)."=".urlencode($value);
		}
		return join("&",$out);
	}


	function _writeCache($url,$status_code,$data,$errors){
		$value = array(
			"url" => $url,
			"status_code" => $status_code,
			"data" => $data,
			"errors" => $errors,
			"created" => time(),
		);
		$this->cache_storage->write($url,$value);
		$this->logger->debug("writing cache");
	}

	function _readCache($url,$max_age){
		if($ar = $this->cache_storage->read($url)){
			if($ar["created"]>=time()-$max_age){
				$this->logger->debug("loaded from cache: $url");
				return $ar;
			}else{
				$this->logger->debug("exists in cache but outdated: $url");
				return;
			}
		}
		$this->logger->debug("doesn't exist in cache: $url");
	}

	function getQueriesExecuted(){
		return sizeof(ApiDataFetcher::$QueriesExecuted);
	}

	function getStatistics(){
		$stats = ApiDataFetcher::$QueriesExecuted;
		$total_time = 0.0;
		foreach($stats as $item){
			$total_time += $item["duration"];
		}
		$out = array();
		$out[] = "<div style=\"text-align: left;\">";
		$out[] = "<h3>total requests: ".sizeof($stats)."</h3>";
		$out[] = "<h3>total time: ".$this->_formatSeconds($total_time)."s</h3>";
		$out[] = "<pre>";
		foreach($stats as $el){
			$out[] = $this->_formatSeconds($el["duration"])."s";
			$out[] = "$el[method] $el[url]";
			foreach($el["params"] as &$_p){
				$_p = is_object($_p) ? "$_p" : $_p; // prevod zejmena $api_session na string
			}
			$out[] = h(print_r($el["params"],true));
			$out[] = "";
		}
		$out[] = "</pre>";
		$out[] = "</div>";
		return join("\n",$out);
	}

	function _formatSeconds($sec){
		return number_format($sec,3,".","");
	}
}
