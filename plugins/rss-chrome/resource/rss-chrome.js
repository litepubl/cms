/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

;(function ($, document, window) {
  if (typeof(chrome) !== 'undefined'){
    $(document).ready(function() {
      $(document).on("click.rss", "a[href$='.xml']", function() {
        var url = $(this).attr("href");
        $.confirmbox(lang.dialog.confirm, lang.rsschrome.warn, lang.rsschrome.follow, lang.dialog.cancel,
        function(index) {
          if (index == 0) window.location = url;
        });
        
        return false;
      });
    });
  }
}(jQuery, document, window));