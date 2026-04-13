ApiDataFetcher
==============

[![Build Status](https://app.travis-ci.com/atk14/ApiDataFetcher.svg?token=Kc7UxgK5oqFG8sZAhCzg&branch=master)](https://app.travis-ci.com/atk14/ApiDataFetcher)


PHP client library for communication with ATK14 RESTful APIs and other JSON APIs.

- [Installation](#installation)
- [Basic usage](#basic-usage)
- [HTTP methods](#http-methods)
- [Sending files and raw data](#sending-files-and-raw-data)
- [Error handling](#error-handling)
- [Inspecting the response](#inspecting-the-response)
- [Language](#language)
- [HTTP Basic Authentication](#http-basic-authentication)
- [Additional headers](#additional-headers)
- [Caching](#caching)
- [Proxy](#proxy)
- [SSL verification](#ssl-verification)
- [Timeout](#timeout)
- [Constructor options reference](#constructor-options-reference)
- [Tracy panel integration](#tracy-panel-integration)
- [License](#license)


Installation
------------

    composer require atk14/api-data-fetcher

Optionally, set the default base URL in your project configuration:

    define("API_DATA_FETCHER_BASE_URL", "https://api.example.com/api/");


Basic usage
-----------

    $adf = new ApiDataFetcher("https://api.example.com/api/");

    $data = $adf->get("articles/detail", ["id" => 123]);
    // Calls: GET https://api.example.com/api/en/articles/detail/?id=123&format=json

    echo $data["title"];

Parameters can also be embedded directly in the action string:

    $data = $adf->get("articles/detail/?id=123");

Array parameters are serialized automatically:

    $data = $adf->get("articles/index", ["tags" => ["php", "api"]]);
    // Calls: ...?tags[]=php&tags[]=api&format=json

The `lang` segment and `format=json` parameter are added automatically on every request. See [Language](#language) and [Constructor options reference](#constructor-options-reference) to configure or disable this behaviour.


HTTP methods
------------

    $data = $adf->get("articles/detail",    ["id" => 123]);
    $data = $adf->post("articles/create",   ["title" => "Hello", "body" => "..."]);
    $data = $adf->put("articles/update",    ["id" => 123, "title" => "Hello 2"]);
    $data = $adf->delete("articles/delete", ["id" => 123]);


Sending files and raw data
--------------------------

### File upload

    // Simple form — just pass a path
    $data = $adf->postFile("images/create", "/path/to/image.jpg", ["title" => "Flower"]);

    // Full control over file metadata
    $data = $adf->postFile("images/create", [
        "path"      => "/path/to/image.jpg",
        "name"      => "flower.jpg",       // filename sent to the server
        "mime_type" => "image/jpeg",        // auto-detected when omitted
    ], ["title" => "Flower"]);

### Posting JSON

    // Pass an array — it is encoded automatically
    $data = $adf->postJson("articles/import", ["title" => "Hello", "body" => "..."]);

    // Or pass a pre-encoded string
    $data = $adf->postJson("articles/import", '{"title":"Hello"}');

    // URL parameters alongside the JSON body
    $data = $adf->postJson("articles/import", $payload, ["params" => ["lang" => "en"]]);

### Posting raw data

    $data = $adf->postRawData("endpoint", $binary_content, [], ["mime_type" => "application/octet-stream"]);


Error handling
--------------

By default, any non-2xx response throws an exception. The exception hierarchy is:

| Exception | Thrown when |
|---|---|
| `ApiDataFetcher\HttpException` | Server returned a non-2xx HTTP status code |
| `ApiDataFetcher\NetworkException` | Connection failed (DNS failure, timeout, …) |
| `ApiDataFetcher\NoContentException` | Response body is empty (and status is not 204) |
| `ApiDataFetcher\InvalidContentException` | Response body is not valid JSON |

All four extend `ApiDataFetcher\Exception`.

### Catching exceptions

    try {
        $data = $adf->get("articles/detail", ["id" => 999]);
    } catch (ApiDataFetcher\HttpException $e) {
        echo $e->getStatusCode();  // e.g. 404
        echo $e->getContent();     // raw response body
        echo $e->getHeaders();     // raw response headers
    } catch (ApiDataFetcher\NetworkException $e) {
        echo "Connection failed: " . $e->getMessage();
    }

### Acceptable error codes

To handle specific error responses as valid (non-exceptional) results, list them in `acceptable_error_codes`. The method then returns `null` instead of throwing.

    $data = $adf->post("logins/create", [
        "login"    => "johny",
        "password" => "secret",
    ], [
        "acceptable_error_codes" => [401, 404],
    ]);

    if (!$data) {
        if ($adf->getStatusCode() == 401) { /* bad password */ }
        if ($adf->getStatusCode() == 404) { /* user not found */ }
    }


Inspecting the response
-----------------------

These methods are available after every request:

    $adf->getStatusCode();    // int,    e.g. 200, 201, 404
    $adf->getStatusMessage(); // string, e.g. "OK", "Created", "Not Found"
    $adf->getRawResponse();   // string, raw response body
    $adf->getUrl();           // string, full URL that was called
    $adf->getMethod();        // string, "GET", "POST", "PUT", "DELETE"
    $adf->getDuration();      // float,  request duration in seconds
    $adf->isResponseCached(); // bool,   true when the response came from cache
    $adf->getErrors();        // array,  error payload on non-2xx responses

### Returning raw content

To skip JSON decoding and get the raw response string directly:

    $html = $adf->get("pages/export", ["id" => 5], ["return_raw_content" => true]);


Language
--------

ApiDataFetcher automatically inserts the current application language into every URL (e.g. `.../en/...`). The language is detected from the ATK14 framework; it defaults to `"en"` outside ATK14.

    // Set language globally in the constructor
    $adf = new ApiDataFetcher("https://api.example.com/api/", ["lang" => "cs"]);

    // Override per request
    $data = $adf->get("articles/detail", ["id" => 123, "lang" => "de"]);

    // Disable language segment entirely (for non-ATK14 APIs)
    $adf = new ApiDataFetcher("https://external-api.com/v1/", ["lang" => ""]);


HTTP Basic Authentication
-------------------------

Embed credentials directly in the base URL. Passwords are never written to logs.

    $adf = new ApiDataFetcher("https://user:password@api.example.com/api/");


Additional headers
------------------

    // Set headers for all requests
    $adf = new ApiDataFetcher("https://api.example.com/api/", [
        "additional_headers" => [
            "X-Forwarded-For: 1.2.3.4",
            "Authorization: Bearer " . $token,
        ],
    ]);

    // Or set headers for a single request
    $data = $adf->get("articles/detail", ["id" => 123], [
        "additional_headers" => ["X-Request-Id: abc123"],
    ]);

If the same header name appears in both the instance-level and the request-level list, the request-level value wins (case-insensitive matching).


Caching
-------

Responses can be cached to a local file. Pass the TTL in seconds:

    // Cache for 10 minutes
    $data = $adf->get("articles/index", ["year" => 2025], ["cache" => 600]);

    // Fall back to an expired cache entry when a live request fails
    $data = $adf->get("articles/index", ["year" => 2025], [
        "cache"                       => 600,
        "return_cached_content_on_error" => true,
    ]);

By default, cache files are stored in the system temp directory. Pass a custom `CacheFileStorage` instance to change the location:

    $adf = new ApiDataFetcher("https://api.example.com/api/", [
        "cache_storage" => new CacheFileStorage("/var/cache/my-app"),
    ]);

`CacheFileStorage` can also be used independently:

    $cache = new CacheFileStorage("/var/cache/my-app");
    $cache->write("key", $data, 300); // expires in 5 minutes
    $data = $cache->read("key");      // returns null when expired or missing


Proxy
-----

    $adf = new ApiDataFetcher("https://api.example.com/api/", [
        "proxy" => "tcp://proxy.example.com:8118",
    ]);


SSL verification
----------------

    // Disable peer name verification (e.g. when the certificate covers a different hostname)
    $adf = new ApiDataFetcher("https://api.example.com/api/", [
        "verify_peer_name" => false,
    ]);

    // Disable certificate verification entirely (not recommended in production)
    $adf = new ApiDataFetcher("https://api.example.com/api/", [
        "verify_peer" => false,
    ]);


Timeout
-------

Set the socket timeout in the constructor or change it at any time:

    $adf = new ApiDataFetcher("https://api.example.com/api/", [
        "socket_timeout" => 10.0, // seconds, default is 5.0
    ]);

    // Change timeout temporarily and restore afterwards
    $previous = $adf->setSocketTimeout(30.0);
    $data = $adf->get("reports/generate");
    $adf->setSocketTimeout($previous);


Constructor options reference
-----------------------------

| Option | Type | Default | Description |
|---|---|---|---|
| `url` | string | `API_DATA_FETCHER_BASE_URL` | Base API URL |
| `lang` | string | `"en"` | Language code inserted into the URL; `""` disables it |
| `default_params` | array | `["format" => "json"]` | Parameters added to every request |
| `additional_headers` | array\|string | `[]` | HTTP headers sent with every request |
| `user_agent` | string | `ApiDataFetcher/x.y UrlFetcher/x.y` | User-Agent header value |
| `cache_storage` | object | `new CacheFileStorage()` | Cache storage instance |
| `socket_timeout` | float | `5.0` | Connection timeout in seconds |
| `proxy` | string | `""` | Proxy URL, e.g. `"tcp://proxy:8118"` |
| `verify_peer` | bool | `true` | Verify the SSL certificate |
| `verify_peer_name` | bool | `true` | Verify the SSL certificate hostname |
| `automatically_add_leading_slash` | bool | `true` | Ensure base URL ends with `/` |
| `automatically_add_trailing_slash` | bool | `true` | Ensure action URL ends with `/` |
| `communicate_via_command` | string\|null | `null` | Path to a command used instead of a network socket |
| `get_content_callback` | callable | — | Custom callback to extract the response body from `UrlFetcher` |
| `logger` | object\|null | ATK14 logger or `null` | PSR-compatible logger |


Tracy panel integration
-----------------------

The package ships with `ApiDataFetcherPanel` for the [Tracy](https://tracy.nette.org) debugger. It shows the number of API calls and their details in the debug bar.

    $bar = Tracy\Debugger::getBar();
    $bar->addPanel(new ApiDataFetcherPanel($adf));

    // Custom panel title when multiple APIs are used
    $bar->addPanel(new ApiDataFetcherPanel($adf, ["title" => "Products API"]));


License
-------

ApiDataFetcher is free software distributed [under the terms of the MIT license](http://www.opensource.org/licenses/mit-license).
