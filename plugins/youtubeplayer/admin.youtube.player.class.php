<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;

class tadminyoutubeplayer {

    public function getContent() {
        $plugin = tyoutubeplayer::i();
        $about = tplugins::getabout(tplugins::getname(__file__));
        $args = new Args();
        $args->formtitle = $about['formtitle'];
        $args->data['$lang.template'] = $about['template'];
        $args->template = $plugin->template;
        $html = tadminhtml::i();
        return $html->adminform('[editor:template]', $args);
    }

    public function processForm() {
        $plugin = tyoutubeplayer::i();
        $plugin->template = $_POST['template'];
        $plugin->save();
    }

} //class