<?php
/**
 * Base part of the URL of the data API
 */
if(!defined("API_DATA_FETCHER_BASE_URL")) {
	define("API_DATA_FETCHER_BASE_URL","http://skelet.atk14.net/api/");
}

/**
 * ApiDataFetcher provides download and decoding data from an data API
 *
 * <code>
 *	$apd = new ApiDataFetcher([
 *		"lang" => "en",
 *		"additional_headers" => array("Cookie: ".$request->getHeader("Cookie"))
 *	]);
 *	$article_data = $apd->get("articles/detail",["id" => 123],["acceptable_error_codes" => [404]]);
 *	if(!$article_data){
 *		// article 123 was not found
 *		print_r($apd->getErrors());
 *	}
 * </code>
 */
class ApiDataFetcher{

	const VERSION = "1.9.1";

	var $logger;
	var $request;
	var $response;
	var $default_params;
	var $lang;
	var $base_url;

	var $errors;
	var $status_code;
	var $user_agent;
	var $additional_headers;
	var $automatically_add_leading_slash;
	var $automatically_add_trailing_slash;

	var $url;
	var $method;
	var $duration;

	protected static $QueriesExecuted = array();

	/**
	 *
	 * <code>
	 *	$adf = new ApiDataFetcher();
	 *	// or
	 *	$adf = new ApiDataFetcher("https://skelet.atk14.net/api/");
	 *	// or
	 *	$adf = new ApiDataFetcher(["additional_headers" => ["X-Forwarded-For: 127.0.0.1"]]);
	 * </code>
	 *
	 * There are special cases when the presence of the lang in URL is not desirable (e.g. non-ATK14 APIs).
	 *
	 * <code>
	 *	$adf = new ApiDataFetcher(["lang" => ""]);
	 * </code>
	 */
	function __construct($url_or_options = null,$options = array()){
		if(is_array($url_or_options)){
			$options = $url_or_options;
			$url_or_options = null;
		}

		$url = $url_or_options ? $url_or_options : API_DATA_FETCHER_BASE_URL;

		$options += array(
			"logger" => null,
			"request" => $GLOBALS["HTTP_REQUEST"],
			"response" => $GLOBALS["HTTP_RESPONSE"],
			"default_params" => array(
				"format" => "json"
			),
			"lang" => null, // default language; "en", "cs", ""
			"url" => $url,
			"cache_storage" => new CacheFileStorage(),
			"user_agent" => sprintf("ApiDataFetcher/%s UrlFetcher/%s",self::VERSION,UrlFetcher::VERSION),
			"additional_headers" => array(), // array("X-Forwarded-For: 127.0.0.1","X-Logged-User-Id: 123")
			"automatically_add_leading_slash" => true,
			"automatically_add_trailing_slash" => true,
		);

		if(is_null($options["logger"])){
			$options["logger"] = isset($GLOBALS["ATK14_GLOBAL"]) ? $GLOBALS["ATK14_GLOBAL"]->getLogger() : null;
		}

		if(is_null($options["lang"])){
			$options["lang"] = isset($GLOBALS["ATK14_GLOBAL"]) ? $GLOBALS["ATK14_GLOBAL"]->getLang() : "en";
		}

		$this->default_params = (array)$options["default_params"];

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
		$this->user_agent = $options["user_agent"];
		$this->additional_headers = $options["additional_headers"];
		$this->automatically_add_leading_slash = $options["automatically_add_leading_slash"];
		$this->automatically_add_trailing_slash = $options["automatically_add_trailing_slash"];
	}

	/**
	 * Returns the base API URL
	 *
	 *	echo $apf->getApiUrl(); // e.g. "https://site.net/api/"
	 *
	 * @return string
	 */
	function getApiUrl(){
		return $this->base_url;
	}

	/**
	 * @return string[]
	 */
	function getErrors(){ return $this->errors; }

	/**
	 * Performs HTTP GET call
	 *
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
	 * Performs HTTP POST call
	 *
	 *	$api_data_fetcher->post("articles/create_new",array(
	 *		"title" => "Very Nice Article"
	 *	));
	 */
	function post($action,$params = array(),$options = array()){
		$options["method"] = "POST";
		return $this->_doRequest($action,$params,$options);
	}

	/**
	 * Performs HTTP PUT call
	 */
	function put($action,$params = array(),$options = array()){
		$options["method"] = "PUT";
		return $this->_doRequest($action,$params,$options);
	}

	/**
	 * Performs HTTP DELETE call
	 */
	function delete($action,$params = array(),$options = array()){
		$options["method"] = "DELETE";
		return $this->_doRequest($action,$params,$options);
	}

