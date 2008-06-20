function highlight_incomplete_form(elt) {
	if ($(elt).val() == '') {
      $(elt).parent().addClass('unfinished_form');
	}
}

function remove_highlighting(elt) {
	$(elt).parent().removeClass('unfinished_form');
}

function show_comment_form() {
	$('#comment_form_container').show();
}

function submit_comment() {
	$('#comment_holder').load('/comments/create', $('#comment_form').serialize(), place_new_comment()); // might be good alternative http://malsup.com/jquery/form/#api except I don't know if I can use it to prepend
    $('#comment_body').clearFields();
	return false;
}

function place_new_comment() {
	$('#comments').prepend($('#comment_holder').html()).hide().show();
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
  $('#comment_form').bind("submit", submit_comment)

  if ($.query.get('comment')) {
	show_comment_form();
	$('#comment_body').focus();
  }
})
