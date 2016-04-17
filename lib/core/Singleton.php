<?php


namespace litepubl\core;

trait Singleton
{

public static function i() {
return litepubl::$app->classes->getInstance(get_called_class());
}
}