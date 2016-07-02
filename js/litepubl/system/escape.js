/**
 * Lite Publisher CMS
 *  copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link https://github.com/litepubl\cms
 *  version 7.00
 *
 */


(function($, document) {
  'use strict';

  $.onEscape = function(callback) {
    if (!$.isFunction(callback)) return;

    $(document).off('keydown.onEscape').on('keydown.onEscape', function(e) {
      if (e.keyCode == 27) {
        callback();
        e.preventDefault();
        $(document).off('keydown.onEscape');
      }
    });
  };

}(jQuery, document));