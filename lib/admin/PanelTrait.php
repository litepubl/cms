<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.04
  */

namespace litepubl\admin;

use litepubl\core\Plugins as PluginItems;
use litepubl\view\Args;
use litepubl\view\Lang;
use litepubl\view\Schema;
use litepubl\view\Schemes;

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

    public function getSchema(): Schema
    {
        $app = $this->getApp();
        if (isset($app->context) && isset($app->context->view)) {
            return Schema::getSchema($app->context->view);
        } else {
            return Schema::i(Schemes::i()->defaults['admin']);
        }
    }

    public function getLangAbout(): Lang
    {
        $reflector = new \ReflectionClass($this);
        $filename = $reflector->getFileName();
        return PluginItems::getLangAbout($filename);
    }
}
