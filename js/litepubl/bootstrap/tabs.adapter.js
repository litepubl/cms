(function($, document, litepubl) {
  'use strict';

  litepubl.bootstrap = litepubl.bootstrap || {};
  litepubl.bootstrap.Tabs = Class.extend({

    init: function() {
      var self = this;
      $(document).on('show.bs.tab', function(e) {
          var link = $(e.target);
          self.before(link);
          self.event('before', link);
        })
        .on('shown.bs.tab', function(e) {
          self.event('activated', $(e.target));
        });

      this.navtabs($($(".nav-tabs").toArray().reverse()));
    },

    tabs: function(tabs, events) {
      this.navtabs(tabs.children('.nav-tabs'));
      if (events) {
        tabs.data('tabevents.litepubl', events);
      }
    },

    navtabs: function(navtabs) {
      //activate first item
      return navtabs.each(function() {
        var ul = $(this);
        if (!ul.children(".active").length) {
          ul.find("a:first").click();
        }
      });
    },

    before: function(link) {
      var url = link.attr("data-ajax");
      if (url && !link.data("loaded")) {
        this.load(link, url);
      }
    },

    getpanel: function(link) {
      var panel = $(link.data("target") || link.attr("data-target") ||
        this.striphref(link.attr("href")) ||
        ('#' + link.attr('aria-controls')));

      if (panel) {
        link.data('target', panel);
      }

      return panel;
    },

    load: function(link, url) {
      link.data("loaded", "loading");
      var tml = litepubl.tml.bootstrap.tabs;
      link.data("spin", $(tml.spin).prependTo(link));
      var panel = this.getpanel(link);

      //create panel if not exists
      if (!panel.length) {
        var parent = link.closest("ul").parent().children(".tab-content:first");
        var html = tml.tab.replace(/%%id%%/gim, litepubl.guid++);
        panel = $(html).appendTo(parent);
        link.data("target", panel);
      }

      panel.attr("aria-busy", "true");
      var self = this;
      return $.ajax(this.getajax(url, function(html) {
          panel.html(html);
          self.event('loaded', link);
        }))
        .always(function() {
          panel.removeAttr("aria-busy");
          link.data("loaded", "loaded");
          link.data('spin').remove();
          link.removeData('spin');
        })
        .fail(function(jq, textStatus, errorThrown) {
          alert(jq.responseText);
        });
    },

    getajax: function(url, success) {
      return {
        type: 'get',
        url: url,
        cache: false,
        dataType: "html",
        success: success
      };
    },

    striphref: function(url) {
      if (url) {
        return url.replace(/.*(?=#[^\s]*$)/, '') // strip for ie7
      }

      return '';
    },

    add: function() {},

    on: function(tabs, events) {
      tabs.data("tabevents.litepubl", events);
    },

    off: function(tabs) {
      tabs.data('tabevents.litepubl', false);
    },

    event: function(name, link) {
      var events = link.closest('.adminpanel').data('tabevents.litepubl');
      if (events && name in events) {
        events[name](this.getpanel(link));
      }
    }

  });

  $(document).ready(function() {
    litepubl.tabs = new litepubl.bootstrap.Tabs();
  });

})(jQuery, document, litepubl);