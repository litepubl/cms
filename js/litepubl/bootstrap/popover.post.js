/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

(function( $){
  'use strict';
  
  $.pophelp = {
    tml_link: '<a href="#" class="dashed"><span class="fa fa-question"></span>  %%title%%</a>',
    
    create: function(holder, title, content) {
      holder.html(this.tml_link.replace('%%title%%', title));
      holder.removeClass("hide");
      
      var link = holder.find("a");
      link.data("pophelp.content", content);
      return this.popover(link);
    },
    
    popover: function(link) {
      return link.addClass("popover-help")
      .popover(this.getoptions(link))
      .on("click.prevent", function() {
        return false;
      });
    },
    
    getoptions: function (link, options) {
      var self = this;
      return $.extend({
        container: 'body',
        delay: 120,
        html:true,
        trigger: 'hover focus click',
        placement: 'auto ' + (link.attr('data-placement') || 'right'),
        title: lang.dialog.help,
        content: function() {
          var holder = $(this);
          return holder.data("pophelp.content") || self.getcontent(holder);
        }
      }, options);
    },
    
    getcontent: function(holder) {
      var container = holder.data("pophelp.container") || $(holder.attr("data-holder") || holder.attr("href"));
      if (container.hasClass("text-to-list")) {
        var result = this.text2ul(container.text());
      }else {
        var result = container.data("popcontent") || container.html();
      }
      
      holder.data("pophelp.content", result);
      return result;
    },
    
    text2ul: function(s) {
      return"<ul><li>" +
      s.replace(/\n/gm, "</li><li>") +
      "</li></ul>";
    }
    
  };
  
  $.fn.pophelp = function() {
    return this.each(function() {
      $.pophelp.popover($(this));
    });
  };
  
  $.fn.createhelp = function() {
    return this.each(function() {
      var holder = $(this);
      $.pophelp.create(holder, holder.attr("title"), $.pophelp.text2ul(holder.text()));
      holder.removeAttr("title");
    });
  };
  
})( jQuery);