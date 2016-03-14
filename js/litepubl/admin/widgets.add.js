/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

(function($, document) {
  'use strict';

  $(document).ready(function() {
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
}(jQuery, document));