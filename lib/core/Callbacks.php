<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
  */

namespace litepubl\core;

trait Callbacks
{
    private $callbacks = [];

    public function addCallback(string $eventName, callable $callback, int $priority = 500): int
    {
if (!isset($this->callbacks[$eventName])) {
$this->callbacks[$eventName[$priority] = $callback;
} else {
Arr::append($this->callbacks[$eventName], $priority, $callback);
}

return true;
    }

    public function deleteCallback(string $event, callable $callback): bool
    {
if (isset($this->callbacks[$event])) {
foreach ($this->callbacks[$event] as $i => $item) {
if ($item == $callback) {
unset($this->callbacks[$event][$i];
return true;
}
}
}

return false;
    }

    public function clearCallbacks(string $event): bool
    {
if (isset($this->callbacks[$event])) {
        unset($this->callbacks[$event]);
return true;
}

return false;
    }

public function getCallbacksCount(string $event): int
{
return isset($this->callbacks[$event]) ? count($this->callbacks[$event]) : 0;
}

    public function triggerCallback($event, $params = array())
    {
if (is_object($event)) {
$eventName = $event->getName();
} else {
$eventName = $event;
}

if (isset($this->callbacks[$eventName])) {
if (is_string($event)) {
$event = new Event($this, $eventName);
$event->setParams($params);
}

foreach ($this->callbacks[$eventName] as $i => $callback) {
if ($event->isPropagationStopped()) {
break;
}

try {
                call_user_func_array($callback, [$event]);
if ($event->once) {
$event->once = false;
unset($this->callbacks[$eventName][$i]);
}
            } catch (\Exception $e) {
                $this->getApp()->logException($e);
            }
        }
    }
}
