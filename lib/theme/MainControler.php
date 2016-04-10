<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\theme;
use litepubl\widget\Widgets;

class MainControler extends \litepubl\core\Events
{
use \litepubl\core\DataStorageTrait;

    public $model;
    public $path;
    public $url;
    public $itemplate;
    public $view;
    public $ltoptions;
    public $custom;
    public $hover;
    public $extrahead;
    public $extrabody;
    public $result;

    protected function create() {
        //prevent recursion
        litepubl::$classes->instances[get_class($this) ] = $this;
        parent::create();
        $this->basename = 'template';
        $this->addevents('beforecontent', 'aftercontent', 'onhead', 'onbody', 'onrequest', 'ontitle', 'ongetmenu');
        $this->path = litepubl::$paths->themes . 'default' . DIRECTORY_SEPARATOR;
        $this->url = litepubl::$site->files . '/themes/default';
        $this->itemplate = false;
        $this->ltoptions = array(
            'url' => litepubl::$site->url,
            'files' => litepubl::$site->files,
            'idurl' => litepubl::$urlmap->itemrequested['id'],
            'lang' => litepubl::$site->language,
            'video_width' => litepubl::$site->video_width,
            'video_height' => litepubl::$site->video_height,
            'theme' => array() ,
            'custom' => array() ,
        );
        $this->hover = true;
        $this->data['heads'] = '';
        $this->data['js'] = '<script type="text/javascript" src="%s"></script>';
        $this->data['jsready'] = '<script type="text/javascript">$(document).ready(function() {%s});</script>';
        $this->data['jsload'] = '<script type="text/javascript">$.load_script(%s);</script>';
        $this->data['footer'] = '<a href="http://litepublisher.com/">Powered by Lite Publisher</a>';
        $this->data['tags'] = array();
        $this->addmap('custom', array());
        $this->extrahead = '';
        $this->extrabody = '';
        $this->result = '';
    }

    public function assignmap() {
        parent::assignmap();
        $this->ltoptions['custom'] = & $this->custom;
        $this->ltoptions['jsmerger'] = & $this->data['jsmerger'];
        $this->ltoptions['cssmerger'] = & $this->data['cssmerger'];
    }

    public function __get($name) {
        if (method_exists($this, $get = 'get' . $name)) return $this->$get();
        if (array_key_exists($name, $this->data)) return $this->data[$name];
        if (preg_match('/^sidebar(\d)$/', $name, $m)) {
            $widgets = Widgets::i();
            return $widgets->getsidebarindex($this->model, $this->view, (int)$m[1]);
        }

        if (array_key_exists($name, $this->data['tags'])) {
            $tags = ttemplatetags::i();
            return $tags->$name;
        }
        if (isset($this->model) && isset($this->model->$name)) return $this->model->$name;
        return parent::__get($name);
    }

    protected function get_view($model) {
        return $this->itemplate ? View::getview($model) : View::i();
    }

    public function request($model) {
        $this->model = $model;
        ttheme::$vars['model'] = $model;
        ttheme::$vars['template'] = $this;
        $this->itemplate = $model instanceof itemplate;
        $this->view = $this->get_view($model);
        $theme = $this->view->theme;
        $this->ltoptions['theme']['name'] = $theme->name;
        litepubl::$classes->instances[get_class($theme) ] = $theme;
        $this->path = litepubl::$paths->themes . $theme->name . DIRECTORY_SEPARATOR;
        $this->url = litepubl::$site->files . '/themes/' . $theme->name;
        if ($this->view->hovermenu) {
            $this->hover = $theme->templates['menu.hover'];
            if ($this->hover != 'bootstrap') $this->hover = ($this->hover == 'true');
        } else {
            $this->hover = false;
        }

        $this->result = $this->httpheader();
        $this->result.= $theme->gethtml($model);

        $this->onbody($this);
        if ($this->extrabody) $this->result = str_replace('</body>', $this->extrabody . '</body>', $this->result);
        $this->onrequest($this);

        unset(ttheme::$vars['model'], ttheme::$vars['template']);
        return $this->result;
    }

    protected function httpheader() {
        $ctx = $this->model;
        if (method_exists($ctx, 'httpheader')) {
            $result = $ctx->httpheader();
            if (!empty($result)) return $result;
        }

        if (isset($ctx->idperm) && ($idperm = $ctx->idperm)) {
            $perm = tperm::i($idperm);
            if ($result = $perm->getheader($ctx)) {
                return $result . turlmap::htmlheader($ctx->cache);
            }
        }

        return turlmap::htmlheader($ctx->cache);
    }

