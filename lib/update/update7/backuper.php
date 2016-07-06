<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
  */

namespace litepubl\update;

class backuper
{

    public static function create(string $dump)
    {
        $storageDir = dirname(dirname(dirname(__DIR__))) . '/storage/';
        $filename = $storageDir . 'backup/backup' . time() . '.zip';
                    $zip = new \ZipArchive();
        if ($zip->open($filename, \ZipArchive::CREATE) === true) {
            static::add($zip, $storageDir, 'data');
            $zip->addFromString('dump.sql', $dump);
            $zip->close();
        }
    }

    public static function add(\ZipArchive $zip, string $dir, string $subdir)
    {
        $list = dir($dir . $subdir);
        $subdir .= '/';
        while ($filename = $list->read()) {
            if ($filename == '.' || $filename == '..') {
                continue;
            }

            if (is_dir($dir . $subdir . $filename)) {
                static::add($zip, $dir, $subdir . $filename);
            } else {
                        $zip->addFromString($subdir . $filename, file_get_contents($dir . $subdir . $filename));
            }
        }

        $list->close();
    }

}
