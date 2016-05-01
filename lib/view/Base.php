<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\view;
use litepubl\utils\Filer;
use litepubl\debug\LogException;
use litepubl\post\Post;
use litepubl\post\View as PostView;
use litepubl\core\Str;

class Base extends \litepubl\core\Events
 {
    public static $instances = array();
    public static $vars = array();
    public static $defaultargs;

    public $name;
    public $parsing;
    public $templates;
    public $extratml;

    public static function exists($name) {
        return file_exists( static::getAppInstance()->paths->themes . $name . '/about.ini');
    }

    public static function getTheme($name) {
        return static ::getByName(get_called_class() , $name);
    }

    public static function getByName($classname, $name) {
        if (isset(static ::$instances[$name])) {
            return static ::$instances[$name];
        }

        $result = static::iGet($classname);
        if ($result->name) {
            $result =  static::getAppInstance()->classes->newinstance($classname);
        }

        $result->name = $name;
        $result->load();
        return $result;
    }

    protected function create() {
        parent::create();
        $this->name = '';
        $this->parsing = array();
        $this->data['type'] = 'litepublisher';
        $this->data['parent'] = '';
        $this->addmap('templates', array());
        $this->templates = array();

        if (!isset(static ::$defaultargs)) {
static ::set_defaultargs();
}

        $this->extratml = '';
    }

    public static function set_defaultargs() {
$site = static::getAppInstance()->site;
        static ::$defaultargs = array(
            '$site.url' =>  $site->url,
            '$site.files' =>  $site->files,
            '{$site.q}' =>  $site->q,
            '$site.q' =>  $site->q
        );
    }

    public function __destruct() {
        unset(static ::$instances[$this->name], $this->templates);
        parent::__destruct();
    }

    public function getBasename() {
        return 'themes/' . $this->name;
    }

    public function getParser() {
        return BaseParser::i();
    }

    public function load() {
        if (!$this->name) {
 return false;
}



        if (parent::load()) {
            static ::$instances[$this->name] = $this;
            return true;
        }

        return $this->parsetheme();
    }

    public function parsetheme() {
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

    public function __set($name, $value) {
        if (array_key_exists($name, $this->templates)) {
            $this->templates[$name] = $value;
            return;
        }
        return parent::__set($name, $value);
    }

    public function reg($exp) {
        if (!strpos($exp, '\.')) $exp = str_replace('.', '\.', $exp);
        $result = array();
        foreach ($this->templates as $name => $val) {
            if (preg_match($exp, $name)) $result[$name] = $val;
        }
        return $result;
    }

    protected function getVar($name) {
        switch ($name) {
            case 'site':
                return  $this->getApp()->site;

            case 'lang':
                return lang::i();

            case 'post':
if ($context = $this->getApp()->context) {
if (isset($context->view and $context->view instanceof PostView) {
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

    public function parsecallback($names) {
        $name = $names[1];
        $prop = $names[2];
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
        }
        catch(Exception $e) {
             $this->getApp()->options->handexception($e);
        }
        return '';
    }

    public function parse($s) {
        if (!$s) {
 return '';
}


        $s = strtr((string)$s, static ::$defaultargs);
        if (isset($this->templates['content.admin.tableclass'])) $s = str_replace('$tableclass', $this->templates['content.admin.tableclass'], $s);
        array_push($this->parsing, $s);
        try {
            $s = preg_replace('/%%([a-zA-Z0-9]*+)_(\w\w*+)%%/', '\$$1.$2', $s);
            $result = preg_replace_callback('/\$([a-zA-Z]\w*+)\.(\w\w*+)/', array(
                $this,
                'parsecallback'
            ) , $s);
        }
        catch(Exception $e) {
            $result = '';
             $this->getApp()->options->handexception($e);
        }
        array_pop($this->parsing);
        return $result;
    }

    public function parsearg($s, Args $args) {
        $s = $this->parse($s);
        $s = $args->callback($s);
        return strtr($s, $args->data);
    }

    public function replacelang($s, $lang) {
        $s = preg_replace('/%%([a-zA-Z0-9]*+)_(\w\w*+)%%/', '\$$1.$2', (string)$s);
        static ::$vars['lang'] = isset($lang) ? $lang : Lang::i('default');
        $s = strtr($s, static ::$defaultargs);
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

    public static function parsevar($name, $var, $s) {
        static ::$vars[$name] = $var;
        return static ::i()->parse($s);
    }

    public static function clearcache() {
        Filer::delete( $this->getApp()->paths->data . 'themes', false, false);
         $this->getApp()->cache->clear();
    }

    public function h($s) {
        return sprintf('<h4>%s</h4>', $s);
    }

    public function link($url, $title) {
        return sprintf('<a href="%s%s">%s</a>', Str::begin($url, 'http') ? '' :  $this->getApp()->site->url, $url, $title);
    }

    public static function quote($s) {
        return strtr($s, array(
            '"' => '&quot;',
            "'" => '&#039;',
            '\\' => '&#092;',
            '$' => '&#36;',
            '%' => '&#37;',
            '_' => '&#95;',
            '<' => '&lt;',
            '>' => '&gt;',
        ));
    }

}