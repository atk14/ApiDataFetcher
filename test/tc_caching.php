<?php
class TcCaching extends TcBase {

	function test(){
		$apf = new ApiDataFetcher("http://worldclockapi.com/api/json",array("lang" => "")); // non-ATK14 API - so there is need to set empty lang
		$data1 = $apf->get("/est/now",array(),array("cache" => 60));
		$this->assertEquals(200,$apf->getStatusCode());
		$this->assertEquals("OK",$apf->getStatusMessage());

		sleep(2);

		$apf = new ApiDataFetcher("http://worldclockapi.com/api/json",array("lang" => "")); // non-ATK14 API - so there is need to set empty lang
		$data2 = $apf->get("/est/now",array(),array("cache" => 60));
		$this->assertEquals(200,$apf->getStatusCode());
		$this->assertEquals("OK",$apf->getStatusMessage());
		
		$this->assertTrue($data1["currentFileTime"]>0);
		$this->assertEquals($data1["currentFileTime"],$data2["currentFileTime"]);
	}
}
