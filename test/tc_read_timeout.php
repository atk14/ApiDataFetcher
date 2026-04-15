<?php
class TcReadTimeout extends TcBase {

	function test(){
		$adf = new ApiDataFetcher("https://www.atk14.net/api/",[
			"read_timeout" => 2,
		]);

		$exception_thrown = false;
		try {
			$data = $adf->get("delayed_responses/detail",["delay" => 3]);
		}catch(Exception $e){
			$exception_thrown = true;
		}
		$this->assertEquals(true,$exception_thrown);
		$this->assertStringContains("read timeout",$e->getMessage());

		$exception_thrown = false;
		try {
			$data = $adf->get("delayed_responses/detail",["delay" => 1]);
		}catch(Exception $e){
			$exception_thrown = true;
		}
		$this->assertEquals(false,$exception_thrown);
		$this->assertEquals([],$data);
	}
}
