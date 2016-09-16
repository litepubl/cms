/**
 * Lite Publisher CMS
 *
 *  copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link      https://github.com/litepubl\cms
 *  version   7.07
  */

(function($) {
  'use strict';

  $.fn.moveitem = function(options) {
    options = $.extend({
      speed: "slow",
      target: 1,
      wrap: '<div class="moveitem-wrap"><ul></ul></div>',
      temp: '<li style="list-style-type:none;width:5px;height:0"></li>'
    }, options);

    var parent = this.parent();
    var list = parent.children();
    var curindex = list.index(this);
    var newindex = typeof options.target === "number" ? curindex + options.target : list.index(options.target);
    if ((newindex < 0) || (newindex >= list.length) || (curindex == newindex)) return this;

    var h = this.height();
    var target = list.eq(newindex);

    $(options.temp).insertBefore(target).animate({
      height: h
    }, options.speed, function() {
      $(this).remove();
    });

    $(options.temp).insertBefore(this).css("height", h).animate({
      height: 0
    }, options.speed, function() {
      $(this).remove();
    });


    var pos = this.position();
    var tempprops = {
      position: "absolute",
      top: pos.top,
      left: pos.left,
      zIndex: 1200,
      backgroundColor: "rgba(255,255,255,05)"
    };

    var savedprops = {};
    for (var prop in tempprops) {
      savedprops[prop] = this.css(prop);
    }

    this.data("savedprops", savedprops)
      .css(tempprops)
      .insertBefore(target)
      .animate({
        top: target.position().top
      }, this.speed, function() {
        var self = $(this);
        self.css(self.data("savedprops"));
        self.removeData("savedprops");
      });


    return this;
  };

})(jQuery);