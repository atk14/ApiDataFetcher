<?php
class TcFileUpload extends TcBase {

	function test(){
		// There is a testing API method for file upload on ATK14 website.
		// See http://www.atk14.net/api/en/file_uploads/create_new/

		$adf = new ApiDataFetcher("http://www.atk14.net/api/",array(
			"logger" => new Logger()
		));
		$data = $adf->postFile("file_uploads/create_new","sandokan.jpg");

		$this->assertEquals(201,$adf->getStatusCode());
		$this->assertEquals("sandokan.jpg",$data["filename"]);
		$this->assertEquals("image/jpeg",$data["mime_type"]);
		$this->assertEquals("d5d4599e3586064e0524d18e8ee8bce5",$data["md5sum"]);
	}
}
