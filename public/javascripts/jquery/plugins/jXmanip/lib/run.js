// Init code
load('env.js');
window.location = "blank.html";
load("jquery.js");
var _globals = {};

function jXmanip_parse_args() {
	rawargs = readFile('a');
	rawargs = rawargs.substring(0, rawargs.length-1).split(' ');
	formatargs = {};
	for (f = 0; f < rawargs.length; f++) {
		if (rawargs[f].substring(0, 1) == '-') {
			formatargs[rawargs[f].substring(1)] = rawargs[++f].substring(-5);
		}
	}
	if (formatargs.type == undefined) {
		print("\n-type needs to be included.  Call ./scripts/run --help for help\n");
		_globals.thrown = true;
		return;
	}
	if (formatargs.file == undefined) {
		print("\n-file needs to be included.  Call ./scripts/run --help for help\n");
		_globals.thrown = true;
		return;
	}
	_globals.type = '../main/'+ formatargs.type +'/';
	_globals.args = formatargs;
}

function jXmanip_load_parse_conf() {
	// LOAD CONF
	var conf = $(readFile(_globals.type +'CONF.xml'));
	
	_globals['conf'] = {};
	// PARSE CONF
	conf.children().each(function() {
		_globals['conf'][$(this).attr('name')] = $(this).attr('value');
	});
}

function jXmanip_run() {
	if (_globals.conf.html != window.undefined) {
		window.location = _globals.type + _globals.conf.html;
	}
	load(_globals.type + _globals.conf.make);
	_globals.xml = readFile('../'+ _globals.args.file);
	run(_globals.xml);
}

function jXmanip_output(output, file) {
    request = new XMLHttpRequest;
    request.open("PUT", (file || _globals.args.output));
    request.send(output);
}

jXmanip_parse_args();
if (_globals.thrown != true)
	jXmanip_load_parse_conf();

if (_globals.thrown != true)
	jXmanip_run();

