/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function( $){
  'use strict';

  $.load_lobster = function() {
    //$.load_css("http://fonts.googleapis.com/css?family=Lobster&subset=latin,cyrillic");
var css = "@font-face {" +
  "font-family: 'Lobster';" +
  "font-style: normal;" +
  "font-weight: 400;" +
  "src: url('%s.eot');" +
  "src: local('Lobster'), local('Lobster-Regular'), " +
"url('%s.eot?#iefix') format('embedded-opentype')," +
"url('%s.woff') format('woff')," +
"url('%s.woff2') format('woff2')," +
"url('%s.ttf') format('truetype')," +
"url('%s.svg#Lobster') format('svg');" +
"}";

css = css.replace(/%s/gm, ltoptions.files + '/js/fonts/lobster/lobster');

$.css_loader.addtext(css);

    var observer = new FontFaceObserver('Lobster', {weight: 400});
observer .check().then(function () {
$("body").addClass("lobster");
});
  };
})( jQuery);