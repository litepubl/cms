<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 7.00
 *
 */


namespace litepubl\plugins\singletagwidget;

use litepubl\core\Plugins;
use litepubl\view\Args;

class Admin extends \litepubl\admin\widget\Widget
{

    public function getContent(): string
    {
        $widget = Widget::i();
        $lang = $this->getLangAbout();
        $args = $this->args;
        $id = (int)$this->getParam('idwidget', 0);
        if (isset($widget->items[$id])) {
            $args->add($widget->items[$id]);
            $args->idwidget = $id;
            $args->formtitle = $widget->getTitle($id);
            return $this->admin->form(
                '[text=maxcount]
      [checkbox=invertorder]
      [hidden=idwidget]', $args
            );
        }

        $tags = array();
        foreach ($widget->items as $id => $item) {
            $tags[] = $item['idtag'];
        }

        $args->formtitle = $lang->formtitle;
        return $this->admin->form($this->admin->getcats($tags), $args);
    }

    public function processForm()
    {
        $widget = Widget::i();
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

        $list = $this->admin->processCategories();
        $add = array_diff($list, $tags);
        $delete = array_diff($tags, $list);
        if (!count($add) && !count($delete)) {
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
