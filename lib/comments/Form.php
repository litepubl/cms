<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

namespace litepubl\comments;

use litepubl\core\Context;
use litepubl\core\Session;
use litepubl\core\Str;
use litepubl\core\UserOptions;
use litepubl\core\Users;
use litepubl\pages\Simple;
use litepubl\perms\Perm;
use litepubl\post\Post;
use litepubl\view\Args;
use litepubl\view\Filter;
use litepubl\view\Lang;
use litepubl\view\Vars;

/**
 * Manage comment form data
 *
 * @property-write callable $onComuser
 * @method         array onComuser(array $params)
 */

class Form extends \litepubl\core\Events implements \litepubl\core\ResponsiveInterface
{
    use \litepubl\utils\TempProps;

    public $helper;

    protected function create()
    {
        parent::create();
        $this->basename = 'commentform';
        $this->addEvents('oncomuser');
    }

    public function request(Context $context)
    {
        $response = $context->response;
        $response->cache = false;

        if ($this->getApp()->options->commentsdisabled) {
            $response->status = 404;
            return;
        }

        if ('POST' != $_SERVER['REQUEST_METHOD']) {
            $response->status = 405;
            $response->headers['Allow'] = 'POST';
            $response->headers['Content-Type'] = 'text/plain';
            return;
        }

        $temp = $this->newProps();
        $temp->context = $context;
        $response->body .= $this->doRequest($context->request->getPost());
    }

    public function doRequest(array $args)
    {
        if (isset($args['confirmid'])) {
            return $this->confirmRecevied($args['confirmid']);
        }

        return $this->processForm($args, false);
    }

    public function getShortPost(int $id)
    {
        if ($id == 0) {
            return false;
        }

        $db = $this->getApp()->db;
        return $db->selectAssoc("select id, idurl, idperm, status, comstatus, commentscount from $db->posts where id = $id");
    }

    public function invalidate(array $shortpost): string
    {
        $lang = Lang::i('comment');
        if (!$shortpost) {
            return $this->getErrorContent($lang->postnotfound);
        }

        if ($shortpost['status'] != 'published') {
            return $this->getErrorContent($lang->commentondraft);
        }

        if ($shortpost['comstatus'] == 'closed') {
            return $this->getErrorContent($lang->commentsdisabled);
        }

        return '';
    }

protected function checkEmpty(array $values): string
{
        $lang = Lang::i('comment');
        if (!trim($values['content'])) {
            return $this->getErrorContent($lang->emptycontent);
        }

        if (!$this->checkspam(isset($values['antispam']) ? $values['antispam'] : '')) {
            return $this->getErrorContent($lang->spamdetected);
        }

return '';
}

    public function processForm(array $values, $confirmed)
    {
if ($error = $this->checkEmpty($values)) {
return $error;
}

        $app = $this->getApp();
        $lang = Lang::i('comment');
        $shortpost = $this->getshortpost(isset($values['postid']) ? (int)$values['postid'] : 0);
        if ($err = $this->invalidate($shortpost)) {
            return $err;
        }

if (!$this->hasPerm($shortpost)) {
return $this->context->response->forbidden();
}

        $cm = Manager::i();
        if ($cm->checkduplicate && $cm->is_duplicate($shortpost['id'], $values['content'])) {
            return $this->getErrorContent($lang->duplicate);
        }

        unset($values['submitbutton']);

        if (!$confirmed) {
            $values['ip'] = preg_replace('/[^0-9., ]/', '', $_SERVER['REMOTE_ADDR']);
        }

        if ($app->options->ingroups($cm->idgroups)) {
            if (!$confirmed && $cm->confirmlogged) {
                return $this->requestConfirm($values, $shortpost);
            }

            $iduser = $app->options->user;
        } else {
            switch ($shortpost['comstatus']) {
            case 'reg':
                return $this->getErrorContent($lang->reg);

            case 'guest':
                if (!$confirmed && $cm->confirmguest) {
                    return $this->requestConfirm($values, $shortpost);
                }

                $iduser = $cm->idguest;
                break;


            case 'comuser':
                //hook in regservices social plugin
                $r = $this->oncomuser(
                    [
                    'values' => $values,
                    'confirmid' => $confirmed,
                    'result' => false
                    ]
                );
                if ($r['result']) {
                    return $r['result'];
                }

                if (!$confirmed && $cm->confirmcomuser) {
                    return $this->requestConfirm($values, $shortpost);
                }

                if ($err = $this->processcomuser($values)) {
                    return $err;
                }

                $users = Users::i();
                if ($iduser = $users->emailExists($values['email'])) {
                    if ('comuser' != $users->getValue($iduser, 'status')) {
                        return $this->getErrorContent($lang->emailregistered);
                    }
                } else {
                    $iduser = $cm->addcomUser($values['name'], $values['email'], $values['url'], $values['ip']);
                }

                $cookies = [];
                foreach ([
                'name',
                'email',
                'url'
                ] as $field) {
                    $cookies["comuser_$field"] = $values[$field];
                }
                break;
            }
        }

        $user = Users::i()->getItem($iduser);
        if ('hold' == $user['status']) {
            return $this->getErrorContent($lang->holduser);
        }

        if (!$cm->canAdd($iduser)) {
            return $this->getErrorContent($lang->toomany);
        }

        if (!$cm->add($shortpost['id'], $iduser, $values['content'], $values['ip'])) {
            return $this->getErrorContent($lang->spamdetected);
        }

        //subscribe by email
        switch ($user[Users::STATUS]) {
        case Users::APPROVED:
            if ($user['email'] != '') {
                // subscribe if its first comment
                if (1 == Comments::i()->db->getcount("post = {$shortpost['id']} and author = $iduser")) {
                    if ('enabled' == UserOptions::i()->getvalue($iduser, 'subscribe')) {
                        Subscribers::i()->update($shortpost['id'], $iduser, true);
                    }
                }
            }
            break;


        case Users::COMUSER:
            if (('comuser' == $shortpost['comstatus']) && $cm->comuser_subscribe) {
                Subscribers::i()->update($shortpost['id'], $iduser, $values['subscribe']);
            }
            break;
        }

        //$post->lastcommenturl;
        $shortpost['commentscount']++;
        if (!$app->options->commentpages || ($shortpost['commentscount'] <= $app->options->commentsperpage)) {
            $c = 1;
        } else {
            $c = ceil($shortpost['commentscount'] / $app->options->commentsperpage);
        }

        $url = $app->router->getvalue($shortpost['idurl'], 'url');
        if (($c > 1) && !$app->options->comments_invert_order) {
            $url = rtrim($url, '/') . "/page/$c/";
        }

        $app->cache->clearUrl($url);
        return $this->sendResult($app->site->url . $url, isset($cookies) ? $cookies : []);
    }

