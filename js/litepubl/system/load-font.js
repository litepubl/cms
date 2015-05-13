/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

(function ($) {
  'use strict';
  
  $.load_font = function(font_name, class_name, css_url) {
    $.load_css(css_url, function() {
    var observer = new FontFaceObserver(font_name, {weight: 400});
      observer .check().then(function () {
        $("body").addClass(class_name);
      });
    });
  };
  
}(jQuery));