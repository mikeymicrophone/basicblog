/*
 * Automatic setup for Web Forms plugin
 * 
 * Copyright (c) 2007 - 2008 Scott Gonzalez
 * 
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 */
;(function($) {

$.extend($.webForms, {
	beforeValidate: function(elem) {
		$(':input', elem).add(elem).removeClass('error').next('div.error').remove();
	},
	
	errorHandler: function(elem) {
		$(elem).addClass('error').after(
			'<div class="error">' + $(elem).validationMessage() + '</div>');
	}
});

function initFormValidation() {
	if ($.fn.livequery) {
		$('form').livequery(function() {
			$(this).bind('submit', function() {
				return $(this).checkValidity();
			});
		});
	} else {
		$('form').bind('submit', function() {
			return $(this).checkValidity();
		});
	}
}

$(document).ready(function() {
	initFormValidation();
});

})(jQuery);