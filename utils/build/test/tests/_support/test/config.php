<?php

namespace test;

//config::init();

class config
{
public static $home;
public static $_data;
public static $screenshot = true;
private static $lang;

public function init()
{
static::$home = dirname(dirname(dirname(dirname(dirname(dirname(__DIR__))))));
static::$_data = dirname(dirname(__DIR__)) . '/_data/';
}

public static function getLang(){
if (!static::$lang) {
static::$lang = new \ArrayObject(parse_ini_file(
static::$home . '/lib/languages/ru/admin.ini', false),
\ArrayObject::ARRAY_AS_PROPS);
}

return static::$lang;
}

public function load($name)
{
return json_decode(file_get_contents(static::$_data . $name . '.json'), false);
}

public function save($name, $data)
{
file_put_contents(
static::$_data . $name . '.json',
 json_encode($data, JSON_UNESCAPED_UNICODE | JSON_PRETTY_PRINT)
);
}

public function exists($name)
{
return file_exists(static::$_data . $name . '.json');
}

}