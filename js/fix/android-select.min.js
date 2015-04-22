/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function ($, document) {
  'use strict';

$(document).ready(function() {
  var nua = navigator.userAgent
  var isAndroid = (nua.indexOf('Mozilla/5.0') > -1 && nua.indexOf('Android ') > -1 && nua.indexOf('AppleWebKit') > -1 && nua.indexOf('Chrome') === -1)
  if (isAndroid) {
$.fn.fixAndroid = function() {
    this.find('select.form-control').removeClass('form-control').css('width', '100%');
return this;
};

$("body").fixAndroid();
} else {
$.fn.fixAndroid = function() {
return this;
};
  }

});
}(jQuery, document));