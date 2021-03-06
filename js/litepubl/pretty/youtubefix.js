/**
 * LitePubl CMS
 *
 *  copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link      https://github.com/litepubl\cms
 *  version   7.08
  */

;
(function($, window) {
  $(function() {
    window.setTimeout(function() {
      var prettyClose = $.prettyPhoto.close;
      $.prettyPhoto.close = function() {
        // if iframe opened in pretty
        var iframe = $('#pp_full_res').find('iframe:first');
        if (iframe.length == 0) {
          prettyClose();
        } else {
          iframe.attr("src", "");
          window.setTimeout(function() {
            prettyClose();
          }, 100);
        }
      };
    }, 20);
  });
}(jQuery, window));