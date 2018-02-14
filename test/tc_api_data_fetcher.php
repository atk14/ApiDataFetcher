<?php
class TcApiDataFetcher extends TcBase {

	function test(){
		$apf = new ApiDataFetcher("http://www.atk14.net/api/",array(
			"logger" => new Logger()
		));

		$login = "testing".uniqid();

		// ### login_availabilities

		$data = $apf->post("login_availabilities/detail",array(
			"login" => $login,
		));
		$this->assertEquals(200,$apf->getStatusCode());
		$this->assertEquals(array("status" => "available"),$data);

		// ### users/create_new

		$data = $apf->post("users/create_new",array(
			"login" => $login,
			"name" => "John Doe",
			"email" => "john@doe.com",
			"password" => "secret1234",
		));

		$this->assertEquals(201,$apf->getStatusCode());

		// ### login_availabilities

		$data = $apf->post("login_availabilities/detail",array(
			"login" => $login,
		));
		$this->assertEquals(200,$apf->getStatusCode());
		$this->assertEquals(array("status" => "taken"),$data);

		// ### logins/create_new

		$options = array(
			"acceptable_error_codes" => array(
				401, // bad password
				404, // there is no such user
			)
		);
		
		$data = $apf->post("logins/create_new",array(
			"login" => "badass",
			"password" => "badTRY"
		),$options);
		$this->assertEquals(404,$apf->getStatusCode());
		$this->assertNull($data);

		$data = $apf->post("logins/create_new",array(
			"login" => $login,
			"password" => "badTRY"
		),$options);
		$this->assertEquals(401,$apf->getStatusCode());
		$this->assertNull($data);

		$data = $apf->post("logins/create_new",array(
			"login" => $login,
			"password" => "secret1234"
		),$options);
		$this->assertEquals(201,$apf->getStatusCode());
		$this->assertInternalType("array",$data);
		$this->assertEquals($login,$data["login"]);
	}

	function test_lang(){
		$apf = new ApiDataFetcher("http://skelet.atk14.net/api/",array(
			"logger" => new Logger(),
			"lang" => "en",
		));

		$data = $apf->get("login_availabilities/detail",array("login" => "yuri"));
		$this->assertEquals(array("status" => "available"),$data);
		$this->assertEquals("http://skelet.atk14.net/api/en/login_availabilities/detail/?login=yuri&format=json",$apf->getUrl());

		$data = $apf->get("login_availabilities/detail",array("login" => "yuri", "lang" => "cs"));
		$this->assertEquals(array("status" => "available"),$data);
		$this->assertEquals("http://skelet.atk14.net/api/cs/login_availabilities/detail/?login=yuri&format=json",$apf->getUrl());

		// invalid login in the default language (en)
		$data = $apf->post("logins/create_new",array(
			"login" => "yuri",
			"password" => "badass",
		),array("acceptable_error_codes" => array("404")));
		$this->assertEquals(null,$data);
		$this->assertEquals(array("There is no such user"),$apf->getErrors());

		// invalid login in czech
		$data = $apf->post("logins/create_new",array(
			"login" => "yuri",
			"password" => "badass",
			"lang" => "cs",
		),array("acceptable_error_codes" => array("404")));
		$this->assertEquals(null,$data);
		$this->assertEquals(array("Takový uživatel tady není"),$apf->getErrors());

		// suppressing lang
		$apf = new ApiDataFetcher("http://skelet.atk14.net/api/",array(
			"logger" => new Logger(),
			"lang" => "",
		));

		$data = $apf->get("non_existing_resource/detail",array("id" => "123"),array("acceptable_error_codes" => [404]));
		$this->assertEquals("http://skelet.atk14.net/api/non_existing_resource/detail/?id=123&format=json",$apf->getUrl());
	}
}
