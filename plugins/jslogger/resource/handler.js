/**
 * Lite Publisher CMS
 *
 *  copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link      https://github.com/litepubl\cms
 *  version   7.05
 */

(function($, Logger) {
  'use strict';

  var commit = [];
  var pushStatus = 'wait';

  function pushCommit() {
    if (commit.length) {
      var params = {
        messages: commit
      };
      commit = [];
      pushStatus = 'send';

      $.jsonrpc({
          method: "logger_send",
          params: params
        })
        .always(function() {
          pushStatus = 'wait';
          if (commit.length) {
            pushDelay();
          }
        });
    }
  }

  function pushDelay() {
    if (pushStatus == 'wait') {
      pushStatus = 'delay';
      setTimeout(function() {
        pushCommit();
      }, 50);
    }
  }

  Logger.setHandler(function(messages, context) {
    commit.push({
      message: messages[0],
      level: context.level.name
    });
    pushDelay();
  });
}(jQuery, Logger));