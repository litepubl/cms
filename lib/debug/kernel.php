<?php
//EmptyFormatter.php
namespace litepubl\debug;

class EmptyFormatter implements \Monolog\Formatter\FormatterInterface
{

    public function format(array $record)
{
return '';
}

    public function formatBatch(array $records)
{
return '';
}
}

//loadAll.php
namespace litepubl\debug;

function includeDir($dir) {
$list = dir($dir );
while ($name = $list->read()) {
if ($name == '.' || $name == '..' || $name == 'kernel.php') {
 continue;
}



$filename = $dir .'/' . $name;
if (is_dir($filename)) {
if ($name != 'include') {
includeDir($filename);
}
} elseif ('.php' == substr($name, -4)) {
echo "$name<br>";
include_once $filename;
}
}

$list->close();
}

        spl_autoload_register(function($class) {
//echo "$class<br>";
$class = trim($class, '\\');
$class = substr($class, strpos($class, '\\') + 1);
$filename = dirname(__DIR__) . '/' . $class . '.php';
echo "$class<br>";
//echo "$filename\n";
require $filename;
});

//include (dirname(dirname(__DIR__ )). '/index.debug.php');
include (__DIR__ . '/Config.php');
include (__DIR__ . '/kernel.php');

includeDir(dirname(__DIR__));includeDir(dirname(dirname(__DIR__)) . '/plugins');

//LogException.php
namespace litepubl\debug;

class LogException
{

public static function getLog(\Exception $e) {
return static::getTraceLog($e->getTrace());
}

public static function trace() {
return static::getTraceLog(debug_backtrace());
}

public static function getTraceLog(array $trace) {
$result = '';
        foreach ($trace as $i => $item) {
            if (isset($item['line'])) {
                $result .= sprintf('#%d %d %s ', $i, $item['line'], $item['file']);
            }

            if (isset($item['class'])) {
                $result .= $item['class'] . $item['type'] . $item['function'];
            } else {
                $result .= $item['function'] . '()';
            }

            if (isset($item['args']) && count($item['args'])) {
                $result .= "\n";
                $args = array();
                foreach ($item['args'] as $arg) {
                    $args[] = static ::dump($arg);
                }

                $result .= implode(', ', $args);
            }

            $result .= "\n";
        }

return $result;
}

    public static function dump(&$v) {
        switch (gettype($v)) {
            case 'string':
if ((strlen($v) > 60) && ($i = strpos($v, ' ', 50))) {
$v = substr($v, 0, $i);
}

                return sprintf('\'%s\'', $v);

            case 'object':
                return get_class($v);

            case 'boolean':
                return $v ? 'true' : 'false';

            case 'integer':
            case 'double':
            case 'float':
                return $v;

            case 'array':
                $result = '';
                foreach ($v as $k => $item) {
                    $s = static ::dump($item);
                    $result.= "$k = $s;\n";
                }

                return "[\n$result]\n";

            default:
                return gettype($v);
        }
    }

}

//LogManager.php
namespace litepubl\debug;
use litepubl\Config;
use Monolog\Logger;
use Monolog\ErrorHandler;
use Monolog\Handler\StreamHandler;
use Monolog\Formatter\LineFormatter;
use Monolog\Formatter\HtmlFormatter;
use Monolog\Handler\NativeMailerHandler;
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
$handler->setFormatter(new LineFormatter(static::format,  null,true, false));
$logger->pushHandler($handler);

            $this->runtime = new RuntimeHandler(Logger::WARNING);
$this->runtime->setFormatter(new EmptyFormatter());
$logger->pushHandler($this->runtime);

if (!Config::$debug) {
$handler = new NativeMailerHandler($app->options->email, '[error] ' . $app->site->name, $app->options->fromemail, Logger::WARNING );
$handler->setFormatter(new LineFormatter(static::format,  null,true, false));
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
        $log .= $_SERVER['REQUEST_URI'] . "\n";
}

        if (!is_string($s)) {
$s = var_export($s, true);
}

$log .= $s;
$log .= "\n";
        Filer::append(static::getAppInstance()->paths->data . 'logs/filer.log', $log);
}

}

//Proxy.php
namespace litepubl\debug;

class Proxy 
{
    public static $trace;
    public static $total;
    public static $stat;
    public static $counts;
    public $obj;
    public $data;
    public $items;
    public $templates;

    public function __construct($obj) {
        $this->obj = $obj;
        if (isset($obj->data)) {
$this->data = & $obj->data;
}

        if ($obj instanceof \litepubl\core\Items) {
$this->items = & $obj->items;
}

        if ($obj instanceof \litepubl\view\Base) {
$this->templates = & $obj->templates;
}
    }

    public function __isset($name) {
        return $this->obj->__isset($name);
    }

    public function __get($name) {
        $m = microtime(true);
        $r = $this->obj->$name;
        $this->addstat(" get $name", microtime(true) - $m);
        return $r;
    }

    public function __set($name, $value) {
        $m = microtime(true);
        $this->obj->$name = $value;
        $this->addstat(" set $name", microtime(true) - $m);
    }

    public function __call($name, $args) {
        //echo get_class($this->obj), " call $name<br>";
        $m = microtime(true);
        $r = call_user_func_array(array(
            $this->obj,
            $name
        ) , $args);
        $this->addstat(" call $name", microtime(true) - $m);
        return $r;
    }

    public function addstat($s, $time) {
        $name = get_class($this->obj) . $s;
        //echo "$name<br>";
        static ::$trace[] = array(
            $name,
            $time
        );
        if (isset(static ::$total[$name])) {
            static ::$total[$name]+= $time;
            ++static ::$counts[$name];
        } else {
            static ::$total[$name] = $time;
            static ::$counts[$name] = 1;
        }
    }

    public static function showperformance() {
        echo "<pre>\n";
        arsort(static ::$total);
        $total = 0;
        foreach (static ::$total as $k => $v) {
            $total+= $v;
            $v = round($v * 1000, 4);
            //$v = round($v * 100000);
            echo static ::$counts[$k];
            echo " $k $v\n";
        }
        $total = $total * 1000;
        echo "total $total\n";
    }

}

//RuntimeHandler.php
namespace litepubl\debug;
use Monolog\Logger;
use Monolog\Handler\AbstractProcessingHandler;

/**
 * Description of runtimeHandler
 *
 * @author Sinisa Culic  <sinisaculic@gmail.com>
 */

class RuntimeHandler extends AbstractProcessingHandler
{
    public $log;

    /**
     * @param integer $level  The minimum logging level at which this handler will be triggered
     * @param Boolean $bubble Whether the messages that are handled can bubble up the stack or not
     */
    public function __construct($level = Logger::DEBUG, $bubble = true)
    {
        parent::__construct($level, $bubble);
        $this->log = [];
    }

    /**
     * {@inheritdoc}
     */
    protected function write(array $record)
    {
        $this->log[] = $record;
    }

}

