/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function( $){
  'use strict';
  
  $.Simplerdialog = $.BootstrapDialog.extend({
    tml: '<div class="modal fade" id="dialog-%%id%%" tabindex="-1" role="dialog" aria-hidden="true">' +
    '<div class="modal-dialog center-block"><div class="modal-content">' +
    '<button type="button" class="close" data-dismiss="modal" aria-label="%%close%%"><span aria-hidden="true">&times;</span><span class="sr-only">%%close%%</span></button>' +
    '<div class="modal-body">%%body%%</div>' +
    '</div></div></div>',
    
    init: function() {
      this.default_options = {
        title: "",
        html: "",
css: "button.close{margin-top:-10px}",
        width: false,
        height: false,
        open: $.noop,
        close: $.noop,
        buttons: []
};
}

  });
  
})( jQuery);