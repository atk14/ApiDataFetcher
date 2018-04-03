<?php
class TcPostJson extends TcBase {

	function test(){
		$adf = new ApiDataFetcher("http://www.atk14.net/api/",array(
			"logger" => new Logger()
		));

		$json_unencoded = array("a" => "b", "c" => "d");
		$json_encoded = json_encode($json_unencoded);

		$data = $adf->postJson("http_requests/detail",$json_encoded);
		$this->assertEquals(200,$adf->getStatusCode());
		$this->assertEquals("POST",$data["method"]);
		$headers = $this->_headersToAssociativeArray($data["headers"]);
		$this->assertEquals("application/json",$headers["Content-Type"]);
		$this->assertEquals(md5($json_encoded),$data["raw_post_data_md5"]);

		// unencoded JSON
		$data = $adf->postJson("http_requests/detail",$json_unencoded);
		$this->assertEquals(200,$adf->getStatusCode());
		$this->assertEquals(md5($json_encoded),$data["raw_post_data_md5"]);

		// params in URL
		$data = $adf->postJson("http_requests/detail/?p1=v1&p2=v2",$json_unencoded);
		$this->assertEquals("http://www.atk14.net/api/en/http_requests/detail/?p1=v1&p2=v2&format=json",$data["url"]);

		$data = $adf->postJson("http_requests/detail",$json_unencoded,array("params" => array("p3" => "v3", "p4" => "v4")));
		$this->assertEquals("http://www.atk14.net/api/en/http_requests/detail/?p3=v3&p4=v4&format=json",$data["url"]);
	}

	function _headersToAssociativeArray($headers){
		$out = array();
		foreach($headers as $header){
			$out[$header["name"]] = $header["value"];
		}
		return $out;
	}
}
