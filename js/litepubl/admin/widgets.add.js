/**
 * LitePubl CMS
 *
 *  copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link      https://github.com/litepubl\cms
 *  version   7.08
  */

(function($) {
  'use strict';

  $(function() {
    $("#addwidgets-header").on("click.addwidgets", function(e) {
      e.preventDefault();

      var dialog = $.litedialog({
        title: $(this).text(),
        html: $("#addwidgets-body ").get(0).firstChild.nodeValue,
        buttons: [{
            title: "Ok",
            click: function() {
              dialog.find("form:first").submit();
            }
          },

          $.get_cancel_button()
        ]
      });
    });

    if (!("button" in $.fn)) {
      $.load_script(ltoptions.files + "/js/bootstrap/button.min.js");
    }
  });
}(jQuery));