    public function confirmRecevied($confirmid)
    {
        $lang = Lang::i('comment');
        Session::start(md5($confirmid));
        if (!isset($_SESSION['confirmid']) || ($confirmid != $_SESSION['confirmid'])) {
            session_destroy();
            return $this->getErrorContent($lang->notfound);
        }

        $values = $_SESSION['values'];
        session_destroy();
        return $this->processForm($values, true);
    }

    protected function requestConfirm(array $values, array $shortpost)
    {
        $values['date'] = time();
        $values['ip'] = preg_replace('/[^0-9., ]/', '', $_SERVER['REMOTE_ADDR']);

        $confirmid = Str::md5Uniq();
        if ($sess = Session::start(md5($confirmid))) {
            $sess->lifetime = 900;
        }
        $_SESSION['confirmid'] = $confirmid;
        $_SESSION['values'] = $values;
        session_write_close();

        if ((int)$shortpost['idperm']) {
$this->getPermHeader($shortpost);
        }

        return $this->confirm($confirmid);
    }

    protected function getPermheader(array $shortpost)
    {
        $post = Post::i((int)$shortpost['id']);
        $perm = Perm::i($post->idperm);
        $perm->setResponse($this->context->response, $post);
    }

protected function hasPerm(array $shortpost): bool
{
        if ((int)$shortpost['idperm']) {
            $post = Post::i((int)$shortpost['id']);
            $perm = Perm::i($post->idperm);
return $perm->hasperm($post);
        }

return true;
}

    private function getConfirmform($confirmid)
    {
        $vars = new Vars();
        $vars->lang = Lang::i('comment');
        $args = new Args();
        $args->confirmid = $confirmid;
        $theme = Simple::i()->getSchema()->theme;
        return $theme->parseArg($theme->templates['content.post.templatecomments.confirmform'], $args);
    }

    //htmlhelper
    public function confirm($confirmid)
    {
        if (isset($this->helper) && ($this != $this->helper)) {
            return $this->helper->confirm($confirmid);
        }

        return Simple::html($this->getconfirmform($confirmid));
    }

    public function getErrorContent($s)
    {
        if (isset($this->helper) && ($this != $this->helper)) {
            return $this->helper->getErrorContent($s);
        }

        return Simple::content($s);
    }

    private function checkspam($s)
    {
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

    public function processcomuser(array & $values)
    {
        $lang = Lang::i('comment');
        if (empty($values['name'])) {
            return $this->getErrorContent($lang->emptyname);
        }

        $values['name'] = Filter::escape($values['name']);
        $values['email'] = isset($values['email']) ? strtolower(trim($values['email'])) : '';
        if (!Filter::ValidateEmail($values['email'])) {
            return $this->getErrorContent($lang->invalidemail);
        }

        $values['url'] = isset($values['url']) ? Filter::escape(Filter::clean_website($values['url'])) : '';
        $values['subscribe'] = isset($values['subscribe']);
    }

    public function sendResult($link, $cookies)
    {
        if (isset($this->helper) && ($this != $this->helper)) {
            return $this->helper->sendresult($link, $cookies);
        }

        foreach ($cookies as $name => $value) {
            setcookie($name, $value, time() + 30000000, '/', false);
        }

        return $this->context->response->redir($link);
    }
}
