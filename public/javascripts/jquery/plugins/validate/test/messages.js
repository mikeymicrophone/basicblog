module("messages");

test("group error messages", function() {
	$.validator.addClassRules({
		requiredDateRange: {required:true, date:true, dateRange:true}
	});
	$.validator.addMethod("dateRange", function() {
		return new Date($("#fromDate").val()) < new Date($("#toDate").val());
	}, "Please specify a correct date range.");
	var form = $("#dateRangeForm");
	form.validate({
		groups: {
			dateRange: "fromDate toDate"
		},
		errorPlacement: function(error) {
			form.find(".errorContainer").append(error);
		}
	});
	ok( !form.valid() );
	equals( 1, form.find(".errorContainer *").length );
	equals( "Please enter a valid date.", form.find(".errorContainer label.error").text() );
	
	$("#fromDate").val("12/03/2006");
	$("#toDate").val("12/01/2006");
	ok( !form.valid() );
	equals( "Please specify a correct date range.", form.find(".errorContainer label.error").text() );
	
	$("#toDate").val("12/04/2006");
	ok( form.valid() );
	ok( form.find(".errorContainer label.error").is(":hidden") );
});

test("read messages from metadata", function() {
	var form = $("#testForm9")
	form.validate();
	var e = $("#testEmail9")
	e.valid();
	equals( form.find("label").text(), "required" );
	e.val("bla").valid();
	equals( form.find("label").text(), "email" );
});
