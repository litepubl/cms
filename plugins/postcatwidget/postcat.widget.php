<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl;
use litepubl\core\Arr;

class tpostcatwidget extends tclasswidget {
    public $items;

    public static function i() {
        return getinstance(__class__);
    }

    protected function create() {
        parent::create();
        $this->cache = false;
        $this->adminclass = 'tadminpostcatwidget';
        $this->basename = 'widget.postcat';
        $this->addmap('items', array());
    }

    public function add($title, $content, $template, $cats) {
        $widgets = twidgets::i();
        $widgets->lock();
        $id = $widgets->addclass($this, 'tpost');
        $widgets->items[$id]['title'] = $title;
        $widgets->unlock();
        $this->items[$id] = array(
            'title' => $title,
            'content' => $content,
            'template' => $template,
            'cats' => $cats
        );

        $this->save();
        //$this->added($id);
        return $id;
    }

    public function delete($id) {
        if (isset($this->items[$id])) {
            unset($this->items[$id]);
            $this->save();

            $widgets = twidgets::i();
            $widgets->delete($id);
            //$this->deleted($id);
            
        }
    }

    public function widgetdeleted($id) {
        if (isset($this->items[$id])) {
            unset($this->items[$id]);
            $this->save();
        }
    }

    public function tagdeleted($idtag) {
        foreach ($this->items as & $item) {
            Arr::deleteValue($item['cats'], $idtag);
        }
        $this->save();
    }

    public function getWidget($id, $sidebar) {
        if (!isset($this->items[$id])) {
 return '';
}


        $item = $this->items[$id];
        $post = $this->getcontext('tpost');
        if (0 == count(array_intersect($item['cats'], $post->categories))) {
 return '';
}


        if ($item['template'] == '') {
 return $item['content'];
}


        $theme = ttheme::i();
        return $theme->getwidget($item['title'], $item['content'], $item['template'], $sidebar);
    }

    public function getTitle($id) {
        if (isset($this->items[$id])) {
 return $this->items[$id]['title'];
}


        return '';
    }

    public function getContent($id, $sidebar) {
        if (isset($this->items[$id])) {
 return $this->items[$id]['content'];
}


        return '';
    }

} //class