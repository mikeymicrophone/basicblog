var DEBUG = true;
(function($) { // main code
	var version = 'v0.2';
	$(function(){ // ready code
		if (!("console" in window) || !("firebug" in console)){
			if (DEBUG)
				$('<div id="DEBUG" style ="background-color:#c1ebeb;position: fixed;left: 0px;height: 0px;overflow: scroll;bottom: 0px;z-index: 1000;width: 100%;border-top: 5px solid #0000FF"><ol></ol></div>')
				.attr('title','Click to show more, shift click to show less, command(or alt or meta) click to clear')
				.click(clicker)
				.appendTo(document.body);
		}
	});
	var clicker = function(e){
		if (e.ctrlKey||e.metaKey||e.altKey) {
			$("ol",this).html('');
			this.height = 0
		}
		this.height = Math.max(0,(this.height || 0) +(e.shiftKey ?-100 : 100));
		$(this).animate({height: this.height}, "slow");
		this.scrollTop = this.scrollHeight
	};
	var qw = function(s){return '"' + s.replace(/"/g,'&quot;') + '"'};
	var qi = function(s){return ( 'string' == typeof s) ? qw(s) : s};
	var debug = function(msg){ $('#DEBUG ol').append( '<li>' + msg + '</li>' )};
	$.fn.debug = function(message) {
		if (DEBUG) {
			$.log.apply(this,[message, ':',this]);
			$("#DEBUG").each(function() {this.scrollTop = this.scrollHeight})
		}
		return this
	};
	$.log =$.fn.log = function() {
		if(DEBUG )
			if("console" in window && 'firebug' in console)
				console.debug.apply('',arguments);
			else
				debug($.map(arguments,function(o,i){
					return jsO(o,true)
				}).join(' '));
		return this
	};
	if (!(("console" in window) && (("open" in console)||("firebug" in console)))){
		window.console = 
		{timer:{}
		,time:function(timer) {console.timer[timer] = new Date()}
		,timeEnd:function(timer) {console.timer[timer] && $.log('timer',timer + ':' , (new Date()-console.timer[timer]) , 'ms');delete console.timer[timer]}
		};
		$.each("log,debug,info,warn,error,assert,dir,dirxml,group,groupEnd,count,trace,profile,profileEnd".split(/,/), function(i,o){
			window.console[o] = $.log
		})
	};
	$.fn.clicklogger = function(url){
		return this.click(function(e) {
			e.stopPropagation();
			var event = {nodeName:this.nodeName,id:this.id,className:this.className,innerHTML:this.innerHTML,text:$(this).text()};
			for (var x in e){
				if ((typeof e[x]).match(/number|string|boolean/) && x != x.toUpperCase())
					event[x] = e[x]
			};
			$.get(url,event)
		})
	};
	$.fn.xhtml = function () {return $.xhtml(this[0])};
	$.xhtml = function(obj) { // dump the dom back to xhtml text
		if (!obj) return "(null)";
		var res = "";
		var tag = obj.nodeName.toLowerCase();
		var tagShow = tag.charAt(0) != "#";
		if (tagShow) res += '<' + tag;
		if (obj.attributes) 
			res += $.map(obj.attributes,function(attr){
				if (attr.specified && attr.name.charAt(0) != '$') 
					return ' '+attr.name.toLowerCase() + '=' + qw(attr.value) 
			}).join('');
		if (tagShow && obj.nodeValue == null && !obj.hasChildNodes())
			return res+" />";
		if (tagShow)
			res+= ">";
		if (obj.nodeType == 8)
			res += "<!-- " + obj.nodeValue + " -->";
		else if (obj.nodeValue != null)
			res +=  obj.nodeValue;
		if (obj.hasChildNodes && obj.hasChildNodes())
			res += $.map(obj.childNodes,function(child){return $.xhtml(child)}).join('');
		if (tagShow)  res += '</' + tag + '>';
		return res
	};
	var ep = function(e,type){
		var res = type+":{";
		var re	= type.match(/change/i) ? /ZZ/
				: type.match(/submit/i) ? /K/
				: type.match(/key/i) ? /K|I|C/
				: type.match(/click/i) ? /X|Y|K/
				: /./;
		for (var x in e){
			if (!re.test(x))
				continue;
			var tex = typeof e[x];
			if (tex.match(/number|string|boolean/) && x != x.toUpperCase())
				res+= x + ':' + qi(e[x]) +', '
		};
		return res+"}"
	};
	var abbr = function(o,bare){
		var s = o.toString().replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;");
		if (bare) 
			return s;
		if (s.length>25)
			return qw(s.substr(0,25) + "…");
		return  qw(s)
	};
	var tiv = function(obj,title,pop) {
		var $obj = $(obj);
		try{
			var res = $obj[0].tagName.toLowerCase();
			if ($obj.attr('id')) res+="#" + $obj.attr('id').bold();
			if ($obj.is(":input")) res+= " " +abbr($obj.val(),true).italics();
			if ($obj.attr('className')) res+="." + $obj.attr('className').fontcolor('red');
		}catch(e){
			var res = obj.nodeValue||obj
		}
		return "<span title=" + qw(pop) + ">" +res + "</span>"
	};
	var typeOf = function (o){return (/\[object *(.*)\]/.exec(Object.prototype.toString.apply(o))[1])};
	var jsO = function(obj,bare) {
			if (typeof obj != "object")
				return bare || typeof obj != "string" ? obj : abbr(obj,false);
			if (obj.nodeName)
				return $.xhtml(obj).replace(/&/g,"&amp;").replace(/</g,"&lt;").replace(/>/g,"&gt;");
			if (obj.jquery )
				return "[" + $.map(obj,function(o){return tiv(o,'jQuery',jsO(o,false)).fontcolor('blue')}).join(',') + ']';
			if (obj.constructor == Array)
				return "[" + $.map(obj,function(o){return jsO(o,false)}).join(',') + ']';
			if (typeOf(obj).match(/Event/) || (obj.screenX && $.browser.msie))
				return ep(obj,obj.type);
			var res = [];
			$.each(obj,function(i,o){
				try{
					res.push(i + ":" + jsO(o,false))
				} catch(e) {
					res.push(i+':'+qw(typeOf(o)))
				}
			});
			return '{' + res.join(',') + '}'
	}
}(jQuery));
