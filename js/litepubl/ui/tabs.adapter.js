(function( $, document, litepubl){
  'use strict';

litepubl.ui.tabs = Class.extend({

tabs: function(tabs) {
return tabs.each(function() {
var list = $(this).children('ul').children();

//prepare ajax

//activate first item
if (!list.filter(".active")) {
list.first().tab("show");
}
},

add: function() {
}

before: function(event, ui) {
    if (ui.tab.data("loaded")) {
      event.preventDefault();
    } else {
    ui.jqXHR.success(function() {
      ui.tab.data("loaded", true);
    });
}
  },

  $.inittabs = function() {
      $($(".admintabs").toArray().reverse()).tabs({
        hide: true,
        show: true,
        beforeLoad: this.before
      });
    });
  };


});
  
litepubl.tabs = new litepubl.bs.tabs(".admintabs");

  $(document).ready(function() {
  });

})( jQuery, document, litepubl);