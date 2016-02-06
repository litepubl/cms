/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 **/

(function($, document, window) {
  'use strict';

  litepubl.Posteditor = Class.extend({
    tabs: false,

    init: function() {
      this.tabs = $("#tabs");
      if (!this.tabs.length) return;

      var self = this;
      litepubl.tabs.tabs(this.tabs, {
        before: function(panel) {
          self.init_tab(panel);
        }
      });

      $("#posteditor-init-raw-tabs").one('click', function() {
        self.init_raw_tabs();
        return false;
      });

      $("#posteditor-init-files").one('click.initfiles', function() {
        litepubl.fileman = new litepubl.Fileman("#posteditor-files");
        return false;
      });

      this.tabs.closest('form').on('submit.posttitle', function(event) {
        var title = $("input[name='title']", this);
        if (!$.trim(title.val())) {
          event.stopImmediatePropagation();
          $.messagebox(lang.dialog.error, lang.posteditor.emptytitle, function() {
            title.focus();
          });
          return false;
        }
      });

    },

    init_tab: function(panel) {
if (!panel.data('init.litepubl')) {
panel.data('init.litepubl', true);

switch (panel.attr('data-id')) {
case 'tags':
var self = this;
          panel.on('click.tag', 'a', function() {
            self.addtag($(this).text());
            return false;
          });
break;

case 'posted':
var node = panel.get(0).firstChild;
while (node.nodeType  != 8) {
node = node.nextSibling;
}

panel.html(node.nodeValue);
      litepubl.calendar.on(panel);
break;

case 'seo':
var node = panel.get(0).firstChild;
while (node.nodeType  != 8) {
node = node.nextSibling;
}

panel.html(node.nodeValue);
break;
}
      }
    },

    addtag: function(newtag) {
      var tags = $('#text-tags').val();
      if (tags == '') {
        $('#text-tags').val(newtag);
      } else {
        var re = /\s*,\s*/;
        var list = tags.split(re);
        for (var i = list.length; i >= 0; i--) {
          if (newtag == list[i]) return false;
        }
        $('#text-tags').val(tags + ', ' + newtag);
      }
    },

    init_raw_tabs: function() {
      $("#posteditor-init-raw-tabs").remove();
      var holder = $("#posteditor-raw-holder");
      var html = holder.get(0).firstChild.nodeValue;
      $(holder.get(0).firstChild).remove();

      html = html.replace(/<comment>/gim, '<div class="tab-holder"><!--')
        .replace(/<\/comment>/gim, '--></div>');
      //divide on list and div's
      var i = html.indexOf('<div');
      $("#posteditor-raw").before(html.substring(0, i)).after(html.substring(i));

      litepubl.tabs(holder, {
        before: function(panel) {
          var inner = $(".tab-holder", panel);
          if (inner.length) {
            inner.replaceWith(inner.get(0).firstChild.nodeValue);
          }
        }
      });
    },

    init_visual_link: function(url, text) {
      $('<a href="#">' + text + '</a>').appendTo("#posteditor-visual").data("url", url).one("click", function() {
        $.load_script($(this).data("url"));
        $("#posteditor-visual").remove();
        return false;
      });
    }

  }); //posteditor

  $(document).ready(function() {
    try {
      litepubl.posteditor = new litepubl.Posteditor();
    } catch (e) {
      erralert(e);
    }
  });
}(jQuery, document, window));