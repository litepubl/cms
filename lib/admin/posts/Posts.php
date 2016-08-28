<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.05
  */

namespace litepubl\admin\posts;

use litepubl\admin\AuthorRights;
use litepubl\admin\Link;
use litepubl\post\Post;
use litepubl\post\Posts as PostItems;
use litepubl\view\Args;
use litepubl\view\Lang;

class Posts extends \litepubl\admin\Menu
{
    private $isauthor;

    public function canrequest()
    {
        $this->isauthor = false;
        if (!$this->getApp()->options->hasgroup('editor')) {
            $this->isauthor = $this->getApp()->options->hasgroup('author');
        }
    }

    public function getContent(): string
    {
        if (isset($_GET['action']) && in_array(
            $_GET['action'], [
            'delete',
            'setdraft',
            'publish'
            ]
        )) {
            return $this->doaction(PostItems::i(), $_GET['action']);
        }

        return $this->gettable(PostItems::i(), $where = "status <> 'deleted' ");
    }

    public function doaction($posts, $action)
    {
        $id = $this->idget();
        if (!$posts->itemExists($id)) {
            return $this->notfound;
        }

        $post = Post::i($id);
        if ($this->isauthor && !AuthorRights::canStatus($action)) {
            return AuthorRights::getMessage();
        }

        if ($this->isauthor && ($this->getApp()->options->user != $post->author)) {
            return $this->notfound;
        }

        $admintheme = $this->admintheme;
        if (!$this->confirmed) {
            $args = new Args();
            $args->id = $id;
            $args->adminurl = $this->adminurl;
            $args->action = $action;
            $args->confirm = sprintf($this->lang->confirm, $this->lang->$action, $post->bookmark);
            return $admintheme->parseArg($admintheme->templates['confirmform'], $args);
        }

        switch ($_GET['action']) {
        case 'delete':
            $posts->delete($id);
            $result = $admintheme->h($lang->confirmeddelete);
            break;


        case 'setdraft':
            $post->status = 'draft';
            $posts->edit($post);
            $result = $admintheme->h($lang->confirmedsetdraft);
            break;


        case 'publish':
            $post->status = 'published';
            $posts->edit($post);
            $result = $admintheme->h($lang->confirmedpublish);
            break;
        }

        return $result;
    }

    public function getTable($posts, $where)
    {
        $perpage = 20;
        if ($this->isauthor) {
            $where.= ' and author = ' . $this->getApp()->options->user;
        }
        $count = $posts->db->getcount($where);
        $from = $this->getfrom($perpage, $count);
        $items = $posts->select($where, " order by posted desc limit $from, $perpage");
        if (!$items) {
            $items = [];
        }

        $admintheme = $this->admintheme;
        $lang = Lang::admin();
        $form = $this->newForm();
        $form->body = $admintheme->getcount($from, $from + count($items), $count);

        $tb = $this->newTable();
        $tb->setposts(
            [
            [
                'center',
                $lang->date,
                '$post.date'
            ] ,
            [
                $lang->posttitle,
                '$post.bookmark'
            ] ,
            [
                $lang->category,
                '$post.category'
            ] ,
            [
                $lang->status,
                '$poststatus'
            ] ,
            [
                $lang->edit,
                '<a href="' . Link::url('/admin/posts/editor/?id') . '=$post.id">' . $lang->edit . '</a>'
            ] ,
            [
                $lang->delete,
                "<a class=\"confirm-delete-link\" href=\"$this->adminurl=\$post.id&action=delete\">$lang->delete</a>"
            ] ,
            ]
        );

        $form->body.= $tb->build($items);
        $form->body.= $form->centergroup(
            '[button=publish]
    [button=setdraft]
    [button=delete]'
        );

        $form->submit = false;
        $result = $form->get();
        $result.= $this->theme->getpages('/admin/posts/', $this->getApp()->context->request->page, ceil($count / $perpage));
        return $result;
    }

    public function processForm()
    {
        $posts = PostItems::i();
        $posts->lock();
        $status = isset($_POST['publish']) ? 'published' : (isset($_POST['setdraft']) ? 'draft' : 'delete');
        if ($this->isauthor && !AuthorRights::canStatus($status)) {
            return AuthorRights::getMessage();
        }
        $iduser = $this->getApp()->options->user;
        foreach ($_POST as $key => $id) {
            if (!is_numeric($id)) {
                continue;
            }

            $id = (int)$id;
            if ($status == 'delete') {
                if ($this->isauthor && ($iduser != $posts->db->getvalue('author'))) {
                    continue;
                }

                $posts->delete($id);
            } else {
                $post = Post::i($id);
                if ($this->isauthor && ($iduser != $post->author)) {
                    continue;
                }

                $post->status = $status;
                $posts->edit($post);
            }
        }
        $posts->unlock();
    }
}
