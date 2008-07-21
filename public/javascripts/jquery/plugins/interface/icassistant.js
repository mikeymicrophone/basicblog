jQuery.icA = {
	helper: null,
	move: function(e)
	{
		var pointer = jQuery.iUtil.getPointer(e);
		jQuery.icA.helper.css(
			{
				top: pointer.y + 12 +'px',
				left: pointer.x + 12 +'px'
			}
		);
	},
	html: function(html) 
	{
		jQuery.icA.helper.html(html);
	},
	empty: function() 
	{
		jQuery.icA.helper.empty();
	},
	show: function() 
	{
		jQuery.icA.helper.show();
	},
	hide: function() 
	{
		jQuery.icA.helper.hide();
	},
	className: function(className) 
	{
		jQuery.icA.helper.get(0).className = className;
	},
	init: function() {
		if (!jQuery.icA.helper) {
			jQuery('body').append('<div id="cAssistentHelper" style="position: absolute;z-index: 9999"></div>');
			jQuery.icA.helper = jQuery('#cAssistentHelper');
			jQuery(document)
				.bind('mousemove', jQuery.icA.move);
		}
	}
};
jQuery(document).ready(function(){jQuery.icA.init()});