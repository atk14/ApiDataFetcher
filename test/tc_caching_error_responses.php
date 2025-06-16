<?php
class TcCachingErrorResponses extends TcBase {

	function test(){
		$cache_storage = new CacheFileStorage(Files::GetTempDir()."/".uniqid()."/");

		$apf = new ApiDataFetcher("https://www.atk14.net/api/",["cache_storage" => $cache_storage]);

		$data = $apf->get("echoes/detail",[
			"response" => json_encode(["msg" => "ok"]),
			"status_code" => 400,
			"content_type" => "text/json",
		],[
			"cache" => 60,
			"acceptable_error_codes" => [400],
		]);

		$this->assertEquals(null,$data);
		$this->assertEquals('{"msg":"ok"}',$apf->getRawResponse());
		$this->assertEquals(400,$apf->getStatusCode());
		$this->assertEquals(false,$apf->isResponseCached());

		$data = $apf->get("echoes/detail",[
			"response" => json_encode(["msg" => "ok"]),
			"status_code" => 400,
			"content_type" => "text/json",
		],[
			"cache" => 60,
			"acceptable_error_codes" => [400],
		]);

		$this->assertEquals(null,$data);
		$this->assertEquals('{"msg":"ok"}',$apf->getRawResponse());
		$this->assertEquals(400,$apf->getStatusCode());
		$this->assertEquals(true,$apf->isResponseCached());
	}
}
