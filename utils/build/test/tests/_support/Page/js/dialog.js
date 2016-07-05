/**
 * Lite Publisher CMS
 *  copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link https://github.com/litepubl\cms
 *  version 7.00
 *
 */

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