/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

(function( $, document){
  'use strict';
  
function likebuttons(holder) {
if (!holder.length) r

var html = '';
var tml = '<a role="button" class="btn btn--default tooltip-toggle" target="_blank" href="%%url%%" title="%%title%%"><span class="%%icon%%"></span></a>';

var url = encodeURIComponent(location.href);
var title = encodeURIComponent($("title:first").text());

var photo = $(".photo:first");
if (photo.length) {
var image = encodeURIComponent(photo.attr("href"));
} else {
var image = encodeURIComponent(ltoptions.files + '/js/litepubl/logo/logo800x800.png');
}

html += $.parsetml(tml, {
title: 'FaceBook',
icon: 'fa fa-facebook',
url: 'https://www.facebook.com/dialog/feed?' +
              'app_id=' + ltoptions.facebook_appid +
              '&link=' + url +
              '&name=' + title +
              '&picture=' + image +
              '&display=popup' +
              '&redirect_uri=' + encodeURIComponent(ltoptions.files + '/files/close-window.htm')
});

html += $.parsetml(tml, {
title: 'Twitter',
icon: 'fa fa-twitter',
url: 'https://twitter.com/intent/tweet?url=' + url + '&text=' + title
});

if (ltoptions.lang == 'ru') {
html += $.parsetml(tml, {
title: 'VKontakte',
icon: 'fa fa-vk',
url: 'https://vk.com/share.php?url=' + url
});

html += $.parsetml(tml, {
title: 'Odnoklassniki',
icon: 'odnoklassniki-icon',
 url: 'http://www.odnoklassniki.ru/dk?st.cmd=addShare&st.s=1&st._surl=' + url + '&st.comments=' + title
});
}

holder.append(html);
}

  $(document).ready(function() {
likebuttons($("#likebuttons"));
  });
  
})( jQuery, document );