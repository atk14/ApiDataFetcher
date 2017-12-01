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
}
