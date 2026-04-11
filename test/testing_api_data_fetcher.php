<?php
class TestingApiDataFetcher extends ApiDataFetcher {

	function hidePasswordInMessage($messages){
		return $this->_hidePasswordInMessage($messages);
	}

	function serializeErrorMessages($errors,$array_leading_seq = "",$array_trailing_seq = ""){
		return $this->_serializeErrorMessages($errors,$array_leading_seq,$array_trailing_seq);
	}
}
