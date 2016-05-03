<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\view;
use litepubl\core\Context;
use litepubl\widget\Widgets;
use litepubl\core\Str;
use litepubl\perms\Perm;

class MainView extends \litepubl\core\Events
{
use \litepubl\core\PoolStorageTrait;

public $context;
    public $custom;
    public $extrahead;
    public $extrabody;
    public $hover;
    public $ltoptions;
    public $view;
    public $path;
    public $schema;
    public $url;

    protected function create() {
$app = $this->getApp();
        //prevent recursion
$app->classes->instances[get_class($this) ] = $this;
        parent::create();
        $this->basename = 'template';
        $this->addevents('beforecontent', 'aftercontent', 'onhead', 'onbody', 'onrequest', 'ontitle', 'ongetmenu');
        $this->path =  $app->paths->themes . 'default' . DIRECTORY_SEPARATOR;
        $this->url =  $app->site->files . '/themes/default';
        $this->ltoptions = array(
            'url' =>  $app->site->url,
            'files' =>  $app->site->files,
            'idurl' =>  0,
            'lang' =>  $app->site->language,
            'theme' => [],
            'custom' => [],
        );

        $this->hover = true;
        $this->data['heads'] = '';
        $this->data['js'] = '<script type="text/javascript" src="%s"></script>';
        $this->data['jsready'] = '<script type="text/javascript">$(document).ready(function() {%s});</script>';
        $this->data['jsload'] = '<script type="text/javascript">$.load_script(%s);</script>';
        $this->data['footer'] = '<a href="http://litepublisher.com/">Powered by Lite Publisher</a>';
        $this->addmap('custom', array());
        $this->extrahead = '';
        $this->extrabody = '';
    }

    public function assignMap() {
        parent::assignMap();
        $this->ltoptions['custom'] = & $this->custom;
        $this->ltoptions['jsmerger'] = & $this->data['jsmerger'];
        $this->ltoptions['cssmerger'] = & $this->data['cssmerger'];
    }

    public function __get($name) {
        if (method_exists($this, $get = 'get' . $name)) {
return $this->$get();
} elseif (array_key_exists($name, $this->data)) {
return $this->data[$name];
} elseif (preg_match('/^sidebar(\d)$/', $name, $m)) {
            $widgets = Widgets::i();
            return $widgets->getSidebarIndex($this->view, (int)$m[1]);
        } elseif (isset($this->view) && isset($this->view->$name)) {
return $this->view->$name;
}

        return parent::__get($name);
    }

    public function render(Context $context) {
$this->context = $context;
        $this->view = $context->view;

$vars = new Vars();
$vars->view = $context->view;
$vars->template = $this;
$vars->mainview = $this;

        $this->schema = Schema::getSchema($context->view);
        $theme = $this->schema->theme;
        $this->ltoptions['theme']['name'] = $theme->name;
        $this->ltoptions['idurl'] = $context->itemRoute['id'];

$app = $this->getApp();
         $app->classes->instances[get_class($theme) ] = $theme;
        $this->path =  $app->paths->themes . $theme->name . DIRECTORY_SEPARATOR;
        $this->url =  $app->site->files . '/themes/' . $theme->name;
$this->hover = $this->getHover($this->schema);

        if (isset($context->model->idperm) && ($idperm = $context->model->idperm)) {
            $perm = Perm::i($idperm);
$perm->setResponse($context->response, $context->model);
        }

        $context->response->body .= $theme->render($context->view);
        $this->onbody($this);
        if ($this->extrabody) {
$context->response->body = str_replace('</body>', $this->extrabody . '</body>', $context->response->body);
}

        $this->onrequest($this);

$this->context = null;
$this->view = null;
$this->schema = null;
    }

protected function getHover(Schema $schema)
{
$result = false;
        if ($schema->hovermenu) {
$result = $schema->theme->templates['menu.hover'];
            if ($result != 'bootstrap') {
$result = ($result == 'true');
}
        }

return $result;
}

    //html tags
    public function getSidebar() {
        return Widgets::i()->getSidebar($this->view);
    }

    public function getTitle() {
        $title = new str($this->view->gettitle());
        if ($this->ontitle($title)) {
            return $title->value;
        } else {
            return $this->parsetitle($title, $this->schema->theme->templates['title']);
        }
    }

    public function parsetitle(Str $title, $tml) {
        $args = new Args();
        $args->title = $title->value;
        $result = $this->schema->theme->parseArg($tml, $args);
        $result = trim($result, " |.:\n\r\t");
        if (!$result) {
$result = $this->getApp()->site->name;
}

        return $result;
    }

    public function getKeywords() {
        $result = $this->view->getkeywords();
        if (!$result) {
 $result = $this->getApp()->site->keywords;
}

        return $result;
    }

    public function getDescription() {
        $result = $this->view->getdescription();
        if (!$result) {
 $result = $this->getApp()->site->description;
}

        return $result;
    }

    public function getMenu() {
        if ($r = $this->ongetmenu()) {
            return $r;
        }

$app = $this->getApp();
        $schema = $this->schema;
        $menuclass = $schema->menuclass;
        $filename = $schema->theme->name
 . sprintf('.%s.%s.php', str_replace('\\', '-', $menuclass) ,  $app->options->group ?  $app->options->group : 'nobody');

        if ($result =  $app->cache->getString($filename)) {
            return $result;
        }

        $menus = static::iGet($menuclass);
        $result = $menus->getmenu($this->hover, 0);
         $app->cache->setString($filename, $result);
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

    public function addToHead($s) {
        $s = trim($s);
        if (false === strpos($this->heads, $s)) {
            $this->heads = trim($this->heads) . "\n" . $s;
            $this->save();
        }
    }

    public function deleteFromHead($s) {
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
$result .= $this->view->gethead();
        $result = $this->getltoptions() . $result;
        $result .= $this->extrahead;
        $result = $this->schema->theme->parse($result);

$s = new Str($result);
        $this->onhead($s);
        return $s->value;
    }

    public function getContent() {
        $result = new Str('');
        $this->beforecontent($result);
        $result->value .= $this->view->getCont();
        $this->aftercontent($result);
        return $result->value;
    }

    protected function setFooter($s) {
        if ($s != $this->data['footer']) {
            $this->data['footer'] = $s;
            $this->Save();
        }
    }

    public function getPage() {
        $page =  $this->context->request->page;
        if ($page <= 1) {
 return '';
}

        return sprintf(Lang::get('default', 'pagetitle') , $page);
    }

}