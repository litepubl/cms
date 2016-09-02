<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.07
  */

namespace litepubl\plugins\singlecat;

class Admin extends \litepubl\admin\Panel
{

    public function getContent(): string
    {
        $plugin = Plugin::i();
        $lang = $this->getLangAbout();
        $args = $this->args;
        $args->maxcount = $plugin->maxcount;
        $args->invertorder = $plugin->invertorder;
        $args->tml = $plugin->tml;
        $args->tmlitems = $plugin->tmlitems;
        $args->formtitle = $lang->formtitle;
        return $this->admin->form(
            '
 [checkbox=invertorder]
    [text=maxcount]
    [editor=tml]
    [editor=tmlitems]', $args
        );
    }

    public function processForm()
    {
        $plugin = Plugin::i();
        $plugin->invertorder = isset($_POST['invertorder']);
        $plugin->maxcount = (int)$_POST['maxcount'];
        $plugin->tml = $_POST['tml'];
        $plugin->tmlitems = $_POST['tmlitems'];
        $plugin->save();
    }
}
