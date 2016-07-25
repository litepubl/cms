/**
 * Lite Publisher CMS
 *
 *  copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link      https://github.com/litepubl\cms
 *  version   7.01
 */

(function($, Logger) {
  'use strict';

var messages = [];
var lastCommit = 0;

function commitReport() {
if (messages.length) {
$.jsonrpc ({
      method: "logger_send",
      params: {messages: messages}
});
}
}

Logger.setHandler(function (messages, context) {
{ message: messages[0], level: context.level });
commitReport();
});
}(jQuery, Logger));