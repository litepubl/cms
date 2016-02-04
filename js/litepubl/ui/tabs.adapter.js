(function($, document, litepubl) {
  'use strict';

  litepubl.ui = litepubl.ui || {};
  litepubl.ui.Tabs = Class.extend({

    init: function() {
      this.tabs($($(".admintabs").toArray().reverse()));
    },

    tabs: function(tabs, events) {
      return tabs.tabs({
        hide: true,
        show: true,
        beforeLoad: this.beforeLoad,
        beforeActivate: events && 'before' in events ? function(event, ui) {
          events.before(ui.newPanel);
        } : null,

        activate: events && 'activated' in events ? function(event, ui) {
          events.activated(ui.newPanel);
        } : null,

        load: events && 'loaded' in events ? function(event, ui) {
          events.loaded(ui.panel);
        } : null
      });
    },

    on: function(tabs, events) {
      for (var name in events) {
        switch (name) {
          case 'before':
            tabs.on("tabsbeforeactivate.litepubl", function(event, ui) {
              events.before(ui.newPanel);
            });
            break;

          case 'activated':
            tabs.on("tabsactivate.litepubl", function(event, ui) {
              events.activated(ui.newPanel);
            });
            break;

          case 'loaded':
            tabs.on("tabsload.litepubl", function(event, ui) {
              events.loaded(ui.panel);
            });
            break;
        }
      }
    },

    off: function(tabs) {
      tabs.off('.litepubl');
    },

    add: function() {},

    beforeLoad: function(event, ui) {
      if (ui.tab.data("loaded")) {
        event.preventDefault();
      } else {
        ui.jqXHR.success(function() {
          ui.tab.data("loaded", true);
        });
      }
    }

  });

  $(document).ready(function() {
    litepubl.tabs = new litepubl.ui.Tabs();
  });

})(jQuery, document, litepubl);