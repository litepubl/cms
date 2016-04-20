<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\view;
use litepubl\post\Post;
use litepubl\post\Posts;
use litepubl\pages\Users as UserPages;
use litepubl\core\Str;

class Theme extends Base
 {

    public static function context() {
        $result = static ::i();
        if (!$result->name) {
            if (($model =  litepubl::$app->router->model) && isset($model->IdSchema)) {
                $result = Schema::getSchema($model)->theme;
            } else {
                $result = Schema::i()->theme;
            }
        }

        return $result;
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

    protected function create() {
        parent::create();
        $this->templates = array(
            'index' => '',
            'title' => '',
            'menu' => '',
            'content' => '',
            'sidebars' => array() ,
            'custom' => array() ,
            'customadmin' => array()
        );
    }

    public function __tostring() {
        return $this->templates['index'];
    }

    public function getParser() {
        return ThemeParser::i();
    }

    public function getSidebarscount() {
        return count($this->templates['sidebars']);
    }
    private function get_author() {
        $model = isset( $this->getApp()->router->model) ?  $this->getApp()->router->model : MainView::i()->model;
        if (!is_object($model)) {
            if (!isset(static ::$vars['post'])) {
return new EmptyClass();
}

            $model = static ::$vars['post'];
        }

        if ($model instanceof UserPages) {
return $model;
}

        $iduser = 0;
        foreach (array(
            'author',
            'idauthor',
            'user',
            'iduser'
        ) as $propname) {
            if (isset($model->$propname)) {
                $iduser = $model->$propname;
                break;
            }
        }

        if (!$iduser) {
return new EmptyClass();
}

        $pages = UserPages::i();
        if (!$pages->itemexists($iduser)) {
return new emptyclass();
}

        $pages->request($iduser);
        return $pages;
    }

    public function render($model) {
$vars = new Vars();
$vars->context = $model;
$vars->model = $model;

        if (isset($model->index_tml) && ($tml = $model->index_tml)) {
            return $this->parse($tml);
        }

        return $this->parse($this->templates['index']);
    }

public function setVar($name, $obj) {
static::$vars[$name] = $obj;
}

    public function getNotfount() {
        return $this->parse($this->templates['content.notfound']);
    }

    public function getPages($url, $page, $count, $params = '') {
        if (!(($count > 1) && ($page >= 1) && ($page <= $count))) {
            return '';
        }

        $args = new Args();
        $args->count = $count;
        $from = 1;
        $to = $count;
        $perpage =  $this->getApp()->options->perpage;
        $args->perpage = $perpage;
        $items = array();
        if ($count > $perpage * 2) {
            //$page is midle of the bar
            $from = (int)max(1, $page - ceil($perpage / 2));
            $to = (int)min($count, $from + $perpage);
        }

        if ($from == 1) {
            $items = range($from, $to);
        } else {
            $items[0] = 1;
            if ($from > $perpage) {
                if ($from - $perpage - 1 < $perpage) {
                    $items[] = $perpage;
                } else {
                    array_splice($items, count($items) , 0, range($perpage, $from - 1, $perpage));
                }
            }
            array_splice($items, count($items) , 0, range($from, $to));
        }

        if ($to < $count) {
            $from2 = (int)($perpage * ceil(($to + 1) / $perpage));
            if ($from2 + $perpage >= $count) {
                if ($from2 < $count) $items[] = $from2;
            } else {
                array_splice($items, count($items) , 0, range($from2, $count, $perpage));
            }
            if ($items[count($items) - 1] != $count) $items[] = $count;
        }

        $currenttml = $this->templates['content.navi.current'];
        $tml = $this->templates['content.navi.link'];
        if (!Str::begin($url, 'http')) $url =  $this->getApp()->site->url . $url;
        $pageurl = rtrim($url, '/') . '/page/';
        if ($params) $params =  $this->getApp()->site->q . $params;

        $a = array();
        if (($page > 1) && ($tml_prev = trim($this->templates['content.navi.prev']))) {
            $i = $page - 1;
            $args->page = $i;
            $link = $i == 1 ? $url : $pageurl . $i . '/';
            if ($params) $link.= $params;
            $args->link = $link;
            $a[] = $this->parsearg($tml_prev, $args);
        }

        foreach ($items as $i) {
            $args->page = $i;
            $link = $i == 1 ? $url : $pageurl . $i . '/';
            if ($params) $link.= $params;
            $args->link = $link;
            $a[] = $this->parsearg(($i == $page ? $currenttml : $tml) , $args);
        }

        if (($page < $count) && ($tml_next = trim($this->templates['content.navi.next']))) {
            $i = $page + 1;
            $args->page = $i;
            $link = $pageurl . $i . '/';
            if ($params) $link.= $params;
            $args->link = $link;
            $a[] = $this->parsearg($tml_next, $args);
        }

        $args->link = $url;
        $args->pageurl = $pageurl;
        $args->page = $page;
        $args->items = implode($this->templates['content.navi.divider'], $a);
        return $this->parsearg($this->templates['content.navi'], $args);
    }

    public function keyanounce($postanounce) {
        if (!$postanounce || $postanounce == 'excerpt' || $postanounce == 'default') {
            return 'excerpt';
        }

        if ($postanounce === true || $postanounce === 1 || $postanounce == 'lite') {
            return 'lite';
        }

        return 'card';
    }

    public function getPosts(array $items, $postanounce) {
        if (!count($items)) {
            return '';
        }

        $result = '';
        $tml_key = $this->keyanounce($postanounce);
        Posts::i()->loaditems($items);

        static ::$vars['lang'] = Lang::i('default');
        foreach ($items as $id) {
            $post = Post::i($id);
            $result.= $post->getcontexcerpt($tml_key);
            // has $author.* tags in tml
            if (isset(static ::$vars['author'])) {
                unset(static ::$vars['author']);
            }
        }

        if ($tml = $this->templates['content.excerpts' . ($tml_key == 'excerpt' ? '' : '.' . $tml_key) ]) {
            $result = str_replace('$excerpt', $result, $this->parse($tml));
        }

        unset(static ::$vars['post']);
        return $result;
    }

    public function getPostsnavi(array $items, $url, $count, $postanounce, $perpage) {
        $result = $this->getposts($items, $postanounce);
        if (!$perpage) $perpage =  $this->getApp()->options->perpage;
        $result.= $this->getpages($url,  $this->getApp()->router->page, ceil($count / $perpage));
        return $result;
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

    public function simple($content) {
        return str_replace('$content', $content, $this->templates['content.simple']);
    }

    public function getButton($title) {
        return strtr($this->templates['content.admin.button'], array(
            '$lang.$name' => $title,
            'name="$name"' => '',
            'id="submitbutton-$name"' => ''
        ));
    }

    public function getSubmit($title) {
        return strtr($this->templates['content.admin.submit'], array(
            '$lang.$name' => $title,
            'name="$name"' => '',
            'id="submitbutton-$name"' => ''
        ));
    }

    public function getInput($type, $name, $value, $title) {
        return strtr($this->templates['content.admin.' . $type], array(
            '$lang.$name' => $title,
            '$name' => $name,
            '$value' => $value
        ));
    }

    public function getRadio($name, $value, $title, $checked) {
        return strtr($this->templates['content.admin.radioitem'], array(
            '$lang.$name' => $title,
            '$name' => $name,
            '$value' => $title,
            '$index' => $value,
            '$checked' => $checked ? 'checked="checked"' : '',
        ));
    }

    public function getRadioItems($name, array $items, $selected) {
        $result = '';
        foreach ($items as $index => $title) {
            $result.= $this->getradio($name, $index, static ::specchars($title) , $index == $selected);
        }
        return $result;
    }


    public function comboItems(array $items, $selected) {
        $result = '';
        foreach ($items as $i => $title) {
            $result.= sprintf('<option value="%s" %s>%s</option>', $i, $i == $selected ? 'selected' : '', static ::specchars($title));
        }

        return $result;
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