<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\plugins\blackip;

use litepubl\admin\Tabs;
use litepubl\view\Lang;

class AdminBlackIP extends \litepubl\admin\Panel
{
    use \litepubl\core\Singleton;

    public function getContent(): string
    {
        $plugin = BlackIP::i();
        $lang = $this->getLangAbout();
        $args = $this->args;
        $args->ip = implode("\n", $plugin->ip);
        $args->words = implode("\n", $plugin->words);
        $args->ipstatus = $this->theme->comboItems(Lang::i()->ini['commentstatus'], $plugin->ipstatus);
        $args->wordstatus = $this->theme->comboItems(Lang::i()->ini['commentstatus'], $plugin->wordstatus);

        $tabs = new tabs($this->admin);
        $tabs->add($lang->wordtitle, '[combo=wordstatus] [editor=words]');
        $tabs->add('IP', '[combo=ipstatus] [editor=ip]');

        $args->formtitle = $lang->formtitle;
        return $this->admin->form($tabs->get(), $args);
    }

    public function processForm()
    {
        $plugin = BlackIP::i();
        $plugin->ipstatus = $_POST['ipstatus'];
        $plugin->wordstatus = $_POST['wordstatus'];
        $ip = str_replace(
            [
            "\r\n",
            "\r"
            ],
            "\n",
            $_POST['ip']
        );
        $ip = str_replace("\n\n", "\n", $ip);
        $plugin->ip = explode("\n", trim($ip));
        $words = str_replace(
            [
            "\r\n",
            "\r"
            ],
            "\n",
            $_POST['words']
        );
        $words = str_replace("\n\n", "\n", $words);
        $plugin->words = explode("\n", trim($words));
        $plugin->save();
    }
}
