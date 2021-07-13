<?php
class TcApiDataFetcher extends TcBase {

	function test(){
		$adf = new ApiDataFetcher("https://www.atk14.net/api/",array(
			"logger" => new Logger()
		));

		$login = "testing".uniqid();

		// ### login_availabilities

		$data = $adf->get("login_availabilities/detail",array(
			"login" => $login,
		));
		$this->assertEquals(200,$adf->getStatusCode());
		$this->assertEquals(array("status" => "available"),$data);
		$this->assertEquals("GET",$adf->getMethod());
		$this->assertEquals("https://www.atk14.net/api/en/login_availabilities/detail/?login=$login&format=json",$adf->getUrl());
		$duration = $adf->getDuration();
		$this->assertTrue(is_float($duration));
		$this->assertTrue($duration>0.0);

		// ### users/create_new

		$data = $adf->post("users/create_new",array(
			"login" => $login,
			"name" => "John Doe",
			"email" => "john@doe.com",
			"password" => "secret1234",
		));

		$this->assertEquals(201,$adf->getStatusCode());
		$this->assertEquals("POST",$adf->getMethod());
		$this->assertEquals("https://www.atk14.net/api/en/users/create_new/",$adf->getUrl());

		// ### login_availabilities

		$data = $adf->get("login_availabilities/detail",array(
			"login" => $login,
		));
		$this->assertEquals(200,$adf->getStatusCode());
		$this->assertEquals(array("status" => "taken"),$data);

		// ### logins/create_new

		$options = array(
			"acceptable_error_codes" => array(
				401, // bad password
				404, // there is no such user
			)
		);
		
		$data = $adf->post("logins/create_new",array(
			"login" => "badass",
			"password" => "badTRY"
		),$options);
		$this->assertEquals(404,$adf->getStatusCode());
		$this->assertNull($data);

		$data = $adf->post("logins/create_new",array(
			"login" => $login,
			"password" => "badTRY"
		),$options);
		$this->assertEquals(401,$adf->getStatusCode());
		$this->assertNull($data);

		$data = $adf->post("logins/create_new",array(
			"login" => $login,
			"password" => "secret1234"
		),$options);
		$this->assertEquals(201,$adf->getStatusCode());
		$this->assertInternalType("array",$data);
		$this->assertEquals($login,$data["login"]);

		// ### put method

		$data = $adf->put("http_requests/detail",array("id" => 123));
		$this->assertEquals("PUT",$data["method"]);
		$this->assertEquals("https://www.atk14.net/api/en/http_requests/detail/?id=123&format=json",$data["url"]);

		// ### delete method

		$data = $adf->delete("http_requests/detail",array("id" => 456));
		$this->assertEquals("DELETE",$data["method"]);
		$this->assertEquals("https://www.atk14.net/api/en/http_requests/detail/?id=456&format=json",$data["url"]);
	}

