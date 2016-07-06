/**
 * Lite Publisher CMS
 *
 *  copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link      https://github.com/litepubl\cms
 *  version   7.00
  */

(function($, document) {
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

}(jQuery, document));