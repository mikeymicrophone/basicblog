$(document).ready(function() {
	$("p.blue, p.red, p.grey, p.green, div.blue, div.red, div.grey, div.green, div.link, #message").corner();
	$("#instruction_field").html($("#instruction").html());
	
	if($("#expectation").html()) {
		$("#expectation_field").html($("#expectation").html());
		$("#bottombar div.bg").animate({ top: "0px" }, 500);
	}
});

function registerUser() {
	$("div.exchange div.out").animate({ left: "-=600", opacity: 0 }, 500);
	$("div.exchange div.in").animate({ left: "-=600", opacity: 1 }, 500);
	$("#registerlink").fadeOut(500, function() {
		$('input[name=email_register]').focus();
	});
}

function submitRegisterUser() {
	var fm = $('form[name=register]'), data = fm.serialize();
	$.post('action/register/save.php', data, function(data) {
		if (data > 0) message('Test saved successfully!');
		else message('An error has ocurred.', 'error');
	});
}

function login() {
	$("#loginstatus").animate({ top: 29 }, 500);
	$("#loginbar").animate({ top: 0 }, 500);
	$("#username")[0].focus();
}

function signout() {
	if (confirm('Are you sure?'))
		document.location.href = 'action/login/off.php';
}

function submit(id) {
	if($.browser.mozilla) var browser = "Mozilla";
	if($.browser.msie) var browser = "Internet Explorer";
	if($.browser.safari) var browser = "Safari";
	if($.browser.opera) var browser = "Opera";
	
	$.get("action/statistics/save.php", { result: id, engine: browser, version: $.browser.version, platform: navigator.platform }, function(data){
		if (data > 0) message('Test saved successfully!');
		else message('An error has ocurred.', 'error');
	});
}

function saveTest() {
	var fm = $('form[name=register]'), data = fm.serialize();
	$.post('action/test/save.php', data, function(data) {
		if (data > 0) message('Test saved successfully!');
		else message('An error has ocurred.', 'error');
	});
}

function message(msg, type, delay) {
	$('#message').removeClass('message-error message-success').animate(
		{ top: -5, opacity: .70 }, 1000
	)
	.html(msg).addClass(
		/error/.test(type) ? 'message-error' : 'message-success'
	);

	var t = setInterval(function() {
		$('#message').animate({ top: -70, opacity: 0 }, 1000); clearInterval(t);
	}, delay || 5000);
}
