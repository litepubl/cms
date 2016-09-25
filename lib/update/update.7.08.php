<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\update;

use litepubl\post\Meta;
use litepubl\perms\Files;
use litepubl\plugins\ulogin\Ulogin;

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
}