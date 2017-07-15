<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\admin\users;

use litepubl\admin\GetPerm;
use litepubl\admin\Link;
use litepubl\core\UserGroups;
use litepubl\view\Args;
use litepubl\view\Lang;

class Groups extends \litepubl\admin\Menu
{

    public function getContent(): string
    {
        $groups = UserGroups::i();
        $admin = $this->admintheme;
        $lang = Lang::admin('users');
        $args = new Args();
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
                $result.= $admin->form(
                    '
      [text=title]
      [text=name]
      [text=home]
      [hidden=action]
' . $admin->h($lang->parentgroups) . GetPerm::groups([]),
                    $args
                );
                break;


            case 'edit':
                $result.= $admin->help($lang->notegroup);
                $args->add($groups->items[$id]);
                $args->id = $id;
                $args->action = 'edit';
                $args->formtitle = $lang->editgroup;
                $result.= $admin->form(
                    '
      [text=title]
      [text=name]
      [text=home]
      [hidden=id]
      [hidden=action]
' . $admin->h($lang->parentgroups) . GetPerm::groups($groups->items[$id]['parents']),
                    $args
                );
                break;


            case 'delete':
                $result.= $this->confirmDeleteItem($groups);
                break;
        }

        $tb = $this->newTable();
        $tb->setStruct(
            [
            [
                $lang->name,
                '<a href="' . $adminurl . '=$id&action=edit" title="$title">$title</a>'
            ] ,
            [
                $lang->users,
                sprintf('<a href="%s">%s</a>', Link::url('/admin/users/?idgroup=$id'), $lang->users)
            ] ,
            $tb->action('delete', $adminurl)
            ]
        );

        $result.= $admin->h($lang->grouptable);
        $result.= $tb->build($groups->items);
        return $result;
    }

    public function processForm()
    {
        $groups = UserGroups::i();
        $admin = $this->admintheme;
        switch ($this->action) {
            case 'add':
                $groups->lock();
                $id = $groups->add($_POST['name'], $_POST['title'], $_POST['home']);
                $groups->items[$id]['parents'] = $admin->check2array('idgroup-');
                $groups->unlock();
                $_POST['id'] = $id;
                $_GET['id'] = $id;
                $_GET['action'] = 'edit';
                break;


            case 'edit':
                $id = $this->idget();
                if ($groups->itemExists($id)) {
                    foreach ([
                    'name',
                    'title',
                    'home'
                    ] as $name) {
                        $groups->items[$id][$name] = $_POST[$name];
                    }
                    $groups->items[$id]['parents'] = $admin->check2array('idgroup-');
                    $groups->save();
                }
                break;
        }
    }
}
