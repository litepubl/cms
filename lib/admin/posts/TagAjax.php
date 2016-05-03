<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\admin\posts;
use litepubl\core\Context;
use litepubl\tag\Tags as TagItems;
use litepubl\tag\Cats as TatItems;
use litepubl\view\Admin;
use litepubl\view\Lang;
use litepubl\view\Schemes;
use litepubl\view\Schema;
use litepubl\admin\GetSchema;
use litepubl\admin\GetPerm;
use litepubl\view\Args;

class TagAjax extends Ajax
{

    public function install() {
         $this->getApp()->router->addget('/admin/ajaxtageditor.htm', get_class($this));
    }

    public function request(Context $context)
    {
    $response = $context->response;
        $response->cache = false;

$this->auth($context);
if ($response->status == 200) {
        $response->body = $this->getcontent();
}
    }

    public function getContent() {
        $type = !empty($_GET['type']) ? $_GET['type'] : (!empty($_POST['type']) ? $_POST['type'] : 'tags');
if ($type != 'tags') {
$type = 'categories';
}

        $tags = $type == 'tags' ? Tagitems::i() : CatItems::i();
        if ($err = static ::auth()) {
            return $err;
        }

        $id = $this->idparam();
        if (($id > 0) && !$tags->itemExists($id)) {
            return static ::error403();
        }

        $theme = Schema::i(Schemes::i()->defaults['admin'])->theme;
        $admin = Admin::admin();
        $lang = Lang::i('tags');

        if ($id == 0) {
            $schemes = Schemes::i();
            $name = $type == 'tags' ? 'tag' : 'category';
            $item = array(
                'title' => '',
                'idschema' => isset($schemes->defaults[$name]) ? $schemes->defaults[$name] : 1,
                'idperm' => 0,
                'icon' => 0,
                'includechilds' => $tags->includechilds,
                'includeparents' => $tags->includeparents,
                'url' => '',
                'keywords' => '',
                'description' => '',
                'head' => ''
            );
        } else {
            $item = $tags->getitem($id);
        }

        switch ($_GET['get']) {
            case 'view':
                if ($id > 0) {
                    foreach (array(
                        'includechilds',
                        'includeparents'
                    ) as $prop) {
                        $item[$prop] = ((int)$item[$prop]) > 0;
                    }
                }

                $args = new Args();
                $args->add($item);
                $result = GetSchema::combo($item['idschema']);
                $result.= $admin->parseArg('[checkbox=includechilds] [checkbox=includeparents]', $args);
                $result.= GetPerm::combo($item['idperm']);
                break;


            case 'seo':
                $args = new Args();
                if ($id == 0) {
                    $args->url = '';
                    $args->keywords = '';
                    $args->description = '';
                    $args->head = '';
                } else {
                    $args->add($tags->contents->getitem($id));
                    $args->url = $tags->items[$id]['url'];
                }
                $result = $admin->parseArg('[text=url] [text=description] [text=keywords] [editor=head]', $args);
                break;


            case 'text':
                return $this->gettext($id == 0 ? '' : $tags->contents->getcontent($id));
                break;


            default:
                $result = var_export($_GET, true);
        }
        return \litepubl\core\Router::htmlheader(false) . $result;
    }

}