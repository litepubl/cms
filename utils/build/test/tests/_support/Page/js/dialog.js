(function($, document, litepubl) {
  'use strict';

$(function() {
if (!("dialogOpened" in tabs)) {
litepubl.dialogOpened = false;

$(document)
.on('opened.dialog.litepubl', function() {
litepubl.dialogOpened = true;
})
.on('remove.dialog.litepubl', function() {
litepubl.dialogOpened = false;
});

}
});

})(jQuery, document, litepubl);