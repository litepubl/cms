/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

(function ($, document) {
  'use strict';
  
  $.css_loader = {
    items: [],
    counter: 0,
    maxcounter: 60,
    timer: false,
    guid: 0,
    
    add: function(url, callback) {
this.additem($('<link rel="stylesheet" type="text/css" media="' + this.getmedia() + '" href="' + url + '" />').appendTo("head:first").get(0), callback);
},

    addtext: function(text, callback) {
this.additem($('<link rel="stylesheet" type="text/css" media="' + this.getmedia() + '">' + text + '</style>').appendTo("head:first").get(0), callback);
},

getmedia: function() {
if (!this.guid) this.guid = $.now();
return "only x" + this.guid++;
},

additem: function(link, callback) {
            if (this.ready(link)) {
if ($.isFunction(callback)) callback();
} else {
        this.items.push({
link: link,
callback: callback
});

        this.counter = this.maxcounter;
        if (!this.timer) {
this.timer = setInterval($.proxy(this.check, this), 50);
}
      }
    },
    
    check: function() {
      var items = this.items;
      for (var i = items.length - 1; i >= 0; i--) {
var item = items[i];
//ready or expired
        if (!this.counter || this.ready(item.link)) {
item.link.media = "all";
if ($.isFunction(item.callback)) item.callback();
          items.splice(i, 1);
        }
      }
      
      if (!items.length || (this.counter-- < 0)) {
        clearInterval(this.timer);
        this.timer = 0;
        this.counter = 0;
        items.length = 0;
      }
    },
    
    ready: function(link) {
      var sheets = document.styleSheets;
      for( var i = 0, l = sheets.length; i < l; i++ ){
        if( sheets[ i ].media && sheets[ i ].media == link.media) {
          //link.media = "all";
          return true;
        }
      }
      
      return false;
    }
    
  };
  
  $.load_css = $.proxy($.css_loader.add, $.css_loader);
  
}(jQuery, document));