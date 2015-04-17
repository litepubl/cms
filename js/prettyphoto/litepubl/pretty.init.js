/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

;(function ($, document) {
  $(document).ready(function() {
    $("a[rel^='prettyPhoto']").prettyPhoto({
      social_tools: false
    });
    
    $("a[href^='http://youtu.be/'], a[href^='http://www.youtube.com/watch?v=']").prettyPhoto({
      social_tools: false
    });
  });
}(jQuery, document));