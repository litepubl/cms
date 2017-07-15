<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\admin\options;

use litepubl\post\Pinger as PostPinger;
use litepubl\view\Args;
use litepubl\view\Lang;

class Pinger extends \litepubl\admin\Menu
{
    public function getContent(): string
    {
        $pinger = PostPinger::i();
        $args = new Args();
        $args->enabled = $pinger->enabled;
        $args->services = $pinger->services;

        $lang = Lang::admin('options');
        $args->formtitle = $lang->optionsping;
        return $this->admintheme->form(
            '
[checkbox=enabled]
[editor=services]
',
            $args
        );
    }

    public function processForm()
    {
        $pinger = PostPinger::i();
        $pinger->enabled = isset($_POST['enabled']);
        $pinger->services = trim($_POST['services']);
        $pinger->save();
    }
}
