/* Russian (UTF-8) initialisation for the jQuery calendar extension. */
/* Written by Andrew Stromnov (stromnov@gmail.com). */
$(document).ready(function(){
	popUpCal.regional['ru'] = {clearText: 'Очистить', closeText: 'Закрыть',
		prevText: '&lt;Пред', nextText: 'След&gt;', currentText: 'Сегодня',
		dayNames: ['Вс','Пн','Вт','Ср','Чт','Пт','Сб'],
		monthNames: ['Январь','Февраль','Март','Апрель','Май','Июнь',
		'Июль','Август','Сентябрь','Октябрь','Ноябрь','Декабрь'],
		dateFormat: 'DMY.', firstDay: 1};
	popUpCal.setDefaults(popUpCal.regional['ru']);
});