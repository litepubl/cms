<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

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