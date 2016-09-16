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
