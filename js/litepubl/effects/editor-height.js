/**
 * Lite Publisher CMS
 *
 *  copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link      https://github.com/litepubl\cms
 *  version   7.05
 */

(function($) {
  'use strict';

  $.fn.editorheight = function() {
    return this
      .on("focus.height", function() {
        var self = $(this);
        var height = self.data("css.height", self.css("height"));
        if (!height) {
          self.data("css.height", height);
        }

        self.css("height", "14em");
      })
      .on("blur.height", function() {
        var self = $(this);
        var height = self.data("css.height", self.css("height"));
        if (height) {
          self.css("height", height);
        }
      });
  };

})(jQuery);