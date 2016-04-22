<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\view;

class AutoVars extends \litepubl\core\Items
{
use \litepubl\core\PoolStorageTrait;

public $defaults;

public function create() {
parent::create();
$this->basename = 'autovars';
$this->defaults = [
'post' => '\litepubl\post\Post',
'files' => '\litepubl\post\Files',
'archives' => '\litepubl\post\Archives',
'categories' => '\litepubl\tag\Cats',
'cats' => '\litepubl\tag\Cats',
'tags' => '\litepubl\tag\Tags',
'home' => '\litepubl\pages\Home',
'template' => '\litepubl\view\MainView',
'comments' => '\litepubl\comments\Comments',
'menu' => '\litepubl\pages\Menu',
];
}

public function get($name) {
if (isset($this->items[$name])) {
return $this->app->classes->getInstance($this->items[$name]);
}

if (isset($this->defaults[$name])) {
return $this->app->classes->getInstance($this->defaults[$name]);
}

return false;
}

}