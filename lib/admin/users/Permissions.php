<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl;

class tadminperms extends tadminmenu {


    public function getcontent() {
        $result = '';
        $perms = tperms::i();
        $html = $this->html;
        $lang = tlocal::i('perms');
        $args = new targs();
        if (!($action = $this->action)) $action = 'perms';
        switch ($action) {
            case 'perms':
                $tb = new tablebuilder();
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

                $form = new adminform($args);
                $form->title = $lang->table;
                $result.= $form->getdelete($tb->build($items));

                $result.= $html->h4->newperms;
                $result.= '<ul>';
                $addurl = tadminhtml::getadminlink($this->url, 'action=add&class');
                foreach ($perms->classes as $class => $name) {
                    if ($class == 'tsinglepassword') continue;
                    $result.= $html->li("<a href='$addurl=$class'>$name</a>");
                }

                $result.= '</ul>';
                return $html->fixquote($result);

            case 'add':
                $class = tadminhtml::getparam('class', '');
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

                $perm = tperm::i($id);
                return $perm->admin->getcont();

            case 'delete':
                return $this->confirmDeleteItem($perms);
            }

    }

    public function processform() {
        $perms = tperms::i();
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

                $perm = tperm::i($id);
                return $perm->admin->processform();

            case 'add':
                $class = tadminhtml::getparam('class', '');
                if (isset($perms->classes[$class])) {
                    $perm = new $class();
                    $id = tperms::i()->add($perm);
                    $perm->admin->processform();
                    return litepubl::$urlmap->redir(tadminhtml::getadminlink($this->url, 'action=edit&id=' . $id));
                }
            }
    }

} //class
class tadminperm {
    public $perm;

    public function getcont() {
        $html = tadminhtml::i();
        $lang = tlocal::i('perms');
        $args = new targs();
        $args->add($this->perm->data);
        $args->formtitle = $lang->editperm;
        $form = '[text=name] [hidden=id]';
        $form.= $this->getform($args);
        return $html->adminform($form, $args);
    }

    public function getform(targs $args) {
        return '';
    }

    public function processform() {
        $name = trim($_POST['name']);
        if ($name != '') $this->perm->name = $name;
        $this->perm->save();
    }

} //class
class tadminpermpassword extends tadminperm {

    public function getform(targs $args) {
        $args->password = '';
        return '[password=password]';
    }

    public function processform() {
        $this->perm->password = $_POST['password'];
        parent::processform();
    }

} //class
class tadminpermgroups extends tadminperm {

    public function getform(targs $args) {
        $result = '[checkbox=author]
    <h4>$lang.groups</h4>';
        $args->author = $this->perm->author;
        $result.= tadmingroups::getgroups($this->perm->groups);
        return $result;
    }

    public function processform() {
        $this->perm->author = isset($_POST['author']);
        $this->perm->groups = array_unique(tadminhtml::check2array('idgroup-'));
        parent::processform();
    }

} //class