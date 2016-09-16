<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

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
