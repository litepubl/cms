(function($, document, litepubl) {
  'use strict';

$(function() {
var tabs = litepubl.tabs;
// to use in codecept: $I->waitForJS('return litepubl.tabs.flagLoaded');
tabs.flagLoaded = true;

$(document)
.on('loaded' + tabs.namespace, function() {
tabs.flagLoaded = true;
})
.on('beforeLoad' + tabs.namespace, function() {
tabs.flagLoaded = false;
});
});

})(jQuery, document, litepubl);