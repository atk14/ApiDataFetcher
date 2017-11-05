ApiDataFetcher
==============

Client library for communication with ATK14 restful API

Basic usage
-----------

    $adf = new ApiDataFetcher("http://skelet.atk14.net/api/");
    $data = $adf->get("articles/detail",array("id" => 123));

    $title = $data["title"];

Installation
------------

Use the Composer to install the ApiDataFetcher.

    cd path/to/your/atk14/project/
    composer require atk14/api-data-fetcher dev-master

In the project configuration file the constant API_DATA_FETCHER_BASE_URL can be defined.

    define("API_DATA_FETCHER_BASE_URL","http://skelet.atk14.net/api/");

Licence
-------

ApiDataFetcher is free software distributed [under the terms of the MIT license](http://www.opensource.org/licenses/mit-license)
