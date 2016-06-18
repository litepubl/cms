<?php

namespace Page;

trait Singleton
{
    private static $instance;

    public static function i(\AcceptanceTester $I)
    {
        if (!static::$instance) {
            static::$instance = new static($I);
        }

        return static::$instance;
    }
}
