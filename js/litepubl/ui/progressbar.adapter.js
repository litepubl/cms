/**
 * Lite Publisher CMS
 *
 *  copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link      https://github.com/litepubl\cms
 *  version   7.03
  */

(function($, document, litepubl) {
  'use strict';

  litepubl.ui = litepubl.ui || {};
  litepubl.ui.Progressbar = Class.extend({
    tml: '<div></div>',

    add: function(holder) {
      var progress = $(this.tml).appendTo(holder);
      holder.data('progressbar.litepubl', progress);
      progress.progressbar({
        value: 0
      });
    },

    remove: function(holder) {
      holder.data('progressbar.litepubl')
        .progressbar('destroy')
        .remove();
      holder.removeData('progressbar.litepubl');
    },

    setvalue: function(holver, value) {
      holder.data('progressbar.litepubl')
        .progressbar({
          value: value
        });
    }

  });

  $(document).ready(function() {
    litepubl.progressbar = new litepubl.ui.Progressbar();
  });

})(jQuery, document, litepubl);