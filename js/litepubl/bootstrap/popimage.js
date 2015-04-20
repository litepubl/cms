/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function( $, window, document){
  'use strict';
  
  // regexp test image extension in url
  var re = /\.(jpg|jpeg|png|bmp)$/i;
  
  $.fn.popimage = function(options) {
    options = $.extend({
      dataname: 'popimage',
      removedata: true,
      title: "",
      cursorclass: "cursor-loading",
      width: false,
      height: false,
      trigger: "hover focus click",
      oninit: $.noop,
      onerror: $.noop
    }, options);
    
    var inittrigger = '';
    var poptrigger = '';
    if (options.trigger.indexOf('hover') >= 0) {
      inittrigger += "mouseenter.popinit";
      poptrigger += 'hover';
    }
    
    if (options.trigger.indexOf('focus') >= 0) {
      inittrigger += " focus.popinit";
      poptrigger += ' focus';
    }
    
    options.click = options.trigger.indexOf('click') >= 0;
    if (options.click) {
      inittrigger += " click.popinit";
    }
    
    var prevurl = '';
    var prevlink = false;
    
    return this.each(function(){
      var link = $(this);
      var url = link.attr("href");
      if (!url || !re.test(url)) {
        url = link.data("image");
        if (!url || !re.test(url)) return;
      }
      
      link.data(options.dataname, {
        url: url,
        prevurl: prevurl,
        nexturl: false,
        activated: false
      });
      
      if (prevlink) prevlink.data(options.dataname).nexturl = url;
      prevurl = url;
      prevlink = link;
      
      link.one(inittrigger, function(e) {
        var self = $(this);
        self.off(".popinit");
        self.addClass(options.cursorclass);
        
        var clicktrigger = "";
        if (options.click) {
          if (re.test(self.attr("href"))) {
            clicktrigger = " click";
          } else {
            // follow by link if it clicked
            if (e.type == "click") return;
          }
        }
        
        var selfdata = self.data(options.dataname);
        //after load image open popover
        selfdata.activated = e.type;
        self.one((e.type == "mouseenter" ? "mouseleave" : "blur") + ".popinit", function() {
          $(this).data(options.dataname).activated = false;
        });
        
        var img = new Image();
        img.onload = function(){
          this.onload = this.onerror = null;
          self.removeClass(options.cursorclass);
          //calc size
          var ratio = this.width / this.height;
          if (options.width) {
            var w = options.width;
            var h = options.height;
          } else {
            if (ratio >= 1) {
              //horizontal image, midle height and maximum width
              var h = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
              h = Math.floor(h / 2) - 20;
              var w = Math.floor(h * 4 /3);
            } else {
              //vertical image, midle width and maximum height
              var w = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
              w = Math.floor(w / 2) - 20;
              var h = Math.floor(w / 4 *3);
            }
          }
          
          if ((this.width <= w) && (this.height <= h)) {
            w = this.width;
            h = this.height;
          } else {
            if (w /h > ratio) {
              w = Math.floor(h *ratio);
            } else {
              h = Math.floor(w / ratio);
            }
          }
          
          var title = self.attr("title");
          if (re.test(title)) title = options.title;
          
          self.popover({
            container: 'body',
            content: '<img src="' + selfdata.url + '" width="' + w + '" height="' + h + '" />',
            delay: 120,
            html:true,
            placement: 'auto ' + (ratio >= 1 ? 'bottom' : 'right'),
            template: '<div class="popover popover-image" role="tooltip"><div class="arrow"></div>' +
            '<h3 class="popover-title" style="max-width:' + w + 'px;"></h3>' +
            '<div class="popover-content"></div></div>',
            title: title,
            trigger: poptriger  + clicktrigger
          });
          
          //show popover after load image if not lose focus or hover
          if (selfdata.activated) self.trigger(selfdata.activated);
          
          //preload
          if (selfdata.nexturl) {
            imgnext = new Image();
            imgnext.onload = imgnext.onerror = function() {
              this.onload = this.onerror = null;
            };
            imgnext.src = selfdata.nexturl;
          }
          
          if (selfdata.prevurl) {
            imgprev = new Image();
            imgprev.imgprev= imgnext.onerror = function() {
              this.onload = this.onerror = null;
            };
            imgprev.src = selfdata.prevurl;
          }
          
          options.oninit(selfdata.url);
          if (options.removedata) self.data(options.dataname, false);
        };
        
        img.onerror = function() {
          this.onload = this.onerror = null;
          options.onerror(self.data(options.dataname).url);
          if (options.removedata) self.data(options.dataname, false);
        }
        
        img.src =           selfdata.url;
        return false;
      });
    });
  };
  
  $.ready2(function(){
    if ("popover" in $.fn) $("a.photo").popimage({
      oninit: function(url) {
      litepubl.stat('popimage', {src: url});
      }
    });
  });
})( jQuery, window, document);