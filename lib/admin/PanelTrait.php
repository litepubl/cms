<?php

namespace litepubl\admin;

use litepubl\view\Schema;
use litepubl\view\Schemes;
use litepubl\view\Lang;
use litepubl\view\Args;
use litepubl\core\Plugins as PluginItems;

trait PanelTrait
{
public $admin;
public $theme;
public $lang;
public $args;

public function createInstances(Schema $schema)
{
$this->admin = $schema->admintheme;
$this->theme = $schema->theme;
$this->lang = Lang::admin();
$this->args = new Args();
}

public function getSchema()
{
$app = $this->getApp();
if (isset($app->context) && isset($app->context->view)) {
return Schema::getSchema($app->context->view);
} else {
return Schema::i(Schemes::i()->defaults['admin']);
}
}

public function getLangAbout()
 {
        $reflector = new \ReflectionClass($this);
        $filename = $reflector->getFileName();
return PluginItems::getLangAbout(basename(dirname($filename)));
}

}
