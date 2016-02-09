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

      $("#posteditor-init-files").one('click.initfiles', function() {
        litepubl.fileman = new litepubl.Fileman("#posteditor-files");
        return false;
      });

      this.tabs.closest('form').on('submit.posttitle', function(event) {
        var title = $("input[name='title']", this);
        if (!$.trim(title.val())) {
          event.stopImmediatePropagation();
event.preventDefault();
          $.messagebox(lang.dialog.error, lang.posteditor.emptytitle, function() {
            title.focus();
          });
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