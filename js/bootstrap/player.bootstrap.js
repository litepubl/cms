/**
* Lite Publisher
* Copyright (C) 2010, 2015 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

;(function($, document){
  'use strict';
  
  litepubl.Bootstrapplayer= litepubl.Mediaplayer.extend({
    dialog: false,

    video: function(links) {
      var self = this;
      return links.on("click.playvideo", function(event) {
        event.preventDefault();
        self.open($(this));
      });
      },

open: function(link) {
//preload script when animate opening dialog
          this.load($.noop);
if (!this.dialog) this.dialog = new litepubl.Videodialog();

var html = $.simpletml(this.dialog_tml, {
        file: link.data("file"),
        siteurl: ltoptions.files
      });

}

  });//class

litepubl.Videodialog = $.BootstrapDialog.extend({
tml: '<div class="modal fade" tabindex="-1" role="dialog" aria-hidden="true">' + 
    '<div class="modal-dialog center-block"><div class="modal-content">' +
    '<button type="button" class="close" data-dismiss="modal" aria-label="%%close%%"><span aria-hidden="true">&times;</span></button>' +
    '<div class="modal-body">' + '<video src="%%siteurl%%/files/%%file.filename%%" type="%%file.mime%%" controls="controls" autoplay="autoplay"></video>' +
'</div>' +
    '<div class="modal-footer"></div>' +
    '</div></div></div>',

    init: function() {
      this.default_options = {
        title: "",
        html: "",
        width: false,
        height: false,
        open: $.noop,
        close: $.noop,
        buttons: []
};
},

});
  
  $(document).ready(function() {
    litepubl.mediaplayer = new litepubl.Bootstrapplayer($("audio"), $(".videofile"));
  });
})( jQuery, document);