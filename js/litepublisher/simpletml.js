/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function( $ ){
  $.simpletml = function(tml, view) {
tml = tml.replace(/[%\u007b]{2}(\w*)\.(\w*)[%\u007d]{2}/gim, function(str, obj, prop, offset, src) {
      if ((obj in view) && (typeof view[obj] === "object") && (prop in view[obj])) return view[obj][prop];
      return str;
    });
    
return tml.replace(/[%\u007b]{2}(\w*)[%\007d]{2}/gim, function(str, prop, offset, src) {
      if (prop in view) return view[prop];
      return str;
    });
  };
  
  $.replacetml = function(obj, view) {
    var prop = "";
    for (prop in obj) {
      if (typeof obj[prop] == "string") {
        obj[prop] = $.simpletml(obj[prop], view);
      }
    }
  };
})( jQuery );