/**
 * LitePubl CMS
 *
 *  copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link      https://github.com/litepubl\cms
 *  version   7.08
  */

(function($, document) {
  'use strict';

  $(document).ready(function() {
    $('.scrollable').enscroll({
      minScrollbarLength: 15
    });
  });

})(jQuery, document);