<?php
class TcApiDataFetcher extends TcBase {

	function test(){
		$adf = new ApiDataFetcher("http://www.atk14.net/api/",array(
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
		$this->assertEquals("http://www.atk14.net/api/en/login_availabilities/detail/?login=$login&format=json",$adf->getUrl());

		// ### users/create_new

		$data = $adf->post("users/create_new",array(
			"login" => $login,
			"name" => "John Doe",
			"email" => "john@doe.com",
			"password" => "secret1234",
		));

		$this->assertEquals(201,$adf->getStatusCode());
		$this->assertEquals("POST",$adf->getMethod());
		$this->assertEquals("http://www.atk14.net/api/en/users/create_new/",$adf->getUrl());

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
	}

	function test_lang(){
		$adf = new ApiDataFetcher("http://skelet.atk14.net/api/",array(
			"logger" => new Logger(),
			"lang" => "en",
		));

		$data = $adf->get("login_availabilities/detail",array("login" => "yuri"));
		$this->assertEquals(array("status" => "available"),$data);
		$this->assertEquals("http://skelet.atk14.net/api/en/login_availabilities/detail/?login=yuri&format=json",$adf->getUrl());

		$data = $adf->get("login_availabilities/detail",array("login" => "yuri", "lang" => "cs"));
		$this->assertEquals(array("status" => "available"),$data);
		$this->assertEquals("http://skelet.atk14.net/api/cs/login_availabilities/detail/?login=yuri&format=json",$adf->getUrl());

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
		$adf = new ApiDataFetcher("http://skelet.atk14.net/api/",array(
			"logger" => new Logger(),
			"lang" => "",
		));

		$data = $adf->get("non_existing_resource/detail",array("id" => "123"),array("acceptable_error_codes" => array(404)));
		$this->assertEquals("http://skelet.atk14.net/api/non_existing_resource/detail/?id=123&format=json",$adf->getUrl());
	}
}
