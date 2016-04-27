<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;
use litepubl\view\Args;
use litepubl\core\Plugins;
use litepubl\admin\AdminInterface;

class tadminregservices implements \litepubl\admin\AdminInterface {

    public static function i() {
        return getinstance(__class__);
    }

    public function getContent() {
        $plugin = tregservices::i();
        $html = tadminhtml::i();
        $tabs = new tabs();
        $args = new Args();
        $lang = Plugins::getnamelang($plugin->dirname);
        $args->formtitle = $lang->options;
        foreach ($plugin->items as $id => $classname) {
            $service = getinstance($classname);
            $tabs->add($service->title, $service->gettab($html, $args, $lang));
        }

        return $html->adminform($tabs->get() , $args);
    }

    public function processForm() {
        $plugin = tregservices::i();
        $plugin->lock();
        foreach ($plugin->items as $name => $classname) {
            $service = getinstance($classname);
            $service->processForm();
        }

        $plugin->update_widget();
        $plugin->unlock();
        return '';
    }

}