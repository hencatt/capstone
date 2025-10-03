// Simulated load
$(document).ready(function () {
setTimeout(() => {
  $('#contents').css("display", "block");
  $('#contents').hide();
}, 100);
setTimeout(() => {
  $('#contents').fadeIn();
}, 150);
});