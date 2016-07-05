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
var tabs = litepubl.tabs;
if (!("flagLoaded" in tabs)) {
// to use in codecept: $I->waitForJS('return litepubl.tabs.flagLoaded');
tabs.flagLoaded = false;
tabs.ajax = false;

$(document)
.on('loaded' + tabs.namespace, function() {
tabs.flagLoaded = true;
tabs.ajax = false;
})
.on('beforeLoad' + tabs.namespace, function() {
tabs.flagLoaded = false;
tabs.ajax = true;
})
.on('activated' + tabs.namespace, function() {
if (!tabs.ajax) {
tabs.flagLoaded = true;
}
});

}
});

})(jQuery, document, litepubl);