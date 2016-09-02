/**
 * Lite Publisher CMS
 *
 *  copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link      https://github.com/litepubl\cms
 *  version   7.07
  */

(function($, litepubl) {
  'use strict';

  litepubl.bootstrap = litepubl.bootstrap || {};
  litepubl.bootstrap.Progressbar = Class.extend({
    tml: '<div class="progress">' +
      '<div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">' +
      '<span class="sr-only">0%</span>' +
      '</div>' +
      '</div>',

    add: function(holder) {
      return holder.data('progressbar.litepubl', $(this.tml).appendTo(holder));
    },

    remove: function(holder) {
      holder.data('progressbar.litepubl').remove();
      holder.removeData('progressbar.litepubl');
    },

    setvalue: function(holder, value) {
      var progress = holder.data('progressbar.litepubl');
      if (!progress) {
        progress = this.add(holder);
      }

      var percent = value + '%';
      $('.progress-bar', progress).get(0).style.width = percent;
      $('.sr-only', progress).text(percent);
    }

  });

  $(function() {
    litepubl.progressbar = new litepubl.bootstrap.Progressbar();
  });

})(jQuery, litepubl);