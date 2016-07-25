/**
 * Lite Publisher CMS
 *
 *  copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link      https://github.com/litepubl\cms
 *  version   7.01
 */

;
(function($, document, window) {
  if (typeof(chrome) !== 'undefined') {
    $(document).ready(function() {
      $(document).on("click.rss", "a[href$='.xml']", function() {
        var url = $(this).attr("href");
        $.confirmbox(lang.dialog.confirm, lang.rsschrome.warn, lang.rsschrome.follow, lang.dialog.cancel,
          function(index) {
            if (index == 0) window.location = url;
          });

        return false;
      });
    });
  }
}(jQuery, document, window));