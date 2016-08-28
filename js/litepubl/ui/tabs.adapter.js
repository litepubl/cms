/**
 * Lite Publisher CMS
 *
 *  copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link      https://github.com/litepubl\cms
 *  version   7.04
  */

(function($, litepubl) {
  'use strict';

  litepubl.ui = litepubl.ui || {};
  litepubl.ui.Tabs = Class.extend({
    namespace: '.litepubl.tabs',
    uispace: '.litepubl.uitabs',

    init: function() {
      this.tabs($($(".admintabs").toArray().reverse()));
    },

    tabs: function(tabs, events) {
      tabs.tabs({
        hide: true,
        show: true,
        beforeLoad: this.beforeLoad
      });

      this.proxy(tabs);
      if (events) {
        this.on(tabs, events);
      }

      return tabs;
    },

    proxy: function(tabs) {
      var self = this;
      tabs
        .on('tabsbeforeactivate' + this.uispace, function(event, ui) {
          tabs.trigger($.Event('before' + self.namespace, {
            target: event.target,
            relatedTarget: event.relatedTarget,
            panel: ui.newPanel
          }));
        })
        .on('tabsactivate' + this.uispace, function(event, ui) {
          tabs.trigger($.Event('activated' + self.namespace, {
            target: event.target,
            relatedTarget: event.relatedTarget,
            panel: ui.newPanel
          }));
        })
        .on('tabsload' + this.uispace, function(event, ui) {
          tabs.trigger($.Event('loaded' + self.namespace, {
            target: event.target,
            relatedTarget: event.relatedTarget,
            panel: ui.panel
          }));
        });
    },

    on: function(tabs, events) {
      if (tabs && events) {
        for (var name in events) {
          tabs.on(name + this.namespace, events[name]);
        }
      }
    },

    off: function(tabs) {
      tabs.off(this.namespace).off(this.uispace);
    },

    beforeLoad: function(event, ui) {
      if (ui.tab.data("loaded")) {
        event.preventDefault();
      } else {
        panel.closest('.admintabs').trigger($.Event('beforeLoad' + this.namespace, {
          target: event.target,
          relatedTarget: event.relatedTarget,
          panel: ui.panel
        }));

        ui.jqXHR.success(function() {
          ui.tab.data("loaded", true);
        });
      }
    },

    setenabled: function(link, enabled) {
      link.closest('.admintabs').tabs(enabled ? 'enable' : 'disable', link.parent().index());
    },

    gettml: function() {
      return litepubl.tml.ui.tabs;
    }

  });

  $(function() {
    litepubl.tabs = new litepubl.ui.Tabs();
  });

})(jQuery, litepubl);