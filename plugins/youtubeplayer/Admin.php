<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 6.15
 *
 */

namespace litepubl\plugins\youtubeplayer;

class Admin extends \litepubl\admin\Panel
{

    public function getContent(): string
    {
        $plugin = Plugin::i();
        $lang = $this->getLangAbout();
        $args = $this->args;
        $args->formtitle = $lang->formtitle;
        $args->template = $plugin->template;

        return $this->admin->form('[editor=template]', $args);
    }

    public function processForm()
    {
        $plugin = Plugin::i();
        $plugin->template = $_POST['template'];
        $plugin->save();
    }
}
