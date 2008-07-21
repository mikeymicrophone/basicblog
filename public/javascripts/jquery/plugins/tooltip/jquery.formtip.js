;(function($) {

	var helper,
		body,
		arrow;

	$.fn.formtip = function(settings) {
		settings = $.extend({}, $.formtip.defaults, settings);
		createHelper(settings);
		return this.delegate("focusin", "input", function() {
			if (!this.attr("title"))
				return;
			body.html(this.attr("title"));
			helper.show();
			var positionParent = settings.positionParent(this);
			helper.css({
				top: positionParent.offset().top + positionParent.outerHeight() / 2 - helper.outerHeight() / 2,
				left: positionParent.offset().left + positionParent.width() + settings.left
			});
			arrow.css({
				top: helper.outerHeight() / 2 - arrow.outerHeight() / 2
			});
		}).delegate("focusout", "input", function() {
			helper.hide();
		});
	}
	
	function createHelper(settings) {
		helper = $(settings.element).appendTo(document.body);
		body = helper.children(".formtip-body");
		arrow = helper.children(".formtip-arrow");
		createHelper = function() {};
	}
	
	$.formtip = {
		defaults: {
			element: "<div id='formtip'><div class='formtip-arrow'>&lt;</div><div class='formtip-body'></div></div>",
			positionParent: function(element) {
				return element;
			},
			left: 10
		}
	}
	
})(jQuery);
