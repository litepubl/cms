/**
 * Lite Publisher CMS
 *
 *  copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link      https://github.com/litepubl\cms
 *  version   7.00
  */

(function($, document) {
  'use strict';

  $(function() {
    $("textarea").editorheight();
    $(document)
      .settooltip()
      .on("click.scrollto", ".scroll-to", function() {
        var hash = $(this).attr("href");
        $(hash).scrollto(2000, function() {
          window.location.hash = hash;
        });
        return false;
      });
  });

})(jQuery, document);