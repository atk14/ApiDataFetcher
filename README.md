ApiDataFetcher
==============

Client library designed for communication with ATK14 restful API.
It should be usable also for other JSON APIs.

Basic usage
-----------

    $adf = new ApiDataFetcher("http://skelet.atk14.net/api/");

    $data = $adf->get("articles/detail",["id" => 123]);
    // or
    $data = $adf->get("articles/detail/?id=123");

    $title = $data["title"];

In fact in this example a HTTP GET request is made on URL http://skelet.atk14.net/api/en/articles/detail/?id=123&format=json and decoded JSON data is returned.

A post request can be made this way:

    $data = $adf->post("articles/create_new",[
      "title" => "Brand New Article",
      "body" => "Once upon a time..."
    ]);
    // or
    $data = $adf->post("articles/create_new/?title=Brand+New+Article&body=Once+upon+a+time...");

The parameter format=json is added automatically to every request. It's a convention in ATK14 APIs. This behaviour can be disabled by setting an option in the constructor.

For completeness there are also methods *put* and *delete*.

    $data = $adf->put("articles",[
      "title" => "Brand New Article",
      "body" => "Once upon a time..."
    ]);
    // or
    $data = $adf->put("articles/?title=Brand+New+Article&body=Once+upon+a+time...");

    $data = $adf->delete("article",["id" => 123]);
    // or
    $data = $adf->delete("article/?id=123");

A method for uploading a file:

    $data = $adf->postFile("images/create_new","/path/to/image.jpg",["title" => "Beautiful Flower", "description" => "..."]);
    // or
    $file_specs = [
      "path" => "/path/to/file",
      "postname" => "image",
      "name" => "flower.jpg",
      "mime_type" => "image/jpeg",
    ];
    $data = $adf->postFile("images/create_new",$file_specs,["title" => "Beautiful Flower", "description" => "..."]);

A handy method for posting JSON:

    $params = ["param1" => "val1", "param2" => "val2"];
    $json = json_encode($params);

    $data = $adf->postJson("endpoint",$json);
    // or
    $data = $adf->postJson("endpoint",$params);

### Handling error codes

By default ApiDataFetcher throws an exception in case of a non 2XX response code.
In order to handle valid error codes on an API method, specify the codes in the option acceptable_error_codes.

    $data = $adf->post("logins/create_new",[
      "login" => "johny.long",
      "password" => "JulieIsNoMore"
    ],[
      "acceptable_error_codes" => [
        401, // Unauthorized: Bad password
        404, // Not Found: There is no such user
      ]
    ]);
 
    if(!$data){
      if($adf->getStatusCode()==401){
         // Bad password
      }
      if($adf->getStatusCode()==404){
         // There is no such user
      }
    }

### Language

ApiDataFetcher tries to detect automatically currently used language in the running application and use it in the API method call.

Language can be also specified in the constructor or in the specific API method call.

    $adf = new ApiDataFetcher("http://skelet.atk14.net/api/",["lang" => "en"]);

    $data_in_english = $adf->get("articles/detail",["id" => 123]); // performs call to http://skelet.atk14.net/api/en/articles/detail/?id=123&format=json

    $data_in_czech = $adf->get("articles/detail",["id" => 123],["lang" => "cs"]); // performs call to http://skelet.atk14.net/api/cs/articles/detail/?id=123&format=json

On a non-ATK14 API you may want to disable language considering at all.

    $adf = new ApiDataFetcher("http://somewhere-on-the.net/json-api/",["lang" => ""]);

    $data = $adf->get("articles",["id" => 123]);

### HTTP Basic Authentication

Does an API require basic authentication? No problem for the ApiDataFetcher!

    $adf = new ApiDataFetcher("https://username:password@api-on-the.net/api/");

### Tracy panel integration

ApiDataFetcher package comes with ApiDataFetcherPanel for easy integration into the popular debugger Tracy (https://packagist.org/packages/tracy/tracy)

    $tracy_bar = Tracy\Debugger::getBar();
    $tracy_bar->addPanel(new ApiDataFetcherPanel($api_data_fetcher));

Installation
------------

Use the Composer to install the ApiDataFetcher.

    cd path/to/your/atk14/project/
    composer require atk14/api-data-fetcher

In the project configuration file the constant API_DATA_FETCHER_BASE_URL can be defined.

    define("API_DATA_FETCHER_BASE_URL","http://skelet.atk14.net/api/");

Licence
-------

ApiDataFetcher is free software distributed [under the terms of the MIT license](http://www.opensource.org/licenses/mit-license)
