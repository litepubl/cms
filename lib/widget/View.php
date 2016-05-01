<?php

namespace litepubl\widget;
use litepubl\view\Theme;
use litepubl\view\Args;
use litepubl\view\Vars;

class View
{
public $theme;

public function __construct(Theme $theme = null)
{
$this->theme = $theme ? $theme :: Theme::context();
}

    public static function getWidgetnames() {
        return array(
            'categories',
            'tags',
            'archives',
            'links',
            'posts',
            'comments',
            'friends',
            'meta'
        );
    }

    public function getPosts(array $items, $sidebar, $tml) {
        if (!count($items)) {
 return '';
}

        $result = '';
        if (!$tml) {
$tml = $this->getItem('posts', $sidebar);
}

$vars = new Vars();
        foreach ($items as $id) {
$vars->post = Post::i($id);
            $result.= $this->theme->parse($tml);
        }

        return str_replace('$item', $result, $this->getItems('posts', $sidebar));
    }

    public function getContent($items, $name, $sidebar) {
        return str_replace('$item', $items, $this->getItems($name, $sidebar));
    }

    public function getWidget($title, $content, $template, $sidebar) {
        $args = new Args();
        $args->title = $title;
        $args->items = $content;
        $args->sidebar = $sidebar;
        return $this->theme->parsearg($this->getTml($sidebar, $template, '') , $args);
    }

    public function getWidgetId($id, $title, $content, $template, $sidebar) {
        $args = new Args();
        $args->id = $id;
        $args->title = $title;
        $args->items = $content;
        $args->sidebar = $sidebar;
        return $this->theme->parsearg($this->getTml($sidebar, $template, '') , $args);
    }

    public function getItem($name, $index) {
        return $this->getTml($index, $name, 'item');
    }

    public function getItems($name, $index) {
        return $this->getTml($index, $name, 'items');
    }

    public function getTml($index, $name, $tml) {
        $count = count($this->theme->templates['sidebars']);
        if ($index >= $count) {
$index = $count - 1;
}

        $widgets =  $this->theme->templates['sidebars'][$index];
        if (($tml != '') && ($tml[0] != '.')) {
$tml = '.' . $tml;
}

        if (isset($widgets[$name . $tml])) {
 return $widgets[$name . $tml];
}

        if (isset($widgets['widget' . $tml])) {
 return $widgets['widget' . $tml];
}

        $this->error("Unknown widget '$name' and template '$tml' in $index sidebar");
    }

    public function getAjax($id, $title, $sidebar, $tml) {
        $args = new Args();
        $args->title = $title;
        $args->id = $id;
        $args->sidebar = $sidebar;
        return $this->theme->parsearg($this->theme->templates[$tml], $args);
    }

    public static function getWidgetpath($path) {
        if ($path === '') {
 return '';
}

        switch ($path) {
            case '.items':
                return '.items';

            case '.items.item':
            case '.item':
                return '.item';

            case '.items.item.subcount':
            case '.item.subcount':
            case '.subcount':
                return '.subcount';

            case '.items.item.subitems':
            case '.item.subitems':
            case '.subitems':
                return '.subitems';

            case '.classes':
            case '.items.classes':
                return '.classes';
        }

        return false;
    }

}