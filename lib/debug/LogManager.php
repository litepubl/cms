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
use litepubl\core\App;
use Monolog\Logger;
use Monolog\ErrorHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\HtmlFormatter;

class LogManager
{
public $logger;
public $runtime;

public function __construct(App $app)
 {
$logger = new logger('general');
$this->logger = $logger;

if (!Config::$debug) {
$handler = new ErrorHandler($logger);
$handler->registerErrorHandler([], false);
//$handler->registerExceptionHandler();
$handler->registerFatalHandler();
}

$handler = new StreamHandler($app->paths->data . 'logs/logs.log', Logger::DEBUG, true, 0666);
$handler->setFormatter(new LineFormatter(null,  null,true, false));
$logger->pushHandler($handler);

            $handler = new RuntimeHandler();
$handler->setFormatter(new EmptyFormatter());
$logger->pushHandler($handler);

$this->runtime = $handler;
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