/**
 * traverseDir/addDir ,  add content from a directory on an apache server. And a some support routines.
 * @example 	$("#footer").addDir({dir:"Pix/tiny/",height:30,randomize:true})
 * @desc  add a randomized set of pictures to the footer
 * @param hash of the directory and the options to build the html.
 * @type jQuery
 *
 * @name addDir

 * @example 	$("#footer").traverseDir({urlHandler:callback})
 * @desc  add a randomized set of pictures to the footer, with a custom callback
 * @param hash of the directory and the options to build the html.
 * @type jQuery
 *
 * @name traverseDir

 * @example 		var hash = $.traverseDir({types: 'jpe?g|gif|png' ,dir:"flags/"})
 * @desc  add a randomized set of pictures to the footer
 * @param hash of the directory and the options to build the html.
 * @type hash
 *
 * @name traverseDir

 * @cat Directory Reading
 * @author Jake Wolpert (jakecigar@gmail.com)
 */
jQuery.fn.randomOne=function(){
	return this.eq( Math.floor(Math.random()*this.length))
}
jQuery.traverseDir=function(parm){
	var options = {
		types: 'jpe?g|gif|png'
	}
	jQuery.extend(options,parm);
	var hrefsRE = new RegExp ('href\s*=\s*"[^"]*(?:\.(?:' + options.types + ')|\/)"(.*)','gi')
	var urlRE = new RegExp ('(' + options.types + ')$','i')
	var hash ={}
	jQuery.ajax({
		async:false,
		url:options.dir, 
		dataType: "text",
		complete:function(res) {
			var data = res.responseText;
			var hrefs = data.match(hrefsRE);
			hrefs.shift(); // the parent directory
			for (var i = 0; i < hrefs.length; i++){
				var href=hrefs[i].toString()
				var rest =  href.match(/>(.*)<\/a>\s+(.*?)\s+(.*?)\s+(.*)$/i)
				var url =options.dir +rest[1]
				if (url.match(urlRE)){
					var fp = rest[1].split(".")
					if (!hash[fp[0]]) hash[fp[0]] = {}
					hash[fp[0]][fp[1]] = hash[fp[0]]["*"] = [url,i,rest[1],rest[2],rest[3],rest[4]]
				} else if (url.match(/\/$/i)){
					jQuery.extend(hash,jQuery.traverseDir(jQuery.extend({}, options, {dir:url})))
				}
			}
		}
	})
	return hash
}
jQuery.fn.traverseDir=function(parm){ //    <-- this is the main plugin
	var options = {
		types: 'jpe?g|gif|png',
		urlHandler: function(url,index){
			return  '<img src="' + url + '"/>';
		}
	}
	jQuery.extend(options,parm);
	var hrefsRE = new RegExp ('href\s*=\s*"[^"]*(?:\.(?:' + options.types + ')|\/)"(.*)','gi');
	var urlRE = new RegExp ('(' + options.types + ')$','i');
	return this.each(function(){
		var self = this;
		jQuery.get(options.dir,{}, function(data) {
			var hrefs = data.match(hrefsRE);
			hrefs.shift(); // the parent directory
			if (options.randomize) 
				hrefs.sort(function(){return Math.round(Math.random())-0.5})
			for (var i = 0; i < hrefs.length; i++){
				var href=hrefs[i].toString()
				var url =options.dir + href.match(/"(.*)"/)[1];
				var rest =  href.match(/>(.*)<\/a>\s+(.*?)\s+(.*?)\s+(.*)$/i);
				if (url.match(urlRE)){
					jQuery(self).append(options.urlHandler(url,i,rest[1],rest[2],rest[3],rest[4]));
				} else if (url.match(/\/$/i)){
					jQuery(self).traverseDir(jQuery.extend({}, options, {dir:url}));
				}
			}
		})
	})
}
jQuery.fn.traverseDir_little=function(parm){ //    <-- this is a trivial extension
	var options = {
		types: 'jpe?g|gif|png|bmp',
		urlHandler: function(url,index){
			return  '<img width="5%" src="' + url + '"/>';
		}
	}
	return this.traverseDir(jQuery.extend(options,parm));
}
jQuery.fn.addDir=function(parm){ //    <-- this is what I usually use
	var options = {
		types: 'jpe?g|gif|png|bmp',
		urlHandler: function(url,index){
			try {var img = new Image();img.src = url} catch(e){};
			var qurl = '"' + url + '"';
			var style = '';
			if (typeof options.hidden =="boolean" && options.hidden) style = ' style="display: none"'
			if (typeof options.hidden =="number" && options.hidden<=index) style = ' style="display: none"'
			var height =options.height ? ' height="' + options.height + '"' : '';
			var width  =options.width  ? ' width="' + options.width + '"' : '';
			var img = '<img src=' + qurl + (!options.wrap ? style : '')+ height + width + '/>';
			if (!options.naked)
				img = '<a href=' + qurl + '>' + img + '</a>';
			if (options.wrap)
				img = '<' + options.wrap + style +'>' + img + '</' + options.wrap + '>';
			if (options.nodeName == 'UL')
				img = '<li>' + img + '</li>';
			return  img;
		}
	}
	jQuery.extend(options,parm)
	return this.each(function(){
		jQuery(this).traverseDir(jQuery.extend(options,{nodeName:this.nodeName}))
	})
}
