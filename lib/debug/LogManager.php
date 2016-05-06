<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\debug;
use litepubl\Config;
use Monolog\Logger;
use Monolog\ErrorHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\HtmlFormatter;
use Monolog\Handler\NativeMailerHandler;

class LogManager
{
use \litepubl\core\AppTrait;

public $logger;
public $runtime;

public function __construct()
 {
$logger = new logger('general');
$this->logger = $logger;

$app = $this->getApp();
if (!Config::$debug) {
$handler = new ErrorHandler($logger);
$handler->registerErrorHandler([], false);
//$handler->registerExceptionHandler();
$handler->registerFatalHandler();
}

$handler = new StreamHandler($app->paths->data . 'logs/logs.log', Logger::DEBUG, true, 0666);
$handler->setFormatter(new LineFormatter(null,  null,true, false));
$logger->pushHandler($handler);

            $this->runtime = new RuntimeHandler(Logger::WARNING);
$this->runtime->setFormatter(new EmptyFormatter());
$logger->pushHandler($this->runtime);

if (!Config::$debug) {
$handler = new NativeMailerHandler($app->options->email, '[error] ' . $app->site->name, $app->options->fromemail, Logger::WARNING );
$handler->setFormatter(new LineFormatter(null,  null,true, false));
$logger->pushHandler($handler);
}
}

public function logException(\Exception $e) {
        $log = "Caught exception:\n" . $e->getMessage() . "\n";
$log .= LogException::getLog($e);
$log = str_replace(dirname(dirname(__DIR__)), '', $log);
$this->logger->alert($log);
}

public function getTrace()
{
$log = LogException::trace();
$log = str_replace(dirname(dirname(__DIR__)), '', $log);
return $log;
}

public function trace()
{
$this->logger->info($this->getTrace());
}

public function getHtml()
{
$formatter = new HtmlFormatter();
$result = $formatter->formatBatch($this->runtime->log);
//clear current log
$this->runtime->log = [];
return $result;
}

}