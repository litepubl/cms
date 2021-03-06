<?php
//Args.php
namespace litepubl\view;

class Args
{
    use \litepubl\core\AppTrait;

    public static $defaultArgs;
    public $data;
    public $callbacks;
    public $callbackParams;

    public static function i()
    {
        return static ::getAppInstance()->classes->newInstance(get_called_class());
    }

    public function __construct($thisthis = null)
    {
        $this->callbacks = [];
        $this->callbackParams = [];
        $this->data = static ::getDefaultArgs();
        if (isset($thisthis)) {
            $this->data['$this'] = $thisthis;
        }
    }

    public static function getDefaultArgs()
    {
        if (!static ::$defaultArgs) {
            $site = static ::getAppInstance()->site;
            static ::$defaultArgs = [
                '$site.url' => $site->url,
                '$site.files' => $site->files,
                '{$site.q}' => $site->q,
                '$site.q' => $site->q,
            ];
        }

        return static ::$defaultArgs;
    }

    public function __get($name)
    {
        if (($name == 'link') && !isset($this->data['$link']) && isset($this->data['$url'])) {
            return $this->getApp()->site->url . $this->data['$url'];
        }

        return $this->data['$' . $name];
    }

    public function __set($name, $value)
    {
        if (!$name || !is_string($name)) {
            return;
        }

        if (is_array($value)) {
            return;
        }

        if (!is_string($value) && is_callable($value)) {
            $this->callbacks['$' . $name] = $value;
            return;
        }

        if (is_bool($value)) {
            $value = $value ? 'checked="checked"' : '';
        }

        $this->data['$' . $name] = $value;
        $this->data["%%$name%%"] = $value;

        if (($name == 'url') && !isset($this->data['$link'])) {
            $this->data['$link'] = $this->getApp()->site->url . $value;
            $this->data['%%link%%'] = $this->getApp()->site->url . $value;
        }
    }

    public function add(array $a)
    {
        foreach ($a as $k => $v) {
            $this->__set($k, $v);
            if ($k == 'url') {
                $this->data['$link'] = $this->getApp()->site->url . $v;
                $this->data['%%link%%'] = $this->getApp()->site->url . $v;
            }
        }

        if (isset($a['title']) && !isset($a['text'])) {
            $this->__set('text', $a['title']);
        }

        if (isset($a['text']) && !isset($a['title'])) {
            $this->__set('title', $a['text']);
        }
    }

    public function parse($s)
    {
        return Theme::i()->parseArg($s, $this);
    }

    public function callback($s)
    {
        if (!count($this->callbacks)) {
            return $s;
        }

        $params = $this->callbackParams;
        array_unshift($params, $this);

        foreach ($this->callbacks as $tag => $callback) {
            $s = str_replace($tag, call_user_func_array($callback, $params), $s);
        }

        return $s;
    }
}

//AutoVars.php
namespace litepubl\view;

class AutoVars extends \litepubl\core\Items
{
    use \litepubl\core\PoolStorageTrait;

    public $defaults;

    public function create()
    {
        $this->dbversion = false;
        parent::create();
        $this->basename = 'autovars';
        $this->defaults = [
        'post' => '\litepubl\post\View',
        'files' => '\litepubl\post\FileView',
        'archives' => '\litepubl\post\Archives',
        'categories' => '\litepubl\tag\Cats',
        'cats' => '\litepubl\tag\Cats',
        'tags' => '\litepubl\tag\Tags',
        'home' => '\litepubl\pages\Home',
        'template' => 'litepubl\view\MainView',
        'comments' => 'litepubl\comments\Comments',
        'menu' => 'litepubl\pages\Menu',
        ];
    }

    public function get($name)
    {
        if (isset($this->items[$name])) {
            $result = $this->app->classes->getInstance($this->items[$name]);
        } elseif (isset($this->defaults[$name])) {
            $result = $this->app->classes->getInstance($this->defaults[$name]);
        } else {
            return false;
        }

        if ($result instanceof ViewInterface) {
                return $result;
        } elseif (isset($result->view)) {
                return $result->view;
        } else {
                return $result;
        }
    }

    public function add(string $name, string $class)
    {
        $this->items[$name] = $class;
        $this->save();
    }
}

//Base.php
namespace litepubl\view;

use litepubl\core\Str;
use litepubl\post\Post;
use litepubl\post\View as PostView;
use litepubl\utils\Filer;

class Base extends \litepubl\core\Events
{
    public static $instances = [];
    public static $vars = [];

    public $name;
    public $parsing;
    public $templates;
    public $extratml;

    public static function exists($name)
    {
        return file_exists(static ::getAppInstance()->paths->themes . $name . '/about.ini');
    }

    public static function getTheme($name)
    {
        return static ::getByName(get_called_class(), $name);
    }

    public static function getByName($classname, $name)
    {
        if (isset(static ::$instances[$name])) {
            return static ::$instances[$name];
        }

        $result = static ::iGet($classname);
        if ($result->name) {
            $result = static ::getAppInstance()->classes->newinstance($classname);
        }

        $result->name = $name;
        $result->load();
        return $result;
    }

