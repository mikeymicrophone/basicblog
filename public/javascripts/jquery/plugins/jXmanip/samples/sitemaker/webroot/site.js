$(function() {
  $('.navLinks a').click(function() {
    loc = $(this).attr('href').substring(1);
    $('.main > div:not(:hidden)').slideUp('slow', function() {
      $('.'+ loc).slideDown('slow');
    });
    return false;
  });  
  $('.main > div:gt(0)').hide();
});