    //html tags
    public function getsidebar() {
        return Widgets::i()->getsidebar($this->model, $this->view);
    }

    public function gettitle() {
        $title = $this->itemplate ? $this->model->gettitle() : '';
        if ($this->callevent('ontitle', array(&$title
        ))) {
            return $title;
        } else {
            return $this->parsetitle($this->view->theme->templates['title'], $title);
        }
    }

    public function parsetitle($tml, $title) {
        $args = new targs();
        $args->title = $title;
        $result = $this->view->theme->parsearg($tml, $args);
        //$result = trim($result, sprintf(' |.:%c%c', 187, 150));
        $result = trim($result, " |.:\n\r\t");
        if ($result == '') return litepubl::$site->name;
        return $result;
    }

    public function geticon() {
        $result = '';
        if (isset($this->model) && isset($this->model->icon)) {
            $icon = $this->model->icon;
            if ($icon > 0) {
                $files = Files::i();
                if ($files->itemexists($icon)) $result = $files->geturl($icon);
            }
        }
        if ($result == '') return litepubl::$site->files . '/favicon.ico';
        return $result;
    }

    public function getkeywords() {
        $result = $this->itemplate ? $this->model->getkeywords() : '';
        if ($result == '') return litepubl::$site->keywords;
        return $result;
    }

    public function getdescription() {
        $result = $this->itemplate ? $this->model->getdescription() : '';
        if ($result == '') return litepubl::$site->description;
        return $result;
    }

    public function getmenu() {
        if ($r = $this->ongetmenu()) {
            return $r;
        }

        $view = $this->view;
        $menuclass = $view->menuclass;
        $filename = $view->theme->name . sprintf('.%s.%s.php', str_replace('\\', '-', $menuclass) , litepubl::$options->group ? litepubl::$options->group : 'nobody');

        if ($result = litepubl::$urlmap->cache->get($filename)) {
            return $result;
        }

        $menus = getinstance($menuclass);
        $result = $menus->getmenu($this->hover, 0);
        litepubl::$urlmap->cache->set($filename, $result);
        return $result;
    }

    private function getltoptions() {
        return sprintf('<script type="text/javascript">window.ltoptions = %s;</script>', tojson($this->ltoptions));
    }

    public function getjavascript($filename) {
        return sprintf($this->js, litepubl::$site->files . $filename);
    }

    public function getready($s) {
        return sprintf($this->jsready, $s);
    }

    public function getloadjavascript($s) {
        return sprintf($this->jsload, $s);
    }

    public function addtohead($s) {
        $s = trim($s);
        if (false === strpos($this->heads, $s)) {
            $this->heads = trim($this->heads) . "\n" . $s;
            $this->save();
        }
    }

    public function deletefromhead($s) {
        $s = trim($s);
        $i = strpos($this->heads, $s);
        if (false !== $i) {
            $this->heads = substr_replace($this->heads, '', $i, strlen($s));
            $this->heads = trim(str_replace("\n\n", "\n", $this->heads));
            $this->save();
        }
    }

    public function gethead() {
        $result = $this->heads;
        if ($this->itemplate) $result.= $this->model->gethead();
        $result = $this->getltoptions() . $result;
        $result.= $this->extrahead;
        $result = $this->view->theme->parse($result);
        $this->callevent('onhead', array(&$result
        ));
        return $result;
    }

    public function getcontent() {
        $result = '';
        $this->callevent('beforecontent', array(&$result
        ));
        $result.= $this->itemplate ? $this->model->getcont() : '';
        $this->callevent('aftercontent', array(&$result
        ));
        return $result;
    }

    protected function setfooter($s) {
        if ($s != $this->data['footer']) {
            $this->data['footer'] = $s;
            $this->Save();
        }
    }

    public function getpage() {
        $page = litepubl::$urlmap->page;
        if ($page <= 1) return '';
        return sprintf(tlocal::get('default', 'pagetitle') , $page);
    }

    public function trimwords($s, array $words) {
        if ($s == '') return '';
        foreach ($words as $word) {
            if (strbegin($s, $word)) $s = substr($s, strlen($word));
            if (strend($s, $word)) $s = substr($s, 0, strlen($s) - strlen * ($word));
        }
        return $s;
    }

} //class