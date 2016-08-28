/**
 * Lite Publisher CMS
 *
 *  copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link      https://github.com/litepubl\cms
 *  version   7.05
 */

(function($, window, document) {
  'use strict';

  $.BootstrapDialog = Class.extend({
    dialog: false,
    footer: false,
    style: false,
    options: false,
    padwidth: 32,
    padheight: 0,
    removeOnclose: true,

    tml: '<div class="modal fade" id="dialog-%%id%%" tabindex="-1" role="dialog" aria-labelledby="modal-title-%%id%%">' +
      '<div class="modal-dialog" role="document">' +
      '<div class="modal-content">' +
      '<div class="modal-header">' +
      '<button type="button" class="close" data-dismiss="modal" aria-label="%%close%%"><span aria-hidden="true">&times;</span><span class="sr-only">%%close%%</span></button>' +
      '<h4 class="modal-title" id="modal-title-%%id%%">%%title%%</h4>' +
      '</div>' +
      '<div class="modal-body">%%body%%</div>' +
      '<div class="modal-footer">%%buttons%%</div>' +
      '</div></div></div>',

    button: '<button type="button" class="btn btn-default" id="button-%%id%%-%%index%%" data-index="%%index%%">%%icon%%%%title%%</button>',

    init: function() {
      var close = $.proxy(this.close, this);
      $.closedialog = close;
      $.litedialog = $.proxy(this.open, this);

      this.default_options = {
        title: "",
        html: "",
        css: "",
        width: false,
        height: false,
        open: $.noop,
        close: $.noop,
        buttons: [{
          title: "Ok",
          click: close
        }]
      };
    },

    close: function(callback) {
      if (!this.dialog) return false;
      if ($.isFunction(callback)) {
        this.dialog.on("hidden.bs.modal", function() {
          setTimeout(function() {
            callback();
          }, 20);
        });
      }

      this.dialog.modal("hide");
    },

    doclose: function() {
      if (this.dialog && $.isFunction(this.options.close)) {
        this.options.close(this.dialog);
      }

      if (this.removeOnclose) {
        this.remove();
      }
    },

    remove: function() {
      if (!this.dialog) return false;

      this.trigger('remove');
      this.options = false;
      this.footer = false;
      this.dialog.find(".tooltip-toggle, .tooltip-ready").tooltip("destroy");
      this.dialog.find(".popover-toggle").popover("destroy");
      this.dialog.remove();
      this.dialog = false;

      if (this.style) {
        this.style.remove();
        this.style = false;
      }
    },

    closetips: function() {
      if ("closetooltips" in $) $.closetooltips();
      if ("closepopovers" in $) $.closepopovers();
    },

    addstyle: function() {
      var options = this.options;
      var css = options.css;
      if (options.width) css = css + ".modal-dialog{width:" + (options.width + this.padwidth) + "px}";
      if (options.height) css = css + ".modal-dialog{height:" + (options.height + this.padheight) + "px}";
      if (!options.buttons.length) css = css + '.modal-footer{display:none}';
      if (css) this.style = $('<style type="text/css">' + css + '</style>').appendTo("head:first");
    },

    open: function(o) {
      if (this.dialog) return alert('Dialog already opened');
      this.closetips();

      var id = litepubl.guid++;
      this.options = $.extend({}, this.default_options, o);

      var buttons = this.options.buttons;
      var html_buttons = '';
      for (var i = 0, l = buttons.length; i < l; i++) {
        var btnitem = buttons[i];
        html_buttons += $.simpletml(this.button, {
          index: i,
          id: id,
          title: btnitem.title,
          icon: "icon" in btnitem ? btnitem.icon : ""
        });
      }

      //single button change class to "btn-primary"
      if (buttons.length == 1) html_buttons = html_buttons.replace(/btn-default/g, "btn-primary");

      var html = $.simpletml(this.tml, {
        id: id,
        title: this.options.title,
        close: lang.dialog.close,
        body: this.options.html,
        buttons: html_buttons
      });

      this.addstyle();
      var dialog = this.dialog = $(html).appendTo("body");
      //dialog.settooltip();
      this.footer = $(".modal-footer:first", dialog);
      for (var i = 0, l = buttons.length; i < l; i++) {
        this.getbutton(i).on("click.dialog", buttons[i].click);
      }

      return dialog
        .fixAndroid()
        .on("shown.bs.modal.dialog", $.proxy(this.opened, this))
        .on("hidden.bs.modal.dialog", $.proxy(this.doclose, this))
        .modal();
    },

    opened: function() {
      if ($.isFunction(this.options.open)) {
        this.options.open(this.dialog);
      }

      this.trigger('opened');
    },

    trigger: function(name) {
      this.dialog.trigger($.Event(name + '.dialog.litepubl', {
        target: this.dialog[0],
        dialog: this
      }));
    },

    getbutton: function(index) {
      if (!this.footer) return false;
      return this.footer.find("button[data-index=" + index + "]");
    },

    geticon: function(name) {
      if (!name) return '';

      var icons = {
        info: "info-circle",
        success: "check",
        //warning: "warning",
        danger: "times-circle",
        error: "times-circle"
      };

      if (name in icons) name = icons[name];
      return '<span class="fa fa-' + name + '"></span>';
    }

  });

  $.ready2(function() {
    if ("modal" in $.fn) $.bsdialog = new $.BootstrapDialog();
  });

})(jQuery, window, document);