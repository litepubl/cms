/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

(function ($, document, litepubl) {
  'use strict';
  
  litepubl.tml.tablecols = {
    dropdown: '<div class="dropdown">' +
    '<button type="button" class="btn btn-default dropdown-toggle" id="guid-%%guid%%" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' +
    '%%title%%' +
    '    <span class="caret"></span>' +
    '</button>' +
    
    '<ul class="dropdown-menu" aria-labelledby="guid-%%guid%%">' +
    //items
    '</ul>' +
    '</div>',
    
    item: '<li class="checkbox"><label><input type="checkbox" value="%%index%%" %%checked%% />%%title%%</label></li>'
  };
  
  litepubl.Tablecols = Class.extend({
    table: false,
    headers: false,
    dropdown: false,
    columns: false,
    keystorage: "",
    
    init: function(table) {
      this.table = $(table);
      this.headers = this.table.find("tr:first");
      this.keystorage = this.getkeystorage();
      this.load();
      this.create_dropdown();
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
    
    create_dropdown: function() {
      var html = $.parsetml(litepubl.tml.tablecols.dropdown, {
        guid: litepubl.guid++,
        title: lang.admin.togglecols
      });
      
      var self = this;
      this.dropdown = $(html).insertBefore(this.table.closest("form"));
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
      var tml = litepubl.tml.tablecols.item;
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
  
  $(document).ready(function() {
    litepubl.tablecols = new litepubl.Tablecols("table:first");
  });
  
}(jQuery, document, litepubl));