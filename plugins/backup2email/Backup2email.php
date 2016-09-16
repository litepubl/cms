<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\plugins\backup2email;

use litepubl\updater\Backuper;
use litepubl\utils\Mailer;

class Backup2email extends \litepubl\core\Plugin
{

    protected function create()
    {
        parent::create();
        $this->data['idcron'] = 0;
    }

    public function send()
    {
        $backuper = Backuper::i();
        $filename = $backuper->createBackup();

        $ini = parse_ini_file(__DIR__ . '/about.ini');

        Mailer::SendAttachmentToAdmin("[backup] $filename", $ini['body'], basename($filename), file_get_contents($filename));
    }
}
