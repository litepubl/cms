<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\admin\users;
use litepubl\core\UserGroups;
use litepubl\admin\Link;

class Groups extends \litepubl\admin\Menu
{

    public static function getgroups(array $idgroups) {
        $result = '';
        $groups = UserGroups::i();
        $tml = '<li><input type="checkbox" name="idgroup-$id" id="checkbox-idgroup-$id" value="$id" $checked />
    <label for="checkbox-idgroup-$id"><strong>$title</strong></label></li>';
        $theme = ttheme::i();
        $args = new targs();
        foreach ($groups->items as $id => $item) {
            $args->add($item);
            $args->id = $id;
            $args->checked = in_array($id, $idgroups);
            $result.= strtr($tml, $args->data);
        }
        return sprintf('<ul>%s</ul>', $result);
    }

    public function getcontent() {
        $groups = UserGroups::i();
$admin = $this->admintheme;
        $lang = tlocal::admin('users');
        $args = targs::i();
        $adminurl = $this->adminurl;
        $result = "<h4><a href='$adminurl=0&action=add'>$lang->addgroup</a></h4>";

        $id = $this->idget();

        switch ($this->action) {
            case 'add':
                $result.= $admin->help($lang->notegroup);
                $args->name = '';
                $args->title = '';
                $args->home = '';
                $args->action = 'add';
                $args->formtitle = $lang->editgroup;
                $result.= $admin->form('
      [text=title]
      [text=name]
      [text=home]
      [hidden=action]' . $html->h4->parentgroups . static ::getgroups(array()) , $args);
                break;


            case 'edit':
                $result.= $admin->help($lang->notegroup);
                $args->add($groups->items[$id]);
                $args->id = $id;
                $args->action = 'edit';
                $args->formtitle = $lang->editgroup;
                $result.= $admin->form('
      [text=title]
      [text=name]
      [text=home]
      [hidden=id]
      [hidden=action]' . $html->h4->parentgroups . static ::getgroups($groups->items[$id]['parents']) , $args);
                break;


            case 'delete':
                $result.= $this->confirmDeleteItem($groups);
                break;
        }

        $tb = $this->newTable();
        $tb->setstruct(array(
            array(
                $lang->name,
                '<a href="' . $adminurl . '=$id&action=edit" title="$title">$title</a>'
            ) ,
            array(
                $lang->users,
                sprintf('<a href="%s">%s</a>', Link::url('/admin/users/?idgroup=$id') , $lang->users)
            ) ,
            $tb->action('delete', $adminurl)
        ));

        $result.= $admin->h($lang->grouptable);
        $result.= $tb->build($groups->items);
        return $result;
    }

    public function processform() {
        $groups = UserGroups::i();
        switch ($this->action) {
            case 'add':
                $groups->lock();
                $id = $groups->add($_POST['name'], $_POST['title'], $_POST['home']);
                $groups->items[$id]['parents'] = tadminhtml::check2array('idgroup-');
                $groups->unlock();
                $_POST['id'] = $id;
                $_GET['id'] = $id;
                $_GET['action'] = 'edit';
                break;


            case 'edit':
                $id = $this->idget();
                if ($groups->itemexists($id)) {
                    foreach (array(
                        'name',
                        'title',
                        'home'
                    ) as $name) {
                        $groups->items[$id][$name] = $_POST[$name];
                    }
                    $groups->items[$id]['parents'] = tadminhtml::check2array('idgroup-');
                    $groups->save();
                }
                break;
        }
    }

}