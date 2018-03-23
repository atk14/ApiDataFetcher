<?php
class ApiDataFetcherPanel implements Tracy\IBarPanel{

	function __construct($api_data_fetcher){
		$this->api_data_fetcher = $api_data_fetcher;
	}

	function getTab(){
		return "<strong>API</strong> ".$this->api_data_fetcher->getQueriesExecuted();
	}

	function getPanel(){
		$out = array();
		$out[] = '<div style="height: 500px; width: 800px; overflow:scroll;">';
		$out[] = "<code>";
		$out[] = $this->api_data_fetcher->getStatistics();
		$out[] = "</code>";
		$out[] = '</div>';
		return join("\n",$out);
	}
}
