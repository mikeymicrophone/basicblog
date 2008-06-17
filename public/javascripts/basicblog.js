function highlight_incomplete_form(elt) {
	jQuery(elt);
	if (elt.text() == '') {
      elt.parent().addClass('unfinished_form');
	}
}

$(document).ready(function() {
  $('.required').bind("blur", function(elt) {
    highlight_incomplete_form(elt);
  })
})