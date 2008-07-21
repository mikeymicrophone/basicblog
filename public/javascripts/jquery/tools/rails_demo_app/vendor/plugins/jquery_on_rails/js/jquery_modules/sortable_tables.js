$.meta.setType("class");
jQuery(function($) {
  $("table.sortable_table").each(function() {
    $(this).tableSorter($(this).data());
  })
})