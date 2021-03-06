/**
 * LitePubl CMS
 *
 *  copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link      https://github.com/litepubl\cms
 *  version   7.08
  */

(function($, ltoptions) {
  'use strict';

  $(function() {
    var theme = ltoptions.theme;
    theme.comments = $.extend({
      comments: "#commentlist",
      hold: "#holdcommentlist",
      loadhold: "#load-hold-comments",
      comment: "#comment-",
      content: "#commentcontent-",
      buttons: ".moderationbuttons",
      replyclass: "replycomment",
      quoteclass: "quotecomment",
      button: '<button type="button" class="btn btn-default tooltip-toggle" data-moder="%%name%%" title="%%title%%"><span class="fa moder-%%name%%" aria-hidden="true"></span> <span class="sr-only">%%title%%</span></button>',
      form: "#commentform",
      editor: "#comment",
      // rights of current user
      confirmcomment: true,
      comuser: false,
      canedit: false,
      candelete: false,
      ismoder: false,
      //for moderate functions
      holdcomments: false,
      holdcontainer: false,
      holdtemplate: false
    }, theme.comments);

    var comtheme = theme.comments;

    // normalize ismoder value
    comtheme.ismoder = (comtheme.ismoder === 'true') || (comtheme.ismoder === true);

    //cache dom search
    comtheme.comments = $(comtheme.comments);
    comtheme.form = $(comtheme.form);
    if (comtheme.form.length) {
      comtheme.editor = comtheme.form.find(comtheme.editor);
    }
  });
}(jQuery, ltoptions));