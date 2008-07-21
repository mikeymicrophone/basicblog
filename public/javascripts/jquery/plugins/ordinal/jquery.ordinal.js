/*
 * Ordinal 1.0 - jQuery plugin
 * 
 * Copyright (c) 2007 - 2008 Scott González
 * 
 * Dual licensed under the MIT and GPL licenses:
 *   http://www.opensource.org/licenses/mit-license.php
 *   http://www.gnu.org/licenses/gpl.html
 */

;(function($) {

$.fn.extend({
	ordinal: function(childExpr, parentExpr) {
		parentExpr && (parentExpr += ':eq(0)');
		var parentMethod = parentExpr ? 'parents' : 'parent';
		var childMethod = childExpr ? 'find' : 'children';
		
		var self = this[0];
		var position;
		this[parentMethod](parentExpr)[childMethod](childExpr).each(function(pos, elem) {
			if (elem === self) {
				position = pos;
				return false;
			}
		});
		return position;
	}
});

})(jQuery);