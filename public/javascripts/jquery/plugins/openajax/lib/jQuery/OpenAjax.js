/*
 * Adds OpenAjax Alliance Support to jQuery
 *
 * Include this plugin in a page to hook jQuery into the OpenAjax platform.
 * For example:
 *   <script src="jquery.js"></script>
 *   <script src="openajax.js"></script>
 */

if ( typeof OpenAjax != "undefined" ) {
	OpenAjax.registerLibrary("jQuery", "http://jquery.com/", "1.1");
	OpenAjax.registerGlobals("jQuery", ["jQuery"]);

	var old_add = jQuery.event.add;

	jQuery.event.add = function( element, type, handler ) {
		if ( element == window || element == document ) {
			if ( type == "load" )
				return OpenAjax.addOnLoad( handler );
			else if ( type == "unload" )
				return OpenAjax.addOnUnload( handler );
		}

		return old_add.apply( this, arguments );
	};
}
