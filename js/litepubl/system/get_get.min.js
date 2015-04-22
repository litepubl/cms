/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function (window) {
  'use strict';
  window.get_get=  function (name, url) {
    if (url) {
      var q = url.substring(url.indexOf('?') + 1);
    } else {
      var q = window.location.search.substring(1);
    }
    
    var vars = q.split('&');
    for (var i=0, l=  vars.length; i < l; i++) {
      var pair = vars[i].split('=');
      if (name == pair[0]) return decodeURIComponent(pair[1]);
    }
    return false;
  };
  
}(window));