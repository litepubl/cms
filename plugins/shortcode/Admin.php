<?php
/**
 * Lite Publisher CMS
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
  */

namespace litepubl\plugins\shortcode;

class Admin extends \litepubl\admin\Panel
{

    public function getContent(): string
    {
        $plugin = Plugin::i();
        $lang = $this->getLangAbout();
        $args = $this->args;

        $s = '';
        foreach ($plugin->items as $name => $value) {
            $s.= "$name = $value\n";
        }

        $args->codes = $s;
        $args->formtitle = $lang->formtitle;

        return $this->admin->form('[editor=codes]', $args);
    }

    public function processForm()
    {
        $plugin = Plugin::i();
        $plugin->items = parse_ini_string($_POST['codes'], false);
        $plugin->save();
    }
}
