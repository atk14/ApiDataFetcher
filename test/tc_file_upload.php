<?php
class TcFileUpload extends TcBase {

	function test(){
		$adf = new ApiDataFetcher("http://pupiq_srv.localhost/api/",array(
			"logger" => new Logger()
		));
		$data = $adf->postFile("attachments/create_new","sandokan.jpg",array(
			"auth_token" => $this->_getAuthToken()
		));

		$this->assertEquals(201,$adf->getStatusCode());
		$this->assertEquals("sandokan.jpg",$data["filename"]);

		$adf = new ApiDataFetcher("http://pupiq_srv.localhost/api/");
		$data = $adf->postFile("attachments/create_new",array(
			"path" => __DIR__ . "/sandokan.jpg",
			"name" => "česýlko.jpg",
		),
		array(
			"auth_token" => $this->_getAuthToken(),
		));

		$this->assertEquals(201,$adf->getStatusCode());
		$this->assertEquals("cesylko.jpg",$data["filename"]);
	}

	function _getAuthToken(){
		$API_KEY = "1.k8qVPFrubmdosxhU7jRSc3Qp2NHW5gJTMviGDlzK";
		$USER_ID = 1;
		$current_time = time();
		$t = $current_time - ($current_time % (60 * 10)); // kazdych 10 minut jiny token
		return $USER_ID.".".hash("sha256",$API_KEY.$t);
	}
}
