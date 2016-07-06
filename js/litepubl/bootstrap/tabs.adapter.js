/**
 * Lite Publisher CMS
 *
 *  copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link      https://github.com/litepubl\cms
 *  version   7.00
  */

(function($, document, litepubl) {
  'use strict';

  litepubl.bootstrap = litepubl.bootstrap || {};
  litepubl.bootstrap.Tabs = Class.extend({
    //event namespace
    namespace: '.litepubl.tabs',

    init: function() {
      var self = this;
      $(document).on('show.bs.tab', function(e) {
          var link = $(e.target);
          self.before(link);
          self.trigger('before', link);
        })
        .on('shown.bs.tab', function(e) {
          self.trigger('activated', $(e.target));
        });

      var self = this;
      this.navtabs($($(".admintabs>.nav-tabs").toArray().reverse()));
    },

    tabs: function(tabs, events) {
      this.navtabs(tabs.children('.nav-tabs'));
      this.on(tabs, events);
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
      this.trigger('beforeLoad', link);

      var self = this;
      return $.ajax(this.getajax(url, function(html) {
          panel.html(html);
          self.trigger('loaded', link);
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

    gettml: function() {
      return litepubl.tml.bootstrap.tabs;
    },

    on: function(tabs, events) {
      if (tabs && events) {
        for (var name in events) {
          tabs.on(name + this.namespace, events[name]);
        }
      }
    },

    off: function(tabs) {
      tabs.off(this.namespace);
    },

    trigger: function(name, link) {
      var panel = this.getpanel(link);
      link.closest('.admintabs').trigger($.Event(name + this.namespace, {
        target: link[0],
        relatedTarget: panel[0],
        panel: panel
      }));
    },

    setenabled: function(link, enabled) {
      if (enabled) {
        link.attr("data-toggle", "tab");
        link.removeAttr("aria-disabled");
        link.parent().removeClass("disabled");
        link.off("click.disabled");
      } else {
        link.attr("data-toggle", "disabled");
        link.attr("aria-disabled", "true");
        link.parent().addClass("disabled");
        link.on("click.disabled", function() {
          return false;
        });
      }
    }

  });

  $(document).ready(function() {
    litepubl.tabs = new litepubl.bootstrap.Tabs();
  });

})(jQuery, document, litepubl);