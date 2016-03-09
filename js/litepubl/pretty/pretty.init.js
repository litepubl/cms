/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 **/
;
(function($, document) {
  $(document).ready(function() {
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
}(jQuery, document));