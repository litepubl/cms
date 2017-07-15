<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\admin\menu;

use litepubl\admin\GetSchema;
use litepubl\core\Context;
use litepubl\pages\Menu;
use litepubl\pages\Menus;
use litepubl\view\Args;
use litepubl\view\Lang;
use litepubl\view\Schema;
use litepubl\view\Schemes;

class Ajax extends \litepubl\admin\posts\Ajax
{
    use \litepubl\utils\TempProps;

    //to prevent call parent method
    public function install()
    {
        $this->getApp()->router->addget('/admin/ajaxmenueditor.htm', get_class($this));
    }

    public function request(Context $context)
    {
        $response = $context->response;
        $this->auth($context);
        if ($response->status == 200) {
            $temp = $this->newProps();
            $temp->response = $response;
            $response->body = $this->getContent();
        }
    }

    public function getContent(): string
    {
        $id = $this->idparam();
        $menus = Menus::i();
        if (($id != 0) && !$menus->itemExists($id)) {
            return $this->response->forbidden();
        }

        $menu = Menu::i($id);
        if (($this->getApp()->options->group == 'author') && ($this->getApp()->options->user != $menu->author)) {
            return $this->response->forbidden();
        }

        if (($id > 0) && !$menus->itemExists($id)) {
            return $this->response->forbidden();
        }

        $schemes = Schemes::i();
        $schema = Schema::i($schemes->defaults['admin']);
        $theme = $schema->theme;
        $admin = $schema->admintheme;
        $lang = Lang::admin('menu');

        switch ($_GET['get']) {
            case 'view':
                $result = GetSchema::combo($id == 0 ? $schemes->defaults['menu'] : $menu->idSchema);
                break;


            case 'seo':
                $args = new Args();
                $args->url = $menu->url;
                $args->keywords = $menu->keywords;
                $args->description = $menu->description;
                $args->head = $menu->data['head'];
                $result = $admin->parseArg('[text=url] [text=description] [text=keywords] [editor=head]', $args);
                break;


            default:
                $result = var_export($_GET, true);
        }
        return $result;
    }
}
