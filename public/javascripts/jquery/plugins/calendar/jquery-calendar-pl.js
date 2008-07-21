/* Polish initialisation for the jQuery calendar extension. */
/* Written by Jacek Wysocki (jacek.wysocki@gmail.com). */
$(document).ready(function(){
	popUpCal.regional['pl'] = {clearText: 'Czyść', closeText: 'Zamknij',
		prevText: '&lt;Poprzedni', nextText: 'Następny&gt;', currentText: 'Teraz',
		dayNames: ['Pn','Wt','Śr','Czw','Pt','So','Nie'],
		monthNames: ['Styczeń','Luty','Marzec','Kwiecień','Maj','Czerwiec',
		'Lipiec','Sierpień','Wrzesień','Październik','Listopad','Grudzień'],
		dateFormat: 'DMY/', firstDay: 0};
	popUpCal.setDefaults(popUpCal.regional['pl']);
});
