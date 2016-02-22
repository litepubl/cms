/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

;
(function($) {
  'use strict';

  $.YoutubeBootstrap = Class.extend({
    vid: '',
    //size in percent 1 - 100
    size: 90,
    tml: '<iframe src="https://www.youtube.com/embed/%%vid%%?autoplay=1" width="%%width%%" height="%%height%%" frameborder="0" type="text/html" ></iframe>',
    dialog: false,

    init: function() {
      var self = this;
      $("a[href^='http://youtu.be/'], a[href^='http://www.youtube.com/watch?v='], a[href^='https://youtu.be/'], a[href^='https://www.youtube.com/watch?v=']").on("click.youtube", function() {
        self.open(self.getvid($(this).attr("href")));
        return false;
      });
    },

    open: function(vid) {
      this.vid = vid;

      if (!this.dialog) {
        this.dialog = new $.Simplerdialog();
        //wait 100ms to clean iframe
        this.dialog.removeOnclose = false;
      }

      var width = Math.round($(window).width() * this.size / 100);
      var height = Math.round($(window).height() * this.size / 100);

      var self = this;
      this.dialog.open({
        width: width,
        height: height,
        html: $.simpletml(this.tml, {
          vid: vid,
          width: width,
          height: height
        }),

        open: function(dialog) {
          //dialog.removeClass('in');
        },

        close: function(dialog) {
          var iframe = dialog.find('iframe:first');
          iframe.attr("src", "");
          setTimeout(function() {
            self.dialog.remove();
          }, 100);
        }

      });

    },

    getvid: function(url) {
      var vid = get_get('v', url);
      if (vid) return vid;

      vid = url.split('youtu.be/').pop();
      var i = vid.indexOf('?');
      if (i > 0) vid = vid.substring(0, i);

      i = vid.indexOf('&');
      if (i > 0) vid = vid.substring(0, i);

      return vid;
    }

  });

  $.ready2(function() {
    if ("modal" in $.fn) $.youtubeBootstrap = new $.YoutubeBootstrap();
  });
}(jQuery));