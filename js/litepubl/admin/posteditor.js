/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

(function($, litepubl) {
  'use strict';

  litepubl.Posteditor = Class.extend({
    tabs: false,

    init: function() {
      var tabs = $("#tabs");
      if (tabs.length) {
        this.init_tabs(tabs);
        this.ontitle(tabs.closest('form'));
      }
    },

    init_tabs: function(tabs) {
      this.tabs = tabs;
      var self = this;
      litepubl.tabs.on(tabs, {
        before: function(e) {
          self.init_tab(e.panel);
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
            panel.extractComment();
            litepubl.calendar.on(panel);
            break;

          case 'seo':
            panel.extractComment();
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

    ontitle: function(form) {
      form.on('submit.posttitle', function(event) {
        var title = $("input[name='title']", this);
        if (!$.trim(title.val())) {
          event.stopImmediatePropagation();
          event.preventDefault();
          $.messagebox(lang.dialog.error, lang.posteditor.emptytitle, function() {
            title.focus();
          });
        }
      });

    }

  }); //posteditor

  $(document).ready(function() {
    litepubl.posteditor = new litepubl.Posteditor();
  });

}(jQuery, litepubl));