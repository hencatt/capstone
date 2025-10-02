// Simulated load
$(document).ready(function () {
  $('#contents').hide();
  $("#loading-bar").css("width", "0%");
  setTimeout(() => {
    $("#loading-bar").css("width", "0%");
    setTimeout(() => $("#loading-bar").fadeOut(), 200);
  }, 100);

  setTimeout(function () {
    $('#loader').hide();
    $('#topBar').show();
    $('#sidebar').show()
    $('#contents').fadeIn(300);
  }, 150);
});