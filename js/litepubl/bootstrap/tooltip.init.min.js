(function( $){
  'use strict';
  
  $.fn.settooltip = function() {
return this.on("mouseenter.settooltip focus.settooltip click.settooltip",".tooltip-toggle",  function(event) {
var self = $(this);
if (self.data("bs.tooltip")) return;

    self.tooltip({
      container: 'body',
      placement: 'auto top'
    });

self.trigger(event);
});
  };

$.fn.removetooltip = function() {
this.find("tooltip-toggle").each(function() {
var data = $(this).data("bs.tooltip");
        if (data) {
          clearTimeout(data.timeout);
          if (("$tip" in data) && data.$tip) data.$tip.remove();
        }
});

return this;
};
  
})( jQuery);