    protected function create()
    {
        parent::create();
        $this->name = '';
        $this->parsing = [];
        $this->data['type'] = 'litepublisher';
        $this->data['parent'] = '';
        $this->addmap('templates', []);
        $this->templates = [];

        $this->extratml = '';
    }

    public function __destruct()
    {
        unset(static ::$instances[$this->name], $this->templates);
        parent::__destruct();
    }

    public function getBasename()
    {
        return 'themes/' . $this->name;
    }

    public function getParser(): BaseParser
    {
        return BaseParser::i();
    }

    public function load()
    {
        if (!$this->name) {
            return false;
        }

        if (parent::load()) {
            static ::$instances[$this->name] = $this;
            return true;
        }

        return $this->parsetheme();
    }

    public function parsetheme()
    {
        if (!static ::exists($this->name)) {
            $this->error(sprintf('The %s theme not exists', $this->name));
        }

        $parser = $this->getparser();
        if ($parser->parse($this)) {
            static ::$instances[$this->name] = $this;
        } else {
            $this->error(sprintf('Theme file %s not exists', $filename));
        }
    }

    protected function setProp(string $name, $value)
    {
        if (array_key_exists($name, $this->templates)) {
            $this->templates[$name] = $value;
        } else {
            parent::setProp($name, $value);
        }
    }

    public function reg($exp)
    {
        if (!strpos($exp, '\.')) {
            $exp = str_replace('.', '\.', $exp);
        }
        $result = [];
        foreach ($this->templates as $name => $val) {
            if (preg_match($exp, $name)) {
                $result[$name] = $val;
            }
        }
        return $result;
    }

    protected function getVar($name)
    {
        switch ($name) {
            case 'site':
                return $this->getApp()->site;

            case 'lang':
                return Lang::i();

            case 'post':
                if ($context = $this->getApp()->context) {
                    if (isset($context->view) and $context->view instanceof PostView) {
                        return $context->view;
                    } elseif (isset($context->model) && $context->model instanceof Post) {
                        return $context->model->getView();
                    }
                }
                break;


            case 'author':
                return static ::get_author();

            case 'metapost':
                return isset(static ::$vars['post']) ? static ::$vars['post']->meta : new emptyclass();
        } //switch
        $var = AutoVars::i()->get($name);
        if (!is_object($var)) {
            $this->app->getLogger()->warning(sprintf('Object "%s" not found in %s', $name, $this->parsing[count($this->parsing) - 1]));
            return false;
        }

        return $var;
    }

    public function parsecallback($names)
    {
        $name = $names[1];
        $prop = $names[2];
        //$this->getApp()->getLogger()->debug("$name.$prop");
        if (isset(static ::$vars[$name])) {
            $var = static ::$vars[$name];
        } elseif ($name == 'custom') {
            return $this->parse($this->templates['custom'][$prop]);
        } elseif ($name == 'label') {
            return "\$$name.$prop";
        } elseif ($var = $this->getvar($name)) {
            static ::$vars[$name] = $var;
        } elseif (($name == 'metapost') && isset(static ::$vars['post'])) {
            $var = static ::$vars['post']->meta;
        } else {
            return '';
        }

        try {
            return $var->{$prop};
        } catch (\Exception $e) {
            $this->getApp()->logException($e);
        }
        return '';
    }

    public function parse(string $s): string
    {
        if (!$s) {
            return '';
        }

        $s = strtr((string)$s, Args::getDefaultArgs());
        if (isset($this->templates['content.admin.tableclass'])) {
            $s = str_replace('$tableclass', $this->templates['content.admin.tableclass'], $s);
        }
        array_push($this->parsing, $s);
        try {
            $s = preg_replace('/%%([a-zA-Z0-9]*+)_(\w\w*+)%%/', '\$$1.$2', $s);
            $result = preg_replace_callback('/\$([a-zA-Z]\w*+)\.(\w\w*+)/', [$this, 'parsecallback'], $s);
        } catch (\Exception $e) {
            $result = '';
            $this->getApp()->logException($e);
        }
        array_pop($this->parsing);
        return $result;
    }

    public function parseArg(string $s, Args $args): string
    {
        $s = $this->parse($s);
        $s = $args->callback($s);
        return strtr($s, $args->data);
    }

    public function replaceLang($s, $lang)
    {
        $s = preg_replace('/%%([a-zA-Z0-9]*+)_(\w\w*+)%%/', '\$$1.$2', (string)$s);
        static ::$vars['lang'] = isset($lang) ? $lang : Lang::i('default');
        $s = strtr($s, Args::getDefaultArgs());
        if (preg_match_all('/\$lang\.(\w\w*+)/', $s, $m, PREG_SET_ORDER)) {
            foreach ($m as $item) {
                $name = $item[1];
                if ($v = $lang->{$name}) {
                    $s = str_replace($item[0], $v, $s);
                }
            }
        }
        return $s;
    }

    public static function parseVar($name, $var, $s)
    {
        static ::$vars[$name] = $var;
        return static ::i()->parse($s);
    }

    public static function clearcache()
    {
        $app = static ::getAppInstance();
        Filer::delete($app->paths->data . 'themes', false, false);
        $app->cache->clear();
    }

    public function h($s)
    {
        return sprintf('<h4>%s</h4>', $s);
    }

