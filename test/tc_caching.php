<?php
class TcCaching extends TcBase {

	function test(){
		$apf = new ApiDataFetcher("http://worldclockapi.com/api/json",array("lang" => "")); // non-ATK14 API - so there is need to set empty lang
		$data1 = $apf->get("/est/now",array(),array("cache" => 60));
		sleep(1);
		$data2 = $apf->get("/est/now",array(),array("cache" => 60));
		
		$this->assertTrue($data1["currentFileTime"]>0);
		$this->assertEquals($data1["currentFileTime"],$data2["currentFileTime"]);
	}
}
