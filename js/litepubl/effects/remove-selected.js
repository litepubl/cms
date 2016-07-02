/**
 * Lite Publisher CMS
 *  copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link https://github.com/litepubl\cms
 *  version 7.00
 *
 */


(function($) {
  'use strict';

  $.fn.removeSelected = function() {
    var selected = this.find(":selected");
    if (!selected.length) return false;

    var next = selected.next();
    if (next.length) {
      next.prop("selected", true);
    } else {
      var prev = selected.prev();
      if (prev.length) prev.prop("selected", true);
    }

    selected.remove();
    return selected;
  };

})(jQuery);