<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\admin;
use litepubl\view\Schema;
use litepubl\view\Schemes;
use litepubl\view\Lang;
use litepubl\view\Args;
use litepubl\core\Plugins as PluginItems;

class Panel implements AdminInterface
{
use \litepubl\core\AppTrait;

public $admin;
public $theme;
public $lang;
public $args;

public function __construct() {
$app = $this->getApp();
if (isset($app->context) && isset($app->context->view)) {
$schema = Schema::getSchema($app->context->view);
} else {
$schema = Schema::i(Schemes::i()->defaults['admin']);
}

$this->admin = $schema->admintheme;
$this->theme = $schema->theme;
$this->lang = Lang::admin();
$this->args = new Args();
}

public function getLangAbout() {
        $reflector = new \ReflectionClass($this);
        $filename = $reflector->getFileName();
return PluginItems::getLangAbout(basename(dirname($filename)));
}

    public function getContent() {
}

    public function processForm() {
}

}