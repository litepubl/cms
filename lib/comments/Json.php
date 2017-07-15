<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\comments;

use litepubl\view\Lang;
use litepubl\view\Theme;

class Json extends \litepubl\core\Events
{

    public function auth($id, $action)
    {
        if (!$this->getApp()->options->user) {
            return false;
        }

        $comments = Comments::i();
        if (!$comments->itemExists($id)) {
            return false;
        }

        if ($this->getApp()->options->ingroup('moderator')) {
            return true;
        }

        $cm = Manager::i();
        switch ($action) {
            case 'edit':
                if (!$cm->canedit) {
                    return false;
                }

                if ('closed' == $this->getApp()->db->getval('posts', $comments->getvalue($id, 'post'), 'comstatus')) {
                    return false;
                }

                return $comments->getvalue($id, 'author') == $this->getApp()->options->user;

            case 'delete':
                if (!$cm->candelete) {
                    return false;
                }

                if ('closed' == $this->getApp()->db->getval('posts', $comments->getvalue($id, 'post'), 'comstatus')) {
                    return false;
                }

                return $comments->getvalue($id, 'author') == $this->getApp()->options->user;
        }

        return false;
    }

    public function forbidden()
    {
        $this->error('Forbidden', 403);
    }

    public function comment_delete(array $args)
    {
        $id = (int)$args['id'];
        if (!$this->auth($id, 'delete')) {
            return $this->forbidden();
        }

        return Comments::i()->delete($id);
    }

    public function comment_setstatus($args)
    {
        $id = (int)$args['id'];
        if (!$this->auth($id, 'status')) {
            return $this->forbidden();
        }

        return Comments::i()->setstatus($id, $args['status']);
    }

    public function comment_edit(array $args)
    {
        $id = (int)$args['id'];
        if (!$this->auth($id, 'edit')) {
            return $this->forbidden();
        }

        $content = trim($args['content']);
        if (empty($content)) {
            return false;
        }

        $comments = Comments::i();
        if ($comments->edit($id, $content)) {
            return [
                'id' => $id,
                'content' => $comments->getvalue($id, 'content')
            ];
        } else {
            return false;
        }
    }

    public function comment_getraw(array $args)
    {
        $id = (int)$args['id'];
        if (!$this->auth($id, 'edit')) {
            return $this->forbidden();
        }

        $comments = Comments::i();
        $raw = $comments->raw->getvalue($id, 'rawcontent');
        return [
            'id' => $id,
            'rawcontent' => $raw
        ];
    }

    public function comments_get_hold(array $args)
    {
        if (!$this->getApp()->options->user) {
            return $this->forbidden();
        }

        $idpost = (int)$args['idpost'];
        $comments = Comments::i($idpost);

        if ($this->getApp()->options->ingroup('moderator')) {
            $where = '';
        } else {
            $where = "and $comments->thistable.author = " . $this->getApp()->options->user;
        }

        return [
            'items' => $comments->getcontentwhere('hold', $where)
        ];
    }

    public function comment_add(array $args)
    {
        if ($this->getApp()->options->commentsdisabled) {
            return [
                'error' => [
                    'message' => 'Comments disabled',
                    'code' => 403
                ]
            ];
        }

        $commentform = Form::i();
        $commentform->helper = $this;
        return $commentform->dorequest($args);
    }

    public function comment_confirm(array $args)
    {
        return $this->comment_add($args);
    }

    //commentform helper
    public function confirm($confirmid)
    {
        return [
            'confirmid' => $confirmid,
            'code' => 'confirm',
        ];
    }

    public function getErrorcontent($s)
    {
        return [
            'error' => [
                'message' => $s,
                'code' => 'error'
            ]
        ];
    }

    public function sendresult($url, $cookies)
    {
        return [
            'cookies' => $cookies,
            'posturl' => $url,
            'code' => 'success'
        ];
    }

    public function comments_get_logged(array $args)
    {
        if (!$this->getApp()->options->user) {
            return $this->forbidden();
        }

        $theme = Theme::context();
        $mesg = $theme->templates['content.post.templatecomments.form.mesg.logged'];
        $mesg = str_replace('$site.liveuser', $this->getApp()->site->getuserlink(), $mesg);

        $lang = Lang::i('comment');
        return $theme->parse($mesg);
    }
}
