<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\debug;
use Monolog\Logger;
use Monolog\Handler\StreamHandler;
use Monolog\Handler\BufferHandler;
use Monolog\Formatter\HtmlFormatter;

class LoggerFactory
{
use \litepubl\core\AppTrait;

public static function create() {
$app = static::getAppInstance();
$logger = new logger('general');
$handler = new StreamHandler($app->paths->data . 'logs/logs.log', Logger::DEBUG, true, 0666);
$logger->pushHandler($handler);

            $handler = new RuntimeHandler();
$handler->setFormatter(new HtmlFormatter());
$logger->pushHandler($handler);
$app->runtimeLog = $handler;
return $logger;
}

public static function getException(\Exception $e) {
        $log = "Caught exception:\n" . $e->getMessage() . "\n";
$log .= LogException::getLog($e);
return str_replace(static::getAppInstance()->paths->home, '', $log);
}

}