    public function link($url, $title)
    {
        return sprintf('<a href="%s%s">%s</a>', Str::begin($url, 'http') ? '' : $this->getApp()->site->url, $url, $title);
    }

    public static function quote($s)
    {
        return strtr(
            $s,
            [
            '"' => '&quot;',
            "'" => '&#039;',
            '\\' => '&#092;',
            '$' => '&#36;',
            '%' => '&#37;',
            '_' => '&#95;',
            '<' => '&lt;',
            '>' => '&gt;',
            ]
        );
    }
}

//DateFormater.php
namespace litepubl\view;

class DateFormater
{
    public $date;

    public function __construct($date)
    {
        $this->date = $date;
    }

    public function __get($name)
    {
        return Lang::translate(date($name, $this->date), 'datetime');
    }
}

//EmptyClass.php
namespace litepubl\view;

class EmptyClass
{
    public function __get($name)
    {
        return '';
    }
}

//EmptyViewTrait.php
namespace litepubl\view;

use litepubl\core\Context;

trait EmptyViewTrait
{

    protected function createData()
    {
        parent::createData();
        $this->data['idschema'] = 1;
    }

    public function request(Context $context)
    {
    }

    public function getHead(): string
    {
        return '';
    }

    public function getKeywords(): string
    {
        return '';
    }

    public function getDescription(): string
    {
        return '';
    }

    public function getIdSchema(): int
    {
        return $this->data['idschema'];
    }

    public function setIdSchema(int $id)
    {
        if ($id != $this->IdSchema) {
            $this->data['idschema'] = $id;
            $this->save();
        }
    }

    public function getSchema(): Schema
    {
        return Schema::getSchema($this);
    }

    public function getView(): ViewInterface
    {
        return $this;
    }
}

//Factory.php
namespace litepubl\view;

trait Factory
{

    public function newArgs(): Args
    {
        return new Args();
    }

    public function newVars(): Vars
    {
        return new Vars();
    }

    public function getLang(string $section = ''): Lang
    {
        return Lang::i($section);
    }

    public function getSchema(): Schema
    {
        return Schema::getSchema($this);
    }

    public function getTheme(): Theme
    {
        return $this->getSchema()->theme;
    }
}

//Lang.php
namespace litepubl\view;

class Lang
{
    use \litepubl\core\AppTrait;

    const ZERODATE = '0000-00-00 00:00:00';
    const DATEFORMAT = 'd F Y';
    const DATETIMEFORMAT = 'd F Y H:i';

    public static $self;
    public $loaded;
    public $ini;
    public $section;
    public $searchsect;

    public static function i(string $section = '')
    {
        if (!isset(static ::$self)) {
            static ::$self = static ::getInstance();
            static ::$self->loadfile('default');
        }

        if ($section != '') {
            static ::$self->section = $section;
        }
        return static ::$self;
    }

    public static function getInstance()
    {
        return static ::getAppInstance()->classes->getInstance(get_called_class());
    }

    public static function admin(string $section = '')
    {
        $result = static ::i($section);
        $result->check('admin');
        return $result;
    }

    public function __construct()
    {
        $this->ini = [];
        $this->loaded = [];
        $this->searchsect = [
            'common',
            'default'
        ];
    }

    public static function get(string $section, string $key): string
    {
        return static ::i()->ini[$section][$key];
    }

    public function __get($name)
    {
        if (isset($this->ini[$this->section][$name])) {
            return $this->ini[$this->section][$name];
        }

        foreach ($this->searchsect as $section) {
            if (isset($this->ini[$section][$name])) {
                return $this->ini[$section][$name];
            }
        }
        return '';
    }

    public function __isset($name)
    {
        if (isset($this->ini[$this->section][$name])) {
            return true;
        }

        foreach ($this->searchsect as $section) {
            if (isset($this->ini[$section][$name])) {
                return true;
            }
        }

        return false;
    }

    public function __call($name, $args)
    {
        return strtr($this->__get($name), $args->data);
    }

    public function addSearch()
    {
        $this->joinSearch(func_get_args());
    }

    public function joinSearch(array $a)
    {
        foreach ($a as $sect) {
            $sect = trim(trim($sect), "\"',;:.");
            if (!in_array($sect, $this->searchsect)) {
                $this->searchsect[] = $sect;
            }
        }
    }

    public function firstSearch()
    {
        $a = array_reverse(func_get_args());
        foreach ($a as $sect) {
            $i = array_search($sect, $this->searchsect);
            if ($i !== false) {
                array_splice($this->searchsect, $i, 1);
            }
            array_unshift($this->searchsect, $sect);
        }
    }

    public function translate(string $s, string $section = 'default'): string
    {
        return strtr($s, $this->ini[$section]);
    }

    public static function date($date, $format = ''): string
    {
        $self = static ::i();
        if (empty($format)) {
            $format = $self->getDateFormat();
        }

        return $self->translate(date($format, $date), 'datetime');
    }

    public function getDateFormat(): string
    {
        $format = $this->getApp()->options->dateformat;
        return $format ? $format : $this->ini['datetime']['dateformat'];
    }

