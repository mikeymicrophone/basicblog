module("batch");

test("batch", function() {
	$.each( ['attrs', 'styles', 'offsets', 'widths', 'heights', 'htmls', 'texts', 'vals'], function(index, name) {
		ok( $.fn[name], "Make sure " + name + " exists" );
	});
	ok( $('input[value=Test]').vals().constructor == Array, "Make sure the returned value is an actual Array" );
});
test("attrs", function() {
	isSet( $('input[value=Test]').attrs('value'), ["Test", "Test"], "$('input[value=Test]').attrs('value')" );
	isSet( $('input[value=Test]').attr('value', function(){ return 'Updated'; }).attrs('value'), ["Updated", "Updated"], "$('input[value=Test]').attrs('value', function(){ return 'Updated'; })" );
});
test("vals", function() {
	isSet( $('input[value=Test]').vals(), ["Test", "Test"], "$('input[value=Test]').vals()" );
	isSet( $('input[value=Test]').attr('value', function(){ return 'Updated'; }).vals(), ["Updated", "Updated"], "$('input[value=Test]').attr('value', function(){ return 'Updated'; }).vals()" );
});