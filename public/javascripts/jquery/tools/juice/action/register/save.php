<?php
	require_once("../../init.php");
	
	extract($_POST);
	
	if ($username_register && $password_register && $email_register) {

		$user = new User();
		
		$user->username = $username_register;
		
		if (!UserUtil::exists($user)) {
			$user->email = $email_register;
			$user->password = $password_register;
			$user->team = true;
			$insertid = $user->insert();
		}
		
	}
	
	echo $insertid ? $insertid : 0;
	
?>