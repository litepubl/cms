<?php
/**
 * Lite Publisher CMS
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
  */

namespace litepubl\plugins\subcat;

class Admin extends \litepubl\admin\widget\Widget
{

    public function getContent(): string
    {
        $widget = Widget::i();
        $lang = $this->getlangAbout();
        $args = $this->args;
        $id = (int)$this->getParam('idwidget', 0);
        if (isset($widget->items[$id])) {
            $args->add($widget->items[$id]);
            $args->sort = $this->theme->comboItems($lang->ini['sortnametags'], $widget->items[$id]['sortname']);
            $args->idwidget = $id;
            $args->formtitle = $widget->getTitle($id);
            return $this->admin->form(
                '
      [combo=sort]
      [checkbox=showsubitems]
      [checkbox=showcount]
      [text=maxcount]
      [hidden=idwidget]', $args
            );
        }
        $tags = array();
        foreach ($widget->items as $id => $item) {
            $tags[] = $item['idtag'];
        }
        $args->formtitle = $lang->formtitle;
        return $this->admin->form($this->admin->getCats($tags), $args);
    }

    public function processForm()
    {
        $widget = Widget::i();
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
        $list = $this->admin->processCategories();
        $add = array_diff($list, $tags);
        $delete = array_diff($tags, $list);
        if ((count($add) == 0) && (count($delete) == 0)) {
            return '';
        }

        $widget->lock();
        foreach ($delete as $idtag) {
            $widget->tagDeleted($idtag);
        }

        foreach ($add as $idtag) {
            $widget->add($idtag);
        }

        $widget->unlock();
    }
}
