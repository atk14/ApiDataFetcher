<?php
class CacheFileStorage{

	function read($key){
		$TEMP = Files::GetTempDir();

		$orig_key = $key;
		$key = sha1($key);
		$filename = $TEMP . "/cache_file_storage/" . $key;
		if(file_exists($filename)){
			$content = Files::GetFileContent($filename);
			$ar = unserialize($content);
			if($ar && ($ar["key"]==$orig_key)){
				return $ar["data"];
			}
		}
	}

	function write($key,$data){
		$TEMP = Files::GetTempDir();

		$content = serialize(array(
			"key" => $key,
			"data" => $data,
		));
		$key = sha1($key);
		//echo "gonna to save:\n";
		//var_dump($data);
		Files::Mkdir($dir = $TEMP . "/cache_file_storage/");
		$tmp_file = Files::WriteToTemp($content);
		Files::MoveFile($tmp_file,"$dir$key");
	}
}
