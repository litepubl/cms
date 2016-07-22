<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.01
  */

namespace litepubl\admin\pages;

use litepubl\admin\Form as AdminForm;
use litepubl\core\Context;
use litepubl\core\Session;
use litepubl\core\Str;
use litepubl\core\UserGroups;
use litepubl\core\Users;
use litepubl\utils\Mailer;
use litepubl\view\Args;
use litepubl\view\Filter;
use litepubl\view\Lang;
use litepubl\view\Theme;

/**
 * User regestration page
 *
 * @property-write callable $onContent
 * @method         array onContent(array $params)
 */

class RegUser extends Form
{
    private $regstatus;
    private $backurl;
    private $trusted;
    public $blackhost;

    protected function create()
    {
        parent::create();
        $this->basename = 'admin.reguser';
        $this->addevents('oncontent');
        $this->section = 'users';
        $this->regstatus = false;
        $this->trusted = ['mail.ru', 'yandex.ru', 'gmail.com'];
        $this->addMap('blackhost', []);
    }

    public function getTitle(): string
    {
        return Lang::get('users', 'adduser');
    }

    public function getLogged()
    {
        return $this->getApp()->options->authcookie();
    }

    public function request(Context $context)
    {
        $response = $context->response;
        if (!$this->getApp()->options->usersenabled || !$this->getApp()->options->reguser) {
            return $response->forbidden();
        }

        parent::request($context);

        if (!empty($_GET['confirm'])) {
            $confirm = $_GET['confirm'];
            $email = $_GET['email'];
            Session::start('reguser-' . md5($this->getApp()->options->hash($email)));
            if (!isset($_SESSION['email']) || ($email != $_SESSION['email']) || ($confirm != $_SESSION['confirm'])) {
                if (!isset($_SESSION['email'])) {
                    session_destroy();
                }

                $this->regstatus = 'error';
                return;
            }

            $this->backurl = $_SESSION['backurl'];
            $users = Users::i();
            $id = $users->add(
                array(
                'password' => $_SESSION['password'],
                'name' => $_SESSION['name'],
                'email' => $_SESSION['email']
                )
            );

            session_destroy();
            if ($id) {
                $this->regstatus = 'ok';
                $expired = time() + 31536000;
                $cookie = Str::md5Uniq();
                $this->getApp()->options->user = $id;
                $this->getApp()->options->updategroup();
                $this->getApp()->options->setcookies($cookie, $expired);
            } else {
                $this->regstatus = 'error';
            }
        }
    }

    public function getContent(): string
    {
        $result = '';
        $theme = $this->theme;
        $lang = Lang::admin('users');

        if ($this->logged) {
            return $schema->admintheme->geterr($lang->logged . ' ' . $theme->link('/admin/', $lang->adminpanel));
        }

        if ($this->regstatus) {
            switch ($this->regstatus) {
            case 'ok':
                $backurl = $this->backurl;
                if (!$backurl) {
                    $backurl = UserGroups::i()->gethome($this->getApp()->options->group);
                }
                if (!Str::begin($backurl, 'http')) {
                    $backurl = $this->getApp()->site->url . $backurl;
                }
                return $theme->h($lang->successreg . ' ' . $theme->link($backurl, $lang->continue));

            case 'mail':
                return $theme->h($lang->waitconfirm);

            case 'error':
                $result.= $theme->h($lang->invalidregdata);
            }
        }

            $args = new Args();
            $args->email = isset($_POST['email']) ? $_POST['email'] : '';
            $args->name = isset($_POST['name']) ? $_POST['name'] : '';
            $args->action = $this->getApp()->site->url . '/admin/reguser/' . (!empty($_GET['backurl']) ? '?backurl=' : '');
            $result.= $theme->parseArg($this->getform(), $args);

        if (!empty($_GET['backurl'])) {
            //normalize
            $result = str_replace('&amp;backurl=', '&backurl=', $result);
            $result = str_replace('backurl=', 'backurl=' . urlencode($_GET['backurl']), $result);
            $result = str_replace('backurl%3D', 'backurl%3D' . urlencode(urlencode($_GET['backurl'])), $result);
        }

        $r = $this->onContent(['content' => $result]);
        return $r['content'];
    }

    public function createForm(): string
    {
        $lang = Lang::i('users');
        $theme = $this->theme;

        $form = new AdminForm();
        $form->title = $lang->regform;
        $form->action = '$action';
        $form->body = $theme->getInput('email', 'email', '$email', 'E-Mail');
        $form->body.= $theme->getInput('text', 'name', '$name', $lang->name);
        $form->submit = 'signup';

        $result = $form->gettml();
        $result .= $theme->parse($theme->templates['content.login']);
        return $result;
    }

    public function processForm()
    {
        $this->regstatus = 'error';
        try {
            if ($this->reguser($_POST['email'], $_POST['name'])) {
                $this->regstatus = 'mail';
            }
        } catch (\Exception $e) {
            return sprintf('<h4 class="red">%s</h4>', $e->getMessage());
        }
    }

    public function reguser(string $email, string $name)
    {
        $email = strtolower(trim($email));
        if (!Filter::ValidateEmail($email)) {
            return $this->error(Lang::get('comment', 'invalidemail'));
        }

        $host = substr($email, strpos($email, '@') + 1);
        if (!strpos($host, '.') || in_array($host, $this->blackhost)) {
            return $this->error(Lang::get('comment', 'invalidemail'));
        }

        $users = Users::i();
        if ($id = $users->emailExists($email)) {
            if ('comuser' != $users->getvalue($id, 'status')) {
                return $this->error(Lang::i()->invalidregdata);
            }
        }

        if (!in_array($host, $this->trusted)) {
                //host already validated but wi want to protect
                $host = $users->db->mysqli->real_escape_string($host);
            if (!$users->db->findId("email like '%@$host'")) {
                if (!$this->hostExists($host)) {
                    $this->blackhost[] = $host;
                    $this->save();
                        return $this->error(Lang::get('comment', 'invalidemail'));
                }
            }
        }

        Session::start('reguser-' . md5($this->getApp()->options->hash($email)));
        $_SESSION['email'] = $email;
        $_SESSION['name'] = $name;
        $confirm = Str::md5Rand();
        $_SESSION['confirm'] = $confirm;
        $password = Str::md5Uniq();
        $_SESSION['password'] = $password;
        $_SESSION['backurl'] = isset($_GET['backurl']) ? $_GET['backurl'] : '';
        session_write_close();

        $args = new Args();
        $args->name = $name;
        $args->email = $email;
        $args->confirm = $confirm;
        $args->password = $password;
        $args->confirmurl = $this->getApp()->site->url . '/admin/reguser/' . $this->getApp()->site->q . 'email=' . urlencode($email);

        Lang::usefile('mail');
        $lang = Lang::i('mailusers');
        $theme = Theme::i();

        $subject = $theme->parseArg($lang->subject, $args);
        $body = $theme->parseArg($lang->body, $args);

        Mailer::sendmail($this->getApp()->site->name, $this->getApp()->options->fromemail, $name, $email, $subject, $body);

        return true;
    }

    public function hostExists(string $host): bool
    {
        if ($records = dns_get_record($host, \DNS_ANY)) {
            return count($records);
        }

        return false;
    }
}
