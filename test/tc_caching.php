<?php
class TcCaching extends TcBase {

	function test(){
		$cache_storage = new CacheFileStorage(Files::GetTempDir()."/".uniqid()."/");

		$apf = new ApiDataFetcher(
			"https://www.timeapi.io/api/",
			array(
				"cache_storage" => $cache_storage,
				"lang" => "",  // non-ATK14 API - so there is need to set empty lang
				"automatically_add_trailing_slash" => false,
				"default_params" => array(),
				"socket_timeout" => 10.0,
			)
		);
		$data1 = $apf->get("timezone/zone",array("timezone" => "Europe/Prague"),array("cache" => 60));
		$this->assertEquals(200,$apf->getStatusCode());
		$this->assertEquals("OK",$apf->getStatusMessage());
		$this->assertEquals(false,$apf->isResponseCached());
		$raw_response = $apf->getRawResponse();

		sleep(2);

		$apf = new ApiDataFetcher(
			"https://www.timeapi.io/api/",
			array(
				"cache_storage" => $cache_storage,
				"lang" => "",
				"automatically_add_trailing_slash" => false,
				"default_params" => array(),
				"socket_timeout" => 10.0
			)
		);
		$data2 = $apf->get("timezone/zone",array("timezone" => "Europe/Prague"),array("cache" => 60));
		$this->assertEquals(200,$apf->getStatusCode());
		$this->assertEquals("OK",$apf->getStatusMessage());
		$this->assertEquals(true,$apf->isResponseCached());
		$this->assertEquals($raw_response,$apf->getRawResponse());

		$data3 = $apf->get("timezone/zone",array("timezone" => "Europe/Prague"));
		$this->assertEquals(200,$apf->getStatusCode());
		$this->assertEquals("OK",$apf->getStatusMessage());
		$this->assertEquals(false,$apf->isResponseCached());
		$this->assertNotEquals($raw_response,$apf->getRawResponse());
		
		$this->assertTrue(strtotime($data1["currentLocalTime"])>0);
		$this->assertEquals(strtotime($data1["currentLocalTime"]),strtotime($data2["currentLocalTime"]));

		$this->assertTrue(strtotime($data3["currentLocalTime"])>0);
		$this->assertNotEquals(strtotime($data1["currentLocalTime"]),strtotime($data3["currentLocalTime"]));
	}
}
