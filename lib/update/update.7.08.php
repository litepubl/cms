<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\update;

use litepubl\perms\Files;
use litepubl\plugins\ulogin\Ulogin;
use litepubl\post\Meta;
use litepubl\view\Js;

function update708()
{
    $meta = Meta::i();
    $man = $meta->db->man;
    if (!$man->tableExists($meta->table)) {
        $meta->install();
    }

    $files = Files::i();
    if (!is_dir($files->getApp()->paths->files . 'private')) {
        $files->install();
    }

    $ulogin = Ulogin::i();
    if ($man->tableExists($ulogin->table)) {
        if (!isset($ulogin->data['remember'])) {
            $ulogin->data['remember'] = true;
            $ulogin->save();
        }
    }

    $js = Js::i();
    $js->lock();
    $section = 'media';
    $lang = $js->getApp()->options->language;
        $js->deleteFile($section, "/lib/languages/$lang/mediaplayer.min.js");
    if ($lang != 'en') {
        $js->add($section, "/js/mediaelement/lang/$lang.min.js");
    }
    $js->unlock();

    include_once(__DIR__ . '/update.7.07.php');
    update707();
}
