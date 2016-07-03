/**
 * Lite Publisher CMS
 *  copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link https://github.com/litepubl\cms
 *  version 7.00
 *
 */


(function($, litepubl) {
  'use strict';

  litepubl.Tablecols = Class.extend({
    dropdown: false,
    table: false,
    headers: false,
    columns: false,
    keystorage: "",
    tml: '<li class="checkbox"><label><input type="checkbox" value="%%index%%" %%checked%% />%%title%%</label></li>',

    init: function(dropdown, tml) {
      this.dropdown = $(dropdown);
      this.table = this.dropdown.parent().find("table:first");
      this.headers = this.table.find("tr:first");
      this.keystorage = this.getkeystorage();

      if ("DataStorage" in litepubl) {
        this.load();
      } else {
        $.load_script(ltoptions.files + "/js/litepubl/system/storage.min.js", $.proxy(this.load, this));
      }

      if (tml) this.tml = tml;
      this.init_dropdown();
    },

    set: function(hide, index) {
      this.columns[index++] = hide;
      var column = this.table.find('td:nth-child(' + index + '),th:nth-child(' + index + ')');
      column[hide ? 'addClass' : 'removeClass']('hidden');
    },

    load: function() {
      var columns = litepubl.getdatastorage().get(this.keystorage);
      if (columns) {
        this.columns = columns;
        for (var i = 0; i < columns.length; i++) {
          this.set(columns[i], i);
        }
      } else {
        var th = this.headers.find("th");
        this.columns = [];
        this.columns.length = th.length;
      }
    },

    save: function() {
      litepubl.getdatastorage().set(this.keystorage, this.columns);
    },

    getkeystorage: function() {
      var result = "tablecols";
      if (ltoptions.idurl) {
        result += ltoptions.idurl;
      }

      return result;
    },
    init_dropdown: function() {
      var self = this;
      this.dropdown.find("button")
        .dropdown()
        .off("click.bs.dropdown")
        .on("click.tablecols", function() {
          var button = $(this);
          var parent = button.parent();
          if (!parent.hasClass('open')) {
            parent.find(".dropdown-menu").html(self.getmenu());
          }

          button.dropdown("toggle");
          return false;
        });

      this.dropdown.find(".dropdown-menu").on("click.tablecols", function(e) {
        e.stopPropagation();
        var target = $(e.target);
        if (target.is("[type=checkbox]")) {
          self.set(!target.prop("checked"), target.val());
          self.save();
        }
      });
    },

    getmenu: function() {
      var result = "";
      var tml = this.tml;
      var columns = this.columns;

      this.headers.find("th").each(function(index) {
        var th = $(this);
        if (!th.hasClass("col-checkbox")) {
          result += $.parsetml(tml, {
            index: index,
            title: th.text(),
            checked: columns[index] ? '' : 'checked="checked"'
          });
        }
      });

      return result;
    }

  });

}(jQuery, litepubl));