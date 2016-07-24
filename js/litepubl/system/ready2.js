/**
 * Lite Publisher CMS
 *
 *  copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link      https://github.com/litepubl\cms
 *  version   7.01
 */

(function($) {
  'use strict';

  var ready2callback = false;
  $.ready2 = function(fn) {
    if (!ready2callback) {
      ready2callback = $.Deferred();
      $(function() {
        setTimeout(function() {
          ready2callback.resolve();
        }, 0);
      });
    }

    ready2callback.done(fn);
  };

}(jQuery));