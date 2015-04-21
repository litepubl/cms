/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function ($, document) {
  'use strict';

    $.css_loader = {
    links: [],
    counter: 0,
maxcounter: 60,
    timer: false,
    
    add: function(url) {
      var link = $('<link rel="stylesheet" type="text/css" media="only x" href="' + url + '" />').appendTo("head:first").get(0);
      
      if (!this.ready(link)) {
        this.links.push(link);
        this.counter = this.maxcounter;
        if (!this.timer) this.timer = setInterval($.proxy(this.wait, this), 50);
      }
    },
    
    wait: function() {
      var links = this.links;
      for (var i = links.length - 1; i >= 0; i--) {
        if (this.ready(links[i])) {
          links.splice(i, 1);
        } else if (!this.counter) {
          links[i].media = "all";
        }
      }
      
      if (!links.length || (this.counter-- < 0)) {
        clearInterval(this.timer);
        this.timer = 0;
        this.counter = 0;
        links.length = 0;
      }
    },
    
    ready: function(link) {
      var url = link.href;
      var sheets = document.styleSheets;
      for( var i = 0, l = sheets.length; i < l; i++ ){
        if( sheets[ i ].href && sheets[ i ].href.indexOf(url) >= 0 ){
          link.media = "all";
          return true;
        }
      }
      
      return false;
    }
    
  };
  
  $.load_css = $.proxy($.css_loader.add, $.css_loader);
}(jQuery, document));