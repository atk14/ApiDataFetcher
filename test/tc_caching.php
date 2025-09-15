<?php
class TcCaching extends TcBase {

	function test(){
		$cache_storage = new CacheFileStorage(Files::GetTempDir()."/".uniqid()."/");

		$api_url = "https://www.atk14.net/api/";
		$endpoint = "timezones/detail";
		$endpoint_params = array("timezone" => "Europe/Prague");

		$apf = new ApiDataFetcher(
			$api_url,
			array(
				"cache_storage" => $cache_storage,
				"socket_timeout" => 10.0,
			)
		);
		$data1 = $apf->get($endpoint,$endpoint_params,array("cache" => 60));
		$this->assertEquals(200,$apf->getStatusCode());
		$this->assertEquals("OK",$apf->getStatusMessage());
		$this->assertEquals(false,$apf->isResponseCached());
		$raw_response = $apf->getRawResponse();

		sleep(2);

		$apf = new ApiDataFetcher(
			$api_url,
			array(
				"cache_storage" => $cache_storage,
				"socket_timeout" => 10.0
			)
		);
		$data2 = $apf->get($endpoint,$endpoint_params,array("cache" => 60));
		$this->assertEquals(200,$apf->getStatusCode());
		$this->assertEquals("OK",$apf->getStatusMessage());
		$this->assertEquals(true,$apf->isResponseCached());
		$this->assertEquals($raw_response,$apf->getRawResponse());

		$data3 = $apf->get($endpoint,$endpoint_params);
		$this->assertEquals(200,$apf->getStatusCode());
		$this->assertEquals("OK",$apf->getStatusMessage());
		$this->assertEquals(false,$apf->isResponseCached());
		$this->assertNotEquals($raw_response,$apf->getRawResponse());
		
		$this->assertTrue($data1["datetime"]>0);
		$this->assertEquals($data1["datetime"],$data2["datetime"]);

		$this->assertTrue(strtotime($data3["datetime"])>0);
		$this->assertNotEquals(strtotime($data1["datetime"]),strtotime($data3["datetime"]));
	}
}
