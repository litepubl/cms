/**
 * Lite Publisher CMS
 *
 *  copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link      https://github.com/litepubl\cms
 *  version   7.07
  */

(function($, document, window) {
  'use strict';

  litepubl.Moderate = Class.extend({
    enabled: true,

    init: function() {
      this.onbuttons = $.Callbacks();

      var self = this;
      var comtheme = ltoptions.theme.comments;
      var loadhold = $(comtheme.loadhold).click(function() {
        $(this).remove();
        self.loadhold();
        return false;
      });

      //extract template from html comment after loadhold
      comtheme.holdtemplate = loadhold.length ? loadhold.get(0).nextSibling : false;

      this.create_buttons(comtheme.comments);
    },

    setenabled: function(value) {
      if (value == this.enabled) return;
      this.enabled = value;

      if (value) {
        $(this.options.buttons).show();
      } else {
        $(this.options.buttons).hide();
      }
    },

    error: function(mesg) {
      this.setenabled(true);
      $.messagebox(lang.dialog.error, mesg);
    },

    setstatus: function(id, status) {
      var self = this;
      var options = ltoptions.theme.comments;
      var idcomment = options.comment + id;
      switch (status) {
        case "delete":
        case "del":
          this.setenabled(false);
          $.confirmbox(lang.dialog.confirm, lang.comments.confirmdelete, lang.comments.yesdelete, lang.comments.nodelete, function(index) {
            if (index != 0) return self.setenabled(true);
            $.jsonrpc({
              type: 'get',
              method: "comment_delete",
              params: {
                id: id
              },
              callback: function(r) {
                if (r == false) return self.error(lang.comments.notdeleted);
                $(idcomment).remove();
                self.setenabled(true);
              },

              error: function(message, code) {
                self.error(lang.comments.notdeleted);
              }
            });
          });
          break;

        case "hold":
        case "approved":
        case "approve":
          this.setenabled(false);
          $.jsonrpc({
            type: 'get',
            method: "comment_setstatus",
            params: {
              id: id,
              status: status == 'hold' ? 'hold' : 'approved'
            },
            callback: function(r) {
              try {
                if (r == false) {
                  return self.error(lang.comments.notmoderated);
                }

                if (!options.holdcomments && options.holdtemplate) {
                  var holdtemplate = options.holdtemplate;
                  options.holdcontainer = $(holdtemplate.nodeValue).insertAfter(options.comments);
                  options.holdcomments = options.holdcontainer.parent().find(options.hold);
                  self.create_buttons(options.holdcomments);
                  holdtemplate.parentNode.removeChild(holdtemplate);
                  comtheme.holdtemplate = null;
                }

                $(status == "hold" ? options.holdcomments : options.comments).append($(options.comment + id));
                self.setenabled(true);
              } catch (e) {
                litepubl.onerror(e);
              }
            },

            error: function(message, code) {
              self.error(lang.comments.notmoderated);
            }
          });
          break;

        case "edit":
          this.setenabled(false);
          $.jsonrpc({
            type: 'get',
            method: "comment_getraw",
            params: {
              id: id
            },
            callback: function(resp) {
              try {
                self.edit(id, resp.rawcontent);
              } catch (e) {
                litepubl.onerror(e);
              }
            },

            error: function(message, code) {
              self.error(lang.comments.errorrecieved);
            }
          });
          break;

        default:
          alert("Unknown status " + status);
      }

    },

    edit: function(id, rawcontent) {
      var area = ltoptions.theme.comments.editor;
      area.data("idcomment", id)
        .data("savedtext", area.val())
        .val(rawcontent)
        .focus();

      $.onEscape($.proxy(this.restore_submit, this));

      var self = this;
      var form = ltoptions.theme.comments.form;
      form.off("submit.confirmcomment").on("submit.moderate", function() {
        try {
          var content = $.trim(area.val());
          if (content == "") {
            self.enabled = true;
            self.error(lang.comment.emptycontent);
            self.enabled = false;
            return false;
          }

          $(":input", form).prop("disabled", true);
          $.jsonrpc({
            method: "comment_edit",
            params: {
              id: area.data("idcomment"),
              content: content
            },
            callback: function(r) {
              try {
                $(":input", form).prop("disabled", false);
                var cc = ltoptions.theme.comments.content + r.id;
                $(cc).html(r.content);
                self.restore_submit();
                location.hash = cc.substring(1);
              } catch (e) {
                litepubl.onerror(e);
              }
            },

            error: function(message, code) {
              $(":input", form).prop("disabled", false);
              self.error(lang.comments.notedited);
              self.restore_submit();
            }
          });

        } catch (e) {
          litepubl.onerror(e);
        }
        return false;
      });
    },

    restore_submit: function() {
      var area = ltoptions.theme.comments.editor;
      area.val(area.data("savedtext"));
      this.setenabled(true);
      ltoptions.theme.comments.form.off("submit.moderate").on("submit.confirmcomment", function() {
        if ("confirmcomment" in litepubl) return litepubl.confirmcomment.submit();
      });
    },

    loadhold: function() {
      var self = this;
      $.jsonrpc({
        type: 'get',
        method: "comments_get_hold",
        params: {
          idpost: ltoptions.idpost
        },
        callback: $.proxy(this.inserthold, this),
        error: function(message, code) {
          self.error(lang.comments.errorrecieved);
        }
      });
    },

    inserthold: function(r) {
      try {
        var comtheme = ltoptions.theme.comments;
        //utilize unused data
        var holdtemplate = comtheme.holdtemplate;
        if (holdtemplate) {
          holdtemplate.parentNode.removeChild(holdtemplate);
          comtheme.holdtemplate = null;
        }

        var holdcontainer = comtheme.holdcontainer;
        if (holdcontainer && holdcontainer.length) {
          contheme.holdcontainer = $(r.items).insertBefore(holdcontainer);
          holdcontainer.remove();
          holdcontainer = contheme.holdcontainer;
        } else {
          holdcontainer = contheme.holdcontainer = $(r.items).insertAfter(comtheme.comments);
        }

        comtheme.holdcomments = holdcontainer.parent().find(comtheme.hold);
        this.create_buttons(comtheme.holdcomments);
      } catch (e) {
        litepubl.onerror(e);
      }
    },

    create_buttons: function(owner) {
      var comtheme = ltoptions.theme.comments;
      var self = this;
      owner.on("click.moder", comtheme.buttons, function() {
        if (!self.enabled) return false;
        var button = $(this);
        self.setstatus(button.parent().attr("data-idcomment"), button.attr("data-moder"));
        return false;
      });

      var comtheme = ltoptions.theme.comments;
      var containers = owner.find(comtheme.buttons + (comtheme.ismoder ? '' : '[data-idauthor="' + litepubl.getuser().id + '"]'));
      if (!containers.length) return;

      if (comtheme.ismoder) {
        var names = ['approve', 'hold', 'del', 'edit'];
      } else {
        if (!comtheme.canedit && !comtheme.candelete) return;
        var names = [];
        if (comtheme.canedit) names.push('edit');
        if (comtheme.candelete) names.push('del');
      }

      var html = '';
      var tml = comtheme.button;
      if (tml.indexOf('data-moder') < 0) tml = tml.replace('class=', 'data-moder="%%name%%" class=');
      for (var i = 0; i < names.length; i++) {
        var name = names[i];
        html += $.simpletml(tml, {
          title: lang.comments[name],
          name: name
        });
      }

      containers.append(html);
      if (containers.first().is(":hidden")) {
        var showbutton = $.simpletml(comtheme.button, {
          title: 'E',
          name: 'show'
        });

        showbutton = showbutton.replace('class="', 'class="showbutton ');
        container.before(showbutton);
        owner.on("click.showbutton mouseenter.showbutton focus.showbutton", ".showbutton", function() {
          $(this).next().show();
          $(this).remove();
          return false;
        });
      }

      this.onbuttons.fire(containers);
    }

  }); //class

  $.ready2(function() {
    //only logged users
    if (litepubl.getuser().id) litepubl.moderate = new litepubl.Moderate();
  });

}(jQuery, document, window));