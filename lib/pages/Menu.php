<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\pages;

use litepubl\core\Context;
use litepubl\view\Admin;
use litepubl\view\Filter;
use litepubl\view\Schema;
use litepubl\view\Theme;

/**
 * This is the base menu class
 *
 * @property      int $author
 * @property      string $content
 * @property      string $rawcontent
 * @property      string $keywords
 * @property      string $description
 * @property      string $head
 * @property      string $password
 * @property      int $idschema
 * @property      string $title
 * @property      string $url
 * @property      int $idurl
 * @property      int $parent
 * @property      int $order
 * @property      string $status
 * @property      string $status
 * @property-read string $instanceName
 * @property-read string $link
 * @property-read string $cont
 * @property-read Schema $schema
 * @property-read Theme $theme
 * @property-read Admin $adminTheme
 */

class Menu extends \litepubl\core\Item implements \litepubl\view\ViewInterface
{

    public $formresult;

    public static $ownerprops = [
        'title',
        'url',
        'idurl',
        'parent',
        'order',
        'status'
    ];

    public static function i($id = 0)
    {
        $class = $id == 0 ? get_called_class() : static::getowner()->items[$id]['class'];
        return static::itemInstance($class, $id);
    }

    public static function iteminstance($class, $id = 0)
    {
        $single = static::iGet($class);
        if (($single->id == $id) || (($id == 0) && ($single->id > 0))) {
            return $single;
        }
        
        if (($single->id == 0) && ($id > 0)) {
            return $single->loaddata($id);
        }
        
        return parent::iteminstance($class, $id);
    }

    public static function singleInstance($class)
    {
        $single = static::iGet($class);
        if ($id = $single->get_owner()->class2id($class)) {
            if ($single->id == $id) {
                return $single;
            }
            
            if (($single->id == 0) && ($id > 0)) {
                return $single->loaddata($id);
            }
        }
        return $single;
    }

    public static function getInstancename()
    {
        return 'menu';
    }

    public static function getOwner()
    {
        return Menus::i();
    }

    public function get_owner()
    {
        return static::getowner();
    }

    protected function create()
    {
        parent::create();
        $this->formresult = '';
        $this->data = [
            'id' => 0,
            // not supported
            'author' => 0,
            'content' => '',
            'rawcontent' => '',
            'keywords' => '',
            'description' => '',
            'head' => '',
            'password' => '',
            'idschema' => 1,
            // owner props
            'title' => '',
            'url' => '',
            'idurl' => 0,
            'parent' => 0,
            'order' => 0,
            'status' => 'published'
        ];
    }

    public function getBasename()
    {
        return 'menus' . DIRECTORY_SEPARATOR . $this->id;
    }

    public function __get($name)
    {
        if ($name == 'content') {
            return $this->formresult . $this->getcontent();
        }
        
        if ($name == 'id') {
            return $this->data['id'];
        }
        
        if (method_exists($this, $get = 'get' . $name)) {
            return $this->$get();
        }
        
        if ($this->is_owner_prop($name)) {
            return $this->getownerprop($name);
        }
        
        return parent::__get($name);
    }

    public function get_owner_props()
    {
        return static::$ownerprops;
    }

    public function is_owner_prop($name)
    {
        return in_array($name, $this->get_owner_props());
    }

    public function getOwnerProp($name)
    {
        $id = $this->data['id'];
        if ($id == 0) {
            return $this->data[$name];
        } elseif (isset($this->getowner()->items[$id])) {
            return $this->getowner()->items[$id][$name];
        } else {
            $this->error(sprintf('%s property not found in %d items', $id, $name));
        }
    }

    public function __set($name, $value)
    {
        if ($this->is_owner_prop($name)) {
            if ($this->id == 0) {
                $this->data[$name] = $value;
            } else {
                $this->owner->setvalue($this->id, $name, $value);
            }
            return;
        }
        parent::__set($name, $value);
    }

    public function __isset($name)
    {
        if ($this->is_owner_prop($name)) {
            return true;
        }
        
        return parent::__isset($name);
    }

    public function getSchema(): Schema
    {
        return Schema::getSchema($this);
    }

    public function getTheme(): Theme
    {
        return $this->schema->theme;
    }

    public function getAdmintheme(): Admin
    {
        return $this->schema->admintheme;
    }
    
    // ViewInterface
    public function request(Context $context)
    {
        if (! $this->loadItem($context->itemRoute['arg']) || ($this->status == 'draft')) {
            $context->response->status = 404;
        } else {
            $this->doProcessForm();
        }
    }

    protected function doProcessForm()
    {
        if (isset($_POST) && count($_POST)) {
            $this->formresult .= $this->processForm();
        }
    }

    public function processForm()
    {
        $r = $this->owner->onprocessForm(['id' => $this->id, 'content' => '']);
        return $r['content'];
    }

    public function getHead(): string
    {
        return $this->data['head'];
    }

    public function getTitle(): string
    {
        return $this->getownerprop('title');
    }

    public function getKeywords(): string
    {
        return $this->data['keywords'];
    }

    public function getDescription(): string
    {
        return $this->data['description'];
    }

    public function getIdSchema(): int
    {
        return $this->data['idschema'];
    }

    public function setIdSchema(int $id)
    {
        if ($id != $this->idschema) {
            $this->data['idschema'] = $id;
            $this->save();
        }
    }

    public function getCont(): string
    {
        return $this->theme->parsevar('menu', $this, $this->theme->templates['content.menu']);
    }

    public function getLink(): string
    {
        return $this->getApp()->site->url . $this->url;
    }

    public function getContent(): string
    {
        $r = $this->owner->oncontent(['menu' => $this, 'content' => $this->data['content']]);
        return $r['content'];
    }

    public function setContent(string $s)
    {
        if (! is_string($s)) {
            $this->error('Error! Page content must be string');
        }
        
        if ($s != $this->rawcontent) {
            $this->rawcontent = $s;
            $filter = Filter::i();
            $this->data['content'] = $filter->filter($s);
        }
    }
}
