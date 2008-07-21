/*
 * Web Forms 0.4.0 - jQuery plugin
 * 
 * Copyright (c) 2007 - 2008 Scott González
 * 
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 */

// http://www.whatwg.org/specs/web-forms/current-work/

/*
We have to use the wftype attribute instead of the type attribute because using custom type attributes doesn't work.

Test results from Firefox 2:
$('<select>').attr('type', 'foo').is('[type="foo"]') === false
$('<select>').attr('type', 'foo').attr('type') === 'foo'
$('<select>').attr('type', 'foo')[0].type === 'select-one'
*/

;(function($) {

function getCheckedCount(element_name) {
	var checked_count = 0;
	$('input[name="' + element_name + '"]').each(function() {
		if ($(this).is(':checked')) {
			checked_count++;
		}
	});
	
	return checked_count;
}

function isNumber(val) {
	return (/^-?\d*\.?\d+(e-?\d+)?$/).test(val);
}

var validityState = {
	typeMismatch: false,
	rangeUnderflow: false,
	rangeOverflow: false,
	stepMismatch: false,
	tooLong: false,
	patternMismatch: false,
	valueMissing: false,
	customError: false,
	valid: true
};

var validationMessages = {
	typeMismatch: function(elem) {
		var type = $(elem).attr('wftype');
		switch (type) {
			case 'email':
				return 'Value must be an email address.';
			case 'number':
				return 'Value must be a number.';
			case 'url':
				return 'Value must be a URL.';
		}
	},
	rangeUnderflow: function(elem) {
		return 'Value may not be less than ' + $(elem).attr('min') + '.';
	},
	rangeOverflow: function(elem) {
		return 'Value may not be more than ' + $(elem).attr('max') + '.';
	},
	stepMismatch: 'Step mismatch.',
	tooLong: function(elem) {
		return 'Value may not be more than ' + $(elem).attr('maxlength') + ' characters.';
	},
	patternMismatch: function(elem) {
		var title = $(elem).attr('title');
		return (title ? title : 'Pattern mismatch');
	},
	valueMissing: 'This field is required.',
	customError: function(elem) {
		return getWebForms(elem).customErrorMessage;
	}
};

var validator = {
	// TODO: make sure all types are handled
	typeMismatch: function($elem) {
		var type = $elem.attr('wftype');
		var val = $elem.val();
		if (val !== '') {
			switch (type) {
				case 'email':
					// http://projects.scottsplayground.com/email_address_validation/
					if (!this.email) {
						this.email = /^((([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+(\.([a-z]|\d|[!#\$%&'\*\+\-\/=\?\^_`{\|}~]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])+)*)|((\x22)((((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(([\x01-\x08\x0b\x0c\x0e-\x1f\x7f]|\x21|[\x23-\x5b]|[\x5d-\x7e]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(\\([\x01-\x09\x0b\x0c\x0d-\x7f]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF]))))*(((\x20|\x09)*(\x0d\x0a))?(\x20|\x09)+)?(\x22)))@((([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|\d|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.)*(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])*([a-z]|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])))\.?$/i;
					}
					return this.email.test(val);
				case 'number':
				case 'range':
					return isNumber(val);
				case 'url':
					// http://projects.scottsplayground.com/iri/
					if (!this.url) {
						this.url = /^([a-z]([a-z]|\d|\+|-|\.)*):(\/\/(((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:)*@)?((\[(|(v[\da-f]{1,}\.(([a-z]|\d|-|\.|_|~)|[!\$&'\(\)\*\+,;=]|:)+))\])|((\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5])\.(\d|[1-9]\d|1\d\d|2[0-4]\d|25[0-5]))|(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=])*)(:\d*)?)(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*|(\/((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)?)|((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)+(\/(([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)*)*)|((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)){0})(\?((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|[\uE000-\uF8FF]|\/|\?)*)?(\#((([a-z]|\d|-|\.|_|~|[\u00A0-\uD7FF\uF900-\uFDCF\uFDF0-\uFFEF])|(%[\da-f]{2})|[!\$&'\(\)\*\+,;=]|:|@)|\/|\?)*)?$/i;
					}
					return this.url.test(val);
			}
		}
		
		return true;
	},
	
	// TODO: update to work with date/time values (and update error message)
	rangeUnderflow: function($elem) {
		var min = $elem.attr('min');
		if ((min !== '') && isNumber(min)) {
			var val = $elem.val();
			if (isNumber(val)) {
				return (Number(min) <= Number(val));
			}
		}
		
		return true;
	},
	
	// TODO: update to work with date/time values (and update error message)
	rangeOverflow: function($elem) {
		var max = $elem.attr('max');
		if ((max !== '') && isNumber(max)) {
			var val = $elem.val();
			if (isNumber(val)) {
				return (Number(max) >= Number(val));
			}
		}
		
		return true;
	},
	
	// TODO: update to work with date/time values (and update error message)
	stepMismatch: function($elem) {
		var step = $elem.attr('step');
		if (!isNumber(step)) {
			step = 1;
		}
		
		var base = $elem.attr('min');
		if ((base === '') || !isNumber(base)) {
			base = $elem.attr('max');
		}
		if ((base !== '') && isNumber(base)) {
			var val = $elem.val();
			if (isNumber(val)) {
				return (parseInt((val - base) / step, 10) == ((val - base) / step));
			}
		}
		
		return true;
	},
	
	tooLong: function($elem) {
		var maxlength = $elem.attr('maxlength');
		if (maxlength && (maxlength > 0)) {
			return (maxlength >= $elem.val().length);
		}
		
		return true;
	},
	
	patternMismatch: function($elem) {
		var pattern = $elem.attr('pattern');
		var val = $elem.val();
		if ((pattern || (pattern === 0)) && (val !== ''))
		{
			var regex = new RegExp('^(?:' + pattern + ')$');
			if (!regex.test(val)) {
				return false;
			}
		}
		
		return true;
	},
	
	valueMissing: function($elem) {
		if ($elem.attr('required')) {
			switch ($elem.attr('type')) {
				case 'checkbox':
				case 'radio':
					var checked_count = getCheckedCount($elem.attr('name'));
					if ($elem.is(':checkbox')) {
						return (checked_count >= 1);
					} else {
						return (checked_count == 1);
					}
				break;
				default:
					if ($elem.val() === '') {
						return false;
					}
				break;
			}
		}
		
		return true;
	}
};

var willValidateExpr = '' +
	':input' +
	':not(:disabled):not([readonly])' +
	':not([type="hidden"]):not(:button):not(:reset):not(:submit)';

function initializeWebForms(elem) {
	var webForms = {
		willValidate: $(elem).willValidate(),
		validity: $.extend({}, validityState),
		customErrorMessage: ''
	};
	$.data(elem, 'webForms', webForms);
	return webForms;
}

function getWebForms(elem) {
	var webForms = $.data(elem, 'webForms');
	if (webForms === undefined) {
		webForms = initializeWebForms(elem);
	}
	return webForms;
}

function validate(elem, webForms) {
	var $elem = $(elem);
	webForms.validity.valid = !webForms.validity.customError;
	$.each(validator, function(e, f) {
		webForms.validity.valid = !(webForms.validity[e] = !f($elem)) &&
			webForms.validity.valid;
	});
}

function getValidationMessage(elem, webForms) {
	var validity = $.extend({}, webForms.validity);
	delete validity.valid;
	
	var message = '';
	$.each(validity, function(e, v) {
		if (v) {
			if (typeof validationMessages[e] == 'string') {
				message += validationMessages[e] + "\n";
			} else if ($.isFunction(validationMessages[e])) {
				message += validationMessages[e](elem) + "\n";
			}
		}
	});
	return $.trim(message);
}

$.extend({
	webForms: {
		beforeValidate: function(elem) {
		},
		
		errorHandler: function(elem) {
		},
		
		validationMessages: function(messages) {
			$.extend(validationMessages, messages);
		}
	},
	
	isDefaultSubmit: function(elem) {
		return elem === $(elem.form).find(':submit:first')[0];
	},
	
	isIndeterminate: function(elem) {
		return elem.type == 'radio' && getCheckedCount(elem.name) === 0;
	}
});

$.extend($.expr[':'], {
	checked: 'a.checked || a.selected || jQuery.attr(a, "selected")',
	indeterminate: 'jQuery.isIndeterminate(a)',
	'default': 'jQuery.isDefaultSubmit(a) || a.defaultChecked || a.defaultSelected',
	valid: 'jQuery(a).validity().valid',
	invalid: '!jQuery(a).validity().valid',
	'in-range': '!jQuery(a).validity().typeMismatch ' +
		'&& !jQuery(a).validity().rangeUnderflow ' +
		'&& !jQuery(a).validity().rangeOverflow',
	'out-of-range': 'jQuery(a).validity().rangeUnderflow ' +
		'|| jQuery(a).validity().rangeOverflow',
	required: 'jQuery(a).attr("required")',
	optional: '/input|textarea/i.test(a.nodeName) ' +
		'&& !/hidden|image|reset|submit|button/i.test(a.type) ' +
		'&& !jQuery(a).attr("required")',
	'read-only': 'jQuery(a).is("[readonly]")',
	'read-write': '!jQuery(a).is("[readonly]")'
});

$.fn.extend({
	willValidate: function() {
		return this.is(willValidateExpr);
	},
	
	validity: function() {
		if (this.length) {
			return getWebForms(this[0]).validity;
		}
	},
	
	setCustomValidity: function(message) {
		message = message || '';
		var flag = !!message;
		return this.each(function() {
			var webForms = getWebForms(this);
			webForms.customErrorMessage = message;
			webForms.validity.valid = !(webForms.validity.customError = flag);
			for (e in validator) {
				webForms.validity.valid = webForms.validity.valid &&
					!webForms.validity[e];
			}
			$.data(this, 'webForms', webForms);
		});
	},
	
	checkValidity: function() {
		if (this.length) {
			var elem = this[0];
			$.webForms.beforeValidate(elem);
			if ($(elem).is('form')) {
				var valid = true;
				$(willValidateExpr, elem).each(function() {
					valid = $(this).checkValidity() && valid;
				});
				if (!valid) {
					$(':invalid:eq(0)', elem)[0].focus();
				}
				return valid;
			} else {
				var webForms = getWebForms(elem);
				if (webForms.willValidate) {
					validate(elem, webForms);
					if (!webForms.validity.valid) {
						if ($(elem).triggerHandler('invalid') !== false) {
							$.webForms.errorHandler(elem);
						}
					}
					return webForms.validity.valid;
				}
			}
		}
	},
	
	validationMessage: function() {
		var message = '';
		if (this.length) {
			var webForms = getWebForms(this[0]);
			if (!webForms.validity.valid) {
				message = getValidationMessage(this[0], webForms);
			}
		}
		return message;
	}
});

// populate select elements
// TODO: how can we do this before document load? can we use the new special event system?
// TODO: how can we monitor the data attribute for changes?
$(document).ready(function() {
	var $elem;
	
	function processData(data) {
		var $select = $(data);
		if ($select.attr('xmlns') != 'http://www.w3.org/1999/xhtml') {
			return;
		}
		
		if ($select.attr('wftype') != 'incremental') {
			$elem.empty();
		}
		
		var val = $elem.val();
		$select.children('option').each(function() {
			$elem.append(this);
		});
		$elem.val(val);
	}
	
	$('select[data]').each(function() {
		$elem = $(this);
		var data = $elem.attr('data');
		data = /^data:/.test(data) ?
			unescape(data.substring(data.indexOf(',') + 1)) :
			$.ajax({
				url: data,
				async: false
			}).responseText;
		processData(data);
	});
});

$.webforms = $.webForms;

})(jQuery);