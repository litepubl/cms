<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\plugins\postcontent;

class Admin extends \litepubl\admin\Panel
{

    public function getContent(): string
    {
        $plugin = Plugin::i();
        $args = $this->args;
        $lang = $this->getLangAbout();
        $args->formtitle = $lang->formtitle;
        $args->before = $plugin->before;
        $args->after = $plugin->after;
        return $this->admin->form('[editor=before] [editor=after]', $args);
    }

    public function processForm()
    {
        extract($_POST, EXTR_SKIP);
        $plugin = Plugin::i();
        $plugin->lock();
        $plugin->before = $before;
        $plugin->after = $after;
        $plugin->unlock();
        return '';
    }
}
