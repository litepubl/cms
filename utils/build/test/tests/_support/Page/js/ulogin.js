(function($, litepubl) {
  'use strict';

litepubl.uloginclicked = false;
$(function() {
litepubl.authdialog.ulogin.ready(function() {
      setTimeout(function() {
$('[data-uloginbutton=twitter]').click();
      setTimeout(function() {
litepubl.uloginclicked = true;
}, 60);
}, 10);
});
});

})(jQuery, litepubl);
