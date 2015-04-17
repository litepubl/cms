/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.ru/ http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

(function ($, document, window) {
  'use strict';
  
  litepubl.Adminview = Class.extend({
    
    init: function() {
      var form = $("#admin-view-form");
      var tabs = $(".admintabs:first", form);
      var woptions = $("#woptions-holder", tabs);
      var sidebars = $("#adminview-sidebars", tabs);
      var ul = $(".adminview-sidebar ul", sidebars);
      var allwidgets = $("#all-widgets", form);
      var widgets = $().add(ul).add(allwidgets);
      var disabled = [];
      var custom = $("#checkbox-customsidebar", form);
      
      //checkbox hasnt in default view
      if (custom.length) {
        var checked = custom.attr("checked");
        if (!checked) disabled = [0];
        var disableajax = $("#checkbox-disableajax", form).prop("disabled", checked ? "disabled" : false);
        custom.click(function() {
          var checked = $(this).prop("checked");
          disableajax.prop("disabled", checked ? "disabled" : false);
          tabs.tabs( "option", "disabled", checked  ? [] : [0]);
        });
      }
      
      tabs.tabs({
        hide: true,
        show: true,
        disabled: disabled,
        active: disabled.length ? 1 : 0,
        beforeLoad: litepubl.uibefore
      });
      
      sidebars.on("click.widget", "li", function() {
        var id = $(this).data('idwidget');
        $(".woptions", woptions).addClass("hidden");
        $("#woptions-" + id, woptions).removeClass("hidden");
        return false;
      });
      
      form.submit(function() {
        ul.each(function() {
          var idwidgets = [];
          $("li", this).each(function() {
            idwidgets.push($(this).data("idwidget"));
          });
          $("#hidden-sidebar" + $(this).data("index")).val(idwidgets.join(","));
        });
      });
      
    widgets.sortable({connectWith: widgets});
      woptions.on("click.delete", "[name^='delete']", function() {
        var holder = $(this).closest(".woptions").hide();
        allwidgets.append(ul.find("[data-idwidget=" + holder.data("idwidget") + "]:first"));
        //widgets.sortable( "refresh" );
        return false;
      });
      
      woptions.on("click.options", "input[id^='ajax']", function() {
        var holder = $(this).closest(".woptions");
        if (holder.data("inline") == "enabled") {
          $("[name='inline" + holder.data("idwidget") + "']", holder).prop("disabled", $(this).prop("checked") ? false : "disabled");
        }
      });
      
    }
    
  });
  
  $(document).ready(function() {
    try {
      litepubl.adminview = new litepubl.Adminview();
  } catch(e) {erralert(e);}
  });
}(jQuery, document, window));