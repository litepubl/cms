/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

(function( $){
  'use strict';
  
  $.fn.editorheight = function() {
return this
.on("focus.height", function() {
var self = $(this);
var height = self.data("css.height", self.css("height"));
if (!height) {
self.data("css.height", height);
}

self.css("height", "14em");
})
.on("blur.height", function() {
var self = $(this);
var height = self.data("css.height", self.css("height"));
if (height) {
self.css("height", height);
}
});
};

})( jQuery);