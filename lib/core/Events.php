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

class Events extends Data
{
use EventsTrait;

    protected $events;
    protected $eventnames;
    protected $map;

    public function __construct()
    {
        if (!is_array($this->eventnames)) {
            $this->eventnames = array();
        }

        if (!is_array($this->map)) {
            $this->map = array();
        }

        parent::__construct();

        $this->assignmap();
        $this->load();
    }

    public function __destruct()
    {
        unset($this->data, $this->events, $this->eventnames, $this->map);
    }

    protected function create()
    {
        parent::create();
        $this->addmap('events', array());
        $this->addmap('coclasses', array());
    }

    public function assignMap()
    {
        foreach ($this->map as $propname => $key) {
            $this->$propname = & $this->data[$key];
        }
    }

    public function afterLoad()
    {
        $this->assignMap();

        foreach ($this->coclasses as $coclass) {
            $this->coinstances[] = static ::iGet($coclass);
        }

        parent::afterload();
    }

    protected function addMap(string $name, $value)
    {
        $this->map[$name] = $name;
        $this->data[$name] = $value;
        $this->$name = & $this->data[$name];
    }

    public function free()
    {
        unset($this->getApp()->classes->instances[get_class($this) ]);
        foreach ($this->coinstances as $coinstance) {
            $coinstance->free();
        }
    }

    public function eventExists(string $name): bool
    {
        return in_array($name, $this->eventnames);
    }

    public function __get($name)
    {
        if (method_exists($this, $name)) {
            return [get_class($this) ,                 $name];
        }

        return parent::__get($name);
    }

    public function __set($name, $value)
    {
        if (parent::__set($name, $value)) {
            return true;
        }

        $eventName = strtolower($name);
        if (in_array($eventName, $this->eventnames)) {
            $this->addEvent($eventName, $value[0], $value[1]);
            return true;
        }

            throw new PropException(get_class($this), $name);
    }

    public function method_exists(string $name): bool
    {
        return in_array($name, $this->eventnames);
    }

    public function __call($name, $params)
    {
        $eventName = strtolower($name);
        if (in_array($eventName, $this->eventnames)) {
            return $this->callEvent($eventName, $params);
        }

        return parent::__call($name, $params);
    }

    public function __isset($name)
    {
        return parent::__isset($name) || in_array($name, $this->eventnames);
    }

    protected function addEvents()
    {
        $a = func_get_args();
        foreach ($a as $name) {
                $this->eventnames[] = strtolower($name);
        }
    }

public function newEvent(string $name): Event
{
return new Event($this, $name);
}

    public function callEvent(string $name, array $params)
    {
$name = strtolower($name);
        if (!isset($this->events[$name])) {
            return '';
        }

$event = $this->newEvent($name);
$event->setParams($params);
$this->trigger($event);
$this->triggerCallback($event);
}

public function trigger(Event $event)
{
$result = '';
$app = $this->getApp();
$eventName = $event->getName();

        foreach ($this->events[$eventName] as $i => $item) {
if ($event->isPropagationStopped()) {
break;
}

if (class_exists($item[0])) {
                try {
                $callback = [$app->classes->getInstance($item[0]), $item[1]];
call_user_func_array($callback, [$event]);
if ($event->once) {
$event->once = false;
unset($this->events[$eventName][$i]);
$this->save();
}
        } catch (\Throwable $e) {
            $app->logException($e);
                }
} else {
unset($this->events[$eventName][$i]);
if (!count($this->events[$eventName])) {
unset($this->events[$eventName]);
}

$this->save();
$app->getLogger()->warning(sprintf('Event subscriber has been removed from %s:%s', get_class($this), $eventName), $item);
}
        }

        return $result;
    }

    public function setEvent($name, $params)
    {
        return $this->addEvent($name, $params['class'], $params['func']);
    }

    public function addEvent($name, $class, $func, $once = false)
    {
        if (!in_array($name, $this->eventnames)) {
            return $this->error(sprintf('No such %s event', $name));
        }

        if (empty($class)) {
            $this->error("Empty class name to bind $name event");
        }

        if (empty($func)) {
            $this->error("Empty function name to bind $name event");
        }

        if (!isset($this->events[$name])) {
            $this->events[$name] = array();
        }

        //check if event already added
        foreach ($this->events[$name] as $event) {
            if (isset($event[0]) && strtolower($event[0]) == strtolower($class) && strtolower($event[1]) == strtolower($func)) {
                return false;
                //backward compability
            } elseif (isset($event['class']) && $event['class'] == $class && $event['func'] == $func) {
                return false;
            }
        }

        if ($once) {
            $this->events[$name][] = array(
                $class,
                $func,
                true
            );
        } else {
            $this->events[$name][] = array(
                $class,
                $func
            );
            $this->save();
        }
    }

    public function delete_event_class($name, $class)
    {
        if (!isset($this->events[$name])) {
            return false;
        }

        $list = & $this->events[$name];
        $deleted = false;
        for ($i = count($list) - 1; $i >= 0; $i--) {
            $event = $list[$i];

            if ((isset($event[0]) && $event[0] == $class) 
                //backward compability
                || (isset($event['class']) && $event['class'] == $class)
            ) {
                array_splice($list, $i, 1);
                $deleted = true;
            }
        }

        if ($deleted) {
            if (count($list) == 0) {
                unset($this->events[$name]);
            }

            $this->save();
        }

        return $deleted;
    }

    public function unsubscribeclass($obj)
    {
        $this->unbind($obj);
    }

    public function unsubscribeclassname($class)
    {
        $this->unbind($class);
    }

    public function unbind($c)
    {
        $class = static ::get_class_name($c);
        foreach ($this->events as $name => $events) {
            foreach ($events as $i => $item) {
                if ((isset($item[0]) && $item[0] == $class) || (isset($item['class']) && $item['class'] == $class)) {
                    array_splice($this->events[$name], $i, 1);
                }
            }
        }

        $this->save();
    }

    public function setEventorder($eventname, $c, $order)
    {
        if (!isset($this->events[$eventname])) {
            return false;
        }

        $events = & $this->events[$eventname];
        $class = static ::get_class_name($c);
        $count = count($events);
        if (($order < 0) || ($order >= $count)) {
            $order = $count - 1;
        }

        foreach ($events as $i => $event) {
            if ((isset($event[0]) && $class == $event[0]) || (isset($event['class']) && $class == $event['class'])) {
                if ($i == $order) {
                    return true;
                }

                array_splice($events, $i, 1);
                array_splice(
                    $events, $order, 0, array(
                    0 => $event
                    )
                );

                $this->save();
                return true;
            }
        }
    }

    private function indexofcoclass($class)
    {
        return array_search($class, $this->coclasses);
    }

    public function addcoclass($class)
    {
        if ($this->indexofcoclass($class) === false) {
            $this->coclasses[] = $class;
            $this->save();
            $this->coinstances = static ::iGet($class);
        }
    }

    public function deletecoclass($class)
    {
        $i = $this->indexofcoclass($class);
        if (is_int($i)) {
            array_splice($this->coclasses, $i, 1);
            $this->save();
        }
    }
}
