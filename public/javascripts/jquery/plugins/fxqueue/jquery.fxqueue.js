/* Copyright (c) 2006 Brandon Aaron (http://brandonaaron.net || brandon.aaron@gmail.com)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) 
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 *
 * Version: 0.1
 *
 * $LastChangedDate$
 * $Rev$
 */

(function($){

/**
 * Queues fx to fire one after the other no matter
 * what element they are running on.
 * It should be called just as you would call animate.
 *
 * @name fxqueue
 * @author Brandon Aaron (http://brandonaaron.net || brandon.aaron@gmail.com)
 */
$.fn.fxqueue = function(prop, speed, easing, callback) {
	var args = $.makeArray(arguments);
	return this.each(function() {
		var fn = args[args.length-1];
		args[args.length-1] = function() { $.fxqueue.next(); if (fn) fn.apply(this); };
		$.fxqueue.queue.push( [this].concat( args ) );
		$.fxqueue.play();
	});
};

$.fxqueue = {
	queue: [],
	next: function() {
		// if no more fx or not playing, return
		if (!$.fxqueue.queue[0] || !$.fxqueue.playing) return;
		var args  = $.fxqueue.queue.shift(),
			$this = $( args.shift() );
		$.fn.animate.apply($this, args);
	},
	play: function() {
		if ($.fxqueue.playing) return;
		else $.fxqueue.playing = true;
		$.fxqueue.next();
	},
	pause: function() {
		$.fxqueue.playing = false;
	}
};
	
})(jQuery);