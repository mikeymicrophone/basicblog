/* Swedish initialisation for the jQuery calendar extension. */
/* Written by Anders Ekdahl ( anders@nomadiz.se). */
$(document).ready(function(){
    popUpCal.regional['sv'] = {clearText: 'Rensa', closeText: 'Stäng',
        prevText: '&laquo;Förra', nextText: 'Nästa&raquo;', currentText: 'Idag', 
        dayNames: ['Sö','Må','Ti','On','To','Fr','Lö'],
        monthNames: ['Januari','Februari','Mars','April','Maj','Juni', 
        'Juli','Augusti','September','Oktober','November','December'],
        dateFormat: 'YMD-', firstDay: 0};
    popUpCal.setDefaults(popUpCal.regional['sv']); 
});
