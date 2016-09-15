<?php
class CacheFileStorage{
	function read($key){
		$orig_key = $key;
		$key = sha1($key);
		$filename = TEMP . "/cache_file_storage/" . $key;
		if(file_exists($filename)){
			$content = Files::GetFileContent($filename);
			$ar = unserialize($content);
			if($ar && ($ar["key"]==$orig_key)){
				return $ar["data"];
			}
		}
	}

	function write($key,$data){
		$content = serialize(array(
			"key" => $key,
			"data" => $data,
		));
		$key = sha1($key);
		//echo "gonna to save:\n";
		//var_dump($data);
		Files::Mkdir($dir = TEMP . "/cache_file_storage/");
		Files::WriteToFile("$dir$key",$content);
	}
}
