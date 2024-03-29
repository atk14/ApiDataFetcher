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

		// The same headers must be deduplicated.
		// The last header occurence is preferred.
		$adf = new ApiDataFetcher("https://www.atk14.net/api/",array(
			"additional_headers" => array("Accept: application/json, text/csv", "X-Test: TEST_1", "x-header: Value_1","x-test: TEST_2"),
		));
		$data = $adf->get("http_requests/detail",array(),array("additional_headers" => array("X-Header: Value_2", "x-test: TEST_3")));
		$headers = $data["headers"];
		//
		$h = array_pop($headers);
		$this->assertEquals(array("name" => "X-Header", "value" => "Value_2"),$h);
		//
		$h = array_pop($headers);
		$this->assertEquals(array("name" => "x-test", "value" => "TEST_3"),$h);
		//
		$h = array_pop($headers);
		$this->assertEquals(array("name" => "Accept", "value" => "application/json, text/csv"),$h);
	}
}
