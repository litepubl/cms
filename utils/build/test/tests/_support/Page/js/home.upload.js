/**
 * Lite Publisher CMS
 *
 *  copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link      https://github.com/litepubl\cms
 *  version   7.04
  */

try {
var input = $('#tempfile-input');
litepubl.homeuploader.status = 'wait';
litepubl.homeuploader.upload(null, input.get(0).files[0]);
        } catch (e) {
return e.message;
}