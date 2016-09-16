<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\plugins\openid;

class Admin extends \litepubl\admin\Panel
{

    public function getContent(): string
    {
        $openid = Plugin::i();
        $args = $this->args;
        $args->confirm = $openid->confirm;
        $args->usebigmath = $openid->usebigmath;
        $args->trusted = implode("\n", $openid->trusted);

        $tml = '[checkbox=confirm]
    [checkbox=usebigmath]
    [editor=trusted]';

        $lang = $this->getLangAbout();
        $args->formtitle = $lang->formtitle;

        return $this->admin->form($tml, $args);
    }

    public function processForm()
    {
        extract($_POST, EXTR_SKIP);
        $openid = Plugin::i();
        $openid->confirm = isset($confirm);
        $openid->usebigmath = isset($usebigmath);
        $openid->trusted = explode("\n", trim($trusted));
        $openid->save();
    }
}
