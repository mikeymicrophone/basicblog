/* Chinese initialisation for the jQuery calendar extension. */
/* Written by Cloudream (cloudream@gmail.com). */
$(document).ready(function(){
	popUpCal.regional['zh-CN'] = {clearText: '清除', closeText: '关闭',
		prevText: '&lt;上月', nextText: '下月&gt;', currentText: '今天',
		dayNames: ['日','一','二','三','四','五','六'],
		monthNames: ['一月','二月','三月','四月','五月','六月',
		'七月','八月','九月','十月','十一月','十二月'],
		dateFormat: 'YMD-', firstDay: 1};
	popUpCal.setDefaults(popUpCal.regional['zh-CN']);
});
