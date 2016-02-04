(function($, document, litepubl) {
  'use strict';

  litepubl.bootstrap = litepubl.bootstrap || {};
  litepubl.bootstrap.Progressbar = Class.extend({
    tml: '<div class="progress">' +
      '<div class="progress-bar progress-bar-success" role="progressbar" aria-valuenow="0" aria-valuemin="0" aria-valuemax="100">' +
      '<span class="sr-only">0%</span>' +
      '</div>' +
      '</div>',

    add: function(holder) {
      holder.data('progressbar.litepubl', $(this.tml).appendTo(holder));
    },

    remove: function(holder) {
      holder.data('progressbar.litepubl').remove();
      holder.removeData('progressbar.litepubl');
    },

    setvalue: function(holver, value) {
      var percent = value + '%';
      var progress = $('.progress-bar', holder.data('progressbar.litepubl'));
      progress[0].style.width = percent;
      $('.sr-only', progress).text(percent);
    }

  });

  $(document).ready(function() {
    litepubl.progressbar = new litepubl.bootstrap.Progressbar();
  });

})(jQuery, document, litepubl);