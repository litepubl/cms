/**
 * Lite Publisher CMS
 *
 *  copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link      https://github.com/litepubl\cms
 *  version   7.06
  */

(function($) {
  'use strict';

  function likebuttons(holder) {
    if (!holder.length) return;

    var html = '';
    var tml = '<a role="button" class="btn btn-default tooltip-toggle" target="_blank" href="%%url%%" title="%%title%%"><span class="fa fa-%%icon%%"></span></a>';

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
      icon: 'facebook',
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
      icon: 'twitter',
      url: 'https://twitter.com/intent/tweet?url=' + url + '&text=' + title
    });

    if (ltoptions.lang == 'ru') {
      html += $.parsetml(tml, {
        title: 'VKontakte',
        icon: 'vk',
        url: 'https://vk.com/share.php?url=' + url
      });

      html += $.parsetml(tml, {
        title: 'Odnoklassniki',
        icon: 'odnoklassniki',
        url: 'http://www.odnoklassniki.ru/dk?st.cmd=addShare&st.s=1&st._surl=' + url + '&st.comments=' + title
      });
    }

    holder.append(html);
  }

  $(function() {
    likebuttons($("#likebuttons-container"));
  });

})(jQuery);