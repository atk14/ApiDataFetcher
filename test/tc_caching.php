<?php
class TcCaching extends TcBase {

	function test(){
		$apf = new ApiDataFetcher("https://www.timeapi.io/api/",array("lang" => "", "automatically_add_trailing_slash" => false, "default_params" => array())); // non-ATK14 API - so there is need to set empty lang
		$data1 = $apf->get("timezone/zone",array("timezone" => "Europe/Prague"),array("cache" => 60));
		$this->assertEquals(200,$apf->getStatusCode());
		$this->assertEquals("OK",$apf->getStatusMessage());
		$raw_response = $apf->getRawResponse();

		sleep(2);

		$apf = new ApiDataFetcher("https://www.timeapi.io/api/",array("lang" => "", "automatically_add_trailing_slash" => false, "default_params" => array())); // non-ATK14 API - so there is need to set empty lang
		$data2 = $apf->get("timezone/zone",array("timezone" => "Europe/Prague"),array("cache" => 60));
		$this->assertEquals(200,$apf->getStatusCode());
		$this->assertEquals("OK",$apf->getStatusMessage());
		$this->assertEquals($raw_response,$apf->getRawResponse());

		$data3 = $apf->get("timezone/zone",array("timezone" => "Europe/Prague"));
		$this->assertEquals(200,$apf->getStatusCode());
		$this->assertEquals("OK",$apf->getStatusMessage());
		$this->assertNotEquals($raw_response,$apf->getRawResponse());
		
		$this->assertTrue(strtotime($data1["currentLocalTime"])>0);
		$this->assertEquals(strtotime($data1["currentLocalTime"]),strtotime($data2["currentLocalTime"]));

		$this->assertTrue(strtotime($data3["currentLocalTime"])>0);
		$this->assertNotEquals(strtotime($data1["currentLocalTime"]),strtotime($data3["currentLocalTime"]));
	}
}
