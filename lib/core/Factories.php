<?php

namespace litepubl\core;

class Factories extends Items
{
use DataStorageTrait;

public $defaults;

public function create() {
parent::create();
$this->basename = 'factories',

$this->defaults = [
'post' => 'litepubl\post\Factory',
'tag' => 'litepubl\tag\Factory',
];
}

public function __get($name) {
if (isset($this->items[$name])) {
return $this->app->getInstance($this->items[$name]);
}

if (isset($this->defaults[$name])) {
return $this->app->getInstance($this->defaults[$name]);
}

return parent::__get($name);
}

}