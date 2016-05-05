<?php

namespace litepubl\test;

init::init();

class init
{
public static $homedir;
public static $admin;
public static $lang;

public function init()
{
require_once (__DIR__ . '/Base.php');

static::$homedir = dirname(dirname(dirname(dirname(dirname(__DIR__)))));
static::$admin = new Base('admin', ['email' => '', 'password' => '']);
static::$lang = new \ArrayObject(parse_ini_file(
static::$homedir . '/lib/languages/ru/admin.ini', false),
\ArrayObject::ARRAY_AS_PROPS);

}

}