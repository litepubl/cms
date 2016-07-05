<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 7.00
 *
 */

namespace litepubl\admin\users;

use litepubl\admin\Link;
use litepubl\perms\Perm as PerItem;
use litepubl\perms\Perms as PermItems;
use litepubl\view\Args;
use litepubl\view\Lang;

class Perms extends \litepubl\admin\Menu
{

    public function getContent(): string
    {
        $result = '';
        $perms = PermItems::i();
        $admin = $this->admintheme;
        $lang = Lang::i('perms');
        $args = new Args();
        if (!($action = $this->action)) {
            $action = 'perms';
        }
        switch ($action) {
        case 'perms':
            $tb = $this->newTable();
            $tb->setowner($perms);
            $tb->setStruct(
                array(
                $tb->checkbox('perm') ,
                array(
                $lang->edit,
                "<a href=\"$this->adminurl=\$id&action=edit\">\$name</a>"
                ) ,
                )
            );

            $items = array_keys($perms->items);
            array_shift($items);

            $form = $this->newForm($args);
            $form->title = $lang->table;
            $result.= $form->getdelete($tb->build($items));

            $result.= $admin->h($lang->newperms);
            $list = $this->newList();
            $list->item = $list->link;
            $url = Link::url($this->url, 'action=add&class=');

            foreach ($perms->classes as $class => $name) {
                if ($class == '\litepubl\perms\Single') {
                    continue;
                }

                $class = str_replace('\\', '-', $class);
                $list->add($url . $class, $name);
            }

            $result.= $list->getResult();
            return $result;

        case 'add':
            $class = $this->getparam('class', '');
            $class = str_replace('-', '\\', $class);
            if (!isset($perms->classes[$class])) {
                return $this->notfound();
            }

            $perm = new $class();
            return $perm->admin->getContent();

        case 'edit':
            $id = $this->idget();
            if (!$perms->itemExists($id)) {
                return $this->notfound();
            }

            $perm = PerItem::i($id);
            return $perm->admin->getContent();

        case 'delete':
            return $this->confirmDeleteItem($perms);
        }

    }

    public function processForm()
    {
        $perms = PermItems::i();
        if (!($action = $this->action)) {
            $action = 'perms';
        }
        switch ($action) {
        case 'perms':
            $perms->lock();
            foreach ($_POST as $name => $value) {
                if (!is_numeric($value)) {
                    continue;
                }

                $id = (int)$value;
                $perms->delete($id);
            }

            $perms->unlock();
            return;

        case 'edit':
            $id = $this->idget();
            if (!$perms->itemExists($id)) {
                return $this->notfound();
            }

            $perm = PerItem::i($id);
            return $perm->admin->processForm();

        case 'add':
            $class = $this->getparam('class', '');
            if (isset($perms->classes[$class])) {
                $perm = new $class();
                $id = PermItems::i()->add($perm);
                $perm->admin->processForm();
                return $this->getApp()->context->response->redir(Link::url($this->url, 'action=edit&id=' . $id));
            }
        }
    }
}
