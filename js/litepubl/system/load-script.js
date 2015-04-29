/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function ($) {
  'use strict';
  
  $.load_script = function( url, callback ) {
    return $.ajax({
      type: 'get',
      url: url,
      data: undefined,
      success: callback,
      dataType: "script",
      cache: true
    });
  };
  
}(jQuery));