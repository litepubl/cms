(function ($, document, litepubl) {
  'use strict';

litepubl.tml.tablecols = {
hidecol: '<a href="#"  class="hidecolumn dashed tooltip-toggle" title="%%lang.hide%%"><span class="fa fa-caret-up"></span> </a>',
dropdown: '<div class="dropdown">' +
  '<button type="button" class="btn btn-default dropdown-toggle" id="guid-%%guid%%" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">' +
'%%title%%' +
'    <span class="caret"></span>' +
  '</button>' +
  
  '<ul class="dropdown-menu" aria-labelledby="guid-%%guid%%">' +
//items
  '</ul>' +
'</div>',

item: '<li><label class="checkbox"><input type="checkbox" value="%%index%%" %%checked%% />%%title%%</label></li>'
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

var self = this;
this.headers.on("click.hidecolumn", ".hidecolumn", function() {
self.set(false, $(this).closest("th").index());
self.save();
return false;
});

var th = this.headers.find("th");
this.columns = [];
this.columns.length = th.length;

var btn = litepubl.tml.tablecols.hidecol.replace("%%lang.hidecol%%", lang.admin.hidecol);
th.each(function() {
var $this= $(this);
if ($this.contains("input")) {
$this.addClass("col-ignore");
} else {
$this.data("title", $this.text());
$this.prepend(btn);
}
});

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
this.dropdown = this.table.closest("form").insertBefore(html);
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

this.headers.find("th not(.col-ignore)").each(function(index) {
result += $.parsetml(tml, {
index: index,
title: $(this).data("title"),
checked: columns[index] ? '' : 'checked="checked"'
});
});

return result;
}

});

$(document).ready(function() {
litepubl.tablecols = new litepubl.Tablecols("table:first");
});

}(jQuery, document, litepubl));