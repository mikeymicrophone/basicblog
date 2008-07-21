/* French initialisation for the jQuery calendar extension. */
/* Written by Keith Wood (kbwood@iprimus.com.au). */
$(document).ready(function(){
	popUpCal.regional['fr'] = {clearText: 'Effacer', closeText: 'Fermer', 
		prevText: '&lt;Préc', nextText: 'Proch&gt;', currentText: 'En cours',
		dayNames: ['Di','Lu','Ma','Me','Je','Ve','Sa'],
		monthNames: ['Janvier','Février','Mars','Avril','Mai','Juin',
		'Juillet','Août','Septembre','Octobre','Novembre','Décembre'],
		dateFormat: 'DMY/', firstDay: 0};
	popUpCal.setDefaults(popUpCal.regional['fr']);
});