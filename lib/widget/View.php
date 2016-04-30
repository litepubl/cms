<?php

namespace litepubl\widget;

class View
{
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

    public function getPostswidgetcontent(array $items, $sidebar, $tml) {
        if (count($items) == 0) {
 return '';
}


        $result = '';
        if ($tml == '') $tml = $this->getwidgetitem('posts', $sidebar);
        foreach ($items as $id) {
            static ::$vars['post'] = Post::i($id);
            $result.= $this->parse($tml);
        }
        unset(static ::$vars['post']);
        return str_replace('$item', $result, $this->getwidgetitems('posts', $sidebar));
    }

    public function getWidgetcontent($items, $name, $sidebar) {
        return str_replace('$item', $items, $this->getwidgetitems($name, $sidebar));
    }

    public function getWidget($title, $content, $template, $sidebar) {
        $args = new Args();
        $args->title = $title;
        $args->items = $content;
        $args->sidebar = $sidebar;
        return $this->parsearg($this->getwidgettml($sidebar, $template, '') , $args);
    }

    public function getIdwidget($id, $title, $content, $template, $sidebar) {
        $args = new Args();
        $args->id = $id;
        $args->title = $title;
        $args->items = $content;
        $args->sidebar = $sidebar;
        return $this->parsearg($this->getwidgettml($sidebar, $template, '') , $args);
    }

    public function getWidgetitem($name, $index) {
        return $this->getwidgettml($index, $name, 'item');
    }

    public function getWidgetitems($name, $index) {
        return $this->getwidgettml($index, $name, 'items');
    }

    public function getWidgettml($index, $name, $tml) {
        $count = count($this->templates['sidebars']);
        if ($index >= $count) $index = $count - 1;
        $widgets = & $this->templates['sidebars'][$index];
        if (($tml != '') && ($tml[0] != '.')) $tml = '.' . $tml;
        if (isset($widgets[$name . $tml])) {
 return $widgets[$name . $tml];
}


        if (isset($widgets['widget' . $tml])) {
 return $widgets['widget' . $tml];
}


        $this->error("Unknown widget '$name' and template '$tml' in $index sidebar");
    }

    public function getAjaxtitle($id, $title, $sidebar, $tml) {
        $args = new Args();
        $args->title = $title;
        $args->id = $id;
        $args->sidebar = $sidebar;
        return $this->parsearg($this->templates[$tml], $args);
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