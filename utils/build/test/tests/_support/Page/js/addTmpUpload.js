/**
 * Lite Publisher CMS
 *
 *  copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link      https://github.com/litepubl\cms
 *  version   7.05
  */

var selector  = '#tempfile-input';
var input = $(selector );
if (input.length) {
input.removeClass('hidden');
} else {
input = $('<input type="file" id="tempfile-input" />').appendTo('body');
}

return selector ;