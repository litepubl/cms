/**
* Lite Publisher
* Copyright (C) 2010 - 2014 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function( $){
  'use strict';
  
  $.fn.poppost = function() {
    return this.popover({
      container: 'body',
      delay: 120,
      html:true,
      placement: 'auto ' + (this.attr('data-placement') || 'right'),
      trigger: 'hover',
      title: function() {
        return $(this).find("poptitle:first").text();
      },
      
      content: function() {
        return $(this).find(".poptext:first").html();
      }
    });
  };
  
  $.fn.poptext = function() {
    return this.popover({
      container: 'body',
      delay: 120,
      html:true,
      placement: 'auto ' + (this.attr('data-placement') || 'right'),
      trigger: 'hover focus click',
      content: function() {
        var self = $(this);
        return $(self.attr("data-holder") || self.attr("href")).html();
      }
    });
  };
  
  $.fn.pophelp = function() {
    return this.popover({
      container: 'body',
      delay: 120,
      html:true,
      placement: 'auto ' + (this.attr('data-placement') || 'right'),
      trigger: 'hover focus click',
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
    })
.pophoverclick();
  };
  
})( jQuery);