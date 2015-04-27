/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
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