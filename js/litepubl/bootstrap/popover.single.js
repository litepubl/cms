/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 **/

(function($) {
  'use strict';

  var popovers = [];

  $.closepopovers = function() {
    if (!popovers.length) return;

    for (var i = popovers.length - 1; i >= 0; i--) {
      $(popovers[i]).oldpopover("hide");
    }

    popovers.length = 0;
  };

  $.fn.setpopover = function(selector, options) {
    selector = selector || '.popover-toggle';
    return this.on('mouseenter.setpopover focus.setpopover', selector + ":not(.popover-ready)", function(event) {
      var self = $(this);
      self.addClass("popover-ready");
      if (self.data("bs.popover")) return;

var o = $.extend({
          container: 'body',
          placement: 'auto right',
        trigger: 'hover focus'
        }, options);

      self.trigger($.Event('getoptions.popover', {
target: this,
        relatedTarget: event.relatedTarget,
options: o
}))
.popover(o)
        .on('show.bs.popover.singletip', function() {
          popovers.push(this);
        })
        .on('hide.bs.popover.singletip', function() {
          //remove from popovers array
          for (var i = popovers.length - 1; i >= 0; i--) {
            if (this === popovers[i]) {
              popovers.splice(i, 1);
              return;
            }
          }
});

      self.trigger(event);
    });
  };

  $(document).ready(function() {
    $("body").on("click.singlepopover", function(e) {
      if (!popovers.length) return;

var target = e.target;
      for (var i = popovers.length - 1; i >= 0; i--) {
        if (target === popovers[i]) return;
        if ($.contains(popovers[i], target)) return;
      }

      if ($(target).closest(".popover").length) return;

      for (var i = popovers.length - 1; i >= 0; i--) {
        $(popovers[i]).popover("hide");
      }

    });
  });

})(jQuery);