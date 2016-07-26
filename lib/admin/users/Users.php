<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.02
  */

namespace litepubl\admin\users;

use litepubl\admin\Form;
use litepubl\admin\GetPerm;
use litepubl\admin\Link;
use litepubl\core\UserGroups;
use litepubl\core\Users as UserItems;
use litepubl\pages\Users as UserPages;
use litepubl\view\Args;
use litepubl\view\Lang;

class Users extends \litepubl\admin\Menu
{

    public function getContent(): string
    {
        $result = '';
        $users = UserItems::i();
        $groups = UserGroups::i();

        $admin = $this->admintheme;
        $lang = Lang::i('users');
        $args = new Args();

        $id = $this->idget();
        switch ($this->action) {
        case 'edit':
            if (!$users->itemExists($id)) {
                $result.= $this->notfound();
            } else {
                $statuses = array();
                foreach (array(
                'approved',
                'hold',
                'comuser'
                ) as $name) {
                    $statuses[$name] = $lang->$name;
                }

                $item = $users->getitem($id);
                $args->add($item);
                $args->registered = UserPages::i()->getvalue($id, 'registered');
                $args->formtitle = $item['name'];
                $args->status = $this->theme->comboItems($statuses, $item['status']);

                $tabs = $this->newTabs();
                $tabs->add($lang->login, '[text=email] [password=password]');
                $tabs->add(
                    $lang->groups, '
[combo=status]
' . GetPerm::groups($item['idgroups'])
                );

                $tabs->add(
                    'Cookie', '
[text=cookie]
 [text=expired]
 [text=registered]
 [text=trust]
'
                );

                $args->password = '';
                $result.= $admin->form($tabs->get(), $args);
            }
            break;


        case 'delete':
            $result.= $this->confirmDeleteItem($users);
            break;


        default:
            $args->formtitle = $lang->newuser;
            $args->email = '';
            $args->action = 'add';

            $tabs = $this->newTabs();
            $tabs->add($lang->login, '[text=email] [password=password] [text=name] [hidden=action]');
            $tabs->add($lang->groups, GetPerm::groups(array()));

            $result.= $admin->form($tabs->get(), $args);
        }

        $args->search = '';

        //table
        $perpage = 20;
        $count = $users->count;
        $from = $this->getfrom($perpage, $count);
        $where = '';
        $params = '';
        if (!empty($_GET['idgroup'])) {
            $idgroup = (int)$this->getparam('idgroup', 0);
            if ($groups->itemExists($idgroup)) {
                $grouptable = $this->getApp()->db->prefix . $users->grouptable;
                $where = "$users->thistable.id in (select iduser from $grouptable where idgroup = $idgroup)";
                $params = "idgroup=$idgroup";
            }
        } elseif ($search = trim($this->getparam('search', ''))) {
            $params = 'search=' . urlencode($search);
            $args->search = $search;
            $search = $this->getApp()->db->escape($search);
            $search = strtr(
                $search, array(
                '%' => '\%',
                '_' => '\_'
                )
            );

            $where = "email like '%$search%' or name like '%$search%' ";
            $count = $users->db->getcount($where);
            $from = $this->getfrom($perpage, $count);
        }

        $items = $users->select($where, " order by id desc limit $from, $perpage");
        if (!$items) {
            $items = array();
        }

        $tb = $this->newTable();
        $tb->args->adminurl = $this->adminurl;
        $tb->setowner($users);
        $tb->setStruct(
            array(
            $tb->checkbox('user') ,
            array(
                $lang->edit,
                sprintf('<a href="%s=$id&action=edit">$name</a>', $this->adminurl)
            ) ,

            array(
                $lang->status,
                '$status'
            ) ,

            array(
                $lang->comments,
                sprintf('<a href="%s">%s</a>', Link::url('/admin/comments/', 'iduser=$id'), $lang->comments)
            ) ,

            array(
                $lang->page,
                sprintf('<a href="%s">%s</a>', Link::url('/admin/users/pages/', 'id=$id'), $lang->page)
            ) ,
            )
        );

        $form = new Form($args);
        $form->title = $lang->userstable;
        $result.= $form->getdelete($tb->build($items));

        $result.= $this->theme->getpages($this->url, $this->getApp()->context->request->page, ceil($count / $perpage), $params);

        $form = new Form($args);
        $form->method = 'get';
        $form->inline = true;
        $form->body = '[text=search]';
        $form->submit = 'find';
        $result.= $form->get();
        return $result;
    }

    public function processForm()
    {
        $users = UserItems::i();
        $groups = UserGroups::i();

        if (isset($_POST['delete'])) {
            foreach ($_POST as $key => $value) {
                if (!is_numeric($value)) {
                    continue;
                }

                $id = (int)$value;
                $users->delete($id);
            }

            return;
        }

        switch ($this->action) {
        case 'add':
            $_POST['idgroups'] = $this->admintheme->check2array('idgroup-');
            if ($id = $users->add($_POST)) {
                $this->getApp()->context->response->redir("$this->adminurl=$id&action=edit");
            } else {
                return $this->admintheme->geterr($this->lang->invalidregdata);
            }
            break;


        case 'edit':
            $id = $this->idget();
            if (!$users->itemExists($id)) {
                return;
            }

            $_POST['idgroups'] = $this->admintheme->check2array('idgroup-');
            if (!$users->edit($id, $_POST)) {
                return $this->notfound;
            }

            if ($id == 1) {
                $this->getApp()->site->author = $_POST['name'];
            }
            break;
        }
    }
}
