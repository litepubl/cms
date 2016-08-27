/**
 * Lite Publisher CMS
 *
 *  copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link      https://github.com/litepubl\cms
 *  version   7.04
  */

(function(window) {
  'use strict';
  window.get_get = function(name, url) {
    if (url) {
      var q = url.substring(url.indexOf('?') + 1);
    } else {
      var q = window.location.search.substring(1);
    }

    var vars = q.split('&');
    for (var i = 0, l = vars.length; i < l; i++) {
      var pair = vars[i].split('=');
      if (name == pair[0]) return decodeURIComponent(pair[1]);
    }
    return false;
  };

}(window));