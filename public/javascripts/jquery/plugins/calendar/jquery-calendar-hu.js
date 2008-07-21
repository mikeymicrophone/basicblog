/* Hungarian initialisation for the jQuery calendar extension. */
/* Written by Istvan Karaszi (jquerycalendar@spam.raszi.hu). */
$(document).ready(function(){
	popUpCal.regional['hu'] = {clearText: 'törlés', closeText: 'bezárás',
		prevText: '&laquo;&nbsp;vissza', nextText: 'előre&nbsp;&raquo;', currentText: 'ma',
		dayNames: ['V', 'H', 'K', 'Sze', 'Cs', 'P', 'Szo'],
		monthNames: ['Január', 'Február', 'Március', 'Április', 'Május', 'Június',
		'Július', 'Augusztus', 'Szeptember', 'Október', 'November', 'December'],
		dateFormat: 'YMD-', firstDay: 1};
	popUpCal.setDefaults(popUpCal.regional['hu']);
});
