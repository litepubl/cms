/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 **/

(function($, document, window, litepubl) {
  'use strict';

  $(document).ready(function() {
    $(".checkall").click(function() {
      $(this).closest("form").find("input[type='checkbox']").prop("checked", true);
      $(this).prop("checked", false);
    });

    $(".invertcheck").click(function() {
      $(this).closest("form").find("input[type=checkbox]").each(function() {
        var self = $(this);
        self.prop("checked", !self.prop("checked"));
      });
      $(this).prop("checked", false);
      return false;
    });


    // switcher template see in lib/admin.files.class.php
    var switcher = $('#files-source');
    if (switcher.length) {
      $('#text-downloadurl').parent().hide();
      switcher.click(function() {
        var mode = $('#hidden-uploadmode');
        if (mode.val() == 'file') {
          mode.val('url');
        } else {
          mode.val('url');
        }

        $('#file-filename').parent().toggle();
        $('#text-downloadurl').parent().toggle();
        return false;
      });
    }

    $("a.confirm-delete-link").on("click.confirm", function() {
      var url = $(this).attr("href");
      $.confirmdelete(function() {
        $('<form action="' + url + '&confirm=1" method="post"><input type="hidden" name="action" value="delete" /></form>')
          .appendTo("body").submit();
      });
      return false;
    });

  });

  litepubl.uibefore = function(event, ui) {
    if (ui.tab.data("loaded")) {
      event.preventDefault();
      return;
    }

    ui.jqXHR.success(function() {
      ui.tab.data("loaded", true);
    });
  };

  $.inittabs = function() {
    $(document).ready(function() {
      $($(".admintabs").toArray().reverse()).tabs({
        hide: true,
        show: true,
        beforeLoad: litepubl.uibefore
      });
    });
  };

}(jQuery, document, window, litepubl));