    public function getDate($date): string
    {
        if ($date == static::ZERODATE) {
            return $this->noword;
        } else {
            return $this->translate(date(static::DATEFORMAT, strtotime($date)), 'datetime');
        }
    }

    public function getDateTime($date): string
    {
        if ($date == Lang::ZERODATE) {
            return $this->noword;
        } else {
            return $this->translate(date(static::DATETIMEFORMAT, strtotime($date)), 'datetime');
        }
    }

    public function check(string $name)
    {
        if (!$name) {
            $name = 'default';
        }

        if (!in_array($name, $this->loaded)) {
            $this->loadFile($name);
        }
    }

    public function loadFile(string $name)
    {
        $this->loaded[] = $name;
        $filename = static ::getcachedir() . $name;
        if (($data = $this->getApp()->storage->loaddata($filename)) && is_array($data)) {
            $this->ini = $data + $this->ini;
            if (isset($data['searchsect'])) {
                $this->joinsearch($data['searchsect']);
            }
        } else {
            $merger = LangMerger::i();
            $merger->parse($name);
        }
    }

    public static function useFile(string $name)
    {
        static ::i()->check($name);
        return static ::$self;
    }

    public static function getCacheDir(): string
    {
        return static ::getAppInstance()->paths->data . 'languages' . DIRECTORY_SEPARATOR;
    }

    public static function clearcache()
    {
        \litepubl\utils\Filer::delete(static ::getcachedir(), false, false);
        static ::i()->loaded = [];
    }
}

//MainView.php
namespace litepubl\view;

use litepubl\Config;
use litepubl\core\Context;
use litepubl\core\Str;
use litepubl\perms\Perm;
use litepubl\widget\Widgets;

