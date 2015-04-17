/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function( $, window, document){
  'use strict';
  
  $.BootstrapDialog = Class.extend({
    dialog: false,
    footer: false,
    style: false,
    options: false,
    
    tml: '<div class="modal fade" id="dialog-%%id%%" tabindex="-1" role="dialog" aria-hidden="true" aria-labelledby="modal-title-%%id%%">' +
    '<div class="modal-dialog center-block"><div class="modal-content">' +
    '<div class="modal-header">' +
    '<button type="button" class="close" data-dismiss="modal" aria-label="%%close%%"><span aria-hidden="true">&times;</span></button>' +
    '<h4 class="modal-title" id="modal-title-%%id%%">%%title%%</h4>' +
    '</div>' +
    '<div class="modal-body">%%body%%</div>' +
    '<div class="modal-footer">%%buttons%%</div>' +
    '</div></div></div>',
    
    button: '<button type="button" class="btn btn-default" id="button-%%id%%-%%index%%" data-index="%%index%%">%%title%%</button>',
    
    init: function() {
      var close = $.proxy(this.close, this);
      $.closedialog = close;
      $.litedialog = $.proxy(this.open, this);
      
      this.default_options = {
        title: "",
        html: "",
        width: false,
        height: false,
        open: $.noop,
        close: $.noop,
        buttons: [
        {
          title: "Ok",
          click: close
        }
        ]
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
      if (!this.dialog) return false;
      
      if ($.isFunction(this.options.close)) this.options.close(this.dialog);
      this.options = false;
      this.footer = false;
      this.dialog.remove();
      this.dialog = false;
      if (this.style) {
        this.style.remove();
        this.style = false;
      }
    },
    
    open: function(o) {
      if (this.dialog) return alert('Dialog already opened');
      var id = litepubl.guid++;
    this.options = $.extend({}, this.default_options, o);
      
      var buttons = this.      options.buttons;
      var html_buttons = '';
      for (var i =0, l= buttons.length;  i < l; i++) {
        html_buttons += $.simpletml(this.button, {
          index: i,
          id: id,
          title:  buttons[i].title
        });
      }
      
      //single button change class to "btn-primary"
      if (buttons.length == 1) html_buttons = html_buttons.replace(/%%btn-default%%/g, "btn-primary");
      
      var html = $.simpletml(this.tml, {
        id: id,
        title: this.options.title,
        close: lang.dialog.close,
        body: this.options.html,
        buttons: html_buttons
      });
      
      var css = "";
      if (this.options.width) css = css + "width:" + (this.options.width + 32) + "px;";
      if (this.options.height) css = css + "height:" + this.options.height + "px;";
    if (css) this.style = $('<style type="text/css">.modal-dialog{' + css + '}</style>').appendTo("head:first");
      
      this.dialog = $(html).appendTo("body");
      this.footer =       $(".modal-footer:first", this.dialog);
      for (var i =0, l= buttons.length;  i < l; i++) {
        this.getbutton(i).on("click.dialog", buttons[i].click);
      }
      
      var self = this;
      this.dialog.on("shown.bs.modal", function() {
        if ($.isFunction(self.options.open)) self.options.open(self.dialog);
        if ("tooltip" in $.fn) {
          $(".tooltip-toggle", this).tooltip({
            container: 'body',
            placement: 'auto top'
          });
        }
      })
      .modal()
      .on("hidden.bs.modal", $.proxy(this.doclose, this));
      return this.dialog;
    },
    
    getbutton: function(index) {
      if (!this.footer) return false;
      return $("button[data-index=" + index + "]", this.footer);
    }
    
  });
  
  $.ready2(function() {
    if ("modal" in $.fn) $.bootstrapDialog = new $.BootstrapDialog();
  });
  
})( jQuery, window, document );