/**
 * Lite Publisher CMS
 *
 *  copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 *  license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 *  link      https://github.com/litepubl\cms
 *  version   7.01
 */

(function(window, Logger) {
  'use strict';

Logger.useDefaults();
if (ltoptions.debug) {
Logger.setLevel(Logger.Debug);
} else {
Logger.setLevel(Logger.WARN);
}

window.onerror = function(msg, url, line, col, error) {
mesg = mesg + "\nurl: " + url + "\non line " + line;
if (col) {
mesg = mesg + " symbol " + col;
}

if (error && "stack" in error) {
mesg = mesg + "\n" + error.stack;
}

Logger.error(mesg);
};
}(window, Logger));