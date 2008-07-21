/* Copyright (c) 2008 Brandon Aaron (http://brandonaaron.net)
 * Dual licensed under the MIT (http://www.opensource.org/licenses/mit-license.php) 
 * and GPL (http://www.opensource.org/licenses/gpl-license.php) licenses.
 *
 * $LastChangedDate$
 * $Rev$
 *
 * Version 0.1
 *
 * Usage: 
 *   $("input[name=q]")
 *      .val("Search")    // set an initial value if it doesn't already exist
 *      .clearonfocus();  // prepare the element for clearing on focus
 */

jQuery.fn.clearonfocus = function() {
	jQuery(this)
		.bind('focus', function() {
			// Set the default value if it isn't set
			if ( !this.defaultValue ) this.defaultValue = this.value;
			// Check to see if the value is different
			if ( this.defaultValue && this.defaultValue != this.value ) return;
			// It isn't, so remove the text from the input
			this.value = '';
		})
		.bind('blur', function() {
			// If the value is blank, return it to the defaultValue
			if ( this.value.match(/^\s*$/) )
				this.value = this.defaultValue;
		});
};