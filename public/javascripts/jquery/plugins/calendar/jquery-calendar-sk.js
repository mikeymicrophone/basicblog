/* Slovak initialisation for the jQuery calendar extension. */
/* Written by Vojtech Rinik (vojto@hmm.sk). */
$(document).ready(function(){
	popUpCal.regional['sk'] = {clearText: 'Zmazať', closeText: 'Zavrieť', 
		prevText: '&lt;Predchádzajúci', nextText: 'Nasledujúci&gt;', currentText: 'Dnes',
		dayNames: ['Ne','Po','Ut','St','Št','Pia','So'],
		monthNames: ['Január','Február','Marec','Apríl','Máj','Jún',
		'Júl','August','September','Október','November','December'],
		dateFormat: 'DMY.', firstDay: 0};
	popUpCal.setDefaults(popUpCal.regional['sk']);
});
