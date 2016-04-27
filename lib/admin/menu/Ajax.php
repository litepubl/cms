<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\admin\menu;
use litepubl\core\Context;
use litepubl\pages\Menus;
use litepubl\pages\Menu;
use litepubl\view\Schema;
use litepubl\view\Schemes;
use litepubl\admin\GetSchema;
use litepubl\view\Args;

class Ajax extends \litepubl\admin\posts\Ajax
{

//to prevent call parent method
    public function install() {
         $this->getApp()->router->addget('/admin/ajaxmenueditor.htm', get_class($this));
    }

    public function request(Context $context)
    {
    $response = $context->response;
$this->auth($context);
if ($response->status == 200) {
        $response->body = $this->getContent();
}
    }

    public function getContent() {
        $id = $this->idparam();
        $menus = Menus::i();
        if (($id != 0) && !$menus->itemexists($id)) {
 return static ::error403();
}


        $menu = Menu::i($id);
        if (( $this->getApp()->options->group == 'author') && ( $this->getApp()->options->user != $menu->author)) {
 return static ::error403();
}


        if (($id > 0) && !$menus->itemexists($id)) {
 return static ::error403();
}



        $schemes = Schemes::i();
$schema = Schema::i($schemes->defaults['admin']);
        $theme = $schema->theme;
$admin = $schema->admintheme;

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
                $result = $admin->parsearg('[text=url] [text=description] [text=keywords] [editor=head]', $args);
                break;


            default:
                $result = var_export($_GET, true);
        }
        return \litepubl\core\Router::htmlheader(false) . $result;
    }

}