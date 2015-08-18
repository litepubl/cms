/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

(function( $, litepubl){
  'use strict';
  
    litepubl.themefonts = {
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
    
  litepubl.load_theme_font  = function(name) {
    if (!name) name = 'default';
    var themefonts = litepubl.themefonts;
    // most case default theme color
    if (name == 'default') {
      $.load_lobster();
    } else if ((name in themefonts) && themefonts[name]) {
      var info = themefonts[name];
if (info.fontname == "Lobster") {
      $.load_lobster();
} else {
      var url = "https://fonts.googleapis.com/css?family=" + info.url + "&subset=latin,cyrillic";
      $.load_font(info.fontname, name, url);
}
    }
  };
  
  $.ready2(function() {
    litepubl.load_theme_font(ltoptions.theme.cssfile);
  });
  
})( jQuery, litepubl);