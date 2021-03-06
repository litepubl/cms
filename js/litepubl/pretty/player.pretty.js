/**
 * LitePubl CMS
 *
 *  copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link      https://github.com/litepubl\cms
 *  version   7.08
  */

;
(function($, document) {
  'use strict';

  litepubl.Prettyplayer = litepubl.Mediaplayer.extend({
    pretty_tml: '<div id="pretty-video-holder"></div>',
    holder: "#pretty-video-holder",
    linkclicked: false,
    tmplink: false,

    video: function(links) {
      if (!this.tmplink) {
        this.tmplink = $('<div class="hidden"><a href="#custom=true&width=' + this.width + '&height=' + this.height + '"></a></div>').appendTo("body").find("a");
      }

      var self = this;
      links.on("click.playvideo", function(event) {
        event.preventDefault();
        self.linkclicked = $(this);
        self.tmplink.click();
      });

      this.tmplink.prettyPhoto({
        custom_markup: this.pretty_tml,
        default_width: this.width,
        default_height: this.height,
        opacity: 0.80,
        /* Value between 0 and 1 */
        modal: true,
        /* If set to true, only the close button will close the window */
        deeplinking: false,
        /* Allow prettyPhoto to update the url to enable deeplinking. */
        keyboard_shortcuts: false,
        /* Set to false if you open forms inside prettyPhoto */
        show_title: false,
        /* true/false */
        social_tools: false,
        //hideflash: true,
        /* Called when prettyPhoto is closed */
        callback: function() {
          $(document).off('keydown.onEscape');
          self.linkclicked = false;
        },

        changepicturecallback: function() {
          $.onEscape($.proxy($.prettyPhoto.close, $.prettyPhoto));
          self.load(function() {
            var html = $.simpletml(self.video_tml, {
              file: self.linkclicked.data("file"),
              siteurl: ltoptions.files
            });

            self.player($(html).appendTo(self.holder));
          });
        }
      });
    }

  }); //class

  $(function() {
    litepubl.mediaplayer = new litepubl.Prettyplayer($("audio"), $(".videofile"));
  });
})(jQuery, document);