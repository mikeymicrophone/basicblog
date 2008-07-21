var agt=navigator.userAgent.toLowerCase();
var is_ie = ((agt.indexOf("msie") != -1) && (agt.indexOf("opera") == -1));
var is_opera = (agt.indexOf("opera") != -1);
var is_ff = (agt.indexOf("firefox") != -1);
var is_win = (agt.indexOf("windows") != -1);

function rollon(button) {
	newAudio('/media/audio/rover.wav', 'rover', 1, 0);
	new Effect.Opacity('number'+button, {duration:0.1, to:1});
	new Effect.Opacity('label'+button, {duration:0.1, to:1});
}

function rolloff(button) {
	new Effect.Opacity('number'+button, {duration:0.5, to:0.5});
	new Effect.Opacity('label'+button, {duration:0.5, to:0.5});
}

function togglesubnav(url, section) {
	if ($('palette').style.top != '129px') {
		newAudio('/media/audio/07_end 47.wav', 'soundpalette', 1, 0);
		new Effect.Opacity('palettecontent', {to: 0, duration:0.5});
		setTimeout("new Effect.Morph('palette', {style: 'opacity:0; top: 129px;', duration:0.5});", 500);
		setTimeout("subnav('"+url+"');", 1500);
	} else {
		subnav(url);
	}
	
	setTimeout("togglecontent('"+url+section+"');", 1000);
}

function subnav(url) {
	if ($('palette').style.top != '169px') {
		makePaletteRequest(url);
	}
}

function togglecontent(url) {
	if ($('page').style.top != '129px') {
		newAudio('/media/audio/07_end 47.wav', 'soundcontent', 1, 0);
		new Effect.Opacity('pagecontentwrapper', {to: 0, duration:0.5});
		new Effect.Opacity('wrap3', {to: 0, duration:0.5});
		new Effect.Opacity('wrap4', {to: 0, duration:0.5});
		setTimeout("new Effect.Morph('page', {style: 'opacity:0; top: 129px;', duration:0.5});", 500);
		setTimeout("content('"+url+"');", 1100);
	} else {
		content(url);
	}
}

function content(url) {
	//alert($('page').style.top);
	if ($('page').style.top != '169px') {
		makePageRequest(url);
		urchinTracker(url);
		document.location.href='#'+url;
	}
}

function toggleContact(url, thepostbody) {
	new Effect.Opacity('pagecontentwrapper', {to: 0, duration:0.5});
	new Effect.Opacity('wrap3', {to: 0, duration:0.5});
	new Effect.Opacity('wrap4', {to: 0, duration:0.5});
	new Effect.Morph('page', {style: 'opacity:0.0; top: 169px;', duration:0.5});
	setTimeout("contact('"+url+"', '"+thepostbody+"');", 600);
}

function contact(url, thepostbody) {
	makeContactRequest(url, thepostbody);
	urchinTracker(url);
}

var errFunc = function(t) {
   $('pagecontent').innerHTML = 'Error.<br>Status Code: ' + t.status + '.<br>Status Text: ' + t.statusText+'.' + t.responseText;
	$('pagecontent').scrollTop=0;
	new Effect.Morph('page', {style: 'opacity:0.85; top: 169px;', duration:1});
	setTimeout("new Effect.Opacity('pagecontentwrapper', {from:0, to:1, duration:1});", 1000);
	setTimeout("loadscroller();", 2200);
}

var handlerPaletteFunc = function(t) {
	$('palettecontent').innerHTML = t.responseText;
	new Effect.Morph('palette', {style: 'opacity:0.85; top: 169px;', duration:1});
	newAudio('/media/audio/49_button 46.wav', 'soundpalette', 1, 0);
	setTimeout("new Effect.Opacity('palettecontent', {from:0, to:1, duration:1});", 1000);
}

var handlerPageFunc = function(t) {
	$('pagecontent').innerHTML = t.responseText;
	$('pagecontent').scrollTop=0;
	new Effect.Morph('page', {style: 'opacity:0.85; top: 169px;', duration:1});
	newAudio('/media/audio/49_button 46.wav', 'soundpage', 1, 0);
	setTimeout("new Effect.Opacity('pagecontentwrapper', {from:0, to:1, duration:1});", 1000);
	setTimeout("loadscroller();", 2200);
}

