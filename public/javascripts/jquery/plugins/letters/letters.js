/**
 * letters , create a div tag with the first letters from the li tags in the list (ul)
 * @example 	$('ul.alpha').letters('slow')
 * @desc quick list to alphabetic show/hide, can be easily cssed.
 * @param String the speed at which to show the li elements that begin with each letter found starting the text of the li.
 * @type $
 *
 * @name letters
 * @cat classic example show hide
 * @author Jake Wolpert (jakecigar@gmail.com)
 */
jQuery.keysOf = function(obj){
	var keys = []
	for(keys[keys.length] in obj);
	return keys
};
jQuery.fn.letters = function(speed){
	var ul = jQuery(this)
	var items = {}
	var lis = ul.children()
	var div = jQuery("<div></div>")
		.prependTo(ul.parent())
	lis.each(function(){
		var let = jQuery(this).hide().text().charAt(0).toUpperCase()
		if (!items[let]) items[let] = []
		items[let].push(this)
	})
	var keys = jQuery.keysOf(items).sort().reverse()
	for (var i in keys){
		(function(i){ // scope i
			jQuery("<span>" + keys[i] +"</span>")
			.css({textDecoration: 'underline',cursor:'pointer'})
			.click(function(){
				lis.hide()
				jQuery(items[keys[i]]).show(speed,function(){jQuery(this).attr('style','')})
			})
			.prependTo(div)
			if (i<keys.length-1) jQuery("<b> | </b>").prependTo(div)
		})(i)
	}
};
