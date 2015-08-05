/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

  $(document).ready(function() {
if ($(".lazybuttons").length == 0) return;

function show_lazybuttons() {
try {
var lazy = $(".lazybuttons");
		  var url = document.location;
		  var title = document.title.replace("'",'&apos;');
//plus one callback
$.plusone_callback = function(r) {
if (_gaq != undefined) {
if(r.state=='on'){
_gaq.push(['_trackEvent','google', 'plussed', title]);
}else{
_gaq.push(['_trackEvent','google', 'unplussed', title]);
}
}
};

lazy.append(
'<div class="g-plusone"></div>');
//'<g:plusone size="standard" count="true" callback="$.plusone_callback" href="'+url +'"></g:plusone>');

 var js = lazyoptions.lang == 'en' ? '' : "{lang: '"+lazyoptions.lang+"'}";
//var script = $('<script src="' +document.location.protocol + '//apis.google.com/js/plusone.js'+ '">' + js + "</script>");
var script = $('<script src="https//apis.google.com/js/plusone.js'+ '">' + js + "</script>");
		    		    $('head:first').append(script);

//facebook
lazy.append('<div><iframe src="http://www.facebook.com/plugins/like.php?locale=ru_RU&href=' + encodeURIComponent(url) + 
'&amp;layout=button_count&amp;show_faces=true&amp;width=450&amp;action=like&amp;font=segoe+ui&amp;colorscheme=light" frameborder="0"></iframe></div>');

//twitter
  		var via = lazyoptions.twituser == '' ? '' : 'via='+lazyoptions.twituser;
/*
lazy.append('<div><a href="http://twitter.com/share?url='+ encodeURIComponent(url) + 
'&amp;text=' + encodeURIComponent(title) + 
'&amp;' + via + '">Tweet</a></div>');
*/

lazy.append('<div><iframe allowtransparency="true" frameborder="0" scrolling="no" src="http://platform.twitter.com/widgets/tweet_button.html?url='+ encodeURIComponent(url) + 
'&amp;text=' + encodeURIComponent(title) + 
'&amp;' + via + '" style="width:130px; height:20px;"></iframe></div>');

//hide button
lazy.append('<div><a href="">' + lazyoptions.hide + '</a></div>').click(function() {
set_cookie("lazybuttons", "hide");
return false;
});

//$("<code></code>").appendTo("body").text(lazy.html());
} catch(e) { alert( e.message); }
}

window.setTimeout(function() {
var cookie  = get_cookie("lazybuttons");
if (cookie  == "hide") {
$('<a href="">' + lazyoptions.show + '</a>').appendTo(".lazybuttons").click(function() {
$(this).remove();
set_cookie("lazybuttons", "show");
show_lazybuttons();
return false;
});
} else {
show_lazybuttons();
}
    }, 220);
});