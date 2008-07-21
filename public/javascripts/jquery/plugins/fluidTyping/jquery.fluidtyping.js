/*
 * jQuery Fluid Typing plugin 1.1
 * 
 * Copyright (c) 2007 Scott González
 * 
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 */
;(function($) {

function jumpNext(curr, next) {
	curr.bind("keyup", function(event) {
		if ((this.value.length === this.maxLength) &&
			(event.keyCode > 46))
		{
			next.focus();
		}
	});
};

function jumpPrev(curr, prev) {
	curr.bind("keydown", function(event) {
		if ((this.value.length === 0) && (event.keyCode === 8)) {
			prev.focus().val(prev.val());
		}
	});
};

$.fn.fluidTyping = function() {
	for (var i = 0; i < this.length; i++) {
		if (i !== this.length - 1) {
			jumpNext($(this[i]), $(this[i + 1]));
		}
		
		if (i !== 0) {
			jumpPrev($(this[i]), $(this[i - 1]));
		}
	}
	
	return this;
};

})(jQuery);