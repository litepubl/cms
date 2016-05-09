<?php

namespace Page;

trait Singleton
{
private $instance;

public static function i()
{
if (!static::$instance) {
static::$instance = new static;
}

return static::$instance;
}

}