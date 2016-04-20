<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\comments;
use litepubl\view\Lang;
use litepubl\view\Vars;
use litepubl\view\Args;
use litepubl\view\Filter;
use litepubl\post\Post;
use litepubl\perms\Perm;
use litepubl\core\Users;
use litepubl\core\UserOptions;
use litepubl\core\Session;
use litepubl\pages\Simple;
use litepubl\core\Str;

class Form extends \litepubl\core\Events
 {
    public $helper;

    protected function create() {
        parent::create();
        $this->basename = 'commentform';
        $this->cache = false;
        $this->addevents('oncomuser');
    }

    public function request($arg) {
        if ( $this->getApp()->options->commentsdisabled) {
 return 404;
}


        if ('POST' != $_SERVER['REQUEST_METHOD']) {
            return "<?php
      header('HTTP/1.1 405 Method Not Allowed', true, 405);
      header('Allow: POST');
      header('Content-Type: text/plain');
      ?>";
        }

        return $this->dorequest($_POST);
    }

    public function dorequest(array $args) {
        if (isset($args['confirmid'])) {
 return $this->confirm_recevied($args['confirmid']);
}


        return $this->processForm($args, false);
    }

    public function getShortpost($id) {
        $id = (int)$id;
        if ($id == 0) {
 return false;
}


        $db =  $this->getApp()->db;
        return $db->selectassoc("select id, idurl, idperm, status, comstatus, commentscount from $db->posts where id = $id");
    }

    public function invalidate(array $shortpost) {
        $lang = Lang::i('comment');
        if (!$shortpost) {
            return $this->geterrorcontent($lang->postnotfound);
        }

        if ($shortpost['status'] != 'published') {
            return $this->geterrorcontent($lang->commentondraft);
        }

        if ($shortpost['comstatus'] == 'closed') {
            return $this->geterrorcontent($lang->commentsdisabled);
        }

        return false;
    }

    public function processForm(array $values, $confirmed) {
        $lang = Lang::i('comment');
        if (trim($values['content']) == '') {
 return $this->geterrorcontent($lang->emptycontent);
}


        if (!$this->checkspam(isset($values['antispam']) ? $values['antispam'] : '')) {
            return $this->geterrorcontent($lang->spamdetected);
        }

        $shortpost = $this->getshortpost(isset($values['postid']) ? (int)$values['postid'] : 0);
        if ($err = $this->invalidate($shortpost)) {
 return $err;
}


        if ((int)$shortpost['idperm']) {
            $post = Post::i((int)$shortpost['id']);
            $perm = Perm::i($post->idperm);
            if (!$perm->hasperm($post)) {
return 403;
}
        }

        $cm = Manager::i();
        if ($cm->checkduplicate && $cm->is_duplicate($shortpost['id'], $values['content'])) {
            return $this->geterrorcontent($lang->duplicate);
        }

        unset($values['submitbutton']);

        if (!$confirmed) $values['ip'] = preg_replace('/[^0-9., ]/', '', $_SERVER['REMOTE_ADDR']);
        if ( $this->getApp()->options->ingroups($cm->idgroups)) {
            if (!$confirmed && $cm->confirmlogged) {
 return $this->request_confirm($values, $shortpost);
}


            $iduser =  $this->getApp()->options->user;
        } else {
            switch ($shortpost['comstatus']) {
                case 'reg':
                    return $this->geterrorcontent($lang->reg);

                case 'guest':
                    if (!$confirmed && $cm->confirmguest) {
                        return $this->request_confirm($values, $shortpost);
                    }

                    $iduser = $cm->idguest;
                    break;


                case 'comuser':
                    //hook in regservices social plugin
                    if ($r = $this->oncomuser($values, $confirmed)) {
                        return $r;
                    }

                    if (!$confirmed && $cm->confirmcomuser) {
                        return $this->request_confirm($values, $shortpost);
                    }

                    if ($err = $this->processcomuser($values)) {
                        return $err;
                    }

                    $users = Users::i();
                    if ($iduser = $users->emailexists($values['email'])) {
                        if ('comuser' != $users->getvalue($iduser, 'status')) {
                            return $this->geterrorcontent($lang->emailregistered);
                        }
                    } else {
                        $iduser = $cm->addcomuser($values['name'], $values['email'], $values['url'], $values['ip']);
                    }

                    $cookies = array();
                    foreach (array(
                        'name',
                        'email',
                        'url'
                    ) as $field) {
                        $cookies["comuser_$field"] = $values[$field];
                    }
                    break;
            }
        }

        $user = Users::i()->getitem($iduser);
        if ('hold' == $user['status']) {
            return $this->geterrorcontent($lang->holduser);
        }

        if (!$cm->canadd($iduser)) {
            return $this->geterrorcontent($lang->toomany);
        }

        if (!$cm->add($shortpost['id'], $iduser, $values['content'], $values['ip'])) {
            return $this->geterrorcontent($lang->spamdetected);
        }

        //subscribe by email
        switch ($user['status']) {
            case 'approved':
                if ($user['email'] != '') {
                    // subscribe if its first comment
                    if (1 == Comments::i()->db->getcount("post = {$shortpost['id']} and author = $iduser")) {
                        if ('enabled' == UserOptions::i()->getvalue($iduser, 'subscribe')) {
                            Subscribers::i()->update($shortpost['id'], $iduser, true);
                        }
                    }
                }
                break;


            case 'comuser':
                if (('comuser' == $shortpost['comstatus']) && $cm->comuser_subscribe) {
                    Subscribers::i()->update($shortpost['id'], $iduser, $values['subscribe']);
                }
                break;
        }

        //$post->lastcommenturl;
        $shortpost['commentscount']++;
        if (! $this->getApp()->options->commentpages || ($shortpost['commentscount'] <=  $this->getApp()->options->commentsperpage)) {
            $c = 1;
        } else {
            $c = ceil($shortpost['commentscount'] /  $this->getApp()->options->commentsperpage);
        }

        $url =  $this->getApp()->router->getvalue($shortpost['idurl'], 'url');
        if (($c > 1) && ! $this->getApp()->options->comments_invert_order) $url = rtrim($url, '/') . "/page/$c/";

         $this->getApp()->router->setexpired($shortpost['idurl']);
        return $this->sendresult( $this->getApp()->site->url . $url, isset($cookies) ? $cookies : array());
    }

    public function confirm_recevied($confirmid) {
        $lang = Lang::i('comment');
        Session::start(md5($confirmid));
        if (!isset($_SESSION['confirmid']) || ($confirmid != $_SESSION['confirmid'])) {
            session_destroy();
            return $this->geterrorcontent($lang->notfound);
        }

        $values = $_SESSION['values'];
        session_destroy();
        return $this->processForm($values, true);
    }

    public function request_confirm(array $values, array $shortpost) {
        $values['date'] = time();
        $values['ip'] = preg_replace('/[^0-9., ]/', '', $_SERVER['REMOTE_ADDR']);

        $confirmid = Str::md5Uniq();
        if ($sess = Session::start(md5($confirmid))) $sess->lifetime = 900;
        $_SESSION['confirmid'] = $confirmid;
        $_SESSION['values'] = $values;
        session_write_close();

        if ((int)$shortpost['idperm']) {
            $header = $this->getpermheader($shortpost);
            return $header . $this->confirm($confirmid);
        }

        return $this->confirm($confirmid);
    }

    public function getPermheader(array $shortpost) {
        $router =  $this->getApp()->router;
        $url = $router->url;
        $saveitem = $router->item;
        $router->item = $router->getitem($shortpost['idurl']);
        $router->url = $router->itemrequested['url'];
        $post = Post::i((int)$shortpost['id']);
        $perm = Perm::i($post->idperm);
        // not restore values because perm will be used this values
        return $perm->getheader($post);
    }

    private function getConfirmform($confirmid) {
$vars = new Vars();
        $vars->lang = Lang::i('comment');
        $args = new Args();
        $args->confirmid = $confirmid;
        $theme = Simple::i()->getSchema()->theme;
        return $theme->parsearg($theme->templates['content.post.templatecomments.confirmform'], $args);
    }

    //htmlhelper
    public function confirm($confirmid) {
        if (isset($this->helper) && ($this != $this->helper)) {
return $this->helper->confirm($confirmid);
}

        return Simple::html($this->getconfirmform($confirmid));
    }

    public function getErrorcontent($s) {
        if (isset($this->helper) && ($this != $this->helper)) {
return $this->helper->geterrorcontent($s);
}

        return Simple::content($s);
    }

    private function checkspam($s) {
        if (!($s = @base64_decode($s))) {
 return false;
}


        $sign = 'superspamer';
        if (!Str::begin($s, $sign)) {
 return false;
}


        $TimeKey = (int)substr($s, strlen($sign));
        return time() < $TimeKey;
    }

    public function processcomuser(array & $values) {
        $lang = Lang::i('comment');
        if (empty($values['name'])) {
 return $this->geterrorcontent($lang->emptyname);
}


        $values['name'] = Filter::escape($values['name']);
        $values['email'] = isset($values['email']) ? strtolower(trim($values['email'])) : '';
        if (!Filter::ValidateEmail($values['email'])) {
            return $this->geterrorcontent($lang->invalidemail);
        }

        $values['url'] = isset($values['url']) ? Filter::escape(Filter::clean_website($values['url'])) : '';
        $values['subscribe'] = isset($values['subscribe']);
    }

    public function sendresult($link, $cookies) {
        if (isset($this->helper) && ($this != $this->helper)) {
 return $this->helper->sendresult($link, $cookies);
}


        foreach ($cookies as $name => $value) {
            setcookie($name, $value, time() + 30000000, '/', false);
        }

        return  $this->getApp()->router->redir($link);
    }

} //class