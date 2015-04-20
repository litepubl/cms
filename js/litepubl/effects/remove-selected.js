/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function( $){
  'use strict';
  
  $.fn.removeSelected = function() {
var selected = this.find(":selected");
if (!selected.length) return false;

var next = selected.next();
if (next.length) {
next.prop("selected", true);
} else {
var prev = selected.prev();
if (prev.length) prev.prop("selected", true);
}

selected.remove();
return selected;
};

})( jQuery);