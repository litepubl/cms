/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

(function($, document) {
  'use strict';

  var ready2callback = false;
  $.ready2 = function(fn) {
    if (!ready2callback) {
      ready2callback = $.Deferred();
      var ready2resolve = function() {
        setTimeout(function() {
          ready2callback.resolve();
        }, 0);
      };

      if ($.isReady) {
        $(document).ready(ready2resolve);
      } else {
        //.on('ready') call after $(document).ready
        $(document).on('ready', ready2resolve);
      }
    }

    ready2callback.done(fn);
  };

}(jQuery, document));