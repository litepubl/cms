<?php

namespace litepubl\debug;

class LogException
{

public static function toString(\Exception $e) {
        $result = "Caught exception:\n" . $e->getMessage() . "\n";
        $result .= static::getLog($e->getTrace());
return $result;

        $log = str_replace(litepubl::$paths->home, '', $log);
        $this->errorlog.= str_replace("\n", "<br />\n", htmlspecialchars($log));
        tfiler::log($log, 'exceptions.log');

        if (!(litepubl::$debug || $this->echoexception || $this->admincookie || litepubl::$urlmap->adminpanel)) {
            tfiler::log($log, 'exceptionsmail.log');
        }
    }

public static function trace() {
return static::getLog(debug_backtrace());
}

public static function getLog(array $trace) {
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
if ((strlen($v) > 60) && ($i = strpos($v, ' ', 40))) {
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