/**
* Lite Publisher
* Copyright (C) 2010 - 2014 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function( $){
  'use strict';

function get_popover_options($this, options) {
return $.extend({
      container: 'body',
      delay: 120,
      html:true,
      trigger: 'hover focus click',
      placement: 'auto ' + ($this.attr('data-placement') || 'right')
}, options);
}
  
  $.fn.poppost = function() {
    return this.popover(get_popover_options(this, {
trigger:  'hover',
title: function() {
        return $(this).find("poptitle:first").text();
      },
      
content: function() {
        return $(this).find(".poptext:first").html();
      }
}));
};
  
  $.fn.poptext = function() {
    return this.popover(get_popover_options(this, {
      content: function() {
        var self = $(this);
        return $(self.attr("data-holder") || self.attr("href")).html();
      }
    }))
.pophoverclick();
  };
  
  $.fn.pophelp = function() {
    return this.popover(get_popover_options(this, {
      title: lang.dialog.help,
      content: function() {
        var self = $(this);
        var holder = self.data("pophelp.holder");
        if (!holder) {
          holder = $(self.attr("data-holder") || self.attr("href"));
          self.data("pophelp.holder", holder);
if (holder.hasClass("text-to-list")) {
var s = holder.text();
s = "<ul><li>" + s.replace("\n", "</li><li>") + "</li></ul>";
holder.data("popcontent", s);
return s;
}
        }
        
        return holder.data("popcontent") || holder.html();
      }
    }))
.pophoverclick();
  };
  
})( jQuery);