	/**
	 * Sends a single file into the specific action
	 *
	 *	$api_data_fetcher->postFile("images/create_new","/path/to/file.jpg");
	 *
	 *	$api_data_fetcher->postFile(
	 *		"images/create_new",																																					// action name
	 *		array("path" => "/path/to/file.jpg", "name" => "earth.jpg", "mime_type" => "image/jpeg"),	// file definition
	 *		array("title" => "Image of Earth", "description" => "...")																		// other parameters
	 *	);
	 *
	 *	$api_data_fetcher->postFile("images/create_new","/path/to/file.jpg",array("title" => "Image of Earth", "description" => "..."));
	 */
	function postFile($action,$file,$params = array(),$options = array()){
		if(is_string($file)){
			$file = array("path" => $file);
		}

		$file += array(
			"path" => "", // "/path/to/file/photo.jpg"
			"postname" => null, // "image"
			"name" => null, // "photo.jpg"
			"mime_type" => null, // "image/jpeg"
		);

		if(!$file["name"]){
			$file["name"] = preg_replace('/^.*\//','',$file["path"]); // "/params/to/image.jpg" -> "image.jpg"
		}

		if(is_null($file["mime_type"])){
			$file["mime_type"] = Files::DetermineFileType($file["path"]);
		}

		$options["method"] = "POST";
		$options["file"] = $file;

		return $this->_doRequest($action,$params,$options);
	}

	/**
	 * Sends raw data to the specific action
	 *
	 *	$raw_data = json_encode($data);
	 *	$api_data_fetcher->postRawData("articles/edit",$raw_data,array("id" => 123),array("mime_type" => "application/json"));
	 */
	function postRawData($action,$content,$params = array(),$options = array()){
		$options += array(
			"mime_type" => "application/data"
		);

		$options["method"] = "POST";
		$options["raw_post_data"] = $content;

		return $this->_doRequest($action,$params,$options);
	}

	/**
	 * Sends JSON to the specific action
	 *
	 *	$api_data_fetcher->postJson('action','{"a":"b","c":"d"}');
	 *
	 *	// array will be automatically encoded into a JSON
	 *	$api_data_fetcher->postJson('action',["a" => "b", "c" => "d"]);
	 *
	 *	// URL params can be a part of the action
	 *	$api_data_fetcher->postJson('action/?url_param=value',["a" => "b", "c" => "d"]);
	 *	// or passed in option
	 *	$api_data_fetcher->postJson('action',["a" => "b", "c" => "d"],["params" => ["url_param" => "value"]]);
	 */
	function postJson($action,$json,$options = array()){
		if(!is_string($json)){
			$json = json_encode($json);
		}
		$options += array(
			"mime_type" => "application/json",
			"params" => array(),
		);
		$params = $options["params"];
		unset($options["params"]);
		return $this->postRawData($action,$json,$params,$options);
	}


