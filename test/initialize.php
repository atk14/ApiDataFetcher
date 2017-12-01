<?php
define("TEST",true);

require(__DIR__ . "/../src/api_data_fetcher.php");
require(__DIR__ . "/../src/cache_file_storage.php");

require(__DIR__ . "/../vendor/autoload.php");

$HTTP_REQUEST = new HTTPRequest();
$HTTP_RESPONSE = new HTTPResponse();
