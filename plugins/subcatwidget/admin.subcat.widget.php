<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;

class tadminsubcatwidget extends tadminwidget {

    public static function i() {
        return getinstance(__class__);
    }

    public function getContent() {
        $widget = tsubcatwidget::i();
        $about = tplugins::getabout(tplugins::getname(__file__));
        $html = $this->html;
        $args = new Args();
        $id = (int)$this->getparam('idwidget', 0);
        if (isset($widget->items[$id])) {
            $args->add($widget->items[$id]);
            $args->sort = $this->theme->comboItems(Lang::admin()->ini['sortnametags'], $widget->items[$id]['sortname']);
            $args->idwidget = $id;
            $args->data['$lang.invertorder'] = $about['invertorder'];
            $args->formtitle = $widget->gettitle($id);
            return $html->adminform('
      [combo=sort]
      [checkbox=showsubitems]
      [checkbox=showcount]
      [text=maxcount]
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
        $widget = tsubcatwidget::i();
        $id = (int)$this->getparam('idwidget', 0);
        if (isset($widget->items[$id])) {
            $item = $widget->items[$id];
            extract($_POST, EXTR_SKIP);
            $item['maxcount'] = (int)$maxcount;
            $item['showcount'] = isset($showcount);
            $item['showsubitems'] = isset($showsubitems);
            $item['sortname'] = $sort;
            $widget->items[$id] = $item;
            $widget->save();
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
    }

} //class