	/**
	 *	$this->_doRequest("products/detail",array("catalog_id" => "1234/3345566"));
	 */
	function _doRequest($action,$params,$options){
		$params += array(
			"lang" => $this->lang,
		);
		$params += $this->default_params;

		$options += array(
			"cache" => 0,
			"return_cached_content_on_error" => false,
			"acceptable_error_codes" => array(),
			"file" => array(), // see postFile(),
			"raw_post_data" => null,
			"additional_headers" => array(), // array("X-Forwarded-For: 127.0.0.1","X-Logged-User-Id: 123")
			"return_raw_content" => false, // TODO: at the moment it doesn't work with "cache" parameter
		);

		$this->errors = array();
		$this->data = null;

		$timer = new StopWatch();
		$timer->start();

		$lang = $params["lang"];
		unset($params["lang"]);

		$url = $this->base_url;
		if($lang){
			if($this->automatically_add_leading_slash && !preg_match('/\/$/',$url)){
				$url .= "/";
			}
			$url .= "$lang/";
		}
		if($this->automatically_add_leading_slash && !preg_match('/\/$/',$url)){
			$url .= "/";
		}
		$url .= "$action";
		if($this->automatically_add_trailing_slash && !preg_match('/\/$/',$url) && !preg_match('/\?/',$url)){
			$url .= "/";
		}

		$action_url = $url;

		if($options["method"]!="POST" || $options["file"] || !is_null($options["raw_post_data"])){
			$url = $this->_addParamsToUrl($url,$params);
		}
		$this->url = $url;
		$this->method = $options["method"];
		$this->duration = null;

		$cached_ar = null;
		if($options["cache"]>0 && ($ar = $this->_readCache($url,$options["cache"],$cached_ar))){
			return $this->__setCacheAndReturnData($ar);
		}

		// Pro stahovani dat se pouziva UrlFetcher - soucast ATK14
		$headers = $this->additional_headers;
		//if($options["file"]){
		//	$headers["X-FileName"] = $options["name"];
		//}
		$u = new UrlFetcher($url,array(
			"user_agent" => $this->user_agent,
			"additional_headers" => $headers
		));

		if(!is_null($options["raw_post_data"])){
			$u->post($options["raw_post_data"],array(
				"content_type" => $options["mime_type"],
				"additional_headers" => $options["additional_headers"],
			));

		}elseif($options["file"]){
			$content = new StringBuffer();
			$content->addFile($options["file"]["path"]);
			$u->post($content,array(
				"content_type" => $options["file"]["mime_type"],
				"additional_headers" => array(
					sprintf('Content-Type: %s',$options["file"]["mime_type"]),
					sprintf('Content-Disposition: attachment; filename="%s"',rawurlencode($options["file"]["name"]))
				)
			));

		}elseif($options["method"]=="POST"){
			// Content-Type: application/x-www-form-urlencoded
			// Content-Length: 244

			$u->post($params);
		}else{
			$u->fetchContent(array(
				"request_method" => $options["method"],
			));
		}
		$this->status_code = $u->getStatusCode();

		$timer->stop();

		$this->duration = $timer->getResult();

		$this->status_code = $u->getStatusCode();

		// TODO: vyresit zalogovani parametru POSTem

		$content = $u->getContent();

		if($options["return_raw_content"]){

			$d = null;

		}else{

			if(!strlen($content) && ($this->status_code!=204)){
				if($options["return_cached_content_on_error"] && $cached_ar){
					return $this->__useOutdatedCache($cached_ar);
				}
				throw new Exception("No content on $url (HTTP $this->status_code)");
			}

			if(strlen($content)>0){

				$d = json_decode($content,true);
				if(is_null($d)){
					trigger_error("ApiDataFetcher:
URL: $url
response code: ".$u->getStatusCode()."
invalid json:\n".$u->getContent()
					);
					if($options["return_cached_content_on_error"] && $cached_ar){
						return $this->__useOutdatedCache($cached_ar);
					}
					throw new Exception("json_decode() failed on $url (HTTP status code: $this->status_code; content: ".(strlen($content)>2000 ? substr($content,0,2000)."..." : $content).")");
				}

			}else{

				// The $content is empty (HTTP status code is 204).
				// Empty answer means empty JSON.
				$d = array();

			}

		}

		ApiDataFetcher::$QueriesExecuted[] = array(
			"action" => $action,
			"action_url" => $action_url,
			"method" => $options["method"],
			"url" => $url,
			"status_code" => $this->status_code,
			"status_message" => $u->getStatusMessage(),
			"params" => $params,
			"duration" => $timer->getResult(),
			"data" => $d,
		);

		if(preg_match('/^2/',$this->status_code)){
			$this->data = $d;
			$valid_response = true;
		}else{
			$this->errors = $d;
			$valid_response = false;
		}

		if(!$valid_response && !in_array($this->status_code,$options["acceptable_error_codes"])){
			$this->_loggerLog(
				"ApiDataFetcher: $options[method] $url (HTTP $this->status_code, $timer)\n".
				($options["method"] == "POST" ? "params: ".print_r($params,true)."\n" : "").
				"error: ".join(" | ",$this->errors)."\n".
				"requested URL: ".$this->request->getUrl()
			);
			$this->_loggerFlush();
			if($options["return_cached_content_on_error"] && $cached_ar){
				return $this->__useOutdatedCache($cached_ar);
			}
			throw new Exception("HTTP status code $this->status_code (".join(" | ",$this->errors)."), url: $url");
		}else{
			$this->_loggerDebug(
				"ApiDataFetcher: $options[method] $url (HTTP $this->status_code, $timer)".
				($options["method"] == "POST" ? "\nparams: ".print_r($params,true)."\n" : "")
			);
			if($options["cache"]>0){
				$this->_writeCache($url,$this->status_code,$this->data,$this->errors);
			}
		}

		if($options["return_raw_content"]){
			return $content;
		}
		return $this->data;
	}

	function __setCacheAndReturnData($ar){
		$this->data = $ar["data"];
		$this->status_code = $ar["status_code"];
		$this->errors = $ar["errors"];
		return $this->data;
	}

	function __useOutdatedCache($ar){
		$this->_loggerDebug("outdated cache returned due to an error occurrence: $this->url");
		return $this->__setCacheAndReturnData($ar);
	}

	/**
	 * Returns last requested URL
	 */
	function getUrl(){
		return $this->url;
	}

	/**
	 * Returns last used HTTP method
	 *
	 *	echo $adf->getMethod(); // e.g. "POST", "GET"
	 */
	function getMethod(){
		return $this->method;
	}

