/**
* Lite Publisher
* Copyright (C) 2010 - 2014 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

/*  This plugin solve popover problen when trigger = "hover click". */
(function( $){
  'use strict';
  
$.fn.pophoverclick = function() {
    return this.off('mouseleave.popover')
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