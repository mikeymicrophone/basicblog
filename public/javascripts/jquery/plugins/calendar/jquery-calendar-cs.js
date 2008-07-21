/* Czech initialisation for the jQuery calendar extension. */
/* Written by Tomas Muller (tomas@tomas-muller.net). */
$(document).ready(function(){
	popUpCal.regional['cs'] = {clearText: 'Smazat', closeText: 'Zavøít', 
		prevText: '&lt;Døíve', nextText: 'Pozdìji&gt;', currentText: 'Nyní',
		dayNames: ['Ne','Po','Út','St','Èt','Pá','So'],
		monthNames: ['Leden','Únor','Bøezen','Duben','Kvìten','Èerven',
		'Èervenec','Srpen','Záøí','Øíjen','Listopad','Prosinec'],
		dateFormat: 'DMY.', firstDay: 0};
	popUpCal.setDefaults(popUpCal.regional['cs']);
});