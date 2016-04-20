<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\view;
use litepubl\widget\Widgets;
use litepubl\core\Str;

class MainView extends \litepubl\core\Events
{
use \litepubl\core\SharedStorageTrait;

    public $custom;
    public $extrahead;
    public $extrabody;
    public $hover;
    public $ltoptions;
    public $model;
    public $path;
    public $result;
    public $schema;
public $schemaImplemented;
    public $url;

    protected function create() {
        //prevent recursion
         $this->getApp()->classes->instances[get_class($this) ] = $this;
        parent::create();
        $this->basename = 'template';
        $this->addevents('beforecontent', 'aftercontent', 'onhead', 'onbody', 'onrequest', 'ontitle', 'ongetmenu');
        $this->path =  $this->getApp()->paths->themes . 'default' . DIRECTORY_SEPARATOR;
        $this->url =  $this->getApp()->site->files . '/themes/default';
        $this->viewImplemented = false;
        $this->ltoptions = array(
            'url' =>  $this->getApp()->site->url,
            'files' =>  $this->getApp()->site->files,
            'idurl' =>  $this->getApp()->router->item['id'],
            'lang' =>  $this->getApp()->site->language,
            'video_width' =>  $this->getApp()->site->video_width,
            'video_height' =>  $this->getApp()->site->video_height,
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
        if (method_exists($this, $get = 'get' . $name)) {
return $this->$get();
}

        if (array_key_exists($name, $this->data)) {
return $this->data[$name];
}

        if (preg_match('/^sidebar(\d)$/', $name, $m)) {
            $widgets = Widgets::i();
            return $widgets->getsidebarindex($this->model, $this->schema, (int)$m[1]);
        }

        if (isset($this->model) && isset($this->model->$name)) {
return $this->model->$name;
}

        return parent::__get($name);
    }

    protected function getModellSchema($model) {
        return $this->viewImplemented ? Schema::getSchema($model) : Schema::i();
    }

    public function render($model) {
        $this->model = $model;
$vars = new Vars();
$vars->model = $model;
$vars->template = $this;
$vars->mainview = $this;

        $this->viewImplemented = $model instanceof ViewInterface;
        $this->schema = $this->getModellSchema($model);
        $theme = $this->schema->theme;
        $this->ltoptions['theme']['name'] = $theme->name;
         $this->getApp()->classes->instances[get_class($theme) ] = $theme;
        $this->path =  $this->getApp()->paths->themes . $theme->name . DIRECTORY_SEPARATOR;
        $this->url =  $this->getApp()->site->files . '/themes/' . $theme->name;
        if ($this->schema->hovermenu) {
            $this->hover = $theme->templates['menu.hover'];
            if ($this->hover != 'bootstrap') $this->hover = ($this->hover == 'true');
        } else {
            $this->hover = false;
        }

        $this->result = $this->httpheader();
        $this->result.= $theme->render($model);

        $this->onbody($this);
        if ($this->extrabody) {
$this->result = str_replace('</body>', $this->extrabody . '</body>', $this->result);
}

        $this->onrequest($this);
        return $this->result;
    }

    protected function httpheader() {
        $ctx = $this->model;
        if (method_exists($ctx, 'httpheader')) {
            $result = $ctx->httpheader();
            if (!empty($result)) {
return $result;
}
        }

        if (isset($ctx->idperm) && ($idperm = $ctx->idperm)) {
            $perm = tperm::i($idperm);
            if ($result = $perm->getheader($ctx)) {
                return $result .  $this->getApp()->router->htmlheader($ctx->cache);
            }
        }

        return  $this->getApp()->router->htmlheader($ctx->cache);
    }

    //html tags
    public function getSidebar() {
        return Widgets::i()->getsidebar($this->model, $this->schema);
    }

    public function getTitle() {
        $title = $this->viewImplemented ? $this->model->gettitle() : '';
        if ($this->callevent('ontitle', array(&$title
        ))) {
            return $title;
        } else {
            return $this->parsetitle($this->schema->theme->templates['title'], $title);
        }
    }

    public function parsetitle($tml, $title) {
        $args = new Args();
        $args->title = $title;
        $result = $this->schema->theme->parsearg($tml, $args);
        $result = trim($result, " |.:\n\r\t");
        if (!$result) {
return  $this->getApp()->site->name;
}

        return $result;
    }

    public function getIcon() {
        $result = '';
        if (isset($this->model) && isset($this->model->icon)) {
            $icon = $this->model->icon;
            if ($icon > 0) {
                $files = Files::i();
                if ($files->itemexists($icon)) $result = $files->geturl($icon);
            }
        }
        if ($result == '') {
 return  $this->getApp()->site->files . '/favicon.ico';
}


        return $result;
    }

    public function getKeywords() {
        $result = $this->viewImplemented ? $this->model->getkeywords() : '';
        if ($result == '') {
 return  $this->getApp()->site->keywords;
}


        return $result;
    }

    public function getDescription() {
        $result = $this->viewImplemented ? $this->model->getdescription() : '';
        if ($result == '') {
 return  $this->getApp()->site->description;
}


        return $result;
    }

    public function getMenu() {
        if ($r = $this->ongetmenu()) {
            return $r;
        }

        $schema = $this->schema;
        $menuclass = $schema->menuclass;
        $filename = $schema->theme->name . sprintf('.%s.%s.php', str_replace('\\', '-', $menuclass) ,  $this->getApp()->options->group ?  $this->getApp()->options->group : 'nobody');

        if ($result =  $this->getApp()->router->cache->get($filename)) {
            return $result;
        }

        $menus = getinstance($menuclass);
        $result = $menus->getmenu($this->hover, 0);
         $this->getApp()->router->cache->set($filename, $result);
        return $result;
    }

    private function getLtoptions() {
        return sprintf('<script type="text/javascript">window.ltoptions = %s;</script>', Str::toJson($this->ltoptions));
    }

    public function getJavascript($filename) {
        return sprintf($this->js,  $this->getApp()->site->files . $filename);
    }

    public function getReady($s) {
        return sprintf($this->jsready, $s);
    }

    public function getLoadjavascript($s) {
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

    public function getHead() {
        $result = $this->heads;
        if ($this->viewImplemented) $result.= $this->model->gethead();
        $result = $this->getltoptions() . $result;
        $result.= $this->extrahead;
        $result = $this->schema->theme->parse($result);
        $this->callevent('onhead', array(&$result
        ));
        return $result;
    }

    public function getContent() {
        $result = '';
        $this->callevent('beforecontent', array(&$result
        ));
        $result.= $this->viewImplemented ? $this->model->getcont() : '';
        $this->callevent('aftercontent', array(&$result
        ));
        return $result;
    }

    protected function setFooter($s) {
        if ($s != $this->data['footer']) {
            $this->data['footer'] = $s;
            $this->Save();
        }
    }

    public function getPage() {
        $page =  $this->getApp()->router->page;
        if ($page <= 1) {
 return '';
}


        return sprintf(Lang::get('default', 'pagetitle') , $page);
    }

    public function trimwords($s, array $words) {
        if ($s == '') {
 return '';
}


        foreach ($words as $word) {
            if (Str::begin($s, $word)) $s = substr($s, strlen($word));
            if (Str::end($s, $word)) $s = substr($s, 0, strlen($s) - strlen * ($word));
        }
        return $s;
    }

} //class