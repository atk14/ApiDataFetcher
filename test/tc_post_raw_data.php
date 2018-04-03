<?php
class TcPostRawData extends TcBase {

	function test(){

		// At the moment postRawData() is tested on the same API function as postFile()
		// It's kind of weird but perfectly ok!
		
		$adf = new ApiDataFetcher("http://www.atk14.net/api/",array(
			"logger" => new Logger()
		));

		$raw_post_data = "Lorem ipsum dolor sit amet, consectetur adipiscing elit. Maecenas rhoncus nulla ut felis consequat, sed fringilla eros vulputate.";
		$data = $adf->postRawData("file_uploads/create_new",$raw_post_data,array(),array(
			"content_type" => "text/plain",
			"additional_headers" => array("X-File-Name: test.txt"),
		));

		$this->assertEquals(201,$adf->getStatusCode());
		$this->assertEquals("test.txt",$data["filename"]);
		$this->assertEquals("text/plain",$data["mime_type"]);
		$this->assertEquals(md5($raw_post_data),$data["md5sum"]);
	}
}
