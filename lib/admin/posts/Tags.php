<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.02
  */

namespace litepubl\admin\posts;

use litepubl\admin\Form;
use litepubl\admin\Link;
use litepubl\admin\Table;
use litepubl\tag\Cats as CatItems;
use litepubl\tag\Tags as TagItems;
use litepubl\view\Args;
use litepubl\view\Filter;
use litepubl\view\Lang;

class Tags extends \litepubl\admin\Menu
{

    public function getContent(): string
    {
        $result = '';
        $istags = ($this->name == 'tags') || ($this->name == 'addtag');
        $tags = $istags ? TagItems::i() : CatItems::i();
        $tags->loadAll();

        $parents = [
            0 => '-----'
        ];
        foreach ($tags->items as $id => $item) {
            $parents[$id] = $item['title'];
        }

        $this->basename = 'tags';
        $admin = $this->admintheme;
        $lang = Lang::i('tags');
        $id = $this->idget();
        $args = new Args();
        $args->id = $id;
        $args->adminurl = $this->adminurl;
        $ajax = Link::url('/admin/ajaxtageditor.htm', sprintf('id=%d&type=%s&get', $id, $istags ? 'tags' : 'categories'));
        $args->ajax = $ajax;

        if (isset($_GET['action']) && ($_GET['action'] == 'delete') && $tags->itemExists($id)) {
            $result.= $this->confirmDeleteItem($tags);
        }

        $result.= $admin->h($admin->link('/admin/posts/' . ($istags ? 'addtag' : 'addcat') . '/', $lang->add));
        $item = false;
        if ($id && $tags->itemExists($id)) {
            $item = $tags->getitem($id);
            $args->formtitle = $lang->edit;
        } elseif (($this->name == 'addcat') || ($this->name == 'addtag')) {
            $id = 0;
            $item = [
                'id' => 0,
                'title' => '',
                'parent' => 0,
                'customorder' => 0,
            ];

            $args->formtitle = $lang->add;
        }

        if ($item) {
            $args->add($item);
            $args->parent = $this->theme->comboItems($parents, $item['parent']);
            $args->order = $this->theme->comboItems(array_combine(range(0, 9), range(1, 10)), $item['customorder']);

            $tabs = $this->newTabs();
            $tabs->add(
                $lang->title, '
      [text=title]
      [combo=parent]
      [combo=order]
      [hidden=id]' . $admin->help($lang->ordernote)
            );

            $tabs->ajax($lang->text, "$ajax=text");
            $tabs->ajax($lang->view, "$ajax=view");
            $tabs->ajax('SEO', "$ajax=seo");

            $form = new Form($args);
            $result.= $admin->form($tabs->get(), $args);
        }

        //table
        $perpage = 20;
        $count = $tags->count;
        $from = $this->getfrom($perpage, $count);
        if ($tags->dbversion) {
            $iditems = $tags->db->idselect("id > 0 order by parent asc, title asc limit $from, $perpage");
        } else {
            $iditems = array_slice(array_keys($tags->items), $from, $perpage);
        }

        $items = [];
        foreach ($iditems as $id) {
            $item = $tags->items[$id];
            $item['parentname'] = $parents[$item['parent']];
            $items[] = $item;
        }

        $result.= Table::fromitems(
            $items, [
            [
                'right',
                $lang->count2,
                '$itemscount'
            ] ,
            [
                'left',
                $lang->title,
                '<a href="$link" title="$title">$title</a>'
            ] ,
            [
                'left',
                $lang->parent,
                '$parentname'
            ] ,
            [
                'center',
                $lang->edit,
                "<a href=\"$this->adminurl=\$id\">$lang->edit</a>"
            ] ,
            [
                'center',
                $lang->delete,
                "<a class=\"confirm-delete-link\" href=\"$this->adminurl=\$id&action=delete\">$lang->delete</a>"
            ]
            ]
        );

        $result.= $this->theme->getpages($this->url, $this->getApp()->context->request->page, ceil($count / $perpage));
        return $result;
    }

    private function set_view(array $item)
    {
        extract($_POST, EXTR_SKIP);
        $item['idschema'] = (int)$idschema;
        $item['includechilds'] = isset($includechilds);
        $item['includeparents'] = isset($includeparents);
        if (isset($idperm)) {
            $item['idperm'] = (int)$idperm;
        }

        return $item;
    }

    public function processForm()
    {
        if (empty($_POST['title'])) {
            return '';
        }

        extract($_POST, EXTR_SKIP);
        $istags = ($this->name == 'tags') || ($this->name == 'addtag');
        $tags = $istags ? TagItems::i() : CatItems::i();
        $tags->lock();
        $id = $this->idget();
        if ($id == 0) {
            $id = $tags->add((int)$parent, $title);
            if (isset($order)) {
                $tags->setvalue($id, 'customorder', (int)$order);
            }
            if (isset($url)) {
                $tags->edit($id, $title, $url);
            }
            if (isset($idschema)) {
                $item = $tags->getitem($id);
                $item = $this->set_view($item);
                $tags->items[$id] = $item;
                $item['id'] = $id;
                unset($item['url']);
                if ($tags->dbversion) {
                    $tags->db->updateassoc($item);
                }
            }
        } else {
            $item = $tags->getitem($id);
            $item['title'] = $title;
            if (isset($parent)) {
                $item['parent'] = (int)$parent;
            }
            if (isset($order)) {
                $item['customorder'] = (int)$order;
            }
            if (isset($idschema)) {
                $item = $this->set_view($item);
            }
            $tags->items[$id] = $item;
            if (!empty($url) && ($url != $item['url'])) {
                $tags->edit($id, $title, $url);
            }
            $tags->items[$id] = $item;
            unset($item['url']);
            $tags->db->updateassoc($item);
        }

        if (isset($raw) || isset($keywords)) {
            $item = $tags->contents->getitem($id);
            if (isset($raw)) {
                $filter = Filter::i();
                $item['rawcontent'] = $raw;
                $item['content'] = $filter->filterpages($raw);
            }
            if (isset($keywords)) {
                $item['keywords'] = $keywords;
                $item['description'] = $description;
                $item['head'] = $head;
            }
            $tags->contents->setitem($id, $item);
        }

        $tags->unlock();
        $_GET['id'] = $_POST['id'] = $id;
        return $this->admintheme->success(sprintf($this->lang->success, $title));
    }
}
