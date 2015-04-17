/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function( $, window, document, litepubl){
  'use strict';
  
  litepubl.BootstrapWidgets = Class.extend({
    toggleclass: "",
    
    init: function(options) {
      options = $.extend({
        button: ".widget-button, .widget-title",
        inline: ".widget-inline",
        ajax: ".widget-ajax",
        toggle: "fa-expand fa-collapse"
      },options);
      
      var self = this;
      self.toggleclass = options.toggle;
      var widget_class = options.inline + ',' + options.ajax;
      $(options.button).each(function() {
        var button = $(this);
        var span = button.find(widget_class);
        if (span.length) return self.init_button(button, span);
        
        //no ajax or inline, init necessary plugins
        switch (button.data("model")) {
          case "dropdown":
          button.dropdown();
          break;
          
          case 'collapse':
          button.collapse();
          break;
          
          case "slide":
          button.on("click.widget", function() {
            $(this).next().slideToggle();
            return false;
          });
          break;
          
          case "popover":
          break;
        }
      });
    },
    
    init_button: function(button, span) {
      button.data("span", span);
      if (button.data("model") == "wrap-collapse") {
        var content = "#widget-content-" + span.data("widget").id;
        $(content).addClass("panel-collapse collapse");
        span.wrap('<a class="dashed" href="' + content + '" title="' + lang.widgetlang.clickme + '"></a>');
        span.parent().tooltip({
          container: 'body',
          placement: 'auto top'
        });
      }
      
      var self = this;
      button.one('click.widget', function() {
        var btn = $(this);
        switch (btn.data("span").data("widget").type) {
          case  'inline':
          self.addinline(btn);
          break;
          
          case 'ajax':
          self.load(btn);
          break;
        }
        return false;
      });
    },
    
    load: function(button) {
      var widget = button.data("span").data("widget");
      var self = this;
      $.get(ltoptions.url + '/getwidget.htm', {
        id: widget.id,
        sidebar: widget.sidebar,
        themename: ltoptions.theme.name,
        idurl: ltoptions.idurl
      }, function (html) {
        widget.html = html;
        widget.comment = button.findcomment(widget.id);
        self.add(button);
      }, 'html');
    },
    
    addinline: function(button) {
      var widget = button.data("span").data("widget");
      widget.comment = button.findcomment(false);
      if (! widget.comment) return alert('Widget content not found');
      widget.html =  widget.comment.nodeValue;
      this.add(button);
    },
    
    add: function(button) {
      var span = button.data("span")
      var widget = span.data("widget");
      switch (button.data("model")) {
        case "dropdown":
        widget.body = $(widget.comment).replaceComment( widget.html);
        widget.comment = false;
        button.dropdown("toggle");
        break;
        
        case 'wrap-collapse':
        widget.body = $(widget.comment).replaceComment( widget.html);
        widget.comment = false;
        $("#widget-content-" + widget.id).addClass("in");
        button.find("a")
        .attr("data-toggle", "collapse")
        .attr("data-parent", '#' + button.closest(".panel-group").attr("id"))
        .attr("data-target", "#widget-content-" + widget.id);
        break;
        
        case "slide":
        widget.body = $(widget.comment).replaceComment( widget.html);
        widget.comment = false;
        var self = this;
        self.toggleicon(span);
        button.data("body", widget.body)
        .on("click.widget", function() {
          var btn = $(this);
          self.toggleicon(btn.data("span"));
          btn.data("body").slideToggle();
          return false;
        });
        break;
        
        case "popover":
        if (widget.comment) $(widget.comment).remove();
        widget.comment = false;
        
        var span = button.data("span");
        this.toggleicon(span);
        button.popover({
          title: span.text(),
          html: widget.html,
          container: "body",
          placement: button.data("placement"),
          trigger: "manual"
        });
        
        var self = this;
        button.on("click.widget", function() {
          var btn = $(this);
          btn.popover('toggle');
          self.toggleicon(btn.data("span"));
          return false;
        });
        break;
      }
    },
    
    toggleicon: function(span) {
      span.find("i").toggleClass(this.toggleclass);
    }
    
  });
  
  $.ready2(function() {
    if ("dropdown" in $.fn) litepubl.widgets = new litepubl.BootstrapWidgets();
  });
})( jQuery , window, document, litepubl);