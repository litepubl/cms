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

    protected function addEvents()
    {
        $a = func_get_args();
        foreach ($a as $name) {
                $this->eventnames[] = strtolower($name);
        }
    }

}