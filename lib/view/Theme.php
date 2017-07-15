<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\view;

use litepubl\core\Str;
use litepubl\pages\Users as UserPages;
use litepubl\post\Post;

class Theme extends Base
{

    public static function context()
    {
        $result = static ::i();
        if (!$result->name) {
            if (($view = static ::getAppInstance()->context->view) && isset($view->IdSchema)) {
                $result = Schema::getSchema($view)->theme;
            } else {
                $result = Schema::i()->theme;
            }
        }

        return $result;
    }

    protected function create()
    {
        parent::create();
        $this->templates = [
            'index' => '',
            'title' => '',
            'menu' => '',
            'content' => '',
            'sidebars' => [] ,
            'custom' => [] ,
            'customadmin' => []
        ];
    }

    public function __tostring()
    {
        return $this->templates['index'];
    }

    public function getParser(): BaseParser
    {
        return Parser::i();
    }

    public function getSidebarscount()
    {
        return count($this->templates['sidebars']);
    }
    private function get_author()
    {
        $model = isset($this->getApp()->router->model) ? $this->getApp()->router->model : MainView::i()->model;
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
        foreach ([
            'author',
            'idauthor',
            'user',
            'iduser'
        ] as $propname) {
            if (isset($model->$propname)) {
                $iduser = $model->$propname;
                break;
            }
        }

        if (!$iduser) {
            return new EmptyClass();
        }

        $pages = UserPages::i();
        if (!$pages->itemExists($iduser)) {
            return new emptyclass();
        }

        $pages->request($iduser);
        return $pages;
    }

    public function render($model)
    {
        $vars = new Vars();
        $vars->context = $model;
        $vars->model = $model;

        if (isset($model->index_tml) && ($tml = $model->index_tml)) {
            return $this->parse($tml);
        }

        return $this->parse($this->templates['index']);
    }

    public function setVar($name, $obj)
    {
        static ::$vars[$name] = $obj;
    }

    public function getNotfound()
    {
        return $this->parse($this->templates['content.notfound']);
    }

    public function getPages(string $url, int $page, int $count, string $params = ''): string
    {
        if (!(($count > 1) && ($page >= 1) && ($page <= $count))) {
            return '';
        }

        $args = new Args();
        $args->count = $count;
        $from = 1;
        $to = $count;
        $perpage = $this->getApp()->options->perpage;
        $args->perpage = $perpage;
        $items = [];
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
                    array_splice($items, count($items), 0, range($perpage, $from - 1, $perpage));
                }
            }
            array_splice($items, count($items), 0, range($from, $to));
        }

        if ($to < $count) {
            $from2 = (int)($perpage * ceil(($to + 1) / $perpage));
            if ($from2 + $perpage >= $count) {
                if ($from2 < $count) {
                    $items[] = $from2;
                }
            } else {
                array_splice($items, count($items), 0, range($from2, $count, $perpage));
            }
            if ($items[count($items) - 1] != $count) {
                $items[] = $count;
            }
        }

        $currenttml = $this->templates['content.navi.current'];
        $tml = $this->templates['content.navi.link'];
        if (!Str::begin($url, 'http')) {
            $url = $this->getApp()->site->url . $url;
        }
        $pageurl = rtrim($url, '/') . '/page/';
        if ($params) {
            $params = $this->getApp()->site->q . $params;
        }

        $a = [];
        if (($page > 1) && ($tml_prev = trim($this->templates['content.navi.prev']))) {
            $i = $page - 1;
            $args->page = $i;
            $link = $i == 1 ? $url : $pageurl . $i . '/';
            if ($params) {
                $link.= $params;
            }
            $args->link = $link;
            $a[] = $this->parseArg($tml_prev, $args);
        }

        foreach ($items as $i) {
            $args->page = $i;
            $link = $i == 1 ? $url : $pageurl . $i . '/';
            if ($params) {
                $link.= $params;
            }
            $args->link = $link;
            $a[] = $this->parseArg(($i == $page ? $currenttml : $tml), $args);
        }

        if (($page < $count) && ($tml_next = trim($this->templates['content.navi.next']))) {
            $i = $page + 1;
            $args->page = $i;
            $link = $pageurl . $i . '/';
            if ($params) {
                $link.= $params;
            }
            $args->link = $link;
            $a[] = $this->parseArg($tml_next, $args);
        }

        $args->link = $url;
        $args->pageurl = $pageurl;
        $args->page = $page;
        $args->items = implode($this->templates['content.navi.divider'], $a);
        return $this->parseArg($this->templates['content.navi'], $args);
    }

    public function simple($content)
    {
        return str_replace('$content', $content, $this->templates['content.simple']);
    }

    public function getButton($title)
    {
        return strtr(
            $this->templates['content.admin.button'],
            [
            '$lang.$name' => $title,
            'name="$name"' => '',
            'id="submitbutton-$name"' => ''
            ]
        );
    }

    public function getSubmit($title)
    {
        return strtr(
            $this->templates['content.admin.submit'],
            [
            '$lang.$name' => $title,
            'name="$name"' => '',
            'id="submitbutton-$name"' => ''
            ]
        );
    }

    public function getInput(string $type, string $name, string $value, string $title): string
    {
        return strtr(
            $this->templates['content.admin.' . $type],
            [
            '$lang.$name' => $title,
            '$name' => $name,
            '$value' => $value
            ]
        );
    }

    public function getRadio(string $name, string $value, string $title, bool $checked): string
    {
        return strtr(
            $this->templates['content.admin.radioitem'],
            [
            '$lang.$name' => $title,
            '$name' => $name,
            '$value' => $title,
            '$index' => $value,
            '$checked' => $checked ? 'checked="checked"' : '',
            ]
        );
    }

    public function getRadioItems(string $name, array $items, $selected): string
    {
        $result = '';
        foreach ($items as $index => $title) {
            $result.= $this->getRadio($name, $index, static ::quote($title), $index == $selected);
        }

        return $result;
    }

    public function comboItems(array $items, $selected)
    {
        $result = '';
        foreach ($items as $i => $title) {
            $result.= sprintf('<option value="%s" %s>%s</option>', $i, $i == $selected ? 'selected' : '', static ::quote($title));
        }

        return $result;
    }
}
