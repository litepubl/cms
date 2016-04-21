<?php

namespace litepubl\debug;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;

class LoggerFactory
{
use \litepubl\core\AppTrait;

public static function create() {
$app = static::getAppInstance();
$app->includeComposerAutoload();

$logger = new logger('general');
$logger->pushHandler(new StreamHandler($app->paths->data . 'logs/logs.log', Logger::DEBUG));
$logger->pushHandler(new FirePHPHandler());
return $logger;
}

public static function getException(\Exception $e) {
$log = LogException::getLog($e);
return str_replace(static::getAppInstance()->paths->home, '', $log);
}

}