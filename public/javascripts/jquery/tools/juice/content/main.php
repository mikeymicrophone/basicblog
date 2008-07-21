<?php
	
	$login = $_SESSION["__user"];

?>
<!-- Add the test instruction here -->
<div id="instruction">
	Please chose either to login, register or do a random test.
</div>

<!-- Main content of the test, whatever you want -->
<div class="main">	
	<h1>Welcome to Juice.</h1>

	<form method="post" name="register">
	<div class="exchange">
		<div class="blue out"><strong>Juice</strong> stands for <strong>jQuery UI Testing Center</strong>, it's our solution to user interface testing. Since it's incredibly hard to do automated tests within the DOM,
		we found a solution that involves real human people (read '<em>you</em>') and a very simple testing environment. The results are then automatically submitted to our
		testing database, which we'll use to make our product rock solid.
		</div>
		
		<div class="blue in" style="left: 600px; opacity: 0; filter: alpha(opacity=0); height: 110px;">
			<div id="register-container" style="width: 450px; margin: 0 auto;">
				<label for="email_register">Email</label>
				<input type="text" name="email_register" value="" id="email_register" class="text-bg" style="width: 320px;"><br clear="both" />
				<label for="username_register">Username</label>
				<input type="text" name="username_register" value="" id="username_register" class="text-bg" style="width: 320px;"><br clear="both" />
				<label for="password_register">Password</label>
				<input type="text" name="password_register" value="" id="password_register" class="text-bg" style="width: 320px;">
			
				<br clear="both" />
				<label for="register"></label>
				<input type="button" name="register" class="text-bg btn-form" value="Create an account!" id="register" onclick="submitRegisterUser();">
			</div>
		</div>
	</div>
	</form>
		
	<p class="strong">Helping is simple. Please start right away with a random test, or register to have even more options. Thanks!</p>
	
	<div class="green link"><a href="?random=1&render=random">Start with a random test!</a></div>
	<div class="grey link" id="registerlink"><a href="javascript:registerUser()">Register</a></div>
	<? if ($login['team']) { ?>	
	<div class="blue link" id="addtestlink"><a href="?render=addtest">Add test</a></div>
	<? } ?>
</div>