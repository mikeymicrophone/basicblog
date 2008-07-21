/* 
  $(expr).sequence(function() { console.log(this.id) }); 
    will run through each item in the jQuery object and print its id to the console.

  $(expr).sequence(function() { console.log(this.id) }, function() {
  console.log("Done") });
    will do the same as above and when completely done, print "Done"

  $.sequence.run(function() { // first function }, function() { // second
  function }); 
    will run the first function and then the second one without locking up the browser

  $.sequence.runArray([1,2,3], function() { console.log(this) }); 
    will print "1", then "2", then "3"

  $.sequence.runArray([1,2,3], function() { console.log(this) }, function() {
  console.log("Done") }); 
    will do the same as above and when completely done, print "Done"
    
  $.sequence.runNTimes(5, function() { console.log(this), function() { console.log("Done") }; 
    will print 0, 1, 2, 3, 4 and then "Done"
*/

jQuery.sequence = {
  setTimeoutH: function(f, t) {
    var params = (arguments.length == 3 && arguments[2].constructor == Array) ? arguments[2] : jQuery.merge([], arguments).slice(2);
    window.setTimeout(function() { f.apply(f, params); }, t);
  },

  run: function() {
    if(arguments[0]) arguments[0]();
    if(arguments[1]) jQuery.sequence.setTimeoutH(arguments.callee, 1, jQuery.merge([], arguments).slice(1));
  },

  runArray: function(array, fn, whenDone) {
    if(array[0]) fn.call(array[0], array);
    if(array[1]) jQuery.sequence.setTimeoutH(arguments.callee, 1, jQuery.merge([], array).slice(1), fn, whenDone);
    else if(whenDone) whenDone();
  },
  
  runNTimes: function(times, fn, whenDone, i) {
    n = n || 0;
    if(times > 0) fn.call(n);
    if(times > 1) jQuery.sequence.setTimeoutH(arguments.callee, 1, times - 1, fn, whenDone, n + 1);
    else if(whenDone) whenDone();
  }

};

jQuery.fn.sequence = function(fn, whenDone) {
  jQuery.sequence.runArray(this, fn, whenDone);
  return this;
};
