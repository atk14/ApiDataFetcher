<?php
class TcEmptyOutput extends TcBase {

	function test(){
		$apf = new ApiDataFetcher("http://www.atk14.net/api/");

		$exception_thrown = false;
		$exception_message = "";
		try {
			$data = $apf->get("echoes/detail",["response" => "", "status_code" => 200]);
		} catch(Exception $e) {
			$exception_thrown = true;
			$exception_message = $e->getMessage();
		}
		$this->assertTrue($exception_thrown);
		$this->assertEquals("No content on http://www.atk14.net/api/en/echoes/detail/?response=&status_code=200&format=json (HTTP 200 OK)",$exception_message);
		$this->assertEquals(200,$apf->getStatusCode());

		// Empty output is allowed when status code is 204 (No Content)
		$data = $apf->get("echoes/detail",["response" => "", "status_code" => 204]);
		$this->assertEquals(204,$apf->getStatusCode());
		$this->assertEquals(array(),$data);
	}
}
