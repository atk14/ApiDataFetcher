<?php
class TcApiDataFetcher extends TcBase {

	function test(){
		$apf = new ApiDataFetcher("http://skelet.atk14.net/api/",array(
			"logger" => new Logger()
		));

		// ### login_availabilities/

		$data = $apf->post("login_availabilities/detail",array(
			"login" => "badass",
		));
		$this->assertEquals(200,$apf->getStatusCode());
		$this->assertEquals(array("status" => "available"),$data);

		// ### login/create_new

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
			"login" => "admin",
			"password" => "badTRY"
		),$options);
		$this->assertEquals(401,$apf->getStatusCode());
		$this->assertNull($data);

		$data = $apf->post("logins/create_new",array(
			"login" => "admin",
			"password" => "admin"
		),$options);
		$this->assertEquals(201,$apf->getStatusCode());
		$this->assertInternalType("array",$data);
		$this->assertEquals("admin",$data["login"]);
	}
}
