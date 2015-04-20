/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function ($, document, window) {
  'use strict';

  window.litepubl.class_confirmcomment = Class.extend({
    
    init: function() {
      //ctrl+enter
      ltoptions.theme.comments.editor.off("keydown.confirmcomment").on("keydown.confirmcomment", function (e) {
        if (e.ctrlKey && ((e.keyCode == 13) || (e.keyCode == 10))) {
          ltoptions.theme.comments.form.submit();
        }
      });
      
      ltoptions.theme.comments.form.off("submit.confirmcomment").on("submit.confirmcomment", $.proxy(this.submit, this));
    },
    
    get: function(name) {
var comtheme = ltoptions.theme.comments;
      if (name == 'content') return comtheme.editor;
      return comtheme.form.find("input[name='" + name + "']");
    },
    
    error: function(mesg) {
      return $.messagebox(lang.dialog.error, mesg);
    },
    
    error_field: function(field, mesg) {
      var self = this;
      this.error(mesg).close = function() {
        self.get(field).focus();
      };
    },
    
    empty: function(name) {
      var s = this.get(name).val();
      return $.trim(s) == "";
    },
    
    validemail: function() {
      var s = $.trim(this.get("email").val());
      if (s == "") return false;
    var filter = /^([a-zA-Z0-9_\.\-])+\@(([a-zA-Z0-9\-])+\.)+([a-zA-Z0-9]{2,4})+$/;
      return filter.test(s);
    },
    
    validate: function() {
      if ("" == $.trim(this.get("content").val())) {
        this.error_field("content", lang.comment.emptycontent);
        return false;
      }
      if (!ltoptions.theme.comments.comuser) return true;
      
      if (this.empty("name")) {
        this.error_field("name", lang.comment.emptyname);
      } else if (!this.validemail()) {
        this.error_field("email", lang.comment.invalidemail);
      } else {
        return true;
      }
      
      return false;
    },
    
    send: function() {
    var params = {};
      var inputs = $(":input", ltoptions.theme.comments.form);
      inputs.each(function() {
        var input = $(this);
        params[input.attr("name")] = input.val();
        input.prop("disabled", true);
      });
      
      var self = this;
      $.jsonrpc({
        method: "comment_add",
        params: params,
        callback:  function (resp) {
          try {
            switch (resp.code) {
              case 'confirm':
              self.confirm(resp.confirmid);
              break;
              
              case 'success':
              self.success(resp);
              break;
              
              default: //error
              self.error(resp.message);
              break;
            }
        } catch(e) { form.error(e.message); }
        },
        
        error: $.proxy(self.error, self)
      })
      .always(function() {
        inputs.prop("disabled", false);
      });
    },
    
    confirm: function(confirmid) {
      var self = this;
      $.confirmbox(lang.dialog.confirm, lang.comment.checkspam , lang.comment.robot, lang.comment.human, function(index) {
        if (index !=1) return;
        $.jsonrpc({
          type: 'get',
          method: "comment_confirm",
        params: {confirmid: confirmid},
          callback:  $.proxy(self.success, self),
          error:  $.proxy(self.error, self)
        });
      });
    },
    
    success: function(data) {
      if ("cookies" in data) {
        for (var name in data.cookies) {
          set_cookie(name, data.cookies[name]);
        }
      }
      window.location = data.posturl;
    },
    
    submit: function() {
      try {
        if (!this.validate()) return false;
        if (ltoptions.theme.comments.confirmcomment) {
          this.send();
          return false;
        }
    } catch(e) {erralert(e);}
    }
    
  });
  
  $(document).ready(function() {
if (ltoptions.theme.comments.form.length) litepubl.confirmcomment = new litepubl.class_confirmcomment();
  });
  
}(jQuery, document, window));