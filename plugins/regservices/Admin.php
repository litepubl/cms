<?php
/**
* 
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
 *
 */


namespace litepubl\plugins\regservices;

use litepubl\admin\Tabs;

class Admin extends \litepubl\admin\Panel
{

    public function getContent(): string
    {
        $plugin = Plugin::i();
        $tabs = new Tabs($this->admin);
        $args = $this->args;
        $lang = $this->getLangAbout();
        $args->formtitle = $lang->options;
        foreach ($plugin->items as $id => $classname) {
            $service = static ::iGet($classname);
            $tabs->add($service->title, $service->gettab($this));
        }

        return $this->admin->form($tabs->get(), $args);
    }

    public function processForm()
    {
        $plugin = Plugin::i();
        $plugin->lock();
        foreach ($plugin->items as $name => $classname) {
            $service = static ::iGet($classname);
            $service->processForm();
        }

        $plugin->updateWidget();
        $plugin->unlock();
        return '';
    }
}
