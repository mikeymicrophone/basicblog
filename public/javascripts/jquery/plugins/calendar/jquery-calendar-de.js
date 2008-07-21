/* German initialisation for the jQuery calendar extension. */
/* Written by Milian Wolff (mail@milianw.de). */
$(document).ready(function(){
	popUpCal.regional['de'] = {clearText: 'Löschen', closeText: 'Schließen',
		prevText: '&lt;Zurück', nextText: 'Vor&gt;', currentText: 'Heute',
		dayNames: ['So','Mo','Di','Mi','Do','Fr','Sa'],
		monthNames: ['Januar','Februar','März','April','Mai','Juni',
		'Juli','August','September','Oktober','November','Dezember'],
		dateFormat: 'DMY.', firstDay: 0};
	popUpCal.setDefaults(popUpCal.regional['de']);
});