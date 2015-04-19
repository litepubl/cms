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
var self = this;
if (!this.dialog) this.dialog = new $.Simplerdialog();
this.dialog.open({
html:  $.simpletml(this.tml, {
        file: link.data("file"),
        siteurl: ltoptions.files
      }),

width: this.width,
height: this.height,
open: function(dialog) {
self.load(function() {
self.player(dialog.find("video:first"));
});
}
});
}

  });//class

  $(document).ready(function() {
    litepubl.mediaplayer = new litepubl.Bootstrapplayer($("audio"), $(".videofile"));
  });
})( jQuery, document);