	/**
	 * Returns duration in seconds of the last request
	 *
	 *	echo $adf->getDuration(); // e.g. 1.234
	 */
	function getDuration(){
		return $this->duration;
	}

	/**
	 * Returns HTTP status code of the last request
	 */
	function getStatusCode(){
		return $this->status_code;
	}

	protected function _joinParams($params){
		$out = array();
		foreach($params as $key => $value){
			if(is_object($value)){ $value = $value->getId(); }
			$out[] = urlencode($key)."=".urlencode($value);
		}
		return join("&",$out);
	}

	protected function _addParamsToUrl($url,$params){
		$connector = preg_match('/\?/',$url) ? "&" : "?";
		return $url.$connector.$this->_joinParams($params);
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
		$this->_loggerDebug("writing cache");
	}

	function _readCache($url,$max_age,&$ar){
		if($ar = $this->cache_storage->read($url)){
			if($ar["created"]>=time()-$max_age){
				$this->_loggerDebug("loaded from cache: $url");
				return $ar;
			}else{
				$this->_loggerDebug("exists in cache but outdated: $url");
				return;
			}
		}
		$this->_loggerDebug("doesn't exist in cache: $url");
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
		$out[] = "Total requests: ".sizeof($stats)."<br>";
		$out[] = "Total time: ".$this->_formatSeconds($total_time);
		$out[] = "<br><br>";
		$out[] = "<pre>";
		foreach($stats as $el){
			$out[] = sprintf('action: <a href="%s">%s</a>',h($el["action_url"]),h($el["action"]));
			$out[] = "duration: ".$this->_formatSeconds($el["duration"]);
			if($el["method"]=="GET"){
				$out[] = "$el[method] <a href='$el[url]'>$el[url]</a>";
			}else{
				$out[] = "$el[method] $el[url]";
			}
			$out[] = "response: HTTP $el[status_code] $el[status_message]";
			foreach($el["params"] as &$_p){
				$_p = is_object($_p) ? "$_p" : $_p; // prevod zejmena $api_session na string
			}
			$out[] = $this->_dumpVar("params",$el["params"]);
			$out[] = $this->_dumpVar("result",$el["data"]);
			$out[] = "";
		}
		$out[] = "</pre>";
		$out[] = "</div>";
		return join("\n",$out);
	}

	function _formatSeconds($sec){
		return number_format($sec,3,".","")."s";
	}

	protected function _varExport($var){
		$out = var_export($var,true);
		$out = preg_replace('/\n\s*array \(/s','array (',$out);
		$out = preg_replace('/array \(\n\s*\)/s','array()',$out);
		return $out;
	}

	protected function _dumpVar($label,$var,$options = array()){
		$options += array(
			"make_collapsible" => "auto", // "auto", true, false
		);

		$content = h($this->_varExport($var));
		$content = preg_replace('/^array \(/','',$content);

		if($options["make_collapsible"]=="auto"){
			$options["make_collapsible"] = strlen($content)>1000;
		}

		$out = array();

		if($options["make_collapsible"]){
			$id = "adf_stats_".uniqid();
			$id_to_be_hidden = $id."_h";

			$out[] = '<span id="'.$id_to_be_hidden.'"><a href="#" onclick="JavaScript: document.getElementById(\''.$id.'\').style.display=\'inline\'; document.getElementById(\''.$id_to_be_hidden.'\').style.display=\'none\'; return false;" title="expand">'.$label.': (+)</a></span><span style="display:none;" id="'.$id.'"><a href="#" onclick="JavaScript: document.getElementById(\''.$id_to_be_hidden.'\').style.display=\'inline\'; document.getElementById(\''.$id.'\').style.display=\'none\'; return false;" title="collapse">'.$label.': (</a>';
			$out[] = $content;
			$out[] = "</span>";
		}else{
			$out[] = $label;
			$out[] = ": (";
			$out[] = $content;
		}
		return join("",$out);
	}

	/**
	 * Logs a message using the logger
	 *
	 *	$this->_loggerLog("Some information")
	 *
	 */
	function _loggerLog($message){
		if($this->logger){
			$this->logger->info($message);
		}
	}

	/**
	 * Logs a debug message using the logger
	 * 
	 *	$this->_loggerLog("Some debug information")
	 *
	 */
	function _loggerDebug($message){
		if($this->logger){
			$this->logger->debug($message);
		}
	}

	/**
	 * Flushes out the logger's buffer
	 *
	 *	$this->_loggerFlush();
	 */
	function _loggerFlush(){
		if($this->logger){
			$this->logger->flush();
		}
	}
}
