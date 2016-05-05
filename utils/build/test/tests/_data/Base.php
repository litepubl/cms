<?php

namespace litepubl\test;

class Base
{
protected $data;
protected $name;

public function __construct($name = 'base', array $data = [])
{
$this->name = $name;
$this->data = $data;
$this->create();
$this->load();
}

protected function create()
{
}

public function __get($name)
{
return $this->data[$name];
}

public function __set($name, $value)
{
$this->data[$name] = $value;
$this->save();
}

public function getFileName()
{
return sprintf('%s/%s.json', __DIR__, $this->name);
}

public function load()
{
$filename = $this->getFileName();
if (file_exists($filename) && ($js = file_get_contents($filename))) {
$this->data = json_decode($js, true);
}
}

public function save()
{
$js = json_encode($this->data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT);
$filename = $this->getFileName();
file_put_contents($filename, $js);
@chmod($filename, 0666);
}

}
