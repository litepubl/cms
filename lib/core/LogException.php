<?php

namespace litepubl\core;

class LogException
{

public static function toString(\Exception $e) {
        $log = "Caught exception:\n" . $e->getMessage() . "\n";
        $trace = $e->getTrace();
        foreach ($trace as $i => $item) {
            if (isset($item['line'])) {
                $log.= sprintf('#%d %d %s ', $i, $item['line'], $item['file']);
            }

            if (isset($item['class'])) {
                $log.= $item['class'] . $item['type'] . $item['function'];
            } else {
                $log.= $item['function'];
            }

            if (isset($item['args']) && count($item['args'])) {
                $args = array();
                foreach ($item['args'] as $arg) {
                    $args[] = static ::var_export($arg);
                }

                $log.= "\n";
                $log.= implode(', ', $args);
            }

            $log.= "\n";
        }

        $log = str_replace(litepubl::$paths->home, '', $log);
        $this->errorlog.= str_replace("\n", "<br />\n", htmlspecialchars($log));
        tfiler::log($log, 'exceptions.log');

        if (!(litepubl::$debug || $this->echoexception || $this->admincookie || litepubl::$urlmap->adminpanel)) {
            tfiler::log($log, 'exceptionsmail.log');
        }
    }

    public function trace($msg) {
        try {
            throw new \Exception($msg);
        }
        catch(\Exception $e) {
            $this->handexception($e);
        }
    }

    public function showerrors() {
        if (!empty($this->errorlog) && (litepubl::$debug || $this->echoexception || $this->admincookie || litepubl::$urlmap->adminpanel)) {
            echo $this->errorlog;
        }
    }

    public static function dump(&$v) {
        switch (gettype($v)) {
            case 'string':
                return sprintf('\'%s\''', $v);

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