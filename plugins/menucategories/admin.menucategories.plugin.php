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

class tadmincategoriesmenu {

    public static function i() {
        return getinstance(__class__);
    }

    public function getContent() {
        $plugin = tcategoriesmenu::i();
        $about = Plugins::getabout(Plugins::getname(__file__));
        $args = new Args();
        $args->cats = admintheme::i()->getcats($plugin->exitems);
        $args->formtitle = $about['formtitle'];
        //    $args->data['$lang.before'] = $about['before'];
        $html = tadminhtml::i();
        return $html->adminform('$cats', $args);
    }

    public function processForm() {
        $plugin = tcategoriesmenu::i();
        $plugin->exitems = tadminhtml::check2array('category-');
        $plugin->save();
    }

}