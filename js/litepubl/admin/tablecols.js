(function ($, document) {
  'use strict';

$.Tablecols = Class.extend({
table: false,
tml_hide: '<a href="#"  class="hidecolumn dashed tooltip-toggle" title="%%lang.hidecol%%">' +
'<span class="fa fa-caret-up"></span> ' +
'</a>',

init: function(table) {
this.table = $(table);
var self = this;
var tr = this.table.find("tr:first")
tr.on("click.hidecolumn", ".hidecolumn", function() {
var th = $(this).closest("th");
th.addClass("hidden");
self.hidecolumn()th.index();
return false;
});

tr.find("th").prepend(
this.tml_hide.replace('%%lang.hidecol%%", lang.hidecol)
);
},
});
$(document).ready(function() {
  $(document).on("click", "input[name^='checkbox-showcolumn-']", function() {
    var index = $(this).val();
    var sel = 'td:nth-child(' + index + '),th:nth-child(' + index + ')';
    if ($(this).prop("checked")) {
      $(sel).show();
    } else {
      $(sel).hide();
    }
  });
  
  $("input[name='invert_checkbox']").click(function() {
    $(this).closest("form").find("input[type=checkbox]").each(function() {
      if ('togglecolumn' != $(this).prop('rel')) {
        $(this).prop("checked", ! $(this).prop("checked"));
      }
    });
    $(this).prop("checked", false);
  });
});

}(jQuery, document, window));