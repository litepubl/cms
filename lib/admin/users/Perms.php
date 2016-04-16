<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\admin\users;
use litepubl\perms\Perms as PermItems;
use litepubl\perms\Perm;
use litepubl\admin\Link;

class Perms extends \litepubl\admin\Menu
{

    public function getcontent() {
        $result = '';
        $perms = PermItems::i();
$admin = $this->admintheme;
        $lang = tlocal::i('perms');
        $args = new targs();
        if (!($action = $this->action)) $action = 'perms';
        switch ($action) {
            case 'perms':
                $tb = $this->newTable();
                $tb->setowner($perms);
                $tb->setstruct(array(
                    $tb->checkbox('perm') ,
                    array(
                        $lang->edit,
                        "<a href=\"$this->adminurl=\$id&action=edit\">\$name</a>"
                    ) ,
                ));

                $items = array_keys($perms->items);
                array_shift($items);

                $form = new Form($args);
                $form->title = $lang->table;
                $result.= $form->getdelete($tb->build($items));

                $result.= $admin->h($lang->newperms);
                $result.= '<ul>';
                $addurl = Link::url($this->url, 'action=add&class');
                foreach ($perms->classes as $class => $name) {
                    if ($class == 'tsinglepassword') continue;
                    $result.= $html->li("<a href='$addurl=$class'>$name</a>");
                }

                $result.= '</ul>';
                return $result;

            case 'add':
                $class = $this->getparam('class', '');
                if (!isset($perms->classes[$class])) {
                    return $this->notfound();
                }

                $perm = new $class();
                return $perm->admin->getcont();

            case 'edit':
                $id = $this->idget();
                if (!$perms->itemexists($id)) {
                    return $this->notfound();
                }

                $perm = Perm::i($id);
                return $perm->admin->getcont();

            case 'delete':
                return $this->confirmDeleteItem($perms);
            }

    }

    public function processform() {
        $perms = PermItems::i();
        if (!($action = $this->action)) $action = 'perms';
        switch ($action) {
            case 'perms':
                $perms->lock();
                foreach ($_POST as $name => $val) {
                    if (!is_numeric($value)) continue;
                    $id = (int)$val;
                    $perms->delete($id);
                }
                $perms->unlock();
                return;

            case 'edit':
                $id = $this->idget();
                if (!$perms->itemexists($id)) {
                    return $this->notfound();
                }

                $perm = Perm::i($id);
                return $perm->admin->processform();

            case 'add':
                $class = $this->getparam('class', '');
                if (isset($perms->classes[$class])) {
                    $perm = new $class();
                    $id = PermItems::i()->add($perm);
                    $perm->admin->processform();
                    return litepubl::$urlmap->redir(Link::url($this->url, 'action=edit&id=' . $id));
                }
            }
    }

}