<?php
$lumineConfig = array (
	'configuration' => array (
		'class-path' => dirname(__FILE__),
		'host' => 'localhost',
		'database' => 'juice',
		'dialect' => 'mysqli',
		'port' => '3306',
		'user' => 'root',
		'password' => 'odraude',
		'package' => 'juice',
		'maps' => 'juice',
		'use-cache' => dirname(__FILE__).'/dbCache',
		'crypt-pass' => '',
		'lembrar' => '1',
		'create-classes' => '1',
		'create-maps' => '1',
		'escape' => '1',
		'empty-as-null' => '1',
		'fileDate' => filemtime(__FILE__)
	),
	'maps' => array (
		'juice.User',
		'juice.Statistics',
		'juice.Tests'
	)
);
?>