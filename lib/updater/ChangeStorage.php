<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.04
  */

namespace litepubl\updater;

use litepubl\core\Storage;
use litepubl\core\StorageInc;

class ChangeStorage
{
    public $source;
    public $dest;
    private $callback;

    public function __construct(Storage $source, Storage$dest, $callback = null)
    {
        $this->source = $source;
        $this->dest = $dest;
        $this->callback = $callback;
    }

    public function copy(string $from, string $to)
    {
        if (!is_dir($to)) {
            mkdir($to, 0777);
            @chmod($to, 0777);
        }

        $list = dir($from);
        while ($filename = $list->read()) {
            if ($filename == '.' || $filename == '..') {
                continue;
            }

            if (is_dir($from . '/' . $filename)) {
                $this->copy($from . '/' . $filename, $to . '/' . $filename);
            } else {
                $this->convert($from, $to, $filename);
            }
        }

        $list->close();
    }

    public function convert(string $sourcedir, string $destdir, string $filename)
    {
        if (!strpos($filename, '.bak.')) {
            $base = basename($filename, $this->source->getExt());
            if ($data = $this->source->loadData($sourcedir . '/' . $base)) {
                if ($this->callback) {
                    if ($base == 'storage') {
                        $data = $this->iterateCallback($data);
                        $this->dest->saveData($destdir . '/' . $base, $data);
                    } else {
                        $std = new \StdClass();
                        $std->data = $data;
                        call_user_func_array($this->callback, [$std]);
                        $this->dest->saveData($destdir . '/' . $base, $std->data);
                    }
                } else {
                                $this->dest->saveData($destdir . '/' . $base, $data);
                }
            }
        }
    }

    public function iterateCallback(array $data): array
    {
                    $std = new \StdClass();
        foreach ($data as $name => $subdata) {
            $std->data = $subdata;
            call_user_func_array($this->callback, [$std]);
            $data[$name] = $std->data;
        }

        return $data;
    }

    public static function create($callback = null)
    {
        include_once dirname(__DIR__) . '/core/AppTrait.php';
        include_once dirname(__DIR__) . '/core/Storage.php';
        include_once dirname(__DIR__) . '/core/StorageInc.php';

        return new static(
        new Storage(),
         new StorageInc(),
        $callback
        );
    }

    public function run(string $dirname)
    {
        $dir = dirname(dirname(__DIR__)) . '/storage/';
        $temp= 'temp' . time();
        $this->copy($dir . $dirname, $dir . $temp);
        return $temp;
    }
}
