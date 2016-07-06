<?php
/**
 * Lite Publisher CMS
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
  */

namespace litepubl\comments;

use litepubl\Config;
use litepubl\core\Context;
use litepubl\core\Str;
use litepubl\core\Users;
use litepubl\utils\Mailer;
use litepubl\view\Args;
use litepubl\view\Filter;
use litepubl\view\Lang;
use litepubl\view\Theme;
use litepubl\view\Vars;

class Manager extends \litepubl\core\Events implements \litepubl\core\ResponsiveInterface
{
    use \litepubl\core\PoolStorageTrait;

    protected function create()
    {
        parent::create();
        $this->basename = 'commentmanager';
        $this->addevents('onchanged', 'approved', 'comuseradded', 'is_spamer', 'oncreatestatus');
    }

    public function getCount()
    {
        $this->getApp()->db->table = 'comments';
        return $this->getApp()->db->getcount();
    }

    public function addcomuser($name, $email, $website, $ip)
    {
        $users = Users::i();
        $id = $users->add(
            array(
            'email' => strtolower(trim($email)) ,
            'name' => $name,
            'website' => Filter::clean_website($website) ,
            'status' => 'comuser',
            'idgroups' => 'commentator'
            )
        );

        if ($id) {
            $this->comuseradded($id);
        }
        return $id;
    }

    public function add($idpost, $idauthor, $content, $ip)
    {
        $status = $this->createstatus($idpost, $idauthor, $content, $ip);
        if (!$status) {
            return false;
        }

        $comments = Comments::i();
        return $comments->add($idpost, $idauthor, $content, $status, $ip);
    }

    public function reply($idparent, $content)
    {
        $idauthor = 1; //admin
        $comments = Comments::i();
        $idpost = $comments->getvalue($idparent, 'post');
        $id = $comments->add($idpost, $idauthor, $content, 'approved', '');
        $comments->setvalue($id, 'parent', $idparent);
        return $id;
    }

    public function changed($id)
    {
        $comments = Comments::i();
        $idpost = $comments->getValue($id, 'post');
        $count = $comments->db->getcount("post = $idpost and status = 'approved'");
        $comments->getDB('posts')->setValue($idpost, 'commentscount', $count);
        if ($this->getApp()->options->commentspool) {
            Pool::i()->set($idpost, $count);
        }

        //update trust
        try {
            $idauthor = $comments->getvalue($id, 'author');
            $users = Users::i();
            if ($this->trustlevel > (int)$users->getvalue($idauthor, 'trust')) {
                $trust = $comments->db->getcount("author = $idauthor and status = 'approved' limit " . ($this->trustlevel + 1));
                $users->setvalue($idauthor, 'trust', $trust);
            }
        } catch (\Exception $e) {
        }

        $this->onchanged($id);
    }

    public function sendMail($id)
    {
        if ($this->sendnotification) {
            $this->getApp()->onClose->on($this, 'send_mail', $id);
        }
    }

    public function send_mail($id)
    {
        $comments = Comments::i();
        $comment = $comments->getcomment($id);
        //ignore admin comments
        if ($comment->author == 1) {
            return;
        }
        $vars = new Vars();
        $vars->comment = $comment;
        $args = new Args();
        $adminurl = $this->getApp()->site->url . '/admin/comments/' . $this->getApp()->site->q . "id=$id";
        $ref = md5(Config::$secret . $adminurl . $this->getApp()->options->solt);
        $adminurl.= "&ref=$ref&action";
        $args->adminurl = $adminurl;

        Lang::usefile('mail');
        $lang = Lang::i('mailcomments');
        $theme = Theme::i();

        $subject = $theme->parseArg($lang->subject, $args);
        $body = $theme->parseArg($lang->body, $args);
        return Mailer::sendtoadmin($subject, $body, false);
    }

    public function createstatus($idpost, $idauthor, $content, $ip)
    {
        $status = $this->oncreatestatus($idpost, $idauthor, $content, $ip);
        if (false === $status) {
            return false;
        }

        if ($status == 'spam') {
            return false;
        }

        if (($status == 'hold') || ($status == 'approved')) {
            return $status;
        }

        if (!$this->filterstatus) {
            return $this->defstatus;
        }

        if ($this->defstatus == 'approved') {
            return 'approved';
        }

        return 'hold';
    }

    public function canadd($idauthor)
    {
        return !$this->is_spamer($idauthor);
    }

    public function is_duplicate($idpost, $content)
    {
        $comments = Comments::i($idpost);
        $content = trim($content);
        $hash = Str::baseMd5($content);
        return $comments->raw->findid("hash = '$hash'");
    }

    public function request(Context $context)
    {
        $response = $context->response;
        $id = isset($_GET['id']) ? (int)$_GET['id'] : 1;
        $users = Users::i();
        if (!$users->itemExists($id)) {
            return $response->redir('/');
        }

        $item = $users->getitem($id);
        $url = $item['website'];
        if (!strpos($url, '.')) {
            $url = $this->getApp()->site->url . '/';
        }

        if (!Str::begin($url, 'http://')) {
            $url = 'http://' . $url;
        }

        return $response->redir($url);
    }
}
