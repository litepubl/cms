/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

(function( $){
  'use strict';
  
  $.fn.editorheight = function() {
return this.on("focus.height", function() {
$(this).css("height", "12em");
})
.on("blur.height", function() {
$(this).css("height", "2em");
})
.trigger("blur.height");
};

})( jQuery);