function load(){
	setData();
	setupParts();
	var ver = widget.preferenceForKey('useVersion');
	if(ver && ver.length>0){
		$('#popup').find('option').each(function(){
			if(ver == this.value){
				this.selected=true;
			}
		});
	};
	var container = $('#drop');
	$('#footer img').click(function(){
		container.slideUp();
	});
	$('a[@href]').click(function(){
	    widget.openURL(this.href);
		return false;
	});
}

function showBack(event){
	if (window.widget){
		widget.prepareForTransition("ToBack");
	}
	$('#front').toggle();
	$('#back').toggle();
	if (window.widget){
		setTimeout('widget.performTransition();', 0);
	}
}
function showFront(event){
	if (window.widget)
		widget.prepareForTransition("ToFront");
	$('#front').toggle();
	$('#back').toggle();
	if (window.widget)
		setTimeout('widget.performTransition();', 0);
}
var data, jVer;

function setData(){
	jVer = widget.preferenceForKey('useVersion') || '1.1.4';
	data = eval(jQuery.ajax({'type':'GET', 'url':'Parts/jquery/jquery-'+jVer+'.release/docs/data/jquery-docs-json.js', 'dataType': 'json', 'async': false}).responseText);
	$('#ver').html(jVer);
	$('#src').attr('href','http://code.jquery.com/jquery-'+jVer+'.js');
	var types = {
			'jQuery': 'A jQuery object.',
			'Object': 'A simple Javascript object. For example, it could be a String or a Number.',
			'String': 'A string of characters.',
			'Number': 'A numeric valid.',
			'Element': 'The Javascript object representation of a DOM Element.',
			'Map': 'A Javascript object that contains key/value pairs in the form of properties and values.',
			'Array&lt;Element&gt;': 'An Array of DOM Elements.',
			'Array&lt;String&gt;': 'An Array of strings.',
			'Function': 'A reference to a Javascript function.',
			'XMLHttpRequest': 'An XMLHttpRequest object (referencing a HTTP request).',
			'&lt;Content&gt;': 'A String (to generate HTML on-the-fly), a DOM Element, an Array of DOM Elements or a jQuery object'
		},
		type,
		title;

	for (var i=0; i<data.length; i++) {
		for (var j=0; j<data[i]['params'].length; j++) {
			type = data[i]['params'][j]['type'].split("|");
			for (var k=0; k<type.length; k++) {
				type[k] = '<a title="'+types[type[k]]+'">'+type[k]+'</a>';
			}
			data[i]['params'][j]['type'] = type.join(" | ");
		}
		if(types[data[i]['type']]){
			data[i]['type'] = '<a title="'+types[data[i]['type']]+'">' + data[i]['type'] + '</a>';
		}
	}
};

function findIt(elm){
	var term = elm.searchfield.value,
		html = "",
		container = jQuery('#drop');

	$(data).each(function(){
		if(this.name.toLowerCase().indexOf(term.toLowerCase()) >= 0 && (!this['private'])){
			html += createIt(this);
		}
	});

	$('#content').html(html);

	if(container.css('display')==='none'){
		container.slideDown();
	}

	$('strong').click(function(){
		$(this.parentNode).next().toggle().next().toggle()
	});

	event.preventDefault();
}

function getParams(o){
	var html = '';
	var n = $(o).size() - 1;
	$(o).each(function(i){
		html += '<span class="param-type">'+this.type+'</span>&nbsp;&nbsp;<a class="param-name" title="'+this.desc+'">'+this.name+'</a>';
		if(i<n)
			html += ', ';
	});
	return html;
}

function getExamples(o){
	var html = '<div class="examples">';

	for(var i=0; i<o.length; i++){
		html += '<h2>Example:</h2>';
		if(o[i]['desc']){
			html += '<p class="desc">'+o[i]['desc']+'</li></ul>';
		}
		html += '<dl>';
		if(o[i]['code']){
			html += '<dt>Code</dt>';	
			html += '<dd class="code"><pre>'+o[i]['code']+'</pre></dd>';
		}
		if(o[i]['before']){
			html += '<dt>Before</dt>';		
			html += '<dd class="before">'+o[i]['before']+'</li></dd>';
		}
		if(o[i]['result']){
			html += '<dt>After</dt>';
			html += '<dd class="result">'+o[i]['result']+'</dd>';
		}
		html += '</dl>';
	}
	html += '</div>';
	return html;
}

function setVersion(elm){
	widget.setPreferenceForKey(elm.value, 'useVersion');
	setData();
	var c = $('#drop');
	if(c.css('display')==='block')
		c.slideUp();
}

function createIt(o){
	var html = '<div class="func">';
	html += '<p class="title"><strong>'+o['name']+'</strong>';
	if(!o['property']){
		html += '( '+getParams(o['params'])+' )';
	}
	html += ' returns '+o['type']+'</p>';
	html += '<p class="short">'+o['short']+'</p>';
	html += '<div class="more">';
	html += '<p class="long">'+o['desc'].replace(/\n\n/g, "<span class=\"space\"> </span>")+'</p>';
	html += getExamples(o['examples']);
	html += '</div>';
	html += '</div>';
	return html;
}
if (window.widget) {
	widget.onremove = remove;
    widget.onhide = onhide;
    widget.onshow = onshow;
}

function onshow() {
    if (timerInterval == null) {
        timerInterval = setInterval('updateTime(true);', 1000);
    }
}

function onhide() {
    if (timerInterval != null) {
        clearInterval(timerInterval);
        timerInterval = null;
    }
}