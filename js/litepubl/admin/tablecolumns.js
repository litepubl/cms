/**
* Lite Publisher
* Copyright (C) 2010 - 2015 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

$(document).ready(function() {
  $(document).on("click", "input[name^='checkbox-showcolumn-']", function() {
    var index = $(this).val();
    var sel = 'td:nth-child(' + index + '),th:nth-child(' + index + ')';
    if ($(this).prop("checked")) {
      $(sel).show();
    } else {
      $(sel).hide();
    }
  });
  
  $("input[name='invert_checkbox']").click(function() {
    $(this).closest("form").find("input[type=checkbox]").each(function() {
      if ('togglecolumn' != $(this).prop('rel')) {
        $(this).prop("checked", ! $(this).prop("checked"));
      }
    });
    $(this).prop("checked", false);
  });
});