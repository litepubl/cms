/**
 * Lite Publisher CMS
 *  copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link      https://github.com/litepubl\cms
 *  version   7.00
  */

(function($) {
  'use strict';

  $.Simplerdialog = $.BootstrapDialog.extend({
    tml: '<div class="modal fade" id="dialog-%%id%%" tabindex="-1" role="dialog">' +
      '<div class="modal-dialog" role="document">' +
      '<div class="modal-content">' +
      '<button type="button" class="close" data-dismiss="modal" aria-label="%%close%%"><span aria-hidden="true">&times;</span><span class="sr-only">%%close%%</span></button>' +
      '<div class="modal-body">%%body%%</div>' +
      '</div></div></div>',

    init: function() {
      this.padheight = 32;
      this.default_options = {
        title: "",
        html: "",
        css: "button.close{margin-top:-2px}",
        width: false,
        height: false,
        open: $.noop,
        close: $.noop,
        buttons: []
      };
    }

  });

})(jQuery);