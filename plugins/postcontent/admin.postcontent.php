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

class tadminpostcontentplugin {

    public static function i() {
        return getinstance(__class__);
    }

    public function getContent() {
        $plugin = tpostcontentplugin::i();
        $html = tadminhtml::i();
        $args = new Args();
        $about = Plugins::getabout(Plugins::getname(__file__));
        $args->formtitle = $about['formtitle'];
        $args->data['$lang.before'] = $about['before'];
        $args->data['$lang.after'] = $about['after'];
        $args->before = $plugin->before;
        $args->after = $plugin->after;
        return $html->adminform('[editor=before] [editor=after]', $args);
    }

    public function processForm() {
        extract($_POST, EXTR_SKIP);
        $plugin = tpostcontentplugin::i();
        $plugin->lock();
        $plugin->before = $before;
        $plugin->after = $after;
        $plugin->unlock();
        return '';
    }

}