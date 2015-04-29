/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function( $){
  'use strict';
  
  $.fn.editorheight = function() {
    var state = '';
    return this
    .removeAttr("row")
    .css("minHeight", 20)
    .on("input propertychange keyup", function(){
      if (state == 'expired') {
        state = 'wait';
        var editor = $(this);
        editor.height(20);
        editor.height(this.scrollHeight);
      } else if (state != 'timer'){
        state = 'timer';
        setTimeout(function () {
          state = 'expired';
        }, 1000);
      }
    });
  };
  
})( jQuery);