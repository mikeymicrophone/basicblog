<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN" "http://www.w3.org/TR/html4/loose.dtd">
<html>
<head>
	<title><?= SITE_TITLE ?></title>
	<link rel="stylesheet" href="lib/css/style.css" type="text/css" media="screen">
	
	<?= $Render->renderById("jquery-plugins-javascript"); ?>
	<?= $Render->renderById("jquery-ui-javascript"); ?>
	
	<link rel="stylesheet" href="../../ui/datepicker/core/ui.datepicker.css" type="text/css">
	<script src="lib/js/juice.js"></script>
</head>
<body>
	
	<div id="topbar">
		<? $Render->renderById("topbar"); ?>
	</div>
		
	<div id="message" style="opacity: 0; filter: alpha(opacity=0);">Juice messages</div>
	
	<? $Render->listener(); ?>	
		
	<div id="bottombar">
		<div class="bg">
			<? $Render->renderById("bottombar"); ?>
		<div>
	</div>
	
</body>
</html>