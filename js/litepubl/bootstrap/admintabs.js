(function( $, document, litepubl){
  'use strict';

litepubl.bs.tabs = Class.extend({

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

});
  
litepubl.tabs = new litepubl.bs.tabs(".admintabs");
  $(document).ready(function() {
  });

})( jQuery, document, litepubl);