/**
 * Lite Publisher CMS
 *
 *  copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link      https://github.com/litepubl\cms
 *  version   7.08
  */

(function($, document) {
  'use strict';

  $(function() {
    $(document).on("click.postcard", ".postcard-title", function(event) {
      if (event.target.nodeName.toLowerCase() != "a") {
        location.href = $(event.target).find("a").attr("href");
      }
    });

    $(document).settooltip(".postcard-link", {
      title: function() {
        return $(this).text();
      }
    });
  });
})(jQuery, document);