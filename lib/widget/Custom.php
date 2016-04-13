<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\widget;
use litepubl\view\Theme;

class Custom extends Widget
 {
    public $items;

    protected function create() {
        parent::create();
        $this->basename = 'widgets.custom';
        $this->adminclass = 'tadmincustomwidget';
        $this->addmap('items', array());
        $this->addevents('added', 'deleted');
    }

    public function getwidget($id, $sidebar) {
        if (!isset($this->items[$id])) return '';
        $item = $this->items[$id];
        if ($item['template'] == '') return $item['content'];
        $theme = Theme::i();
        return $theme->getwidget($item['title'], $item['content'], $item['template'], $sidebar);
    }

    public function gettitle($id) {
        return $this->items[$id]['title'];
    }

    public function getcontent($id, $sidebar) {
        return $this->items[$id]['content'];
    }

    public function add($idview, $title, $content, $template) {
        $widgets = Widgets::i();
        $widgets->lock();
        $id = $widgets->addext($this, $title, $template);
        $this->items[$id] = array(
            'title' => $title,
            'content' => $content,
            'template' => $template
        );

        $sidebars = Sidebars::i($idview);
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
        litepubl::$urlmap->clearcache();
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

} //class