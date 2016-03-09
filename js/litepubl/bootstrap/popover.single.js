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

  $.fn.singletip = function() {
    return this.off(".singletip")
      .on('show.bs.popover.singletip', function() {
        for (var i = popovers.length - 1; i >= 0; i--) {
          if (this === popovers[i]) {
            popovers.splice(i, 1);
          } else {
            $(popovers[i]).oldpopover("hide");
          }
        }

        popovers.push(this);
      })
      .on("hide.bs.popover.singletip", function() {
        for (var i = popovers.length - 1; i >= 0; i--) {
          if (this === popovers[i]) {
            popovers.splice(i, 1);
            return;
          }
        }
      })
  };

  $.fn.oldpopover = $.fn.popover;
  $.fn.popover = function(options) {
    if (typeof options === "object") {
      this.singletip();
    }

    return this.oldpopover(options);
  };

  $(document).ready(function() {
    $("body").on("click.singletooltip", function(e) {
      if (!popovers.length) return;

      for (var i = popovers.length - 1; i >= 0; i--) {
        if (e.target === popovers[i]) return;
        if ($.contains(popovers[i], e.target)) return;
      }

      if ($(e.target).closest(".popover").length) return;

      for (var i = popovers.length - 1; i >= 0; i--) {
        $(popovers[i]).oldpopover("hide");
      }

    });
  });

})(jQuery);