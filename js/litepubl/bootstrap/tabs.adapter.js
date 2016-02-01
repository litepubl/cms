(function( $, document, litepubl){
  'use strict';

litepubl.bs.tabs = Class.extend({
spinner: '<span class="fa fa-spin fa-spinner"></span>',

tabs: function(tabs) {
var self = this;
return tabs.each(function() {
var ul = $(this).children("ul");
ul.off("click.litepubl.tab").on("click.litepubl.tab", "a", function(e) {
e.preventDefault();
self.activate($(this));
});

//activate first item
if (!ul.children(".active").length) {
self.activate(ul.find("a:first"));
}
});
},

activate: function(link) {
var url = link.attr("data-ajax");
if (url && !link.data("loaded")) {
this.load(link, url);
}

link.tab("show");
},

load: function(link, url) {
link.data("loaded", "loading");
var tml = litepubl.tml.bootstraptabs;

//create panel if not exists
var panel = $(link.data("target") || link.attr("data-target") || this.striphref(link.attr("href")));
if (!panel.length) {
var parent = link.closest("ul").parent().children(".tab-content:first");
panel = $(tml.tab).appendTo(parent);
}

link.data("target", panel);
panel.html(tml.spinner);
			panel.attr( "aria-busy", "true" );

    return $.ajax(this.getajax(url, panel)).fail( function(jq, textStatus, errorThrown) {
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

return false;
}

add: function() {
}

});
  
litepubl.tabs = new litepubl.bs.tabs(".admintabs");
  $(document).ready(function() {
  });

})( jQuery, document, litepubl);