	function test_lang(){
		$adf = new ApiDataFetcher("https://skelet.atk14.net/api/",array(
			"logger" => new Logger(),
			"lang" => "en",
		));

		$data = $adf->get("login_availabilities/detail",array("login" => "yuri"));
		$this->assertEquals(array("status" => "available"),$data);
		$this->assertEquals("https://skelet.atk14.net/api/en/login_availabilities/detail/?login=yuri&format=json",$adf->getUrl());

		$data = $adf->get("login_availabilities/detail",array("login" => "yuri", "lang" => "cs"));
		$this->assertEquals(array("status" => "available"),$data);
		$this->assertEquals("https://skelet.atk14.net/api/cs/login_availabilities/detail/?login=yuri&format=json",$adf->getUrl());

		// invalid login in the default language (en)
		$data = $adf->post("logins/create_new",array(
			"login" => "yuri",
			"password" => "badass",
		),array("acceptable_error_codes" => array("404")));
		$this->assertEquals(null,$data);
		$this->assertEquals(array("There is no such user"),$adf->getErrors());

		// invalid login in czech
		$data = $adf->post("logins/create_new",array(
			"login" => "yuri",
			"password" => "badass",
			"lang" => "cs",
		),array("acceptable_error_codes" => array("404")));
		$this->assertEquals(null,$data);
		$this->assertEquals(array("Takový uživatel tady není"),$adf->getErrors());

		// suppressing lang
		$adf = new ApiDataFetcher("https://skelet.atk14.net/api/",array(
			"logger" => new Logger(),
			"lang" => "",
		));

		$data = $adf->get("non_existing_resource/detail",array("id" => "123"),array("acceptable_error_codes" => array(404)));
		$this->assertEquals("https://skelet.atk14.net/api/non_existing_resource/detail/?id=123&format=json",$adf->getUrl());

		// leading slash
		$adf = new ApiDataFetcher("https://skelet.atk14.net/api",array( // slash is missing!
			"logger" => new Logger(),
		));

		$data = $adf->get("login_availabilities/detail",array("login" => "jean.marais"));
		$this->assertEquals("https://skelet.atk14.net/api/en/login_availabilities/detail/?login=jean.marais&format=json",$adf->getUrl());

		// parameters in action
		$adf = new ApiDataFetcher("https://www.atk14.net/api/",array(
			"logger" => new Logger(),
		));

		$data = $adf->get("http_requests/detail/?p1=a");
		$this->assertEquals("/api/en/http_requests/detail/?p1=a&format=json",$data["uri"]);

		$data = $adf->get("http_requests/detail/?format=json");
		$this->assertEquals("/api/en/http_requests/detail/?format=json",$data["uri"]);

		$data = $adf->get("http_requests/detail/?format=json&p1=a");
		$this->assertEquals("/api/en/http_requests/detail/?format=json&p1=a",$data["uri"]);

		$data = $adf->get("http_requests/detail/?format=json&p1=a",array("p2" => "b","p3" => "c"));
		$this->assertEquals("/api/en/http_requests/detail/?format=json&p1=a&p2=b&p3=c",$data["uri"]);
	}

	function test_logger(){
 		// no logger given
		$adf = new ApiDataFetcher("https://skelet.atk14.net/api/",array("logger" => null));
		$data = $adf->get("login_availabilities/detail",array("login" => "yuri"));
		$this->assertEquals(array("status" => "available"),$data);
	
		// using TestingLogger (see testing_logger.php)
		$logger = new TestingLogger();
		$adf = new ApiDataFetcher("https://skelet.atk14.net/api/",array("logger" => $logger));
		$this->assertEquals(array(),$logger->messages);
		$data = $adf->get("login_availabilities/detail",array("login" => "yuri"));
		$this->assertEquals(array("status" => "available"),$data);
		//
		$this->assertEquals(1,sizeof($logger->messages));
		$this->assertContains('[debug] ApiDataFetcher: GET https://skelet.atk14.net/api/en/login_availabilities/detail/?login=yuri&format=json',$logger->messages[0]);
	}

	function test_getApiUrl(){
		$adf = new ApiDataFetcher("https://www.atk14.net/api/",array(
			"logger" => new Logger()
		));

		$this->assertEquals("https://www.atk14.net/api/",$adf->getApiUrl());

		$data = $adf->get("login_availabilities/detail",array(
			"login" => "turbo.bug",
		));

		$this->assertEquals("https://www.atk14.net/api/",$adf->getApiUrl());
	}

	function test_error_not_found(){
		$adf = new ApiDataFetcher("https://www.atk14.net/api/");

		$exception_msg = "";

		try {
			$data = $adf->get("nonsence/nonsence");
		} catch(Exception $e) {
			$exception_msg = $e->getMessage();
		}

		$this->assertTrue(!isset($data));
		$this->assertEquals("HTTP status code 404 (Nothing has been found on this address), url: https://www.atk14.net/api/en/nonsence/nonsence/?format=json",$exception_msg);
	}

	function test_error_unknown_host(){
		$adf = new ApiDataFetcher("https://www.nonsence-nonsence-nonsence-nonsence.com/api/");

		$exception_msg = "";

		set_error_handler(function() { /* ignore errors */ });
		try {
			$data = $adf->get("articles/index");
		} catch(Exception $e) {
			$exception_msg = $e->getMessage();
		}
		restore_error_handler();

		$this->assertTrue(!isset($data));
		$this->assertEquals("No content on https://www.nonsence-nonsence-nonsence-nonsence.com/api/en/articles/index/?format=json (failed to open socket: could not resolve host: www.nonsence-nonsence-nonsence-nonsence.com (php_network_getaddresses: getaddrinfo failed: Name or service not known) [0])",$exception_msg);
	}
}
