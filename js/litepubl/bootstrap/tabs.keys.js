/**
* Lite Publisher
* Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* Licensed under the MIT (LICENSE.txt) license.
**/

(function($, document) {
  'use strict';

  /* function exported from Bootstrap Accessibility Plugin, v1.04 */
  $.fn.tab.Constructor.prototype.keydown = function(e) {
    var $this = $(this),
      $items, $ul = $this.closest('ul[role=tablist] '),
      index, k = e.which || e.keyCode

    $this = $(this)
    if (!/(37|38|39|40)/.test(k)) return

    $items = $ul.find('[role=tab]:visible')
    index = $items.index($items.filter(':focus'))

    if (k == 38 || k == 37) index-- // up & left
      if (k == 39 || k == 40) index++ // down & right


        if (index < 0) index = $items.length - 1
    if (index == $items.length) index = 0

    var nextTab = $items.eq(index)
    if (nextTab.attr('role') === 'tab') {

      nextTab.tab('show') //Comment this line for dynamically loaded tabPabels, to save Ajax requests on arrow key navigation
        .focus()
    }
    // nextTab.focus()

    e.preventDefault()
    e.stopPropagation()
  }

  $(document).ready(function() {
    $(document).on('keydown.tab.data-api', '[data-toggle="tab"], [data-toggle="pill"]', $.fn.tab.Constructor.prototype.keydown)
  });
})(jQuery, document);