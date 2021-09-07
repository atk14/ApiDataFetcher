<?php
class ApiDataFetcherPanel implements Tracy\IBarPanel{

	protected $title;
	protected $api_data_fetcher;

	function __construct($api_data_fetcher, $options = array()){
		$options += array(
			"title" => "API",
		);
		$this->title = $options["title"];
		$this->api_data_fetcher = $api_data_fetcher;
	}

	function getTab(){
		$queries_executed = $this->api_data_fetcher->getQueriesExecuted();
		if(!$queries_executed){
			return $this->title;
		}
		return "<strong>$this->title</strong> $queries_executed";
	}

	function getPanel(){
		$out = array();
		$out[] = '<div style="overflow: scroll;">';
		$out[] = "<code>";
		$out[] = $this->api_data_fetcher->getStatistics();
		$out[] = "</code>";
		$out[] = '</div>';
		return join("\n",$out);
	}
}
