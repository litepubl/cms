/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

;
(function($, window) {
  'use strict';

  function home_resize(holder) {
    var data = holder.data("homeimage");
    if (!data || data.error) return false;

    var win = $(window);
    var winw = win.width();
    var winh = win.height();

    if (data.winw == winw && data.winh == winh) return;

    data.winw = winw;
    data.winh = winh;

    var cur = winw >= data.breakpoint ? data.large : data.small;
    if (cur != data.cur) {
      //switch to second image if possible
      if (cur.w) {
        //next image loaded; success switching
        data.cur = cur;
        //data.img.prop("src", cur.src);
        data.img.remove();
        data.img = $('<img src="' + cur.src + '" />').appendTo(holder);
      } else if (!cur.src) {
        //cant switch; single image
        cur = data.cur;
      } else {
        return load_image(cur, function() {
            data.winw = 0;
            home_resize(holder);
          },

          //error callback
          function() {
            //stay to non exists and fallback to single image
            cur.src = false;
            data.winw = 0;
            home_resize(holder);
          });
      }
    }

    var w = holder.width();
    var h = Math.floor(Math.min(winh - data.top, w, cur.h));
    var imgheight = h;
    var imgwidth = Math.floor((cur.w / cur.h) * h);
    if (imgwidth < w) {
      imgwidth = w;
      imgheight = Math.floor(w / (cur.w / cur.h));
      if (h > imgheight) h = imgheight;
    }

    holder.height(h);
    data.img.css({
      width: imgwidth,
      height: imgheight,
      left: (w - imgwidth) / 2,
      top: (h - imgheight) / 2
    });
  }

  function load_image(info, callback, errorcallback) {
    var image = new Image();
    image.onload = function() {
      this.onload = this.onerror = null;
      info.w = this.width;
      info.h = this.height;
      callback();
    };

    image.onerror = function() {
      this.onload = this.onerror = null;
      errorcallback();
    };

    image.src = info.src;
  }

  $.fn.homeimage = function(options) {
    if (!this.length || this.data("homeimage")) return this;

    options = $.extend({
      image: "",
      small: "",
      breakpoint: 768,
      addclass: "home-image"
    }, options);

    //no images in data
    if (!options.image && !options.small) return this;

    var self = this;
    var data = {
      cur: false,
      error: false,
      breakpoint: options.breakpoint,
      top: this.offset().top,
      winw: 0,
      winh: 0,
      addclass: options.addclass,
      img: false,
      large: {
        w: 0,
        h: 0,
        src: options.image
      },

      small: {
        w: 0,
        h: 0,
        src: options.small
      }
    };

    this.data("homeimage", data);
    //set size before loading image
    this.height($(window).height() - data.top);

    var cur = $(window).width() >= data.breakpoint ? data.large : data.small;
    if (!cur.src) {
      cur = cur == data.small ? data.large : data.small;
    }

    data.cur = cur;
    load_image(cur, function() {
        self.addClass(data.addclass);
        data.img = $('<img src="' + data.cur.src + '" />').appendTo(self);
        $(window).on("resize.homeimage", function() {
          home_resize(self);
        });

        home_resize(self);
      },

      function() {
        data.error = true;
        self.hide();
        alert("Error! Home image not load");
      });

    return this;
  };

})(jQuery, window);