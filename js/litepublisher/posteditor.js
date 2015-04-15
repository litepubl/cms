/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function ($, document, window) {
  'use strict';
  litepubl.Posteditor = Class.extend({
    tabs: false,
    
    init: function() {
      this.tabs = $("#tabs");
      if (!this.tabs.length) return;
      var self = this;
      this.tabs.tabs({
        hide: true,
        show: true,
        beforeLoad: litepubl.uibefore,
        
        beforeActivate: function(event, ui) {
          self.init_tab(ui.newPanel);
        },
        
        load: function(event, ui) {
          $(".posteditor-tag", ui.panel).click(function() {
            self.addtag($(this).text());
            return false;
          });
        }
      });
      
      $("#posteditor-init-raw-tabs").one('click', function() {
        self.init_raw_tabs();
        return false;
      });
      
      $("#posteditor-init-files").one('click.initfiles', function() {
        litepubl.fileman = new litepubl.Fileman("#posteditor-files");
        return false;
      });
      
      $('form:first').on('submit.posttitle', function(event) {
        var title = $("input[name='title']", this);
        if (!$.trim(title.val())) {
          event.stopImmediatePropagation();
          $.messagebox(lang.dialog.error, lang.posteditor.emptytitle, function() {
            title.focus();
          });
          return false;
        }
      });
      
    },
    
    init_tab: function(tab) {
      if ($("#datetime-holder", tab).length) {
        this.init_datetime_tab(tab);
      } else  if ($("#seo-holder", tab).length) {
        this.init_seo_tab(tab);
      }
    },
    
    addtag: function(newtag) {
      var tags = $('#text-tags').val();
      if (tags == '') {
        $('#text-tags').val(newtag);
      } else {
        var re = /\s*,\s*/;
        var list = tags.split(re);
        for (var i = list.length; i >= 0; i--) {
          if (newtag == list[i]) return false;
        }
        $('#text-tags').val(tags + ', ' + newtag);
      }
    },
    
    init_seo_tab: function (uipanel) {
      //replace html in comment
      var holder = $("#seo-holder", uipanel);
      holder.replaceWith(holder.get(0).firstChild.nodeValue);
    },
    
    init_datetime_tab: function (uipanel) {
      //replace html in comment
      var holder = $("#datetime-holder", uipanel);
      var calendar = $(holder.get(0).firstChild.nodeValue).appendTo(uipanel);
      holder.remove();
      litepubl.calendar.on(calendar);
    },
    
    init_raw_tabs: function() {
      $("#posteditor-init-raw-tabs").remove();
      var holder = $("#posteditor-raw-holder");
      var html = holder.get(0).firstChild.nodeValue;
      $(holder.get(0).firstChild).remove();
      
      html = html.replace(/<comment>/gim, '<div class="tab-holder"><!--')
      .replace(/<\/comment>/gim, '--></div>');
      //divide on list and div's
      var i = html.indexOf('<div');
      $("#posteditor-raw").before(html.substring(0, i)).after(html.substring(i));
      
      holder.tabs({
        hide: true,
        show: true,
        beforeLoad: litepubl.uibefore,
        beforeActivate: function(event, ui) {
          var inner = $(".tab-holder", ui.newPanel);
          if (inner.length) inner.replaceWith(inner.get(0).firstChild.nodeValue);
        }
      });
    },
    
    init_visual_link: function(url, text) {
      $('<a href="#">' + text + '</a>').appendTo("#posteditor-visual").data("url", url).one("click", function() {
        $.load_script($(this).data("url"));
        $("#posteditor-visual").remove();
        return false;
      });
    }
    
  });//posteditor
  
  $(document).ready(function() {
    try {
      litepubl.posteditor  = new litepubl.Posteditor();
  } catch(e) {erralert(e);}
  });
}(jQuery, document, window));