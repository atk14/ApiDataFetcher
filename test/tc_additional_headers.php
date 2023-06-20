<?php
class TcAdditionalHeaders extends TcBase {

	function test(){
		$adf = new ApiDataFetcher("https://www.atk14.net/api/",array(
			"additional_headers" => array("Accept: application/json, text/csv"),
		));
		$data = $adf->get("http_requests/detail",array(),array("additional_headers" => array("X-Header: X-Value")));
		$headers = $data["headers"];
		//
		$h = array_pop($headers);
		$this->assertEquals(array("name" => "X-Header", "value" => "X-Value"),$h);
		//
		$h = array_pop($headers);
		$this->assertEquals(array("name" => "Accept", "value" => "application/json, text/csv"),$h);

		// Passing a string
		$adf = new ApiDataFetcher("https://www.atk14.net/api/",array(
			"additional_headers" => "Accept: application/xhtml+xml", // !! not an array, but a string !!
		));
		$data = $adf->get("http_requests/detail",array(),array("additional_headers" => "X-Header: X-Value-2")); // !! not an array, but a string !!
		$headers = $data["headers"];
		//
		$h = array_pop($headers);
		$this->assertEquals(array("name" => "X-Header", "value" => "X-Value-2"),$h);
		//
		$h = array_pop($headers);
		$this->assertEquals(array("name" => "Accept", "value" => "application/xhtml+xml"),$h);

		// The same headers must be deduplicated
		$adf = new ApiDataFetcher("https://www.atk14.net/api/",array(
			"additional_headers" => array("Accept: application/json, text/csv", "X-Test: Yes", "x-header: xxx"),
		));
		$data = $adf->get("http_requests/detail",array(),array("additional_headers" => array("X-Header: X-Value", "x-test: YES!!!")));
		$headers = $data["headers"];
		//
		$h = array_pop($headers);
		$this->assertEquals(array("name" => "X-Header", "value" => "X-Value"),$h);
		//
		$h = array_pop($headers);
		$this->assertEquals(array("name" => "x-test", "value" => "YES!!!"),$h);
		//
		$h = array_pop($headers);
		$this->assertEquals(array("name" => "Accept", "value" => "application/json, text/csv"),$h);
	}
}
