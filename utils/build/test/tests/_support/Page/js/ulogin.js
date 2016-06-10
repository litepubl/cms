(function($, litepubl) {
  'use strict';

litepubl.uloginopened  = false;
litepubl.ulog = '';

$.ready2(function() {
litepubl.ulog += 'ready2\n';

litepubl.authdialog.ulogin.onready = function() {
litepubl.ulog += 'clicked\n';
$('[data-uloginbutton=mailru]').click();
};

litepubl.authdialog.ulogin.onopened = function() {
litepubl.ulog += 'opened\n';
litepubl.uloginopened = true;
};

litepubl.authdialog.ulogin.onclosed = function() {
litepubl.ulog += 'closed\n';
//litepubl.uloginopened = false;
};

if (!litepubl.uloginopened && ('uLogin' in window)) {
litepubl.ulog += 'click2\n';
$('[data-uloginbutton=mailru]').click();
}
});

})(jQuery, litepubl);
