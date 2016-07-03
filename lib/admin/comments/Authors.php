<?php
/**
* 
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
 *
 */


namespace litepubl\admin\comments;

use litepubl\admin\Link;
use litepubl\comments\Comments as CommentItems;
use litepubl\core\Users;
use litepubl\view\Args;

class Authors extends \litepubl\admin\Menu
{

    public function getContent(): string
    {
        $result = '';
        $this->basename = 'authors';
        $users = Users::i();
        $admin = $this->admintheme;
        $lang = $this->lang;

        if ('delete' == $this->action) {
            $id = $this->idget();
            if (!$users->itemExists($id)) {
                return $this->notfound();
            }

            if (!$this->confirmed) {
                return $this->confirmDelete($id, $lang->confirmdelete);
            }

            if (!$this->deleteAuthor($id)) {
                return $this->notfount;
            }

            $result.= $admin->success($lang->deleted);
        }

        $args = new Args();
        $perpage = 20;
        $total = $users->db->getcount("status = 'comuser'");
        $from = $this->getfrom($perpage, $total);
        $db = $users->db;
        $res = $db->query("select * from $users->thistable where status = 'comuser' order by id desc limit $from, $perpage");
        $items = $db->res2assoc($res);

        $result.= $admin->getcount($from, $from + count($items), $total);
        $adminurl = $this->adminurl;
        $editurl = Link::url('/admin/users/?id');
        $tb = $this->newTable();
        $tb->setStruct(
            array(
            array(
                $lang->author,
                '$name'
            ) ,

            array(
                'E-Mail',
                '$email'
            ) ,

            array(
                $lang->website,
                '$website'
            ) ,

            array(
                $lang->edit,
                "<a href='$editurl=\$id&action=edit'>$lang->edit</a>"
            ) ,

            array(
                $lang->delete,
                "<a href='$adminurl=\$id&action=delete'>$lang->delete</a>"
            )
            )
        );

        $result.= $tb->build($items);
        $result.= $this->theme->getpages($this->url, $this->getApp()->context->request->page, ceil($total / $perpage));
        return $result;
    }

    private function deleteAuthor($uid)
    {
        $users = Users::i();
        if (!$users->itemExists($uid)) {
            return false;
        }

        if ('comuser' != $users->getvalue($uid, 'status')) {
            return false;
        }

        $comments = CommentItems::i();
        $comments->db->delete("author = $uid");
        $users->setvalue($uid, 'status', 'hold');
        return true;
    }
}
