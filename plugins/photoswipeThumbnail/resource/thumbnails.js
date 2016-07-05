/**
 * Lite Publisher CMS
 *  copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link https://github.com/litepubl\cms
 *  version 7.00
 *
 */

(function($, litepubl, document) {
  'use strict';

  $(document).ready(function() {
    //delete options if already created
    litepubl.photoswipe.options = false;
    litepubl.photoswipe.animatethumbs = true;
  });

})(jQuery, litepubl, document);