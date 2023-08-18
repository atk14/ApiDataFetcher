<?php
class TcCaching extends TcBase {

	function test(){
		$apf = new ApiDataFetcher("http://worldtimeapi.org/api/",array("lang" => "")); // non-ATK14 API - so there is need to set empty lang
		$data1 = $apf->get("timezone/Europe/Prague",array(),array("cache" => 60));
		$this->assertEquals(200,$apf->getStatusCode());
		$this->assertEquals("OK",$apf->getStatusMessage());
		$raw_response = $apf->getRawResponse();

		sleep(2);

		$apf = new ApiDataFetcher("http://worldtimeapi.org/api/",array("lang" => "")); // non-ATK14 API - so there is need to set empty lang
		$data2 = $apf->get("timezone/Europe/Prague",array(),array("cache" => 60));
		$this->assertEquals(200,$apf->getStatusCode());
		$this->assertEquals("OK",$apf->getStatusMessage());
		$this->assertEquals($raw_response,$apf->getRawResponse());

		$data3 = $apf->get("timezone/Europe/Prague",array());
		$this->assertEquals(200,$apf->getStatusCode());
		$this->assertEquals("OK",$apf->getStatusMessage());
		$this->assertNotEquals($raw_response,$apf->getRawResponse());
		
		$this->assertTrue($data1["unixtime"]>0);
		$this->assertEquals($data1["unixtime"],$data2["unixtime"]);

		$this->assertTrue($data3["unixtime"]>0);
		$this->assertNotEquals($data1["unixtime"],$data3["unixtime"]);
	}
}
