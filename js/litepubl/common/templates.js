/**
 * Lite Publisher CMS
 *
 *  copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link      https://github.com/litepubl\cms
 *  version   7.01
 */

(function($, litepubl) {
  $.extend(litepubl.tml, {
    text: '<div class="form-group"><label for="text-%%name%%">%%title%%</label>' +
      '<input type="text" class="form-control" name="%%name%%" id="text-%%name%%" value="%%value%%" /></div>',

    radio: '<div class="radio"><label><input type="radio" name="%%name%%" id="radio_%%name%%_%%value%%" value="%%value%%" />%%title%%</label></div>',

    getedit: function(title, name, value) {
      return $.parsetml(litepubl.tml.text, {
        name: name,
        value: value ? value : '',
        title: title
      });
    }

  });

}(jQuery, litepubl));