<?php
	
	$login = $_SESSION["__user"];

?>

<div id="loginstatus">
	
	<a href="?render=main">Home</a> <span style="padding: 0px 10px 0px 10px;">|</span>
	
	<? if (empty($login['username'])) { ?>	
	
		Not logged in. <a href="javascript:login()">Login now!</a>
	
	<? }else{ ?>
	
		Logged as <b><?= $login['username'] ?></b>, <a href="javascript:signout()">Sign out!</a>
	
	<? } ?>
	
</div>

<div id="sign_out_confirm" style="display: none;"></div>