/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

(function($, document) {
  'use strict';

  $.onEscape = function(callback) {
    if (!$.isFunction(callback)) return;

    $(document).off('keydown.onEscape').on('keydown.onEscape', function(e) {
      if (e.keyCode == 27) {
        callback();
        e.preventDefault();
        $(document).off('keydown.onEscape');
      }
    });
  };

}(jQuery, document));