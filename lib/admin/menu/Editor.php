<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.03
  */

namespace litepubl\admin\menu;

use litepubl\admin\Link;
use litepubl\pages\FakeMenu;
use litepubl\pages\Menu;
use litepubl\pages\Menus;
use litepubl\view\Args;
use litepubl\view\Lang;
use litepubl\view\MainView;

class Editor extends \litepubl\admin\Menu
{

    public function getHead(): string
    {
        $mainView = MainView::i();
        $mainView->ltoptions['idpost'] = $this->idget();
        return parent::gethead();
    }

    public function getTitle(): string
    {
        if ($this->idget()) {
            return $this->lang->edit;
        }

        return parent::gettitle();
    }

    public function getContent(): string
    {
        $id = $this->idparam();
        $menus = Menus::i();
        $parents = [
            0 => '-----'
        ];

        foreach ($menus->items as $item) {
            $parents[$item['id']] = $item['title'];
        }

        $admin = $this->admintheme;
        $lang = Lang::i('menu');
        $args = new Args();
        $args->adminurl = $this->adminurl;
        $args->ajax = Link::url("/admin/ajaxmenueditor.htm?id=$id&get");
        $args->editurl = Link::url('/admin/menu/edit?id');
        if ($id == 0) {
            $args->id = 0;
            $args->title = '';
            $args->parent = $this->theme->comboItems($parents, 0);
            $args->order = $this->theme->comboItems(range(0, 10), 0);
            $status = 'published';
        } else {
            if (!$menus->itemExists($id)) {
                return $this->notfound;
            }

            $menuitem = Menu::i($id);
            $args->id = $id;
            $args->title = $menuitem->getownerprop('title');
            $args->parent = $this->theme->comboItems($parents, $menuitem->parent);
            $args->order = $this->theme->comboItems(range(0, 10), $menuitem->order);
            $status = $menuitem->status;
        }

        $args->status = $this->theme->comboItems(
            [
            'draft' => $lang->draft,
            'published' => $lang->published
            ], $status
        );

        if (($this->name == 'editfake') || (($id > 0) && ($menuitem instanceof FakeMenu))) {
            $args->url = $id == 0 ? '' : $menuitem->url;
            $args->type = 'fake';
            $args->formtitle = $lang->faketitle;
            return $admin->form(
                '[text=title]
        [text=url]
        [combo=parent]
        [combo=order]
        [combo=status]
        [hidden=type]
        [hidden=id]', $args
            );
        }

        $tabs = $this->newTabs();
        $tabs->add(
            $lang->title, '
      [text=title]
      [combo=parent]
      [combo=order]
      [combo=status]
      [hidden=id]
      '
        );

        $ajaxurl = Link::url("/admin/ajaxmenueditor.htm?id=$id&get");
        $tabs->ajax($lang->view, "$ajaxurl=view");
        $tabs->ajax('SEO', "$ajaxurl=seo");

        $ajaxeditor = Ajax::i();
        $args->formtitle = $lang->edit;
        $tml = $tabs->get() . $ajaxeditor->gettext($id == 0 ? '' : $menuitem->rawcontent, $this->admintheme);
        return $admin->form($tml, $args);
    }

    public function processForm()
    {
        extract($_POST, EXTR_SKIP);
        if (empty($title)) {
            return '';
        }

        $id = $this->idget();
        $menus = Menus::i();
        if (($id != 0) && !$menus->itemExists($id)) {
            return $this->notfound;
        }

        if (isset($type) && ($type == 'fake')) {
            $menuitem = FakeMenu::i($id);
        } else {
            $menuitem = Menu::i($id);
        }

        $menuitem->title = $title;
        $menuitem->order = (int)$order;
        $menuitem->parent = (int)$parent;
        $menuitem->status = $status == 'draft' ? 'draft' : 'published';
        if (isset($raw)) {
            $menuitem->content = $raw;
        }

        if (isset($idschema)) {
            $menuitem->idschema = $idschema;
        }
        if (isset($url)) {
            $menuitem->url = $url;
            if (!isset($type) || ($type != 'fake')) {
                $menuitem->keywords = $keywords;
                $menuitem->description = $description;
                $menuitem->head = $head;
            }
        }

        if ($id == 0) {
            $_POST['id'] = $menus->add($menuitem);
        } else {
            $menus->edit($menuitem);
        }

        $admin = $this->admintheme;
        return $admin->success(sprintf($this->lang->success, $admin->link($menuitem->url, $menuitem->title)));
    }
}
