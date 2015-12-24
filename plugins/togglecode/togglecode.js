/**
* Lite Publisher
* Copyright (C) 2010, 2011 Vladimir Yushko http://litepublisher.com/
* Dual licensed under the MIT (mit.txt)
* and GPL (gpl.txt) licenses.
**/

  $(document).ready(function() {
var html = '<h4><a class="togglecode" href="">' + lang.widgetlang.collapse + '</a></h4>';
$("code").each(function() {
//alert($(this).before(html)
$(html).insertBefore(this).children("a").click(function() {
$(this).parent().next().slideToggle();
      $(this).toggleClass("expandwidget collapsewidget");
      $(this).text($(this).hasClass("expandwidget") ? lang.widgetlang.expand : lang.widgetlang.collapse);
return false;
});
});

});