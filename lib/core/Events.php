<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\core;

class Events extends Data
{
    use EventsTrait;

    protected $map;

    public function __construct()
    {
        if (!is_array($this->map)) {
            $this->map = [];
        }

        parent::__construct();

        $this->assignmap();
        $this->load();
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
        parent::free();
        unset($this->getApp()->classes->instances[get_class($this) ]);
    }
}
