/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

function widget_load(node, id, sidebar) {
  $(node).attr("onclick", "");
  var comment = $(node).findcomment(id);
  if (! comment) return alert('Widget not found');
  $.get(ltoptions.url + '/getwidget.htm',
{id: id, sidebar: sidebar, themename: ltoptions.theme.name, idurl: ltoptions.idurl},
  function (html) {
    widget_add(node, $(comment).replaceComment( html));
  }, 'html');
}

function widget_findcomment(node, id) {
  return $(node).findcomment(id);
}

function widget_inline(node) {
  var comment = $(node).findcomment(false);
  if (! comment) return alert('Widget not found');
  widget_add(node,   $(comment).replaceComment());
}

function widget_add(node, widget) {
  $(node).data("litepublisher_widget", widget);
  $(node).click(function(event) {
    widget_toggle(this);
    return false;
  });
}

function widget_toggle(node) {
  $(node).data("litepublisher_widget").slideToggle();
}

$(document).ready(function() {
  window.setTimeout(function() {
    $("*[rel~='inlinewidget']").one('click', function() {
      widget_inline(this);
      return false;
    });
    
    var a = '<a class="expandwidget" href="">' + lang.widgetlang.expand + '</a>';
    $(".inlinewidget, .ajaxwidget").each(function() {
      $(a).appendTo(this).one("click", function() {
        if ($(this).parent().hasClass("inlinewidget")) {
          widget_inline(this);
        } else {
          var rel = $(this).parent().attr("rel").split("-");
          widget_load(this, rel[1], rel[2]);
        }
        return false;
      })
      .click(function() {
        var self = $(this);
        self.toggleClass("expandwidget colapsewidget");
        self.text(self.hasClass("expandwidget") ? lang.widgetlang.expand : lang.widgetlang.colapse);
        return false;
      });
    });
    
    $(".widget-load").one("click", function() {
      var self = $(this);
      widget_load(this, self.data("idwidget"), self.data("sidebar"));
      return false;
    });
  }, 120);
});