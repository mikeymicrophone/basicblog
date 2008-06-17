function highlight_incomplete_form(elt) {
	if ($(elt).val() == '') {
      $(elt).parent().addClass('unfinished_form');
	  $(elt).parent().insertAfter('please add some text its not hard')
	}
}

function remove_highlighting(elt) {
	$(elt).parent().removeClass('unfinished_form');
}

$(document).ready(function() {
  $('.required').bind("blur", function(elt) {
    highlight_incomplete_form(elt.target);
  })
  $('.required').bind("focus", function(elt) {
	remove_highlighting(elt.target);
  })
})