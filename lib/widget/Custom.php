<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\widget;
use litepubl\view\Theme;

class Custom extends Widget
 {
    public $items;

    protected function create() {
        parent::create();
        $this->basename = 'widgets.custom';
        $this->adminclass = '\litepubl\admin\widget\Custom';
        $this->addmap('items', array());
        $this->addevents('added', 'deleted');
    }

    public function getWidget($id, $sidebar) {
        if (!isset($this->items[$id])) {
 return '';
}


        $item = $this->items[$id];
        if ($item['template'] == '') {
 return $item['content'];
}


        $theme = Theme::i();
        return $theme->getwidget($item['title'], $item['content'], $item['template'], $sidebar);
    }

    public function getTitle($id) {
        return $this->items[$id]['title'];
    }

    public function getContent($id, $sidebar) {
        return $this->items[$id]['content'];
    }

    public function add($idschema, $title, $content, $template) {
        $widgets = Widgets::i();
        $widgets->lock();
        $id = $widgets->addext($this, $title, $template);
        $this->items[$id] = array(
            'title' => $title,
            'content' => $content,
            'template' => $template
        );

        $sidebars = Sidebars::i($idschema);
        $sidebars->add($id);
        $widgets->unlock();
        $this->save();
        $this->added($id);
        return $id;
    }

    public function edit($id, $title, $content, $template) {
        $this->items[$id] = array(
            'title' => $title,
            'content' => $content,
            'template' => $template
        );
        $this->save();

        $widgets = Widgets::i();
        $widgets->items[$id]['title'] = $title;
        $widgets->save();
        $this->expired($id);
         $this->getApp()->router->clearcache();
    }

    public function delete($id) {
        if (isset($this->items[$id])) {
            unset($this->items[$id]);
            $this->save();

            $widgets = Widgets::i();
            $widgets->delete($id);
            $this->deleted($id);
        }
    }

    public function widgetdeleted($id) {
        if (isset($this->items[$id])) {
            unset($this->items[$id]);
            $this->save();
        }
    }

} 