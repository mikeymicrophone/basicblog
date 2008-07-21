<!-- Add the test instruction here -->
<div id="instruction">
	Please drag the retangle around.
</div>


<!-- Add the result expectation here -->
<div id="expectation">
	I was able to drag the element around.
</div>

<!-- Content -->
<script type="text/javascript">
	$(document).ready(function() {
		$('div.example').draggable();
	});
</script>

<div class="example green" style="margin: 50px; width: 100px; height: 100px;">Drag me!</div>