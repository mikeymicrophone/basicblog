/* Italian initialisation for the jQuery calendar extension. */
/* Written by Apaella (apaella@gmail.com). */
$(document).ready(function(){
	popUpCal.regional['it'] = {clearText: 'Svuota', closeText: 'Chiudi',
		prevText: '&lt;Prec', nextText: 'Succ&gt;', currentText: 'Oggi',
		dayNames: ['Do','Lu','Ma','Me','Gio','Ve','Sa'],
		monthNames: ['Gennaio','Febbraio','Marzo','Aprile','Maggio','Giugno',
		'Luglio','Agosto','Settembre','Ottobre','Novembre','Dicembre'],
		dateFormat: 'DMY/', firstDay: 0};
	popUpCal.setDefaults(popUpCal.regional['it']);
});
