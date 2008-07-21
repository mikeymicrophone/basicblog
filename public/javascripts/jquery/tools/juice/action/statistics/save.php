<?php
	require_once("../../init.php");
	
	$login = $_SESSION['__user'];
	
	$userid = $login['id'] ? $login['id'] : 0;
	$userIP = $_SERVER['REMOTE_ADDR'];
	$useragent = $_SERVER['HTTP_USER_AGENT'];
	
	$jqueryversion = '1.2.3';
	$test = '1';
	
	extract($_GET);
	
	if ($engine && $platform && $result && $version && $test) {
		$statistic = new Statistics;
		$statistic->userid = $userid;		
		$statistic->ip = $userIP;
		$statistic->useragent = $useragent;
		$statistic->engineversion = $version;		
		$statistic->platform = $platform;		
		$statistic->testid = $test;		
		$statistic->engine = $engine;		
		$statistic->result = $result;		
		$statistic->version = $jqueryversion;		
		$insertid = $statistic->insert();
	}
	
	echo $insertid ? $insertid : 0;
	
?>