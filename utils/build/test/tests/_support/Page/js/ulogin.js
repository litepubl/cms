(function($, litepubl) {
  'use strict';

litepubl.uloginopened  = false;
$(function() {
litepubl.authdialog.ulogin.onready = function() {
$('[data-uloginbutton=mailru]').click();
};

litepubl.authdialog.ulogin.onopened = function() {
litepubl.uloginopened = true;
};
});

})(jQuery, litepubl);
