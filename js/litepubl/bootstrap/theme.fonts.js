/**
* Litepublisher shop script
* Copyright (C) 2010 - 2014 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Comercial license. IMPORTANT: THE SOFTWARE IS LICENSED, NOT SOLD. Please read the following License Agreement (plugins/shop/license.txt)
* You can use one license on one website
**/

(function( $, document){
  'use strict';

  $(document).ready(function() {
var themefonts = {
cerulean: false,
cosmo: {
fontname: "Source Sans Pro",
url: "Source+Sans+Pro:300,400,700"
},

cyborg: {
fontname: "Roboto",
url: "Roboto:400,700"
},

darkly: {
fontname: "Lato",
url: "Lato:400,700,400italic"
},

latly: {
fontname: "Lato",
url: "Lato:400,700,400italic"
},

journal: {
fontname: "News Cycle",
url: "News+Cycle:400,700"
},

lumen: {
fontname: "Source Sans Pro",
url: "Source+Sans+Pro:300,400,700,400italic"
},

paper: {
fontname: "Roboto",
url: "Roboto:300,400,500,700"
},

readable: {
fontname: "Raleway",
url: "Raleway:400,700"
},

sandstone: {
fontname: "Roboto",
url: "Roboto:400,500"
},

slate: false,

simplex: {
fontname: "Open Sans",
url: "Open+Sans:400,700"
},

spacelab: {
fontname: "Open Sans",
url: "Open+Sans:400italic,700italic,400,700"
},

superhero: {
fontname: "Lato",
url: "Lato:300,400,700"
},

united: {
fontname: "Ubuntu",
url: "Ubuntu"
},

yeti: {
fontname: "Open Sans",
url: "Open+Sans:300italic,400italic,700italic,400,300,700"
}

};

var name = window.subtheme;
if ((name in themefonts) && themefonts[name]) {
var info = themefonts[name];
var url = "https://fonts.googleapis.com/css?family=" + info.url + "&subset=latin,cyrillic";
$.load_font(info.fontname, name, url);
} else {
//default or without font
$.load_lobster();
}
      });

})( jQuery, document);