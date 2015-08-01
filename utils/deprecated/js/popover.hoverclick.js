/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

/*  This plugin solve popover problen when trigger = "hover click". */
(function( $){
  'use strict';
  
  $.fn.pophoverclick = function() {
    return this.off('mouseleave.bs.popover')
    .on('mouseleave.hoverclick', function(event) {
      var self = $(this);
      if (!self.data("hoverclicked")) self.data("bs.popover").leave(event);
    })
    .on("click.hoverclick", function(event) {
      var self = $(this);
      var clicked = self.data("hoverclicked");
      var popover = self.data("bs.popover");
      if (popover.tip().hasClass('in')) {
        if (clicked) popover.leave(event);
        clicked = !clicked;
      } else {
        popover.enter(event);
        clicked = true;
      }
      
      self.data("hoverclicked", clicked);
      return false;
    });
  };
  
})( jQuery);