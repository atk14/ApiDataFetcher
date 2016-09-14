<?php
class TcFileUpload extends TcBase {
	function test(){

		$adf = new ApiDataFetcher("http://pupiq_srv.localhost/api/");
		$data = $adf->postFile("attachments/create_new",__DIR__ . "/sandokan.jpg",array(
			"auth_token" => $this->_getAuthToken(),
		));

		var_dump($data); exit;
	}

	function _getAuthToken(){
		$API_KEY = "1.k8qVPFrubmdosxhU7jRSc3Qp2NHW5gJTMviGDlzK";
		$USER_ID = 1;
		$current_time = time();
		$t = $current_time - ($current_time % (60 * 10)); // kazdych 10 minut jiny token
		return $USER_ID.".".hash("sha256",$API_KEY.$t);
	}
}
