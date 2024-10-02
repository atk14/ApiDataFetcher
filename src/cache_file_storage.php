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

	/**
	 *
	 *	if($cache->readInto("snippet",$content)){
	 *		echo $content;
	 *	}
	 */
	function readInto(string $key, &$content, &$content_timestamp = null){
		$content = null;
		$content_timestamp = null;

		$orig_key = $key;
		$key = sha1($key);
		$filename = "$this->dir/$key";
		if(file_exists($filename)){
			$serialized = Files::GetFileContent($filename);
			$ar = unserialize($serialized);
			if($ar && isset($ar["timestamp"]) && $ar["key"]==$orig_key){
				if(isset($ar["expires"]) && ($ar["timestamp"] + $ar["expires"])<time()){
					return false;
				}
				$content = $ar["data"];
				$content_timestamp = $ar["timestamp"];
				return true;
			}
		}
		return false;
	}

	function read(string $key){
		$content = null;
		$this->readInto($key,$content);
		return $content;
	}

	/**
	 *
	 *	$cache->write("snippet","<b>content</b>");
	 *	$cache->write("snippet","<b>content</b>",60); // cache is automatically invalidated after 60 seconds
	 */
	function write(string $key, $data, int $expires = null){
		$content = serialize(array(
			"key" => $key,
			"data" => $data,
			"timestamp" => time(),
			"expires" => $expires,
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
