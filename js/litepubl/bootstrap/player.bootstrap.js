/**
 * Lite Publisher CMS
 *  copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link      https://github.com/litepubl\cms
 *  version   7.00
  */

;
(function($, document) {
  'use strict';

  litepubl.Bootstrapplayer = litepubl.Mediaplayer.extend({
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
        html: $.simpletml(this.tml, {
          file: link.data("file"),
          siteurl: ltoptions.files
        }),

        width: this.width,
        height: this.height,
        open: function(dialog) {
          dialog.removeClass('in');
          self.load(function() {
            self.player(self.dialog.dialog.find("video:first"));
          });
        }
      });
    }

  }); //class

  $(document).ready(function() {
    litepubl.mediaplayer = new litepubl.Bootstrapplayer($("audio"), $(".videofile"));
  });
})(jQuery, document);