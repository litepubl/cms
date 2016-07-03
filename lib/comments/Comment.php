<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 7.00
 *
 */


namespace litepubl\comments;

use litepubl\core\Str;
use litepubl\core\Users;
use litepubl\post\Post;
use litepubl\view\Filter;
use litepubl\view\Lang;
use litepubl\view\Theme;

class Comment extends \litepubl\core\Data
{
    private static $md5 = array();
    private $_posted;

    public function __construct($id = 0)
    {
        if (!isset($id)) {
            return false;
        }

        parent::__construct();
        $this->table = 'comments';
        $id = (int)$id;
        if ($id > 0) {
            $this->setid($id);
        }
    }

    public function setId($id)
    {
        $comments = Comments::i();
        $this->data = $comments->getItem($id);
        if (!isset($this->data['name'])) {
            $this->data = $this->data + Users::i()->getitem($this->data['author']);
        }

        $this->_posted = false;
    }

    public function save()
    {
        extract($this->data, EXTR_SKIP);
        $this->db->UpdateAssoc(compact('id', 'post', 'author', 'parent', 'posted', 'status', 'content'));

        $this->getdb($this->rawtable)->UpdateAssoc(
            array(
            'id' => $id,
            'modified' => Str::sqlDate() ,
            'rawcontent' => $rawcontent,
            'hash' => Str::baseMd5($rawcontent)
            )
        );
    }

    public function getAuthorlink()
    {
        $name = $this->data['name'];
        $website = $this->data['website'];
        if ($website == '') {
            return $name;
        }

        $manager = Manager::i();
        if ($manager->hidelink || ($this->trust <= $manager->trustlevel)) {
            return $name;
        }

        $rel = $manager->nofollow ? 'rel="nofollow"' : '';
        if ($manager->redir) {
            return sprintf('<a %s href="%s/comusers.htm%sid=%d">%s</a>', $rel, $this->getApp()->site->url, $this->getApp()->site->q, $this->author, $name);
        } else {
            if (!Str::begin($website, 'http://')) {
                $website = 'http://' . $website;
            }
            return sprintf('<a class="url fn" %s href="%s" itemprop="url">%s</a>', $rel, $website, $name);
        }
    }

    public function getDate()
    {
        $theme = Theme::i();
        return Lang::date($this->posted, $theme->templates['content.post.templatecomments.comments.comment.date']);
    }

    public function getLocalStatus()
    {
        return Lang::get('commentstatus', $this->status);
    }

    public function getPosted()
    {
        if ($this->_posted) {
            return $this->_posted;
        }

        return $this->_posted = strtotime($this->data['posted']);
    }

    public function setPosted($date)
    {
        $this->data['posted'] = Str::sqlDate($date);
        $this->_posted = $date;
    }

    public function getTime()
    {
        return date('H:i', $this->posted);
    }

    public function getIso()
    {
        return date('c', $this->posted);
    }

    public function getRfc()
    {
        return date('r', $this->posted);
    }

    public function getUrl()
    {
        $post = Post::i($this->post);
        return $post->link . "#comment-$this->id";
    }

    public function getPosttitle()
    {
        $post = Post::i($this->post);
        return $post->title;
    }

    public function getRawcontent()
    {
        if (isset($this->data['rawcontent'])) {
            return $this->data['rawcontent'];
        }

        $comments = Comments::i($this->post);
        return $comments->raw->getvalue($this->id, 'rawcontent');
    }

    public function setRawcontent($s)
    {
        $this->data['rawcontent'] = $s;
        $filter = Filter::i();
        $this->data['content'] = $filter->filtercomment($s);
    }

    public function getIp()
    {
        if (isset($this->data['ip'])) {
            return $this->data['ip'];
        }

        $comments = Comments::i($this->post);
        return $comments->raw->getvalue($this->id, 'ip');
    }

    public function getMd5email()
    {
        $email = $this->data['email'];
        if ($email) {
            if (isset(static ::$md5[$email])) {
                return static ::$md5[$email];
            }

            $md5 = md5($email);
            static ::$md5[$email] = $md5;
            return $md5;
        }
        return '';
    }

    public function getGravatar()
    {
        if ($md5email = $this->getmd5email()) {
            return sprintf('<img class="avatar photo" src="http://www.gravatar.com/avatar/%s?s=90&amp;r=g&amp;d=wavatar" title="%2$s" alt="%2$s"/>', $md5email, $this->name);
        } else {
            return '';
        }
    }
}
