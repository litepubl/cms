/**
 * Lite Publisher CMS
 *
 *  copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link      https://github.com/litepubl\cms
 *  version   7.06
  */

(function($, document, window) {
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

    getquoted: function(authorname, content) {
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
      var area = ltoptions.theme.comments.editor;
      area.val(area.val() + this.getquoted(authorname, sel)).focus();
    },

    reply: function(id, authorname) {
      var area = ltoptions.theme.comments.editor;
      area.val(area.val() + this.getquoted(authorname, ''));
    }
  });

  $(function() {
    if (ltoptions.theme.comments.form.length) {
      litepubl.commentquote = new litepubl.Commentquote();
    }
  });
}(jQuery, document, window));