var handlerContactFunc = function(t) {
	$('pagecontent').innerHTML = t.responseText;
	$('pagecontent').scrollTop=0;
	new Effect.Morph('page', {style: 'opacity:0.85; top: 169px;', duration:1});
	setTimeout("new Effect.Opacity('pagecontentwrapper', {from:0, to:1, duration:1});", 1000);
	setTimeout("loadscroller();", 2200);
}

function makePaletteRequest(url, section) {
	new Ajax.Request(url, {method:'post', postBody:'url='+url, onSuccess:handlerPaletteFunc, onFailure:errFunc});
	setTimeout("if ($('palette').style.top != '169px') { makePaletteRequest('"+url+"', '"+section+"') }", 2500);
}

function makePageRequest(url, section) {
	new Ajax.Request(url, {method:'post', postBody:'url='+url, onSuccess:handlerPageFunc, onFailure:errFunc});
	setTimeout("if ($('page').style.top != '169px') { makePageRequest('"+url+"', '"+section+"') }", 2500);
}

function makeContactRequest(url, thepostbody) {
	new Ajax.Request(url, {method:'post', postBody:thepostbody, onSuccess:handlerContactFunc, onFailure:errFunc});
	setTimeout("newAudio('/media/audio/flight.wav', 'flight', 5, 0);", 1000);
}

function loadscroller(){
	// vertical slider control
	new Effect.Opacity('wrap3', {to:1, duration:0});
	$('wrap3').show();
	new Effect.Opacity('wrap4', {to:1, duration:0});
	$('wrap4').show();
	
	var slider3 = new Control.Slider('handle3', 'track3', {
		axis: 'vertical',
		onSlide: function(v) { scrollVertical(v, $('pagecontent'), slider3);  },
		onChange: function(v) { scrollVertical(v, $('pagecontent'), slider3); }
	});
			
	// horizontal slider control
	var slider4 = new Control.Slider('handle4', 'track4', {
		axis: 'horizontal',
		onSlide: function(v) { scrollHorizontal(v, $('pagecontent'), slider4);  },
		onChange: function(v) { scrollHorizontal(v, $('pagecontent'), slider4); }
	});
		
	// scroll the element vertically based on its width and the slider maximum value
	function scrollVertical(value, element, slider) {
		element.scrollTop = Math.round(value/slider.maximum*(element.scrollHeight-element.offsetHeight));
	}
			
	// scroll the element horizontally based on its width and the slider maximum value
	function scrollHorizontal(value, element, slider) {
		element.scrollLeft = Math.round(value/slider.maximum*(element.scrollWidth-element.offsetWidth));
	}
			
	// disable vertical scrolling if text doesn't overflow the div
	if ($('pagecontent').scrollHeight <= $('pagecontent').offsetHeight) {
		slider3.setDisabled();
		$('wrap3').hide();
	}
			
	// disable horizontal scrolling if text doesn't overflow the div
	if ($('pagecontent').scrollWidth <= $('pagecontent').offsetWidth) {
		slider4.setDisabled();
		$('wrap4').hide();
	}
}

/*** START "JS AUDIO ENGINE" ***/
/*******************************************
CREATED BY JULES GRAVINESE OF WEBVETERAN.COM
Feel free to use the followin audio engine.
But please give credit where it's due.
In other words, leave this disclaimer here.

Usage:
newAudio(filename, trackname[, duration, delay])

Verson 1.3 :: AUGUST 28 2007
Better Windows FF support. Now uses WMV, not QT.

Verson 1.2 :: MARCH 1 2007
Much better performance, less lag.
IE gets bgsound. Others get embed.
A special case for IE? Nah, really?
No Sounds in FF if no QT (ff requires qt).

Verson 1.1 :: FEBRUARY 23 2007
No longer excluding any browser.
Using embed+noembed+object.

Verson 1 :: JANUARY 12 2007
*******************************************/
function newAudio(audioFile, trackName, dur, del) {
	delay = (del*1000); 
	duration = (dur*1000)+delay+300;// Add 300 for the browser to do the work
	
	//if (!is_ff || haveqt) {
	
		// The programmer giveth
			// if I knew how to do variable variable names, I'd no have to use another function...
			// this is important for queueing sound effects and possible overlapping. 
		setTimeout("buildAudio('"+audioFile+"', '"+trackName+"');", delay);
	
		// And the programmer taketh
		setTimeout("document.body.removeChild($('"+trackName+"'));", duration);
	//}
}

