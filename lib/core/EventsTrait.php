<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.02
  */

namespace litepubl\core;

trait EventsTrait
{
    use Callbacks;

    protected $eventnames = [];
    protected $events;

    protected function createData()
    {
        parent::createData();

        if ($this instanceof Events) {
                $this->addMap('events', []);
        } else {
                $this->data['events'] = [];
        }
    }

    protected function getProp(string $name)
    {
        if (method_exists($this, $name)) {
            return [get_class($this) ,                 $name];
        }

        return parent::getProp($name);
    }

    protected function setProp(string $name, $value)
    {
        $eventName = strtolower($name);
        if (in_array($eventName, $this->eventnames)) {
            $this->addEvent($eventName, $value);
        } else {
            parent::setProp($name, $value);
        }
    }

    public function __call($name, $params)
    {
        $eventName = strtolower($name);
        if (in_array($eventName, $this->eventnames)) {
            return $this->callEvent($eventName, $params[0]);
        }

        return parent::__call($name, $params);
    }

    public function __isset($name)
    {
        return parent::__isset($name) || in_array($name, $this->eventnames);
    }

    public function eventExists(string $name): bool
    {
        return in_array(strtolower($name), $this->eventnames);
    }

    public function method_exists($name)
    {
        return in_array(strtolower($name), $this->eventnames);
    }

    protected function addEvents()
    {
        $a = func_get_args();
        foreach ($a as $name) {
                $this->eventnames[] = strtolower($name);
        }
    }

    protected function reIndexEvents()
    {
        foreach ($this->data['events'] as $name => $events) {
            if (!count($events)) {
                unset($this->data['events'][$name]);
            } else {
                ksort($events);
                $sorted = [];
                $newIndex = 0;
                foreach ($events as $i => $item) {
                    if (floor($i / 10) == floor($newIndex / 10)) {
                        $newIndex++;
                    } else {
                        $newIndex  = floor($i / 10) * 10;
                    }

                    $sorted[$newIndex] = $item;
                }

                $this->data['events'][$name] = $sorted;
            }
        }
    }

    public function getEventCount(string $name): int
    {
        return isset($this->data['events'][$name]) ? count($this->data['events'][$name]) : 0;
    }

    public function newEvent(string $name): Event
    {
        return new Event($this, $name);
    }

    public function callEvent(string $name, array $params): array
    {
        $name = strtolower($name);
        if (!$this->getEventCount($name) && !$this->getCallbacksCount($name)) {
            return $params;
        }

        $event = $this->newEvent($name);
        $event->setParams($params);
        $this->triggerCallback($event);
        $this->trigger($event);
        return $event->getParams();
    }

    protected function trigger(Event $event)
    {
        $app = $this->getApp();
        $eventName = $event->getName();

        foreach ($this->data['events'][$eventName] as $i => $item) {
            if ($event->isPropagationStopped()) {
                        break;
            }

            if (class_exists($item[0])) {
                try {
                    $callback = [$app->classes->getInstance($item[0]), $item[1]];
                    call_user_func_array($callback, [$event]);
                    if ($event->once) {
                                        $event->once = false;
                                        unset($this->data['events'][$eventName][$i]);
                                        $this->save();
                    }
                } catch (\Throwable $e) {
                    $app->logException($e);
                }
            } else {
                        unset($this->data['events'][$eventName][$i]);
                if (!count($this->data['events'][$eventName])) {
                    unset($this->data['events'][$eventName]);
                }

                        $this->save();
                        $app->getLogger()->warning(sprintf('Event subscriber has been removed from %s:%s', get_class($this), $eventName), is_array($item) ? $item : []);
            }
        }

    }

    public function addEvent(string $name, $callable, $method = null)
    {
        $name = strtolower($name);
        if (!in_array($name, $this->eventnames)) {
            $this->error(sprintf('No such %s event', $name));
        }

        if (!is_callable($callable)) {
            if (!$method) {
                    $this->error(sprintf('Error bind to event %s', $name));
            }

                $callable = [$callable, $method];
            if (!is_callable($callable)) {
                    $this->error(sprintf('Error bind to event %s', $name));
            }
        }

        if (!is_array($callable) || !is_string($callable[0])) {
                return $this->addCallback($name, $callable);
        }

        if (!isset($this->data['events'][$name])) {
            $this->data['events'][$name] = [500 => $callable];
            $this->save();
        } else {
            //check if event already added
            foreach ($this->data['events'][$name] as $item) {
                if ($callable == $item) {
                    return false;
                }
            }

            Arr::append($this->data['events'][$name], 500, $callable);
            $this->save();
        }
    }

    public function detach(string $name, callable $callback): bool
    {
        $name = strtolower($name);
        $this->deleteCallback($name, $callback);
        if (isset($this->data['events'][$name])) {
            foreach ($this->data['events'][$name] as $i => $item) {
                if ($item == $callback) {
                    unset($this->data['events'][$name][$i]);
                    $this->reIndexEvents();
                    $this->save();
                    return true;
                }
            }
        }

        return false;
    }

    public function unbind($c)
    {
        $class = static ::getClassName($c);
        foreach ($this->data['events'] as $name => $events) {
            foreach ($events as $i => $item) {
                if ($class == $item[0]) {
                    unset($this->data['events'][$name][$i]);
                }
            }
        }
        
        $this->reIndexEvents();
        $this->save();
    }

}
