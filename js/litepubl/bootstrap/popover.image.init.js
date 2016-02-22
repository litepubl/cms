/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

(function($, window, document) {
  'use strict';

  $(document).ready(function() {
    $.popimage = new $.Popimage();

    litepubl.linkimage = function(link) {
      $.popimage.add(link, "focus");
    };

    litepubl.openimage = function(image) {
      $.popimage.open(image);
    };

    $.popimage.oninit = function(url) {
      litepubl.stat('popimage', {
        src: url
      });
    };

  });
})(jQuery, window, document);