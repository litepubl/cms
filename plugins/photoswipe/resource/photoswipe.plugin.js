/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

(function($, litepubl, document) {
  'use strict';

  litepubl.photoswipePlugin = Class.extend({
    links: false,
    opened: false,
    photoswipe: false,
    ready: true,
    script: false,
    holder: false,
    options: false,
    animatethumbs: false,
    onoptions: $.noop,

    init: function(links) {
      //templates and photoswipe  library in mysel module
      this.ready = "photoswipe" in litepubl.tml;

      litepubl.openimage = $.proxy(this.openimage, this);

      if (links && links.length) {
        var self = this;
        this.links = links.on("click.photoswipe", function() {
          self.open($(this));
          return false;
        });

        if (!this.ready) {
          links.one("focus.ready mouseenter.ready", $.proxy(this.load_script, this, false));
        }

        $.ready2($.proxy(this.openhash, this));
      }
    },

    load_script: function(callback) {
      if (this.script) {
        this.script.done(callback);
      } else {
        var self = this;
        this.script = $.load_script(ltoptions.files + "/files/js/photoswipe." + ltoptions.jsmerger + ".js", function() {
          self.ready = true;
          if (callback) callback();
        });
      }
    },

    getoptions: function() {
      if (!this.options) {
        var lng = lang.photoswipe;
        litepubl.tml.photoswipe = $.parsetml(litepubl.tml.photoswipe, {
          lang: lng
        });

        var self = this;
        this.options = {
          index: 0,
          history: true,
          galleryPIDs: true,
          showHideOpacity: !this.animatethumbs,
          getThumbBoundsFn: !this.animatethumbs ? false : function(index) {
            var linkindex = self.photoswipe.items[index].linkindex;
            var img = self.links.eq(linkindex).find("img");
            var offset = img.offset();

            return {
              x: offset.left,
              y: offset.top,
              w: img.data('width')
            };
          },

          errorMsg: '<div class="pswp__error-msg"><a href="%url%" target="_blank">' + lng.error + '</a></div>',
          shareButtons: this.get_sharebuttons(),
          getTextForShare: function(shareButtonData) {
            var result = self.photoswipe.currItem.title || '';
            if (!result || (/\.(jpg|jpeg|png|bmp)$/i).test(result)) {
              result = $("title").text();
            }

            return result;
          }
        };

        this.onoptions(this.options);
      }

      return this.options;
    },

    open: function(link) {
      if (!this.ready) {
        return this.load_script($.proxy(this.open, this, link));
      }

      if (this.opened) return false;
      this.opened = true;

      var items = this.getitems(link.data("idpost"), link.data("file").id);
      this.opened = false;

      if (!items.length) {
        return false;
      }

      return this.openitems(items);
    },

    openitems: function(items) {
      if (this.opened || !items.length) return false;

      if (!this.ready) {
        return this.load_script($.proxy(this.openitems, this, items));
      }

      this.opened = true;
      litepubl.stat("photoswipe_opening");

      this.holder = $(litepubl.tml.photoswipe).appendTo("body");
      var pswp = this.photoswipe = new PhotoSwipe(this.holder.get(0), PhotoSwipeUI_Default, items, this.options);
      pswp.listen('destroy', $.proxy(this.doclose, this));
      this.setstatevents(pswp);
      pswp.init();
    },

    doclose: function() {
      this.photoswipe = false;
      this.holder.remove();
      this.holder = false;
      this.opened = false;
      litepubl.stat("photoswipe_closed");
    },

    getitems: function(idpost, idfile) {
      var result = [];
      var options = this.getoptions();
      options.galleryUID = parseInt(idpost);
      var ismobile = $(window).width() <= 768;
      var animatethumbs = this.animatethumbs;

      this.links.each(function(linkindex) {
        var link = $(this);
        if (idpost == link.data("idpost")) {
          var data = link.data("file");
          var midle = link.data("midle");

          if (idfile == data.id) {
            options.index = result.length;
          }

          if (ismobile && midle) {
            result.push({
              src: midle.link,
              msrc: animatethumbs ? $("img", link).attr("src") : false,
              w: parseInt(midle.width),
              h: parseInt(midle.height),
              title: link.attr("title"),
              //id big image
              pid: parseInt(data.id),
              linkindex: linkindex
            });
          } else {
            result.push({
              src: link.attr("href"),
              msrc: animatethumbs ? $("img", link).attr("src") : false,
              w: parseInt(data.width),
              h: parseInt(data.height),
              title: link.attr("title"),
              pid: parseInt(data.id),
              linkindex: linkindex
            });
          }
        }
      });

      return result;
    },

    openimage: function(image) {
      if (!this.ready) {
        return this.load_script($.proxy(this.openimage, this, image));
      }

      // save current options for swithing single options
      var options = this.getoptions();
      this.options = $.extend({
        index: 0,
        history: false,
        galleryPIDs: false,
        shareEl: false,
        counterEl: false,
        arrowEl: false,
        showHideOpacity: true,
        getThumbBoundsFn: false
      }, this.options);

      this.openitems([{
        src: image.url,
        w: image.width,
        h: image.height,
        title: image.title
      }]);

      //restore back
      this.options = options;
    },

    get_hash: function() {
      var hash = decodeURI(window.location.hash.substring(1));
      if (hash.length < 5) {
        return false;
      }

      var result = {};
      var vars = hash.split('&');
      for (var i = 0; i < vars.length; i++) {
        if (!vars[i]) continue;

        var keys = vars[i].split('=');
        if (keys.length < 2) continue;

        result[keys[0]] = keys[1];
      }

      return {
        gid: "gid" in result ? parseInt(result.gid, 10) : 0,
        pid: "pid" in result ? parseInt(result.pid, 10) : 0
      };
    },

    openhash: function() {
      var hash = this.get_hash();
      if (hash && hash.pid && hash.gid) {
        var items = this.getitems(hash.gid, hash.pid);
        this.openitems(items);
      }
    },

    get_sharebuttons: function() {
      var lng = lang.photoswipe;
      var result = [{
          id: 'photoswipe-facebook',
          label: '<span class="fa fa-facebook"> FaceBook',
          url: 'https://www.facebook.com/dialog/feed?' +
            'app_id=' + ltoptions.facebook_appid +
            '&link=[url]' +
            '&name=[text]' +
            '&picture=[image_url]' +
            '&display=popup' +
            '&redirect_uri=' + encodeURIComponent(ltoptions.files + '/files/close-window.htm')
        },

        {
          id: 'photoswipe-twitter',
          label: '<span class="fa fa-twitter"></span> Tweet',
          url: 'https://twitter.com/share?lang=' + ltoptions.lang + '&url=[url]&text=[text]'
            //'https://twitter.com/intent/tweet?url=[url]&text=[title]'
        },

        {
          id: 'photoswipe-pinterest',
          label: '<span class="fa fa-pinterest"></span> Pin it',
          url: 'http://www.pinterest.com/pin/create/button/' +
            '?url=[url]&media=[image_url]&description=[text]'
        }
      ];

      if (ltoptions.lang == 'ru') {
        result.push({
          id: 'photoswipe-vk',
          label: '<span class="fa fa-vk"></span> ' + lng.vk,
          url: 'https://vk.com/share.php?url=[url]'
        });

        result.push({
          id: 'photoswipe-odnoklassniki',
          label: '<span class="odnoklassniki-icon"></span> ' + lng.ok,
          url: 'http://www.odnoklassniki.ru/dk?st.cmd=addShare&st.s=1&st._surl=[url]&st.comments=[text]'
            //'http://connect.ok.ru/dk?st.cmd=WidgetSharePreview&service=odnoklassniki&st.shareUrl=[url]'
        });
      }

      result.push({
        id: 'photoswipe-download',
        label: '<span class="fa fa-download"></span>' + lng.downlload,
        url: '[raw_image_url]',
        download: true
      });

      for (var i = result.length - 1; i >= 0; i--) {
        result[i].url = result[i].url
          .replace(/\[/g, "\u007b\u007b")
          .replace(/\]/g, "\u007d\u007d");
      }

      return result;
    },

    setstatevents: function(pswp) {
      pswp.listen('afterChange', function() {
        litepubl.stat('photoswipe_afterChange', {
          index: pswp.getCurrentIndex(),
          linkindex: pswp.currItem.linkindex,
          idimage: pswp.currItem.pid
        });
      });

      pswp.listen('shareLinkClick', function(e, target) {
        litepubl.stat('photoswipe_shareLinkClick', {
          url: target.href
        });
      });
    }

  });

  $(document).ready(function() {
    litepubl.photoswipe = new litepubl.photoswipePlugin($(".photo"));
  });

})(jQuery, litepubl, document);