(function($, document, litepubl) {
  'use strict';

  litepubl.ui = litepubl.ui || {};
  litepubl.ui.Tabs = Class.extend({

    init: function() {
      this.tabs($($(".admintabs").toArray().reverse()));
    },

    tabs: function(tabs, events) {
      tabs.tabs({
        hide: true,
        show: true,
        beforeLoad: this.beforeLoad
      });

      if (events) {
        this.on(tabs, events);
      }

      return tabs;
    },

    on: function(tabs, events) {
      for (var name in events) {
        switch (name) {
          case 'before':
            tabs.on("tabsbeforeactivate.litepubl", function(event, ui) {
              event.panel = ui.newPanel;
              events.before(event);
            });
            break;

          case 'activated':
            tabs.on("tabsactivate.litepubl", function(event, ui) {
              event.panel = ui.newPanel;
              events.activated(event);
            });
            break;

          case 'loaded':
            tabs.on("tabsload.litepubl", function(event, ui) {
              event.panel = ui.panel;
              events.loaded(event);
            });
            break;
        }
      }
    },

    off: function(tabs) {
      tabs.off('.litepubl');
    },

    gettml: function() {
      return litepubl.tml.ui.tabs;
    },

    beforeLoad: function(event, ui) {
      if (ui.tab.data("loaded")) {
        event.preventDefault();
      } else {
        ui.jqXHR.success(function() {
          ui.tab.data("loaded", true);
        });
      }
    },

    setenabled: function(link, enabled) {
      link.closest('.admintabs').tabs(enabled ? 'enable' : 'disable', link.parent().index());
    }

  });

  $(document).ready(function() {
    litepubl.tabs = new litepubl.ui.Tabs();
  });

})(jQuery, document, litepubl);