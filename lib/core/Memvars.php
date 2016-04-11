<?php

namespace litepubl\core;

class Memvars
{
public static $vars;

public static function i() {
if (!static::$vars) {
if (litepubl::$memcache) {
static::$vars = new MemvarMemcache();
} else {
static::$vars = new MemvarMysql();
}
}

return  static::$vars;
}
}