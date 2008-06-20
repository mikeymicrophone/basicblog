function highlight_incomplete_form(elt) {
	if ($(elt).val() == '') {
      $(elt).parent().addClass('unfinished_form');
	  $(elt).parent().insertAfter('please add some text its not hard')
	}
}

function remove_highlighting(elt) {
	$(elt).parent().removeClass('unfinished_form');
}

function show_comment_form() {
	$('#comment_form_container').show();
}

function submit_comment() {
	$('#comments').load('/comments/create', $('#comment_form').serialize())
	$('#comment_body').clear();
	return false;
}

$(document).ready(function() {
  $('.required').bind("blur", function(elt) {
    highlight_incomplete_form(elt.target);
  })
  $('.required').bind("focus", function(elt) {
	remove_highlighting(elt.target);
  })
  $('#comment_link').bind("click", function() {
	show_comment_form();
  })
  $('#comment_form').submit(function() {
	submit_comment();
  })

  if ($.query.get('comment')) {
	show_comment_form();
	$('#comment_body').focus();
  }
})