<?php
class TcCacheFileStorage extends TcBase {

	function test(){
		$dir = __DIR__ . "/tmp/testing_".time()."_".rand(0,1000);
		$cfs = new CacheFileStorage($dir);

		$this->assertEquals(null,$cfs->read("test"));

		$cfs->write("test","some_content");
		$this->assertEquals("some_content",$cfs->read("test"));

		$cfs->write("test",["a" => 1, "b" => 11]);
		$this->assertEquals(["a" => 1, "b" => 11],$cfs->read("test"));
	}
}
