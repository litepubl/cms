/**
 * LitePubl CMS
 *
 *  copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link      https://github.com/litepubl\cms
 *  version   7.08
  */

;
(function($) {
  $(function() {
    $("a[rel^='prettyPhoto']").prettyPhoto({
      social_tools: false
    });

    $("a[href^='http://youtu.be/'], a[href^='http://www.youtube.com/watch?v=']").prettyPhoto({
      social_tools: false
    });

    litepubl.openimage = function(image) {
      $.prettyPhoto.open(image.url, image.title, image.description);
    };

  });
}(jQuery));