/**
 * Lite Publisher CMS
 *  copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link https://github.com/litepubl\cms
 *  version 7.00
 *
 */

try {
var input = $('#tempfile-input');
input.addClass('hidden');
var uploader = litepubl.fileman.uploader.handler;
            uploader.queue.push(input.get(0).files[0]);
uploader.start();
        } catch (e) {
return e.message;
}