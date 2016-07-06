<?php

namespace litepubl\core;

class Event
{
protected $name;
protected $target;
protected $stopped;
protected $params;

public function __construct($target, string $name)
{
$this->target = $target;
$this->name = $name;
$this->stopped = false;
$this->params = [];
}

    public function getName(): string
{
return $this->name;
}

    /**
     * Get target/context from which event was triggered
     */
    public function getTarget()
{
return $this->target;
}

    /**
     * Get parameters passed to the event
     */
    public function getParams(): array
{
return $this->params;
}

    /**
     * Get a single parameter by name
     *
     * @return mixed
     */
    public function getParam(string $name)
{
return $this->params[$name];
}

    /**
     * Set the event name
     */
    public function setName(string $name)
{
$this->name = $name;
}

    /**
     * Set the event target
     *
     * @param  null|string|object $target
     * @return void
     */
    public function setTarget($target)
{
$this->target = $target;
}

    /**
     * Set event parameters
     */
    public function setParams(array $params)
{
$this->params = $params;
}

    /**
     * Indicate whether or not to stop propagating this event
     */
    public function stopPropagation(bool $flag)
{
$this->stopped = $flag;
}

    /**
     * Has this event indicated event propagation should stop?
     */
    public function isPropagationStopped(): bool
{
return $this->stopped;
}

    public function trigger(array $callbacks)
{

}
}
