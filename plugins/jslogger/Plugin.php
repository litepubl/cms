<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.02
  */

namespace litepubl\plugins\jslogger;

use litepubl\core\Event;
use litepubl\pages\Json;
use litepubl\view\Js;

class Plugin extends \litepubl\core\Plugin
{
const MESSAGE = "External error in javascript\n";

    public function install()
    {
        $plugindir = basename(dirname(__file__));
        $js = Js::i();
        $js->add('default', "plugins/$plugindir/resource/handler.min.js");

        Json::i()->addEvent('logger_send', $this->logger_send);
    }

    public function uninstall()
    {
        $plugindir = basename(dirname(__file__));
        $js = Js::i();
        $js->deleteFile('default', "plugins/$plugindir/resource/handler.min.js");

        Json::i()->unbind($this);
    }

    public function logger_send(array $args)
    {
        $logger = $this->getApp()->getLogger();
        $levels = [
        'TIME' =>'NOTICE',
        'WARN' => 'WARNING',
        'OFF' => 'EMERGENCY',
        ];

        foreach ($args['messages'] as $item) {
            $level = $levels[$item['level']] ?? $item['level'];
            $logger->log($logger->toMonologLevel($level), static::MESSAGE . $item['message']);
        }

$this->getApp()->addCallback('onShowErrors', function(Event $event) {
$event->show = false;
});

        return ['result' => true];
    }
}