/**
 * Base class for page render
 *
 * @property       string $heads
 * @property       string $footer
 * @property       string $js
 * @property       string $jsready
 * @property       string $jsload
 * @property-write callable $beforeContent
 * @property-write callable $afterContent
 * @property-write callable $onHead
 * @property-write callable $onBody
 * @property-write callable $onRequest
 * @property-write callable $onTitle
 * @property-write callable $onGetMenu
 * @method         array beforeContent(array $params)
 * @method         array afterContent(array $params)
 * @method         array onHead(array $params)
 * @method         array onBody(array $params)
 * @method         array onRequest(array $params)
 * @method         array onTitle(array $params)
 * @method         array onGetMenu(array $params)
 */

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

    protected function create()
    {
        $app = $this->getApp();
        //prevent recursion
        $app->classes->instances[get_class($this) ] = $this;
        parent::create();
        $this->basename = 'template';
        $this->addEvents('beforecontent', 'aftercontent', 'onhead', 'onbody', 'onrequest', 'ontitle', 'ongetmenu');
        $this->path = $app->paths->themes . 'default' . DIRECTORY_SEPARATOR;
        $this->url = $app->site->files . '/themes/default';
        $this->ltoptions = [
            'url' => $app->site->url,
            'files' => $app->site->files,
            'idurl' => 0,
            'lang' => $app->site->language,
            'debug' => Config::$debug,
            'theme' => [],
            'custom' => [],
        ];

        $this->hover = true;
        $this->data['heads'] = '';
        $this->data['js'] = '<script type="text/javascript" src="%s"></script>';
        $this->data['jsready'] = '<script type="text/javascript">$(document).ready(function() {%s});</script>';
        $this->data['jsload'] = '<script type="text/javascript">$.load_script(%s);</script>';
        $this->data['footer'] = '<a href="http://litepublisher.com/">Powered by Lite Publisher</a>';
        $this->addmap('custom', []);
        $this->extrahead = '';
        $this->extrabody = '';
    }

    public function assignMap()
    {
        parent::assignMap();
        $this->ltoptions['custom'] = & $this->custom;
        $this->ltoptions['jsmerger'] = & $this->data['jsmerger'];
        $this->ltoptions['cssmerger'] = & $this->data['cssmerger'];
    }

    public function __get($name)
    {
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

    public function render(Context $context)
    {
        $this->context = $context;
        $this->view = $context->view;

        $vars = new Vars();
        $vars->view = $context->view;
        $vars->template = $this;
        $vars->mainview = $this;

        $this->schema = Schema::getSchema($context->view);
        $theme = $this->schema->theme;
        $this->ltoptions['theme']['name'] = $theme->name;
        $this->ltoptions['idurl'] = $context->itemRoute['id'] ?? 0;

        $app = $this->getApp();
        $app->classes->instances[get_class($theme) ] = $theme;
        $this->path = $app->paths->themes . $theme->name . DIRECTORY_SEPARATOR;
        $this->url = $app->site->files . '/themes/' . $theme->name;
        $this->hover = $this->getHover($this->schema);

        if (isset($context->model->idperm) && ($idperm = $context->model->idperm)) {
            $perm = Perm::i($idperm);
            $perm->setResponse($context->response, $context->model);
        }

        $context->response->body.= $theme->render($context->view);
        $this->onbody([]);
        if ($this->extrabody) {
            $context->response->body = str_replace('</body>', $this->extrabody . '</body>', $context->response->body);
        }

        $this->onrequest([]);

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
    public function getSidebar()
    {
        return Widgets::i()->getSidebar($this->view);
    }

    public function getTitle(): string
    {
        $title = $this->view->gettitle();
        $a =        $this->ontitle(['title' => $title, 'return' => false]);
        if ($a['return']) {
            return $a['title'];
        } else {
            return $this->parsetitle($a['title'], $this->schema->theme->templates['title']);
        }
    }

    public function parseTitle(string $title, string $tml): string
    {
        $args = new Args();
        $args->title = $title;
        $result = $this->schema->theme->parseArg($tml, $args);
        $result = trim($result, " |.:\n\r\t");
        if (!$result) {
            $result = $this->getApp()->site->name;
        }

        return $result;
    }

    public function getKeywords()
    {
        $result = $this->view->getkeywords();
        if (!$result) {
            $result = $this->getApp()->site->keywords;
        }

        return $result;
    }

    public function getDescription()
    {
        $result = $this->view->getdescription();
        if (!$result) {
            $result = $this->getApp()->site->description;
        }

        return $result;
    }

    public function getMenu(): string
    {
        $r = $this->onGetMenu(['content' => '', 'cancel' => false]);
        if ($r['cancel']) {
            return $r['content'];
        }

        $app = $this->getApp();
        $schema = $this->schema;
        $menuclass = $schema->menuclass;
        $filename = $schema->theme->name . sprintf('.%s.%s.php', str_replace('\\', '-', $menuclass), $app->options->group ? $app->options->group : 'nobody');

        if ($result = $app->cache->getString($filename)) {
            return $result;
        }

        $menus = static ::iGet($menuclass);
        $result = $menus->getmenu($this->hover, 0);
        $app->cache->setString($filename, $result);
        return $result;
    }

    private function getLtoptions()
    {
        return sprintf('<script type="text/javascript">window.ltoptions = %s;</script>', Str::toJson($this->ltoptions));
    }

    public function getJavascript($filename)
    {
        return sprintf($this->js, $this->getApp()->site->files . $filename);
    }

    public function getReady($s)
    {
        return sprintf($this->jsready, $s);
    }

    public function getLoadjavascript($s)
    {
        return sprintf($this->jsload, $s);
    }

    public function addToHead($s)
    {
        $s = trim($s);
        if (false === strpos($this->heads, $s)) {
            $this->heads = trim($this->heads) . "\n" . $s;
            $this->save();
        }
    }

    public function deleteFromHead($s)
    {
        $s = trim($s);
        $i = strpos($this->heads, $s);
        if (false !== $i) {
            $this->heads = substr_replace($this->heads, '', $i, strlen($s));
            $this->heads = trim(str_replace("\n\n", "\n", $this->heads));
            $this->save();
        }
    }

    public function getHead()
    {
        $result = $this->heads;
        $result.= $this->view->gethead();
        $result = $this->getLtoptions() . $result;
        $result.= $this->extrahead;
        $result = $this->schema->theme->parse($result);

        $a = $this->onhead(['head' => $result]);
        return $a['head'];
    }

    public function getContent(): string
    {
        $result = $this->beforecontent(['content' => '']);
        $result['content'] .= $this->view->getCont();
        $result = $this->aftercontent($result);
        return $result['content'];
    }

    protected function setFooter($s)
    {
        if ($s != $this->data['footer']) {
            $this->data['footer'] = $s;
            $this->Save();
        }
    }

    public function getPage()
    {
        $page = $this->context->request->page;
        if ($page <= 1) {
            return '';
        }

        return sprintf(Lang::get('default', 'pagetitle'), $page);
    }
}

//Schema.php
namespace litepubl\view;

use litepubl\core\Str;

/**
 * Schema class
 *
 * @property      int $id
 * @property      string $class
 * @property      string $name
 * @property      string $themename
 * @property      string $adminname
 * @property      string $menuclass
 * @property      bool $hovermenu
 * @property      bool $customsidebar
 * @property      bool $disableajax
 * @property      string $postannounce
 * @property      bool $invertorder
 * @property      int $perpage
 * @property      array $custom
 * @property-read Theme $theme
 * @property-read Admin $adminTheme
 */

class Schema extends \litepubl\core\Item
{
    use \litepubl\core\ItemOwnerTrait;

    public $sidebars;
    protected $themeInstance;
    protected $adminInstance;
    private $originalCustom;

    public static function i($id = 1)
    {
        if ($id == 1) {
            $class = get_called_class();
        } else {
            $schemes = Schemes::i();
            $class = $schemes->itemExists($id) ? $schemes->items[$id]['class'] : get_called_class();
        }

        return parent::iteminstance($class, $id);
    }

    public static function newItem($id)
    {
        return static ::getAppInstance()->classes->newItem(static ::getinstancename(), get_called_class(), $id);
    }

    public static function getInstancename()
    {
        return 'schema';
    }

    public static function getSchema($instance): Schema
    {
        $id = $instance->getIdSchema();
        if (isset(static ::$instances['schema'][$id])) {
            return static ::$instances['schema'][$id];
        }

        $schemes = Schemes::i();
        if (!$schemes->itemExists($id)) {
            //1 default, wich always exists
            $id = 1;
            $instance->setIdSchema($id);
        }

        return static ::i($id);
    }

    protected function create()
    {
        parent::create();
        $this->originalCustom = [];
        $this->data = [
            'id' => 0,
            'class' => get_class($this) ,
            'name' => 'default',
            'themename' => 'default',
            'adminname' => 'admin',
            'menuclass' => 'litepubl\pages\Menus',
            'hovermenu' => true,
            'customsidebar' => false,
            'disableajax' => false,
            //possible values: default, lite, card
            'postannounce' => 'excerpt',
            'invertorder' => false,
            'perpage' => 0,

            'custom' => [] ,
            'sidebars' => []
        ];

        $this->sidebars = & $this->data['sidebars'];
        $this->themeInstance = null;
        $this->adminInstance = null;
    }

    public function __destruct()
    {
        $this->themeInstance = null;
        $this->adminInstance = null;
        parent::__destruct();
    }

    public function getOwner()
    {
        return Schemes::i();
    }

    public function afterLoad()
    {
        parent::afterLoad();
        $this->sidebars = & $this->data['sidebars'];
    }

    protected function get_theme($name)
    {
        return Theme::getTheme($name);
    }

    protected function get_admintheme($name)
    {
        return Admin::getTheme($name);
    }

    public function setThemeName(string $name)
    {
        if ($name == $this->themename) {
            return false;
        }

        if (Str::begin($name, 'admin')) {
            $this->error('The theme name cant begin with admin keyword');
        }
        if (!Theme::exists($name)) {
            return $this->error(sprintf('Theme %s not exists', $name));
        }

        $this->data['themename'] = $name;
        $this->themeInstance = $this->get_theme($name);
        $this->originalCustom = $this->themeInstance->templates['custom'];
        $this->data['custom'] = $this->originalCustom;
        $this->save();

        static ::getOwner()->themechanged(['schema' => $this]);
    }

    public function setAdminName(string $name)
    {
        if ($name != $this->adminname) {
            if (!Str::begin($name, 'admin')) {
                $this->error('Admin theme name dont start with admin keyword');
            }
            if (!Admin::exists($name)) {
                return $this->error(sprintf('Admin theme %s not exists', $name));
            }

            $this->data['adminname'] = $name;
            $this->adminInstance = $this->get_admintheme($name);
            $this->save();
        }
    }

    public function getTheme(): Theme
    {
        if ($this->themeInstance) {
            return $this->themeInstance;
        }

        if (Theme::exists($this->themename)) {
            $this->themeInstance = $this->get_theme($this->themename);
            $this->originalCustom = $this->themeInstance->templates['custom'];

            //aray_equal
            if ((count($this->data['custom']) == count($this->originalCustom)) && !count(array_diff(array_keys($this->data['custom']), array_keys($this->originalCustom)))) {
                $this->themeInstance->templates['custom'] = $this->data['custom'];
            } else {
                $this->data['custom'] = $this->originalCustom;
                $this->save();
            }
        } else {
            $this->setthemename('default');
        }
        return $this->themeInstance;
    }

    public function getAdmintheme(): Admin
    {
        if ($this->adminInstance) {
            return $this->adminInstance;
        }

        if (!Admin::exists($this->adminname)) {
            $this->setAdminName('admin');
        }

        return $this->adminInstance = $this->get_admintheme($this->adminname);
    }

    public function resetCustom()
    {
        $this->data['custom'] = $this->originalCustom;
    }

    public function setCustomsidebar($value)
    {
        if ($value != $this->customsidebar) {
            if ($this->id == 1) {
                return false;
            }

            if ($value) {
                $default = static ::i(1);
                $this->sidebars = $default->sidebars;
            } else {
                $this->sidebars = [];
            }
            $this->data['customsidebar'] = $value;
            $this->save();
        }
    }
}

//Schemes.php
namespace litepubl\view;

use litepubl\core\Arr;
use litepubl\core\Event;

/**
 * Common class for join files
 *
 * @property-write callable $themeChanged
 * @method         array themeChanged(array $params)
 */

class Schemes extends \litepubl\core\Items
{
    use \litepubl\core\PoolStorageTrait;

    public $defaults;

    protected function create()
    {
        $this->dbversion = false;
        parent::create();
        $this->basename = 'views';
        $this->addEvents('themechanged');
        $this->addmap('defaults', []);
    }

    public function add(string $name)
    {
        $this->lock();
        $id = ++$this->autoid;
        $schema = Schema::newItem($id);
        $schema->id = $id;
        $schema->name = $name;
        $schema->data['class'] = get_class($schema);
        $this->items[$id] = & $schema->data;
        $this->unlock();
        return $id;
    }

    public function addSchema(Schema $schema)
    {
        $this->lock();
        $id = ++$this->autoid;
        $schema->id = $id;
        if (!$schema->name) {
            $schema->name = 'schema_' . $id;
        }

        $schema->data['class'] = get_class($schema);
        $this->items[$id] = & $schema->data;
        $this->unlock();
        return $id;
    }

    public function delete($id)
    {
        if ($id == 1) {
            return $this->error('You cant delete default view');
        }

        foreach ($this->defaults as $name => $iddefault) {
            if ($id == $iddefault) {
                $this->defaults[$name] = 1;
            }
        }

        return parent::delete($id);
    }

    public function get(string $name)
    {
        foreach ($this->items as $id => $item) {
            if ($name == $item['name']) {
                return Schema::i($id);
            }
        }

        return false;
    }

    public function resetCustom()
    {
        foreach ($this->items as $id => $item) {
            $schema = Schema::i($id);
            $schema->resetCustom();
            $this->save();
        }
    }

    public function widgetDeleted(Event $event)
    {
        $idwidget = $event->id;
        $deleted = false;
        foreach ($this->items as & $schemaitem) {
            unset($sidebar);
            foreach ($schemaitem['sidebars'] as & $sidebar) {
                for ($i = count($sidebar) - 1; $i >= 0; $i--) {
                    if ($idwidget == $sidebar[$i]['id']) {
                        Arr::delete($sidebar, $i);
                        $deleted = true;
                    }
                }
            }
        }
        if ($deleted) {
            $this->save();
        }
    }
}

//Theme.php
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

//Vars.php
namespace litepubl\view;

class Vars
{
    public $keys = [];

    public function __destruct()
    {
        foreach ($this->keys as $name) {
            if (isset(Base::$vars[$name])) {
                unset(Base::$vars[$name]);
            }
        }
    }

    public function __get($name)
    {
        return Base::$vars[$name];
    }

    public function __set($name, $value)
    {
        Base::$vars[$name] = $value;

        if (!in_array($name, $this->keys)) {
            $this->keys[] = $name;
        }
    }

    public function __isset($name)
    {
        return isset(Base::$vars[$name]);
    }

    public function __unset($name)
    {
        unset(Base::$vars[$name]);
    }
}

//ViewInterface.php
namespace litepubl\view;

interface ViewInterface extends \litepubl\core\ResponsiveInterface
{
    public function getTitle(): string;
    public function getKeywords(): string;
    public function getDescription(): string;
    public function getHead(): string;
    public function getCont(): string;
    public function getIdSchema(): int;
    public function setIdSchema(int $id);
}

//ViewTrait.php
namespace litepubl\view;

use litepubl\core\Context;

trait ViewTrait
{

    protected function createData()
    {
        parent::createData();
        $this->data['idschema'] = 1;
        $this->data['keywords'] = '';
        $this->data['description'] = '';
        $this->data['head'] = '';
    }

    public function request(Context $context)
    {
    }

    public function getHead(): string
    {
        return $this->data['head'];
    }

    public function getKeywords(): string
    {
        return $this->data['keywords'];
    }

    public function getDescription(): string
    {
        return $this->data['description'];
    }

    public function getTitle(): string
    {
        return isset($this->data['title']) ? $this->data['title'] : '';
    }

    public function getCont(): string
    {
        return '';
    }

    public function getIdSchema(): int
    {
        return $this->data['idschema'];
    }

    public function setIdSchema(int $id)
    {
        if ($id != $this->data['idschema']) {
            $this->data['idschema'] = $id;
            $this->save();
        }
    }

    public function getSchema(): Schema
    {
        return Schema::getSchema($this);
    }

    public function getView(): ViewInterface
    {
        return $this;
    }
}

//Admin.php
namespace litepubl\view;

use litepubl\admin\DateFilter;
use litepubl\core\Arr;
use litepubl\core\Str;
use litepubl\tag\Cats;

class Admin extends Base
{

    public static function i()
    {
        $result = static ::iGet(get_called_class());
        if (!$result->name) {
            $app = static ::getAppInstance();
            if ($app->context && $app->context->view && isset($app->context->view->idschema)) {
                $result->name = Schema::getSchema($app->context->view)->adminname;
                $result->load();
            }
        }

        return $result;
    }

    public static function admin(): Admin
    {
        return Schema::i(Schemes::i()->defaults['admin'])->admintheme;
    }

    public function getParser(): BaseParser
    {
        return AdminParser::i();
    }

    public function shortCode(string $s, Args $args): string
    {
        $result = trim($s);
        //replace [tabpanel=name{content}]
        if (preg_match_all('/\[tabpanel=(\w*+)\{(.*?)\}\]/ims', $result, $m, PREG_SET_ORDER)) {
            foreach ($m as $item) {
                $name = $item[1];
                $replace = strtr(
                    $this->templates['tabs.panel'],
                    [
                    '$id' => $name,
                    '$content' => trim($item[2]) ,
                    ]
                );

                $result = str_replace($item[0], $replace, $result);
            }
        }

        if (preg_match_all('/\[(editor|text|email|password|upload|checkbox|combo|hidden|submit|button|calendar|tab|ajaxtab|tabpanel)[:=](\w*+)\]/i', $result, $m, PREG_SET_ORDER)) {
            $theme = Theme::i();
            $lang = lang::i();

            foreach ($m as $item) {
                $type = $item[1];
                $name = $item[2];
                $varname = '$' . $name;

                switch ($type) {
                    case 'editor':
                    case 'text':
                    case 'email':
                    case 'password':
                        if (isset($args->data[$varname])) {
                            $args->data[$varname] = static ::quote($args->data[$varname]);
                        } else {
                            $args->data[$varname] = '';
                        }

                            $replace = strtr(
                                $theme->templates["content.admin.$type"],
                                [
                                '$name' => $name,
                                '$value' => $varname
                                ]
                            );
                        break;


                    case 'calendar':
                        $replace = $this->getcalendar($name, $args->data[$varname]);
                        break;


                    case 'tab':
                        $replace = strtr(
                            $this->templates['tabs.tab'],
                            [
                            '$id' => $name,
                            '$title' => $lang->__get($name) ,
                            '$url' => '',
                            ]
                        );
                        break;


                    case 'ajaxtab':
                        $replace = strtr(
                            $this->templates['tabs.tab'],
                            [
                            '$id' => $name,
                            '$title' => $lang->__get($name) ,
                            '$url' => "\$ajax=$name",
                            ]
                        );
                        break;


                    case 'tabpanel':
                        $replace = strtr(
                            $this->templates['tabs.panel'],
                            [
                            '$id' => $name,
                            '$content' => isset($args->data[$varname]) ? $varname : '',
                            ]
                        );
                        break;


                    default:
                        $replace = strtr(
                            $theme->templates["content.admin.$type"],
                            [
                            '$name' => $name,
                            '$value' => $varname
                            ]
                        );
                }

                $result = str_replace($item[0], $replace, $result);
            }
        }

        return $result;
    }

    public function parseArg(string $s, Args $args): string
    {
        $result = $this->shortcode($s, $args);
        $result = strtr($result, $args->data);
        $result = $args->callback($result);
        return $this->parse($result);
    }

    public function form(string $tml, Args $args): string
    {
        return $this->parseArg(str_replace('$items', $tml, Theme::i()->templates['content.admin.form']), $args);
    }

    public function getTable(string $head, string $body, string $footer = ''): string
    {
        return strtr(
            $this->templates['table'],
            [
            '$class' => Theme::i()->templates['content.admin.tableclass'],
            '$head' => $head,
            '$body' => $body,
            '$footer' => $footer,
            ]
        );
    }

    public function success(string $text): string
    {
        return str_replace('$text', $text, $this->templates['success']);
    }

    public function getCount(int $from, int $to, int $count): string
    {
        return $this->h(sprintf(Lang::i()->itemscount, $from, $to, $count));
    }

    public function getIcon(string $name, $screenreader = false): string
    {
        return str_replace('$name', $name, $this->templates['icon'])
        . ($screenreader ? str_replace('$text', $screenreader, $this->templates['screenreader']) : '');
    }

    public function getSection(string $title, string $content): string
    {
        return strtr(
            $this->templates['section'],
            [
            '$title' => $title,
            '$content' => $content
            ]
        );
    }

    public function getErr(string $content): string
    {
        return strtr(
            $this->templates['error'],
            [
            '$title' => Lang::get('default', 'error') ,
            '$content' => $content
            ]
        );
    }

    public function help(string $content): string
    {
        return str_replace('$content', $content, $this->templates['help']);
    }

    public function getCalendar(string $name, $date): string
    {
        $date = DateFilter::timestamp($date);

        $args = new Args();
        $args->name = $name;
        $args->title = Lang::i()->__get($name);
        $args->format = DateFilter::$format;

        if ($date) {
            $args->date = date(DateFilter::$format, $date);
            $args->time = date(DateFilter::$timeformat, $date);
        } else {
            $args->date = '';
            $args->time = '';
        }

        return $this->parseArg($this->templates['calendar'], $args);
    }

    public function getDaterange($from, $to): string
    {
        $from = DateFilter::timestamp($from);
        $to = DateFilter::timestamp($to);

        $args = new Args();
        $args->from = $from ? date(DateFilter::$format, $from) : '';
        $args->to = $to ? date(DateFilter::$format, $to) : '';
        $args->format = DateFilter::$format;

        return $this->parseArg($this->templates['daterange'], $args);
    }

    public function getCats(array $items): string
    {
        Lang::i()->addsearch('editor');
        $result = $this->parse($this->templates['posteditor.categories.head']);
        Cats::i()->loadall();
        $result.= $this->getSubCats(0, $items);
        return $result;
    }

    protected function getSubCats(int $parent, array $items, $exclude = false): string
    {
        $result = '';
        $args = new Args();
        $tml = $this->templates['posteditor.categories.item'];
        $categories = Cats::i();
        foreach ($categories->items as $id => $item) {
            if (($parent == $item['parent']) && !($exclude && in_array($id, $exclude))) {
                $args->add($item);
                $args->checked = in_array($item['id'], $items);
                $args->subcount = '';
                $args->subitems = $this->getSubCats($id, $items, $exclude);
                $result.= $this->parseArg($tml, $args);
            }
        }

        if ($result) {
            $result = str_replace('$item', $result, $this->templates['posteditor.categories']);
        }

        return $result;
    }

    public function processCategories(): array
    {
        $result = $this->check2array('category-');
        Arr::clean($result);
        Arr::deleteValue($result, 0);
        return $result;
    }

    public function check2array(string $prefix): array
    {
        $result = [];
        foreach ($_POST as $key => $value) {
            if (Str::begin($key, $prefix)) {
                $result[] = is_numeric($value) ? (int)$value : $value;
            }
        }

        return $result;
    }
}
