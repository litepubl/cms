/**
 * Lite Publisher CMS
 *
 *  copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link      https://github.com/litepubl\cms
 *  version   7.00
  */

(function($, window, document, litepubl) {
  'use strict';

  litepubl.BootstrapWidgets = Class.extend({
    tml_wrap_collapse: '<a class="dashed tooltip-toggle" href="%%idcontent%%" title="%%lang.clickme%%" aria-expanded="false"></a>',

    init: function(options) {
      options = $.extend({
        button: ".widget-button, .widget-title",
        inline: ".widget-inline",
        ajax: ".widget-ajax"
      }, options);

      var self = this;

      var widget_class = options.inline + ',' + options.ajax;
      $(options.button).each(function() {
        var button = $(this);
        var span = button.find(widget_class);
        if (span.length) return self.init_button(button, span);

        //no ajax or inline, init necessary plugins
        switch (button.attr("data-model")) {
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
      if (button.data("model") == "widget-collapse") {
        var idcontent = "#widget-content-" + span.data("widget").id;
        $(idcontent).addClass("panel-collapse");

        span.wrap($.parsetml(this.tml_wrap_collapse, {
          idwidget: span.data("widget").id,
          idcontent: idcontent,
          lang: lang.widgetlang
        }));
      }

      var self = this;
      button.one('click.widget', function() {
        var btn = $(this);
        switch (btn.data("span").data("widget").type) {
          case 'inline':
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
      }, function(html) {
        widget.html = html;
        widget.comment = button.findcomment(widget.id);
        self.add(button);
      }, 'html');
    },

    addinline: function(button) {
      var widget = button.data("span").data("widget");
      widget.comment = button.findcomment(false);
      if (!widget.comment) return alert('Widget content not found');
      widget.html = widget.comment.nodeValue;
      this.add(button);
    },

    add: function(button) {
      var span = button.data("span");
      var widget = span.data("widget");
      switch (button.attr("data-model")) {
        case "dropdown":
          widget.body = $(widget.comment).replaceComment(widget.html);
          widget.comment = false;

          button.dropdown("toggle");
          break;

        case 'widget-collapse':
          widget.body = $(widget.comment).replaceComment(widget.html);
          widget.comment = false;
          var id_body = "widget-content-" + widget.id;

          $("#" + id_body)
            .addClass("in")
            .attr("aria-expanded", "true");

          button.find("a")
            .removeClass("collapsed")
            .attr("data-toggle", "collapse")
            .attr("data-parent", '#' + button.closest(".panel-group").attr("id"))
            .attr("data-target", "#" + id_body)
            .attr("aria-controls", id_body)
            .attr("aria-expanded", "true")
            .collapse()
            .click(false);
          break;
      }
    }

  });

  $.ready2(function() {
    if ("dropdown" in $.fn) litepubl.widgets = new litepubl.BootstrapWidgets();
  });
})(jQuery, window, document, litepubl);