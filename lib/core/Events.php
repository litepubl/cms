<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\core;

class Events extends Data {
    protected $events;
    protected $eventnames;
    protected $map;

    public function __construct() {
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

    public function __destruct() {
        unset($this->data, $this->events, $this->eventnames, $this->map);
    }

    protected function create() {
parent::create();
        $this->addmap('events', array());
        $this->addmap('coclasses', array());
    }

    public function assignmap() {
        foreach ($this->map as $propname => $key) {
            $this->$propname = & $this->data[$key];
        }
    }

    public function afterload() {
        $this->assignmap();

        foreach ($this->coclasses as $coclass) {
            $this->coinstances[] = getinstance($coclass);
        }

        parent::afterload();
    }

    protected function addmap($name, $value) {
        $this->map[$name] = $name;
        $this->data[$name] = $value;
        $this->$name = & $this->data[$name];
    }

    public function free() {
        unset(litepubl::$classes->instances[get_class($this) ]);
        foreach ($this->coinstances as $coinstance) {
            $coinstance->free();
        }
    }

    public function eventexists($name) {
        return in_array($name, $this->eventnames);
    }

    public function __get($name) {
        if (method_exists($this, $name)) {
            return array(
                get_class($this) ,
                $name
            );
        }

        return parent::__get($name);
    }

    public function __set($name, $value) {
        if (parent::__set($name, $value)) {
            return true;
        }

        if (in_array($name, $this->eventnames)) {
            $this->addevent($name, $value[0], $value[1]);
            return true;
        }
        $this->error(sprintf('Unknown property %s in class %s', $name, get_class($this)));
    }

    public function method_exists($name) {
        return in_array($name, $this->eventnames);
    }

    public function __call($name, $params) {
        if (in_array($name, $this->eventnames)) {
            return $this->callevent($name, $params);
        }

        parent::__call($name, $params);
    }

    public function __isset($name) {
        return parent::__isset($name) || in_array($name, $this->eventnames);
    }

    protected function addevents() {
        $a = func_get_args();
        array_splice($this->eventnames, count($this->eventnames) , 0, $a);
    }

    public function callevent($name, $params) {
        if (!isset($this->events[$name])) {
            return '';
        }

        $result = '';
        foreach ($this->events[$name] as $i => $item) {
            //backward compability
            $class = isset($item[0]) ? $item[0] : (isset($item['class']) ? $item['class'] : '');

            if (is_string($class) && class_exists($class)) {
                $call = array(
                    getinstance($class) ,
                    isset($item[1]) ? $item[1] : $item['func']
                );
            } elseif (is_object($class)) {
                $call = array(
                    $class,
                    isset($item[1]) ? $item[1] : $item['func']
                );
            } else {
                $call = false;
            }

            if ($call) {
                try {
                    $result = call_user_func_array($call, $params);
                }
                catch(CancelEvent $e) {
                    return $e->result;
                }

                // 2 index = once
                if (isset($item[2]) && $item[2]) {
                    array_splice($this->events[$name], $i, 1);
                }

            } else {
                //class not found and delete event handler
                array_splice($this->events[$name], $i, 1);
                if (!count($this->events[$name])) {
                    unset($this->events[$name]);
                }

                $this->save();
            }
        }

        return $result;
    }

    public static function cancelevent($result) {
        throw new CancelEvent($result);
    }

    public function setevent($name, $params) {
        return $this->addevent($name, $params['class'], $params['func']);
    }

    public function addevent($name, $class, $func, $once = false) {
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
            if (isset($event[0]) && $event[0] == $class && $event[1] == $func) {
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

    public function delete_event_class($name, $class) {
        if (!isset($this->events[$name])) {
            return false;
        }

        $list = & $this->events[$name];
        $deleted = false;
        for ($i = count($list) - 1; $i >= 0; $i--) {
            $event = $list[$i];

            if ((isset($event[0]) && $event[0] == $class) ||
            //backward compability
            (isset($event['class']) && $event['class'] == $class)) {
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

    public function unsubscribeclass($obj) {
        $this->unbind($obj);
    }

    public function unsubscribeclassname($class) {
        $this->unbind($class);
    }

    public function unbind($c) {
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

    public function seteventorder($eventname, $c, $order) {
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
                array_splice($events, $order, 0, array(
                    0 => $event
                ));

                $this->save();
                return true;
            }
        }
    }

    private function indexofcoclass($class) {
        return array_search($class, $this->coclasses);
    }

    public function addcoclass($class) {
        if ($this->indexofcoclass($class) === false) {
            $this->coclasses[] = $class;
            $this->save();
            $this->coinstances = getinstance($class);
        }
    }

    public function deletecoclass($class) {
        $i = $this->indexofcoclass($class);
        if (is_int($i)) {
            array_splice($this->coclasses, $i, 1);
            $this->save();
        }
    }

} //class