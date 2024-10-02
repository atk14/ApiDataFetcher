<?php
class CacheFileStorage{

	protected $dir;

	function __construct(string $dir = null){
		if(is_null($dir)){
			$TEMP = Files::GetTempDir();
			$dir = "$TEMP/cache_file_storage";
		}
		$this->dir = $dir;
	}

	function read(string $key){
		$orig_key = $key;
		$key = sha1($key);
		$filename = "$this->dir/$key";
		if(file_exists($filename)){
			$content = Files::GetFileContent($filename);
			$ar = unserialize($content);
			if($ar && ($ar["key"]==$orig_key)){
				return $ar["data"];
			}
		}
	}

	function write(string $key, mixed $data){
		$content = serialize(array(
			"key" => $key,
			"data" => $data,
		));
		$key = sha1($key);
		Files::Mkdir($this->dir,$err,$err_msg);
		if($err){
			throw new Exception(get_class($this).": directory $this->dir cannot be created: $err_msg");
		}
		$tmp_file = Files::WriteToTemp($content);
		Files::MoveFile($tmp_file,"$this->dir/$key");
	}
}
