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

use litepubl\admin\Link;
use litepubl\admin\Table;
use litepubl\pages\Menu;
use litepubl\pages\Menus;
use litepubl\view\Args;
use litepubl\view\Lang;

class Manager extends \litepubl\admin\Menu
{

    public function getContent(): string
    {
        $result = '';
        if (isset($_GET['action']) && in_array(
            $_GET['action'],
            [
            'delete',
            'setdraft',
            'publish'
            ]
        )) {
            $result.= $this->doaction($this->idget(), $_GET['action']);
        }

        $menus = Menus::i();
        $lang = Lang::admin();
        $editurl = Link::url("{$this->url}edit/?id");
        $result.= $this->tableItems(
            $menus->items,
            [
            [
                $lang->menutitle,
                function (Table $tb) use ($menus) {
                
                    return $menus->getlink($tb->item['id']);
                }
            ] ,

            [
                'right',
                $lang->order,
                '$order'
            ] ,

            [
                'center',
                $lang->parent,
                function (Table $tb) use ($menus) {
                
                    return $tb->item['parent'] == 0 ? '---' : $menus->getlink($tb->item['parent']);
                }
            ] ,

            [
                'center',
                $lang->edit,
                "<a href='$editurl=\$id'>$lang->edit</a>"
            ] ,

            [
                'center',
                $lang->delete,
                "<a class=\"confirm-delete-link\" href=\"$this->adminurl=\$id&action=delete\">$lang->delete</a>"
            ] ,
            ]
        );

        return $result;
    }

    private function doaction($id, $action)
    {
        $menus = Menus::i();
        if (!$menus->itemExists($id)) {
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
