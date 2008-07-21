<?php
	require_once("../../init.php");
	
	extract($_POST);
	
	if ($category_register && $title_register && $template_register && $code_register) {
		
		$test = new Tests;
		$test->title = $title_register;
		$test->category = $category_register;
		$test->template = $template_register;
		$test->code = $code_register;
		$test->enabled = true;
		$insertid = $test->insert();
		
	}
	
	echo $insertid ? $insertid : 0;
	
?>