function buildAudio(audioFile, trackName) {
	// 'track' would be the variable variable name, making this function unnecessary
	if (is_ie) {
     	// IE GETS BGSOUND
     	track = Builder.node('bgsound',{id:trackName, src:audioFile, loop:1, autostart:'true'});
	} else if (is_ff && is_win) {
		// FF ON WIN GETS WMV
		track = Builder.node('embed', {type:'application/x-mplayer2', pluginspage:'http://microsoft.com/windows/mediaplayer/en/download/',        id:'mediaPlayer', name:'mediaPlayer', displaysize:'4', autosize:'-1', bgcolor:'darkblue', showcontrols:'false', showtracker:'-1', showdisplay:'0', showstatusbar:'-1', videoborder3d:'-1', width:'0', height:'0', src:audioFile, autostart:'true', designtimesp:'5311', loop:'false'});
	} else {
		// ALL OTHERS ARE GENERIC
		track = Builder.node('embed',{id:trackName, src:audioFile, loop:'false', autostart:'true', hidden:'true'});
	}
	document.body.appendChild(track);
}

/*** END AUDIO ENGINE ***/



function  _CF_checkcontact(_CF_this)
    {
        //reset on submit
        _CF_error_exists = false;
        _CF_error_messages = new Array();
        _CF_error_fields = new Object();
        _CF_FirstErrorField = null;

        //form element Name required check
        if( !_CF_hasValue(_CF_this['Name'], "TEXT", false ) )
        {
            _CF_onError(_CF_this, "Name", _CF_this['Name'].value, "Please enter your full name");
            _CF_error_exists = true;
        }

        //form element Email required check
        if( _CF_hasValue(_CF_this['Email'], "TEXT", false ) )
        {
            //form element Email 'EMAIL' validation checks
            if (!_CF_checkEmail(_CF_this['Email'].value, true))
            {
                _CF_onError(_CF_this, "Email", _CF_this['Email'].value, "Please enter a valid email address");
                _CF_error_exists = true;
            }

        }else {
            _CF_onError(_CF_this, "Email", _CF_this['Email'].value, "Please enter a valid email address");
            _CF_error_exists = true;
        }

        //form element Phone 'TELEPHONE' validation checks
        if (!_CF_checkphone(_CF_this['Phone'].value, false))
        {
            _CF_onError(_CF_this, "Phone", _CF_this['Phone'].value, "Please enter a valid phone number");
            _CF_error_exists = true;
        }

        //form element Location required check
        if( !_CF_hasValue(_CF_this['Location'], "TEXT", false ) )
        {
            _CF_onError(_CF_this, "Location", _CF_this['Location'].value, "Please enter a city and/or state");
            _CF_error_exists = true;
        }

        //form element Qc required check
        if( !_CF_hasValue(_CF_this['Request'], "TEXTAREA", false ) )
        {
            _CF_onError(_CF_this, "Request", _CF_this['Request'].value, "Please enter a request");
            _CF_error_exists = true;
        }


        //display error messages and return success
        if( _CF_error_exists )
        {
            if( _CF_error_messages.length > 0 )
            {
                // show alert() message
                _CF_onErrorAlert(_CF_error_messages);
                // set focus to first form error, if the field supports js focus().
                if( _CF_this[_CF_FirstErrorField].type == "text" )
                { _CF_this[_CF_FirstErrorField].focus(); }

            }
            return false;
        }else {
        	newAudio('/media/audio/morse_1.wav', 'morsecode', 3, 0);
			mypostbody = "Name=" + _CF_this['Name'].value;
        	mypostbody = mypostbody + "&Email=" + _CF_this['Email'].value;
        	mypostbody = mypostbody + "&Phone=" + _CF_this['Phone'].value;
        	mypostbody = mypostbody + "&Company=" + _CF_this['Company'].value;
        	mypostbody = mypostbody + "&WebSite=" + _CF_this['WebSite'].value;
        	mypostbody = mypostbody + "&Location=" + _CF_this['Location'].value;
        	mypostbody = mypostbody + "&Request=" + escape(_CF_this['Request'].value);
        	toggleContact('/contact/sent.cfm', mypostbody);        
            return false;
        }
    }
