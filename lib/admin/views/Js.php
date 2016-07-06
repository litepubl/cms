<?php
/**
 * Lite Publisher CMS
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
  */

namespace litepubl\admin\views;

use litepubl\view\Js;
use litepubl\view\Lang;

class Js extends \litepubl\admin\Menu
{

    public function getMerger()
    {
        return \litepubl\view\Js::i();
    }

    public function getContent(): string
    {
        $merger = $this->getmerger();
        $tabs = $this->newTabs();
        $admin = $this->admintheme;
        $theme = $this->theme;
        $lang = Lang::i('views');
        $args = $this->newArgs();
        $args->formtitle = $this->title;
        foreach ($merger->items as $section => $items) {
            $tab = $this->newTabs();
            $tab->add($lang->files, $theme->getInput('editor', $section . '_files', $admin->quote(implode("\n", $items['files'])), $lang->files));
            foreach ($items['texts'] as $key => $text) {
                $tab->add($key, $theme->getinput('editor', $section . '_text_' . $key, $admin->quote($text), $key));
            }

            $tabs->add($section, $tab->get());
        }

        return $admin->form($tabs->get(), $args);
    }

    public function processForm()
    {
        $merger = $this->getmerger();
        $merger->lock();
        //$merger->items = array();
        //$merger->install();
        foreach (array_keys($merger->items) as $section) {
            $keys = array_keys($merger->items[$section]['texts']);
            $merger->setfiles($section, $_POST[$section . '_files']);
            foreach ($keys as $key) {
                $merger->addtext($section, $key, $_POST[$section . '_text_' . $key]);
            }
        }
        $merger->unlock();
    }
}
