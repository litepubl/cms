<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\widget;
use litepubl\view\Theme;
use litepubl\view\Vars;
use litepubl\view\Schema;

class Widget extends \litepubl\core\Events
 {
    public $id;
    public $template;
    protected $adminclass;
protected $adminInstance;

    protected function create() {
        parent::create();
        $this->basename = 'widget';
        $this->cache = 'cache';
        $this->id = 0;
        $this->template = 'widget';
        $this->adminclass = '\litepubl\admin\widget\Widget';
    }

    public function addToSidebar($sidebar) {
        $widgets = Widgets::i();
        $id = $widgets->add($this);
        $sidebars = Sidebars::i();
        $sidebars->insert($id, false, $sidebar, -1);

        $this->getApp()->cache->clear();
        return $id;
    }

    protected function getAdmin() {
if (!$this->adminInstance) {
            $this->adminInstance = $this->getApp()->classes->getinstance($this->adminclass);
            $this->adminInstance->widget = $this;
        }

return $this->adminInstance;
    }

    public function getwidget($id, $sidebar) {
$vars = new Vars();
$vars->widget = $this;

        try {
            $title = $this->gettitle($id);
            $content = $this->getcontent($id, $sidebar);
        }
        catch(\Exception $e) {
            litepubl::$options->handexception($e);
            return '';
        }
        $theme = Theme::i();
return $theme->getidwidget($id, $title, $content, $this->template, $sidebar);
    }

    public function getdeftitle() {
        return '';
    }

    public function gettitle($id) {
        if (!isset($id)) $this->error('no id');
        $widgets = Widgets::i();
        if (isset($widgets->items[$id])) {
            return $widgets->items[$id]['title'];
        }
        return $this->getdeftitle();
    }

    public function settitle($id, $title) {
        $widgets = Widgets::i();
        if (isset($widgets->items[$id]) && ($widgets->items[$id]['title'] != $title)) {
            $widgets->items[$id]['title'] = $title;
            $widgets->save();
        }
    }

    public function getcontent($id, $sidebar) {
        return '';
    }

    public static function getcachefilename($id) {
        $theme = Theme::context();
        return sprintf('widget.%s.%d.php', $theme->name, $id);
    }

    public function expired($id) {
        switch ($this->cache) {
            case 'cache':
                Cache::i()->expired($id);
                break;


            case 'include':
                $sidebar = static ::findsidebar($id);
                $filename = static ::getcachefilename($id, $sidebar);
                litepubl::$urlmap->cache->set($filename, $this->getcontent($id, $sidebar));
                break;
        }
    }

    public static function findsidebar($id) {
        $schema = Schema::i();
        foreach ($schema->sidebars as $i => $sidebar) {
            foreach ($sidebar as $item) {
                if ($id == $item['id']) {
return $i;
}
            }
        }

        return 0;
    }

    public function expire() {
        $widgets = Widgets::i();
        foreach ($widgets->items as $id => $item) {
            if ($this instanceof $item['class']) $this->expired($id);
        }
    }

    public function getcontext($class) {
        if (litepubl::$urlmap->context instanceof $class) return litepubl::$urlmap->context;
        //ajax
        $widgets = Widgets::i();
        return litepubl::$urlmap->getidcontext($widgets->idurlcontext);
    }

}
 //class