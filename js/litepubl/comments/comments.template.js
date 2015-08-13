/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

(function ($, document, ltoptions) {
  'use strict';
  
  $(document).ready(function() {
    var theme = ltoptions.theme;
    theme.comments= $.extend({
      comments: "#commentlist",
      hold: "#holdcommentlist",
      comment: "#comment-",
      content: "#commentcontent-",
      form: "#commentform",
      editor: "#comment",
      buttons:".moderationbuttons",
      replyclass: "replycomment",
      quoteclass : "quotecomment",
      button: '<button type="button" class="button" data-moder="%%name%%"><span>%%title%%</span></button>',
      confirmcomment: true,
      comuser: false,
      canedit: false,
      candelete: false,
      ismoder: false
    }, theme.comments);
    
    var comtheme = theme.comments;
    //cache dom search
    comtheme.comments= $(comtheme.comments);
    comtheme.holdcomments = $(comtheme.hold);
    comtheme.form = $(comtheme.form);
    if (comtheme.form.length) {
      comtheme.editor = comtheme.form.find(comtheme.editor);
    }
  });
}(jQuery, document, ltoptions));