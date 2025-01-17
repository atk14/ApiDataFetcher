<?php
class TcProxy extends TcBase {

	function test(){
		$adf = new ApiDataFetcher("https://www.atk14.net/api/",array(
			"logger" => new Logger(),
			"proxy" => "tcp://127.0.0.1:8118",
		));
		$data = $adf->get("http_requests/detail");
		$this->assertEquals(200,$adf->getStatusCode());

		$adf = new ApiDataFetcher("https://www.atk14.net/api/",array(
			"logger" => new Logger(),
			"proxy" => "tcp://127.0.0.1:8119", // no proxy server is running on this port
		));
		$exception_message = "";
		try {
			$data = $adf->get("http_requests/detail");
		} catch(Exception $e) {
			$exception_message = $e->getMessage();
		}
		$this->assertStringContains("could not connect to proxy server tcp://127.0.0.1:8119",$exception_message);
	}
}
