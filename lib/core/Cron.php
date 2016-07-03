<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 7.00
 *
 */


namespace litepubl\core;

use litepubl\Config;
use litepubl\utils\Mailer;

class Cron extends Events implements ResponsiveInterface
{
    public static $pinged = false;
    public $disableadd;
    private $socket;

    protected function create()
    {
        parent::create();
        $this->basename = 'cron';
        $this->addevents('added', 'deleted');
        $this->data['password'] = '';
        $this->data['path'] = '';
        $this->data['disableping'] = false;
        $this->disableadd = false;
        $this->table = 'cron';
    }

    protected function getUrl(): string
    {
        return sprintf('/croncron.htm%scronpass=%s', $this->getApp()->site->q, urlencode($this->password));
    }

    public function getLockpath(): string
    {
        if (($result = $this->path) && is_dir($result)) {
                return $result;
            }

        return $this->getApp()->paths->data;
    }

    public function request(Context $context)
    {
            $context->response->cache = false;
        if (!isset($_GET['cronpass']) || ($this->password != $_GET['cronpass'])) {
            $context->response->status = 403;
            return;
        }

        if (($fh = @fopen($this->lockpath . 'cron.lok', 'w')) && flock($fh, LOCK_EX | LOCK_NB)) {
            try {
                set_time_limit(300);
                if (Config::$debug) {
                    ignore_user_abort(true);
                } else {
                    $this->getApp()->router->close_connection();
                }

                if (ob_get_level()) {
                    ob_end_flush();
                }
                flush();

                $this->sendexceptions();
                $this->log("started loop");
                $this->execute();
            } catch (\Exception $e) {
                $this->getApp()->logException($e);
            }
            flock($fh, LOCK_UN);
            fclose($fh);
            @chmod($this->lockpath . 'cron.lok', 0666);
            $this->log("finished loop");
            return 'Ok';
        }
        return 'locked';
    }

    public function run()
    {
        if (ob_get_level()) {
            ob_end_flush();
        }
        flush();

        if (($fh = @fopen($this->lockpath . 'cron.lok', 'w')) && flock($fh, LOCK_EX | LOCK_NB)) {
            set_time_limit(300);

            try {
                $this->execute();
            } catch (\Exception $e) {
                $this->getApp()->logException($e);
            }

            flock($fh, LOCK_UN);
            fclose($fh);
            @chmod($this->lockpath . 'cron.lok', 0666);
            return true;
        }

        return false;
    }

    public function execute()
    {
        while ($item = $this->db->getassoc(sprintf("date <= '%s' order by date asc limit 1", Str::sqlDate()))) {
            extract($item);
            $this->log("task started:\n{$class}->{$func}($arg)");
            $arg = unserialize($arg);
            if ($class == '') {
                if (function_exists($func)) {
                    try {
                        $func($arg);
                    } catch (\Exception $e) {
                        $this->getApp()->logException($e);
                    }
                } else {
                    $this->db->iddelete($id); {
                        continue;
                    }
                }
            } elseif (class_exists($class)) {
                try {
                    $obj = static ::iGet($class);
                    $obj->$func($arg);
                } catch (\Exception $e) {
                    $this->getApp()->logException($e);
                }
            } else {
                $this->db->iddelete($id); {
                    continue;
                }
            }
            if ($type == 'single') {
                $this->db->iddelete($id);
            } else {
                $this->db->setvalue($id, 'date', Str::sqlDate(strtotime("+1 $type")));
            }
        }
    }

    public function add(string $type, string $class, string $func, $arg = null): int
    {
        if (!preg_match('/^single|hour|day|week$/', $type)) {
            $this->error("Unknown cron type $type");
        }

        if ($this->disableadd) {
            return 0;
        }

        $id = $this->doAdd($type, $class, $func, $arg);

        if (($type == 'single') && !$this->disableping && !static ::$pinged) {
            if (Config::$debug) {
                $this->getApp()->getLogger()->info("cron added $id");
            }

            $memvars = Memvars::i();
            if (!$memvars->singlecron) {
                $memvars->singlecron = time() + 300;
            }
        }

        return $id;
    }

    protected function doAdd(string $type, string $class, string $func, $arg): int
    {
        $id = $this->db->add(array(
            'date' => Str::sqlDate() ,
            'type' => $type,
            'class' => $class,
            'func' => $func,
            'arg' => serialize($arg)
        ));

        $this->added($id);
        return $id;
    }

    public function addNightly(string $class, string $func, $arg): int
    {
        $id = $this->db->add(array(
            'date' => date('Y-m-d 03:15:00', time()) ,
            'type' => 'day',
            'class' => $class,
            'func' => $func,
            'arg' => serialize($arg)
        ));
        $this->added($id);
        return $id;
    }

    public function addWeekly(string $class,string $func, $arg): int
    {
        $id = $this->db->add(array(
            'date' => date('Y-m-d 03:15:00', time()) ,
            'type' => 'week',
            'class' => $class,
            'func' => $func,
            'arg' => serialize($arg)
        ));

        $this->added($id);
        return $id;
    }

    public function delete($id)
    {
        $this->db->iddelete($id);
        $this->deleted($id);
    }

    public function deleteClass($c)
    {
        $class = static ::get_class_name($c);
        $this->db->delete("class = '$class'");
    }

    public static function pingOnShutdown()
    {
        if (static ::$pinged) {
            return;
        }

        static ::$pinged = true;

        register_shutdown_function(array(
        static ::i() ,
            'ping'
        ));
    }

    public function ping()
    {
        $p = parse_url($this->getApp()->site->url . $this->url);
        $this->pinghost($p['host'], $p['path'] . (empty($p['query']) ? '' : '?' . $p['query']));
    }

    private function pingHost(string $host, string $path)
    {
        if ($this->socket = @fsockopen($host, 80, $errno, $errstr, 0.10)) {
            fputs($this->socket, "GET $path HTTP/1.0\r\nHost: $host\r\n\r\n");
            //0.01 sec
            usleep(10000);
        }
    }

    public function sendExceptions()
    {
        $filename = $this->getApp()->paths->data . 'logs' . DIRECTORY_SEPARATOR . 'exceptionsmail.log';
        if (!file_exists($filename)) {
            return;
        }

        $time = @filectime($filename);
        if (($time === false) || ($time + 3600 > time())) {
            return;
        }

        $s = file_get_contents($filename);
        $this->getApp()->storage->delete($filename);
        Mailer::SendAttachmentToAdmin('[error] ' . $this->getApp()->site->name, 'See attachment', 'errors.txt', $s);
        sleep(2);
    }

    public function log($s)
    {
        echo date('r') . "\n$s\n\n";
        flush();
        if (Config::$debug) {
                $this->getApp()->getLogger()->info($s);
        }
    }
}
