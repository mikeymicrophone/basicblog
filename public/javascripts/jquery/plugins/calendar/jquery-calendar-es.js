/* Inicialización en español para la extensión 'calendar' para jQuery. */
/* Traducido por Vester (xvester@gmail.com). */
$(document).ready(function(){
	popUpCal.regional['es'] = {clearText: 'Limpiar', closeText: 'Cerrar',
		prevText: '&lt;Ant', nextText: 'Sig&gt;', currentText: 'Hoy',
		dayNames: ['Do','Lu','Ma','Mi','Ju','Vi','S&aacute;'],
		monthNames: ['Enero','Febrero','Marzo','Abril','Mayo','Junio',
		'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'],
		dateFormat: 'DMY/', firstDay: 0};
	popUpCal.setDefaults(popUpCal.regional['es']);
});