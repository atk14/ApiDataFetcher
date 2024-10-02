<?php
class TcCacheFileStorage extends TcBase {

	function test(){
		$dir = __DIR__ . "/tmp/testing_".time()."_".rand(0,1000);
		$cfs = new CacheFileStorage($dir);

		$this->assertEquals(null,$cfs->read("test"));
		$this->assertEquals(false,$cfs->readInto("test",$content,$content_timestamp));
		$this->assertEquals(null,$content);
		$this->assertEquals(null,$content_timestamp);

		$cfs->write("test","some_content");
		$this->assertEquals("some_content",$cfs->read("test"));

		$this->assertEquals(true,$cfs->readInto("test",$content,$content_timestamp));
		$this->assertEquals("some_content",$content);
		$this->assertEquals(time(),$content_timestamp);

		$cfs->write("test",["a" => 1, "b" => 11]);
		$this->assertEquals(["a" => 1, "b" => 11],$cfs->read("test"));
	}

	function test_expires(){
		$dir = __DIR__ . "/tmp/testing_".time()."_".rand(0,1000);
		$cfs = new CacheFileStorage($dir);

		$cfs->write("key","value",60);
		$cfs->write("key2","value2",2);
		$cfs->write("key3","value3");

		$this->assertEquals("value",$cfs->read("key"));
		$this->assertEquals("value2",$cfs->read("key2"));
		$this->assertEquals("value3",$cfs->read("key3"));

		sleep(3);

		$this->assertEquals("value",$cfs->read("key"));
		$this->assertEquals(null,$cfs->read("key2"));
		$this->assertEquals("value3",$cfs->read("key3"));

		$this->assertTrue($cfs->readInto("key",$content));
		$this->assertEquals("value",$content);

		$this->assertFalse($cfs->readInto("key2",$content));
		$this->assertEquals(null,$content);

		$this->assertTrue($cfs->readInto("key3",$content));
		$this->assertEquals("value3",$content);
	}
}
