/**
 * Lite Publisher CMS
 *
 *  copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link      https://github.com/litepubl\cms
 *  version   7.08
  */

(function($, litepubl) {
  'use strict';

  litepubl.Calendar = Class.extend({
    holderclass: ".calendar",
    rangeclass: ".date-range",
    dialogopened: false,
    width: 300,
    tml: '<div  class="datepicker-container"><div></div></div>',

    init: function() {
      this.on(this.holderclass);
      this.onrange(this.rangeclass);
    },

    on: function(holders) {
      var self = this;
      $(holders).each(function() {
        var inputs = $("input", this);
        var date = inputs.eq(0).addClass("date-edit");
        //var time = inputs.eq(1).addClass("time-edit");
        self.add($("button:first", this), date);
      });
    },

    onrange: function(holders) {
      var self = this;
      $(holders).each(function() {
        var inputs = $("input", this);
        var buttons = $("button", this);
        self.add(buttons.eq(0), inputs.eq(0).addClass("date-edit"));
        self.add(buttons.eq(1), inputs.eq(1).addClass("date-edit"));
      });
    },

    add: function(button, edit) {
      var self = this;
      $(button).data("date", edit).off("click.calendar").on("click.calendar", function() {
        self.open($(this).data("date"));
        return false;
      });
    },

    open: function(edit) {
      if (this.dialogopened) return false;
      this.dialogopened = true;

      var self = this;
      litepubl.datepicker.ready(function() {
        $.litedialog({
          title: lang.admin.calendar,
          html: self.tml,
          width: self.width,
          close: function() {
            self.dialogopened = false;
          },

          open: function(dialog) {
            var holder = self.getcontainer(dialog);
            litepubl.datepicker.datepicker(holder, edit);
          },

          buttons: [{
            title: lang.dialog.close,
            click: $.closedialog
          }]
        });
      });

    },

    getcontainer: function(dialog) {
      return dialog.find(".datepicker-container").children();
    }

  }); //class

  $(function() {
    litepubl.calendar = new litepubl.Calendar();
  });
}(jQuery, litepubl));