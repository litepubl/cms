(function( $, document, litepubl){
  'use strict';

litepubl.bootstrap.tabs = Class.extend({

init: function() {
var self = this;
$(document).on('show.bs.tab', function(e) {
self.activate($(e.target));
})
.on('shown.bs.tab', function(e) {
self.activated($(e.target));
});

$(".nav-tab").each(function() {
});
},

tabs: function(tabs) {
var self = this;
return tabs.each(function() {
var ul = $(this).children("ul");
//activate first item
if (!ul.children(".active").length) {
ul.find("a:first").click();
}
});
},

activate: function(link) {
var url = link.attr("data-ajax");
if (url && !link.data("loaded")) {
this.load(link, url);
}

},

load: function(link, url) {
link.data("loaded", "loading");
var tml = litepubl.tml.bootstraptabs;

var panel = $(link.data("target") || link.attr("data-target") ||
 this.striphref(link.attr("href")) || 
('#' + link.attr('aria-controls')));

//create panel if not exists
if (!panel.length) {
var parent = link.closest("ul").parent().children(".tab-content:first");
var html = tml.tab.replace(/%%id%%/gim, litepubl.guid++);
panel = $(html).appendTo(parent);
}

link.data("target", panel);
panel.html(tml.spinner);
			panel.attr( "aria-busy", "true" );

    return $.ajax(this.getajax(url, panel)).fail( function(jq, textStatus, errorThrown) {
			panel.removeAttr( "aria-busy");
alert(jq.responseText);
});
},

getajax: function(url, panel) {
return {
      type: 'get',
      url: url,
      cache: false,
      dataType: "html",
      success: function(html) {
panel.html(html);
			panel.removeAttr( "aria-busy");
}
};
},

striphref: function(url) {
if (url) {
return url.replace(/.*(?=#[^\s]*$)/, '') // strip for ie7
}

return '';
}

add: function() {
}

});
  
litepubl.tabs = new litepubl.bs.tabs(".admintabs");
  $(document).ready(function() {
  });

})( jQuery, document, litepubl);