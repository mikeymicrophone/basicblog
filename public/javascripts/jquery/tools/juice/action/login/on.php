<?php
	require_once("../../init.php");
	
	extract($_POST);
	
	$username = $_POST["username"];
	$password = $_POST["password"];
	
	if (!$username || !$password) {
		header("Location: ../../?login=0");
		return;
	}
	
	$user = new User;
	$user->username = $username;
	$user->password = $password;
	$__total = $user->find(true);
	
	if ($__total > 0 && $user->id) {
		// logged
		
		$_SESSION["__user"] = array(
			id => $user->id,
			email => $user->email,
			username => $user->username,
			team => $user->team,
			createdate => $user->createdate
		);
		
		header("Location: ../../?login=1");
	}
	else{
		// not valid
		
		$_SESSION["__user"] = null;
		header("Location: ../../?login=0");
	}
	
?>