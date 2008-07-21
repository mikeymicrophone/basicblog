$.meta.setType("class");
jQuery(function($) {
  $("div.tab_container").each(function() {
    var data = $(this).data();
    var start = data.start;
    delete data.start;
    $(this).tabs(start, data);
  })
})