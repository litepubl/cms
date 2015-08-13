/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

(function ($, document, window) {
  'use strict';
  
  window.litepubl.Commentquote = Class.extend({
    
    init: function(opt) {
      var self = this;
      var theme = ltoptions.theme.comments;
      theme.comments = $(theme.comments);
      theme.comments.off("click.quotecomment").on("click.quotecomment", "." + theme.replyclass + ", ." + theme.quoteclass, function() {
        var button = $(this);
        self[button.hasClass(theme.replyclass) ? "reply" : "quote"](button.attr("data-idcomment"), button.attr("data-authorname"));
        return false;
      });
    },
    
    getquoted: function( authorname, content) {
      if (content == '') {
        return lang.comment.to + " [b]" + authorname + "[/b]: ";
      } else {
        return "[b]" + authorname + "[/b] " + lang.comment.says + ":\n[quote]" + content + "[/quote]\n";
      }
    },
    
    quote: function(id, authorname) {
      if (window.getSelection) {
        var sel = window.getSelection();
      } else if (document.getSelection) {
        var sel = document.getSelection();
      } else if (document.selection) {
        var sel = document.selection.createRange().text;
      } else {
        var sel = '';
      }
      
      if (sel == '') sel = $("#commentcontent-" + id).text();
      var area =   ltoptions.theme.comments.editor;
      area.val(area.val() + this.getquoted(authorname, sel)).focus();
    },
    
    reply: function(id, authorname) {
      var area =   ltoptions.theme.comments.editor;
      area.val(area.val() + this.getquoted(authorname, ''));
    }
  });
  
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
      litepubl.commentquote = new litepubl.Commentquote();
    }
  });
}(jQuery, document, window));