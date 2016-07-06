<?php
/**
 * Lite Publisher CMS
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
  */

namespace litepubl\core;

class CoEvents extends Events
{
    protected $owner;
    protected $callbacks;

    public function __construct()
    {
        $args = func_get_args();
        if (isset($args[0])) {
            if (is_array($args[0])) {
                $this->callbacks = array_shift($args);
                $this->trigger_callback('construct');
            } elseif (($owner = array_shift($args)) && is_object($owner) && ($owner instanceof Data)) {
                $this->setowner($owner);
            }
        }

        if (is_array($this->eventnames)) {
            array_splice($this->eventnames, count($this->eventnames), 0, $args);
        } else {
            $this->eventnames = $args;
        }

        parent::__construct();
    }

    public function setOwner(data $owner)
    {
        $this->owner = $owner;
        if (!isset($owner->data['events'])) {
            $owner->data['events'] = array();
        }

        $this->events = & $owner->data['events'];
    }

    public function trigger_callback($name)
    {
        if (isset($this->callbacks[$name])) {
            $callback = $this->callbacks[$name];
            if (is_callable($callback)) {
                $callback($this);
            }
        }
    }

    public function __destruct()
    {
        parent::__destruct();
        unset($this->owner, $this->callbacks);
    }

    public function assignmap()
    {
        if (!$this->owner) {
            parent::assignmap();
        }

        $this->trigger_callback('assignmap');
    }

    protected function create()
    {
        if (!$this->owner) {
            parent::create();
        }

        $this->trigger_callback('create');
    }

    public function load()
    {
        if (!$this->owner) {
            return parent::load();
        }
    }

    public function afterload()
    {
        if ($this->owner) {
            $this->events = & $this->owner->data['events'];
        } else {
            parent::afterload();
        }

        $this->trigger_callback('afterload');
    }

    public function save()
    {
        if ($this->owner) {
            return $this->owner->save();
        } else {
            return parent::save();
        }
    }

    public function inject_events()
    {
        $a = func_get_args();
        array_splice($this->eventnames, count($this->eventnames), 0, $a);
    }
}
