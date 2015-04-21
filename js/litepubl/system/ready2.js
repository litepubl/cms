/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function ($, document) {
  'use strict';
  
  var ready2callback = false;
  $.ready2 = function(fn) {
    if (!ready2callback) {
      ready2callback =  $.Deferred();
      var ready2resolve = function() {
        setTimeout(function() {
          ready2callback.resolve();
        }, 0);
      };
      
      if ($.isReady) {
        $(document).ready(ready2resolve);
      } else {
        //.on('ready') call after $(document).ready
        $(document).on('ready', ready2resolve);
      }
    }
    
    ready2callback.done(fn);
  };
  
}(jQuery, document));