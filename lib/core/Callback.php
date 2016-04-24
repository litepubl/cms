<?php

namespace litepubl\core;

class Callback
{
private $events;

public function __construct()
{
$this->events = [];
}

public function on()
{
        return $this->add(func_get_args());
}

public function add(array $callback)
{
if (count($callback)) {
$this->events[] = $callback;
$indexes = array_keys($this->events);
return $indexes[count($indexes) - 1];
}
}

public function delete($index)
{
if (isset($this->events[$index])) {
unset($this->events[$index];
}
}

public function clear()
{
$this->events = [];
}

public function getCount()
{
return count($this->events);}

public function fire()
{
        foreach ($this->events as $a) {
            try {
                $c = array_shift($a);
                if (!is_callable($c)) {
                    $c = [
                        $c,
                        array_shift($a)
                    ];
                }

                call_user_func_array($c, $a);
            }
            catch(\Exception $e) {
litepubl::$app->logException($e);
            }
}
}

}