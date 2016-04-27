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

class tadminsingletagwidget extends tadminwidget {

    public static function i() {
        return getinstance(__class__);
    }

    public function getContent() {
        $widget = tsingletagwidget::i();
        $about = Plugins::getabout(Plugins::getname(__file__));
        $html = $this->html;
        $args = new Args();
        $id = (int)$this->getparam('idwidget', 0);
        if (isset($widget->items[$id])) {
            $args->add($widget->items[$id]);
            $args->idwidget = $id;
            $args->data['$lang.invertorder'] = $about['invertorder'];
            $args->formtitle = $widget->gettitle($id);
            return $html->adminform('[text=maxcount]
      [checkbox=invertorder]
      [hidden=idwidget]', $args);
        }
        $tags = array();
        foreach ($widget->items as $id => $item) {
            $tags[] = $item['idtag'];
        }
        $args->formtitle = $about['formtitle'];
        return $html->adminform(admintheme::i()->getcats($tags) , $args);
    }

    public function processForm() {
        $widget = tsingletagwidget::i();
        $id = (int)$this->getparam('idwidget', 0);
        if (isset($widget->items[$id])) {
            $widget->items[$id]['maxcount'] = (int)$_POST['maxcount'];
            $widget->items[$id]['invertorder'] = isset($_POST['invertorder']);
            $widget->save();
             $this->getApp()->cache->clear();
            return '';
        }

        $tags = array();
        foreach ($widget->items as $id => $item) {
            $tags[] = $item['idtag'];
        }
        $list = admintheme::i()->processcategories();
        $add = array_diff($list, $tags);
        $delete = array_diff($tags, $list);
        if ((count($add) == 0) && (count($delete) == 0)) {
 return '';
}


        $widget->lock();
        foreach ($delete as $idtag) {
            $widget->tagdeleted($idtag);
        }

        foreach ($add as $idtag) {
            $widget->add($idtag);
        }
        $widget->unlock();
         $this->getApp()->cache->clear();
    }

}