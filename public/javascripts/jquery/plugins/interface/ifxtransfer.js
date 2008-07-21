/**
 * Interface Elements for jQuery
 * FX - transfer
 * 
 * http://interface.eyecon.ro
 * 
 * Copyright (c) 2006 Stefan Petre
 * Dual licensed under the MIT (MIT-LICENSE.txt) 
 * and GPL (GPL-LICENSE.txt) licenses.
 *   
 *
 */

jQuery.transferHelper = null;
/**
 * 
 * @name TransferTo
 * @description Animates an new build element to simulate a transfer action from one element to other
 * @param Hash hash A hash of parameters
 * @option Mixed to DOMElement or element ID to transfer to
 * @option String className CSS class to apply to transfer element
 * @option String duration animation speed, integer for miliseconds, string ['slow' | 'normal' | 'fast']
 * @option Function callback (optional) A function to be executed whenever the animation completes.
 *
 * @type jQuery
 * @cat Plugins/Interface
 * @author Stefan Petre
 */
jQuery.fn.TransferTo = function(o)
{
	return this.each(function(){
		if(!o || !o.to) {
			return;
		}
		var el = this;
		jQuery(o.to).each(function() {
			new jQuery.fx.itransferTo(el, this, o);
		});
	});
};
jQuery.fx.itransferTo = function(e, targetEl, o)
{
	var z = this;
	z.el = jQuery(e);
	z.targetEl = targetEl;
	z.transferEl = document.createElement('div');
	jQuery(z.transferEl)
		.css({
			position: 'absolute'
		}).addClass(o.className);
	
	if (!o.duration) {
		o.duration = 500;
	}
	z.duration = o.duration;
	z.complete = o.complete;
	z.diffWidth = 0;
	z.diffHeight = 0;
	
	if(jQuery.boxModel) {
		z.diffWidth = (parseInt(jQuery.curCSS(z.transferEl, 'borderLeftWidth')) || 0 )
					+ (parseInt(jQuery.curCSS(z.transferEl, 'borderRightWidth')) || 0)
					+ (parseInt(jQuery.curCSS(z.transferEl, 'paddingLeft')) || 0)
					+ (parseInt(jQuery.curCSS(z.transferEl, 'paddingRight')) || 0);
		z.diffHeight = (parseInt(jQuery.curCSS(z.transferEl, 'borderTopWidth')) || 0 )
					+ (parseInt(jQuery.curCSS(z.transferEl, 'borderBottomWidth')) || 0)
					+ (parseInt(jQuery.curCSS(z.transferEl, 'paddingTop')) || 0)
					+ (parseInt(jQuery.curCSS(z.transferEl, 'paddingBottom')) || 0);
	}
	z.start = jQuery.extend(
		jQuery.iUtil.getPosition(z.el.get(0)),
		jQuery.iUtil.getSize(z.el.get(0))
	);
	z.end = jQuery.extend(
		jQuery.iUtil.getPosition(z.targetEl),
		jQuery.iUtil.getSize(z.targetEl)
	);
	z.start.wb -= z.diffWidth;
	z.start.hb -= z.diffHeight;
	z.end.wb -= z.diffWidth;
	z.end.hb -= z.diffHeight;
	z.callback = o.complete;
	jQuery('body').append(z.transferEl);
	// Execute the transfer
	jQuery(z.transferEl)
		.css('width', z.start.wb + 'px')
		.css('height', z.start.hb + 'px')
		.css('top', z.start.y + 'px')
		.css('left', z.start.x + 'px')
		.animate(
			{
				top: z.end.y,
				left: z.end.x,
				width: z.end.wb,
				height: z.end.hb
			},
			z.duration,
			function()
			{
				jQuery(z.transferEl).remove();
	
				// Callback
				if (z.complete && z.complete.constructor == Function) {
					z.complete.apply(z.el.get(0), [z.to]);
				}
			}
		);
};