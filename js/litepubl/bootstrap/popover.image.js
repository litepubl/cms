/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function( $, window, document){
  'use strict';
  
  $.Popimage = Class.extend({
    dataname: 'popimage',
    title: "",
    cursorclass: "cursor-loading",
    width: false,
    height: false,
    oninit: $.noop,
    onerror: $.noop,
    
    init: function(options) {
      this.re = /\.(jpg|jpeg|png|bmp)$/i;
      var self = this;
      $(document).on("mouseenter.popinit focus.popinit click.popinit", ".photo:not(.popinit)", function(event) {
        return self.add($(this).addClass("popinit"), event.type);
      });
    },
    
    add: function(link, event_type) {
      //already added
      if (link.data(this.dataname)) return false;
      
      var click_enabled = true;
      var url = link.attr("href");
      if (!url || !this.re.test(url)) {
        //follow by click
        if (event_type == "click") return;
        url = link.attr("data-image");
        if (!url || !this.re.test(url)) return;
        click_enabled  = false;
      }
      
      link.data(this.dataname, {
        url: url,
        click_enabled : click_enabled ,
        wait_event: event_type
      });
      
      var self = this;
      link.one(event_type == "mouseenter" ? "mouseleave.popinit" : "blur.popinit", function() {
        $(this).data(self.dataname).wait_event = false;
      });
      
      link.addClass(this.cursorclass);
      var img = new Image();
      img.onload = function(){
        this.onload = this.onerror = null;
        link.removeClass(self.cursorclass);
        self.calc_size(link, this.width, this.height);
      };
      
      img.onerror = function() {
        this.onload = this.onerror = null;
        link.removeClass(self.cursorclass);
        self.onerror(this.src);
      };
      
      img.src =           url;
      return false;
    },
    
    calc_size: function(link, width, height) {
      var linkdata = link.data(this.dataname);
      linkdata.width = width;
      linkdata.height = height;
      
      var ratio = width / height;
      if (this.width) {
        var w = this.width;
        var h = this.height;
      } else {
        var winwidth = window.innerWidth || document.documentElement.clientWidth || document.body.clientWidth;
        var winheight = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
        if (ratio >= 1) {
          //horizontal image, midle height and maximum width
          var h = Math.floor(winheight / 2) - 40;
          var w = Math.min(winwidth - 60, Math.floor(h * ratio));
        } else {
          //vertical image, midle width and maximum height
          var w = Math.floor(winwidth / 2) - 40;
          var h = Math.min(winheight - 60, Math.floor(w / ratio));
        }
      }
      
      if ((width <= w) && (height <= h)) {
        w = width;
        h = height;
      } else {
        if (w /h >= ratio) {
          w = Math.floor(h *ratio);
        } else {
          h = Math.floor(w / ratio);
        }
      }
      
      var title = link.attr("title");
      if (this.re.test(title)) title = this.title;
      
      link.popover({
        container: 'body',
        content: '<img src="' + linkdata.url + '" width="' + w + '" height="' + h + '" />',
        delay: 120,
        html:true,
        placement: 'auto ' + (ratio >= 1 ? 'bottom' : 'right'),
        template: '<div class="popover popover-image" role="tooltip"><div class="arrow"></div>' +
        '<h3 class="popover-title" style="max-width:' + w + 'px;"></h3>' +
        '<div class="popover-content"></div></div>',
        title: title,
        trigger: "hover focus" + (linkdata.click_enabled ? " click" : "")
      });
      
      // fix if popover trigger has hover and click
      if (linkdata.click_enabled) link.pophoverclick();
      
      //show popover after load image if not lose focus or hover
      if (linkdata.wait_event) link.trigger(linkdata.wait_event);
      this.oninit(linkdata.url);
    }
    
  });
  
  $.fn.popimage = function() {
    return this.each(function(){
      $.popimage.add($(this).addClass("popinit"), false);
    });
  };
  
  $(document).ready(function() {
    $.popimage = new $.Popimage();
  });
  
})( jQuery, window, document);