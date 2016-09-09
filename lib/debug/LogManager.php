<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.07
  */

namespace litepubl\debug;

use Monolog\ErrorHandler;
use Monolog\Formatter\HtmlFormatter;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use litepubl\Config;
use litepubl\utils\Filer;

class LogManager
{
    use \litepubl\core\AppTrait;
    const format = "%datetime%\n%channel%.%level_name%:\n%message%\n%context% %extra%\n\n";
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
        $handler->setFormatter(new LineFormatter(static ::format, null, true, false));
        $logger->pushHandler($handler);

        $this->runtime = new RuntimeHandler(Logger::WARNING);
        $this->runtime->setFormatter(new EmptyFormatter());
        $logger->pushHandler($this->runtime);

        if (!Config::$debug && $app->installed) {
            $handler = new MailerHandler('[error] ' . $app->site->name, Logger::WARNING);
            $handler->setFormatter(new LineFormatter(static ::format, null, true, false));
            $logger->pushHandler($handler);
        }
    }

    public function logException(\Throwable $e, array $context = [])
    {
        $log = "Caught exception:\n" . $e->getMessage() . "\n";
        $log.= LogException::getLog($e);
        $log = str_replace(dirname(dirname(__DIR__)), '', $log);
        $this->logger->alert($log, $context);
    }

    public function getTrace()
    {
        $log = LogException::trace();
        $log = str_replace(dirname(dirname(__DIR__)), '', $log);
        return "\n" . $log;
    }

    public function trace(string $mesg = '')
    {
        $this->logger->info($mesg . $this->getTrace());
    }

    public function getHtml()
    {
        if (count($this->runtime->log)) {
            $formatter = new HtmlFormatter();
            $result = $formatter->formatBatch($this->runtime->log);
            //clear current log
            $this->runtime->log = [];
            return $result;
        }

        return '';
    }

    public static function old($mesg)
    {
        $log = date('r') . "\n";
        if (isset($_SERVER['REQUEST_URI'])) {
            $log.= $_SERVER['REQUEST_URI'] . "\n";
        }

        if (!is_string($s)) {
            $s = var_export($s, true);
        }

        $log.= $s;
        $log.= "\n";
        Filer::append(static ::getAppInstance()->paths->data . 'logs/filer.log', $log);
    }
}
