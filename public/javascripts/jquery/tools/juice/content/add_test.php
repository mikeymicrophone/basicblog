<!-- Add the test instruction here -->
<div id="instruction">
	Add new test
</div>

<!-- Main content of the test, whatever you want -->
<div class="main">	
	<h1>Add new test</h1>

	<form method="post" name="register">
	<div class="exchange" style="position: relative; height: 350px;">
		
		<div class="blue in" style="height: 350px;">
			<div id="register-container" style="width: 450px; margin: 0 auto;">
				
				<label for="category_register">Category</label>
				<select name="category_register" value="" id="category_register" style="width: 300px; margin-left: 4px;">
					<option value="jquery">jQuery Core</option>
					<option value="jqueryui" SELECTED>jQuery UI</option>
				</select>
				
				<br clear="both" />
				
				<label for="title_register">Title</label>
				<input type="text" name="title_register" value="" id="title_register" class="text-bg" style="width: 320px;"><br clear="both" />

				<label for="template_register">Template</label>
				<input type="text" name="template_register" value="" id="template_register" class="text-bg" style="width: 320px;"><br clear="both" />

				<label for="code_register">Code</label>
				<textarea type="text" name="code_register" value="" id="code_register" class="text-bg" style="width: 320px; height: 250px;"></textarea><br clear="both" />
			</div>
		</div>
		
	</div>
	</form>
	<br/>
	<p class="strong">Helping is simple. Please start right away with a random test, or register to have even more options. Thanks!</p>
	
	<div class="green link"><a href="javascript:saveTest();">Save test!</a></div>
	<div class="link" style="background: #AE0000; color: #fff;" id="cancel"><a style="color: #fff;" href="javascript:history.back();">Cancel</a></div>
</div>

<script>
$(function() {

	$('#title_register').focus();
	
});
</script>