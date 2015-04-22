(function( $, document){
  $.ready(function() {
    //$.load_css("http://fonts.googleapis.com/css?family=Lobster&subset=latin,cyrillic");
var css = '<style type="text/css" rel="stylesheet">' +
"@font-face {" +
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
"}</style>";

css = css.replace(/%s/gm, 'http://cdn.litepubl.ru/fonts/lobster');
$("head:first").append(css);

    var observer = new FontFaceObserver('Lobster', {weight: 400});
observer .check().then(function () {
$("body").addClass("lobster");
});

  });
})( jQuery, document);