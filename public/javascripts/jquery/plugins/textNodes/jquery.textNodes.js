(function($) { // main code
	var version = 'Beta v0.3';
/*Send comments to jakecigar@gmail.com 

textNodes are the text nodes that jquery tries to ignore. This plugin makes them easier to use.

When using some of the methods here, the chains include the textNodes, instead of normal nodes.
So, you have to be careful about using these nodes with most methods. 
filter(function), and  each() are notable exceptions.

there are several ways to go back to normal nodes ('enders')

	.end() pops the chain stack, so you are where you were.

	.wrap().parent() converts textNodes into normal nodes. and you can continue the chain with the wrapped elements
	.span() is a shortcut for .wrap('<span/>').parent()

The main methods here are 
	.textNodes(), .match(), .split() . All require 'enders'

	.replace() works on textNodes or normal dom, returning the changed textnodes or the original jQuery. It requires an 'ender'

Others are 
	.hook(), and the 
	.maketags() group. These do not require 'enders', as they return the original jQuery.

*/
	$.textNode = function(s) {return $(document.createTextNode(s))};
	// create a $ containing a single textNode, very useful for appendTo()
	
	$.fn.extend({
		textNodes: function(deep) { // requires an ender
		// return the text noded under a dom node, either deep or shallow (one level down)
			var texts=[];
			this.each(function(){
				var children =this.childNodes;
				for (var i = 0; i < children.length; i++){
					var child = children[i];
					if (child.nodeType == 3) 
						texts.push(child);
					else if (deep && child.nodeType == 1)
						[].push.apply(texts,$(child).textNodes(deep,true));
				}
			});
			return arguments[1] ? texts: this.pushStack(texts);
		},
		match: function(re) { // requires an ender
		// returns the substrings of the textNodes of the $ that match the regular expression, as textNodes
			var texts=[];
			this.textNodes(true).each(function(){
				var res,val = this.nodeValue;
				var ranges=[0];
				while (res = re.exec(val)){
					var lastMatch = res[0];
					ranges.push(res.index,res.index+res[0].length);
					if (!re.global) break
				};
				ranges.push(val.length)
				if (ranges.length>2){
					if (val == lastMatch)
						texts.push(this);
					else{
						var tpn = this.parentNode;
						for (var i=0;i<ranges.length -1;i++){
							var t = document.createTextNode(val.substring(ranges[i],ranges[i+1]));
							tpn.insertBefore(t,this);
							if (i % 2)texts.push(t)
						};
						tpn.removeChild(this)
					}
				}
			});
			return this.pushStack( texts );
		},
		split: function(re) { // requires an ender
		// splits textnodes into more textNodes by a regular expression. 
		//The regular expression should be a simple one that when split&joined it will return the original.
		// the default regular expression is a simple word split.
			var texts=[];
			var re = re || $.browser.opera ? /(\W+)/ : /\b/ ;
			this.each(function(){
				var tpn = this.parentNode;
				var splits = this.nodeValue.split(re);
				for (var i=0;i<splits.length;i++){
					var t = document.createTextNode(splits[i]);
					tpn.insertBefore(t,this);
					texts.push(t)
				};
				tpn.removeChild(this)
			});
			return this.pushStack( texts );
		},
		replace: function(re,f) { // requires an ender
		// replaces text in place. either in a normal $, or a textNodes $.
		// returns the changed textNodes if passed textNodes or the original $
			var texts=[], tNodes=false;
			this.each(function(){
				var $this = $(this);
				if (this.nodeType == 3){
					tNodes=true;
					texts.push(this.parentNode.insertBefore(document.createTextNode(this.nodeValue.replace(re,f)),this));
					this.parentNode.removeChild(this)
				}else
					texts.push($this.textNodes(true).replace(re,f).end().end())
			});
			return this.pushStack(tNodes ? texts: this)
		},
		hook: function(hash,className) {
		// traverses the textNodes word by word, wrapping a <span class...> around the words found in the hash.
			this.textNodes(true).split().each(function(){
				if (this.nodeValue in hash)
					$(this).wrap('<span class="'+(className||'hooked')+'"/>')
			});
			return this;
		},
		maketags: function(hash,tag,attr) {
		// traverses the textNodes word by word, wrapping a tag with one attribute(the value in the hash) around the words found in the hash.
			this.textNodes(true).split().each(function(){
				if (this.nodeValue in hash)
					$(this).wrap(tag).parent().attr(attr,hash[this.nodeValue])
			});
			return this;
		},
		acronyms: function(hash) { return this.maketags(hash,'<acronym/>','title')},
		links: function(hash) { return this.maketags(hash,'<a/>','href')},
		classes: function(hash) { return this.maketags(hash,'<span/>','class')},
		span: function() {return this.wrap('<span/>').parent()},
		// span wraps a textNode into a normal $ span node. span is an ender
		childNodes: function() {return this.pushStack( $.map( this, "$.makeArray(a.childNodes)"))}, // requires an ender
		// childNodes returns all nodes  one level deep.
	})
}(jQuery));

