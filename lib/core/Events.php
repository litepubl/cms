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
    protected $map;

    public function __construct()
    {
        if (!is_array($this->map)) {
            $this->map = array();
        }

        parent::__construct();

        $this->assignmap();
        $this->load();
    }

    protected function create()
    {
        parent::create();
        $this->addmap('events', array());
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

}