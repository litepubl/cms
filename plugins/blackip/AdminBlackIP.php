<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\plugins\blackip;
use litepubl\admin\Tabs;

class AdminBlackIP extends \litepubl\admin\Simple
{
use \litepubl\core\Singleton;

    public function getcontent() {
        $plugin = BlackIP::i();
        $lang = $this->getLangAbbout();
        $args = $this->args;
        $args->ip = implode("\n", $plugin->ip);
        $args->words = implode("\n", $plugin->words);
        $args->ipstatus = $this->theme->comboItems(tlocal::i()->ini['commentstatus'], $plugin->ipstatus);
        $args->wordstatus = $this->theme->comboItems(tlocal::i()->ini['commentstatus'], $plugin->wordstatus);

        $tabs = new tabs($this->admin);
        $tabs->add($lang->wordtitle, '[combo=wordstatus] [editor=words]');
        $tabs->add('IP', '[combo=ipstatus] [editor=ip]');

        $args->formtitle = $lang->formtitle;
        return $this->admin->form($tabs->get() , $args);
    }

    public function processform() {
        $plugin = BlackIP::i();
        $plugin->ipstatus = $_POST['ipstatus'];
        $plugin->wordstatus = $_POST['wordstatus'];
        $ip = str_replace(array(
            "\r\n",
            "\r"
        ) , "\n", $_POST['ip']);
        $ip = str_replace("\n\n", "\n", $ip);
        $plugin->ip = explode("\n", trim($ip));
        $words = str_replace(array(
            "\r\n",
            "\r"
        ) , "\n", $_POST['words']);
        $words = str_replace("\n\n", "\n", $words);
        $plugin->words = explode("\n", trim($words));
        $plugin->save();
    }

}