/**
 * This function allows the binding of certain functions to a user defined key combination.
 * similar to the accesskey property of the hyperlinks.
 *
 * Heavily based upon the code:
 * http://www.openjs.com/scripts/events/keyboard_shortcuts/shortcuts.js
 *
 * Port made to jQuery, code cleaned up and improved performance.
 *
 *
 * The following code fires the  "sCombi" when "fCallback" is pressed on this element,
 * and bPropagate(s) if needed.
 *
 * @example $('input#someid').accesskey(sCombi, fCallback, bPropagate);
 *
 * @name						accesskey
 * @type						jQuery
 * @param String		The combination to check for.
 * @param Function	The callback to fire when the combination matches.
 * @param Boolean		Wether we need to propagate or just continue.
 * @cat Plugins/UI
 */
(function($) {
	//
	// Helper object
	$.accesskey = {
		aShiftKeys: {			// Workaround for Shift key bug created by using lowercase - as a result the shift+num combination was broken
				"`": "~",
				"1": "!",
				"2": "@",
				"3": "#",
				"4": "$",
				"5": "%",
				"6": "^",
				"7": "&",
				"8": "*",
				"9": "(",
				"0": ")",
				"-": "_",
				"=": "+",
				";": ": ",
				"'": "\"",
				",": "<",
				".": ">",
				"/": "?",
				"\\": "|"
			},
		aSpecialKeys: {		// Holds the array of the specialkeys
				'esc': 27,
				'escape': 27,
				'tab': 9,
				'space': 32,
				'return': 13,
				'enter': 13,
				'backspace': 8,
				'scrolllock': 145,
				'capslock': 20,
				'numlock': 144,
				'pause': 19,
				'break': 19,
				'insert': 45,
				'home': 36,
				'delete': 46,
				'end': 35,
				'pageup': 33,
				'pagedown': 34,
				'left': 37,
				'up': 38,
				'right': 39,
				'down': 40,
				'f1': 112,
				'f2': 113,
				'f3': 114,
				'f4': 115,
				'f5': 116,
				'f6': 117,
				'f7': 118,
				'f8': 119,
				'f9': 120,
				'f10': 121,
				'f11': 122,
				'f12': 123
			},
		fCallback: null,
		oEvent: null
	};

	// Actual code:
	$.fn.accesskey = function(sCombi, fCallback, bPropagate) {
		//
		// Split the combination to check for
		var aKeys = sCombi.toLowerCase().split('+');

		// Assign keydown function
		$(this).keydown(function(oEvent) {
				// Get information from event
				var iKey = oEvent.charCode ? oEvent.charCode : oEvent.keyCode ? oEvent.keyCode : -1;
				var sKey = String.fromCharCode(iKey).toLowerCase();

				//
				// Test to see if the combination matches
				var iMatch = 0;
				for(var iCurrent=0; iCurrent<aKeys.length; iCurrent++) {
					sCurrent = aKeys[iCurrent];

					// Test modifiers
					if ((sCurrent == 'ctrl' || sCurrent == 'control') && (oEvent.ctrlKey)) {
						iMatch++;
					} else if ((sCurrent == 'shift') && (oEvent.shiftKey)) {
						iMatch++;
					} else if ((sCurrent == 'alt') && (oEvent.altKey)) {
						iMatch++;
					} else if ((sCurrent.length > 1) && ($.accesskey.aSpecialKeys[sCurrent] == iKey)) {
						iMatch++;											// Special key!
					} else if (sKey == sCurrent) {
						iMatch++;											// Normal key!
					} else if (($.accesskey.aShiftKeys[sCurrent] && e.shiftKey) && ($.accesskey.aShiftKeys[sCurrent] == sCurrent)) {
						iMatch++;											// Stupid Shift key bug created by using lowercase
					}
				}

				//
				// If the keycount matches, there is a match
				if (iMatch == aKeys.length) {
						// If we want to stop the "default" behaviour, e.g. ctrl+s to display "Save As.." dialog,
						// we (only) have to trigger the function in a timer, and return "false" to stop the event.
						// jQuery handles the rest internally.
						if (!bPropagate) {
							//
							// Assign temp variables
							$.accesskey.fCallback = fCallback;
							$.accesskey.oEvent = oEvent;

							//
							// Trigger the event in a timer, and reset the temp variables
							window.setTimeout("$.accesskey.fCallback($.accesskey.oEvent); $.accesskey.fCallback = null; $.accesskey.oEvent = null;", 50);

							//
							// Always return false
							return false;
						} else {
							//
							// Process the callback
							return fCallback(oEvent);
						}
				}
			});
	};

})(jQuery);