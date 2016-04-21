<?php

namespace litepubl\debug;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\FirePHPHandler;
use Monolog\Handler\BrowserConsoleHandler;
use Monolog\Formatter\HtmlFormatter;

class LoggerFactory
{
use \litepubl\core\AppTrait;

public static function create() {
$app = static::getAppInstance();
$app->includeComposerAutoload();

$handler = new StreamHandler($app->paths->data . 'logs/logs.log', Logger::DEBUG);
$handler->setFormatter(new HtmlFormatter());

$logger = new logger('general');
$logger->pushHandler($handler);
$logger->pushHandler(new BrowserConsoleHandler());
$logger->pushHandler(new FirePHPHandler());
return $logger;
}

public static function getException(\Exception $e) {
        $log = "Caught exception:\n" . $e->getMessage() . "\n";
$log .= LogException::getLog($e);
return str_replace(static::getAppInstance()->paths->home, '', $log);
}

}