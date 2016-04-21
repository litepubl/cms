<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\admin\menu;
use litepubl\pages\Menus;
use litepubl\pages\Menu;
use litepubl\admin\Link;
use litepubl\admin\Table;
use litepubl\view\Lang;
use litepubl\view\Args;

class Manager extends \litepubl\admin\Menu
{

    public function getContent() {
        $result = '';
                if (isset($_GET['action']) && in_array($_GET['action'], array(
                    'delete',
                    'setdraft',
                    'publish'
                ))) {
                    $result.= $this->doaction($this->idget() , $_GET['action']);
                }

        $menus = Menus::i();
        $lang = Lang::admin();
        $editurl = Link::url("{$this->url}edit/?id");
        $result .= $this->tableItems($menus->items, array(
            array(
                $lang->menutitle,
                function (Table $tb) use ($menus) {
                    return $menus->getlink($tb->item['id']);
                }
            ) ,

            array(
                'right',
                $lang->order,
                '$order'
            ) ,

            array(
                'center',
                $lang->parent,
                function (Table $tb) use ($menus) {
                    return $tb->item['parent'] == 0 ? '---' : $menus->getlink($tb->item['parent']);
                }
            ) ,

            array(
                'center',
                $lang->edit,
                "<a href='$editurl=\$id'>$lang->edit</a>"
            ) ,

            array(
                'center',
                $lang->delete,
                "<a class=\"confirm-delete-link\" href=\"$this->adminurl=\$id&action=delete\">$lang->delete</a>"
            ) ,
        ));

return $result;
    }

    private function doaction($id, $action) {
        $menus = Menus::i();
        if (!$menus->itemexists($id)) {
 return $this->notfound;
}


        $args = new Args();
        $admin = $this->admintheme;
$lang = $this->lang;
        $menuitem = Menu::i($id);
        switch ($action) {
            case 'delete':
return $this->confirmDeleteItem($menus);

            case 'setdraft':
                $menuitem->status = 'draft';
                $menus->edit($menuitem);
                return $admin->success($lang->confirmedsetdraft);

            case 'publish':
                $menuitem->status = 'published';
                $menus->edit($menuitem);
                return $admin->success($lang->confirmedpublish);
        }

        return '';
    }

}