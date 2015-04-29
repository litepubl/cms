/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function ($, document) {
  'use strict';
  
  $.onEscape = function (callback) {
    if (!$.isFunction(callback)) return;
    
    $(document).off('keydown.onEscape').on('keydown.onEscape',function(e){
      if (e.keyCode == 27) {
        callback();
        e.preventDefault();
        $(document).off('keydown.onEscape');
      }
    });
  };
  
}(jQuery, document));