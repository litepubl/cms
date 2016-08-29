/**
 * Lite Publisher CMS
 *
 *  copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link      https://github.com/litepubl\cms
 *  version   7.06
  */

(function($) {
  'use strict';

  litepubl.themefonts['default'] = function() {
    $.load_font_part('Nautilus', 'nautilus', ltoptions.files + '/plugins/nautilus/font/Nautilus');
  };

})(jQuery);