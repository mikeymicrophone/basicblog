function run(stuff) {
	var $stuff = $(stuff);
	$('.header').append('<h1>'+ $stuff.find('meta sitename').html() +'</h1> <h3>'+ $stuff.find('meta siteslogan').html() +'</h3>');
	$('.footer').append($stuff.find('meta footer').html());
	$stuff.find('content').each(function() {
		$('.navLinks').append('<li><a href="#'+ sanid([$(this).attr('title')]) +'-area">'+ $(this).attr('title') +'</a></li>');
		$('.main').append('<div class="'+ sanid([$(this).attr('title')]) +'-area"><h4>'+ $(this).attr('title') +'</h4>'+ $(this).html() +'</div>');
	});
    jXmanip_output($().html(), 'webroot/index.html');
}
function sanid(val) {
	var sanitizedArray = [];
	for (i = 0; i < val.length; i++) {
		sanitizedArray.push(val[i].toLowerCase().replace(/[^0-9a-z]/, '_'));
	}
	return sanitizedArray.join('-');
};