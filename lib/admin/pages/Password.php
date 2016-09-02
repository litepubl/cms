<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.07
  */

namespace litepubl\admin\pages;

use litepubl\admin\Form as AdminForm;
use litepubl\admin\UList;
use litepubl\core\Session;
use litepubl\core\Str;
use litepubl\core\Users;
use litepubl\utils\Mailer;
use litepubl\view\Args;
use litepubl\view\Lang;
use litepubl\view\Theme;

class Password extends Form
{

    protected function create()
    {
        parent::create();
        $this->section = 'password';
    }

    public function createForm(): string
    {
        $form = new AdminForm();
        $form->id = 'form-lostpass';
        $form->title = Lang::admin('password')->enteremail;
        $form->body = $this->theme->getinput('email', 'email', '', 'E-Mail');
        $form->submit = 'send';
        return $form->gettml();
    }

    public function getContent(): string
    {
        $theme = $this->theme;
        $lang = Lang::admin('password');

        if (empty($_GET['confirm'])) {
            return $this->getForm();
        }

        $email = $_GET['email'];
        $confirm = $_GET['confirm'];
        Session::start('password-restore-' . md5($this->getApp()->options->hash($email)));

        if (!isset($_SESSION['email']) || ($email != $_SESSION['email']) || ($confirm != $_SESSION['confirm'])) {
            if (Session::expired(15) || !isset($_SESSION['email'])) {
                session_destroy();
            }

            return $theme->h($lang->notfound);
        }

        $password = $_SESSION['password'];
        session_destroy();

        if ($id = $this->getIdUser($email)) {
            if ($id == 1) {
                $this->getApp()->options->changePassword($password);
            } else {
                Users::i()->changePassword($id, $password);
            }

            $admin = $this->admintheme;
            $ulist = new UList($admin);
            return $admin->getSection(
                $lang->uselogin, $ulist->get(
                    [
                    $theme->link('/admin/login/', $lang->controlpanel) ,
                    'E-Mail' => sprintf('<span class="email">%s</span>', $email) ,
                    $lang->password => sprintf('<span class="password">%s</span>', $password) ,
                    ]
                )
            );
        } else {
            return $theme->h($lang->notfound);
        }
    }

    public function getIdUser(string $email): int
    {
        if (empty($email)) {
            return 0;
        }

        if ($email == strtolower(trim($this->getApp()->options->email))) {
            return 1;
        }

        return Users::i()->emailExists($email);
    }

    public function processForm()
    {
        try {
            $this->restore($_POST['email']);
        } catch (\Exception $e) {
            return $this->admintheme->geterr($e->getMessage());
        }

        return $this->admintheme->success(Lang::admin('password')->success);
    }

    public function restore(string $email)
    {
        $lang = Lang::admin('password');
        $email = strtolower(trim($email));
        if (empty($email)) {
            return $this->error($lang->error);
        }

        $id = $this->getIdUser($email);
        if (!$id) {
            return $this->error($lang->error);
        }

        $args = new Args();

        Session::start('password-restore-' . md5($this->getApp()->options->hash($email)));
        if (Session::expired(15)) {
            session_destroy();
            return $this->error($lang->outofcount);
        }

        if (!isset($_SESSION['count'])) {
            $_SESSION['count'] = 1;
        } else {
            if ($_SESSION['count']++ > 3) {
                return $this->error($lang->outofcount);
            }
        }

        $_SESSION['email'] = $email;
        $password = Str::md5Uniq();
        $_SESSION['password'] = $password;
        $_SESSION['confirm'] = Str::md5Rand();
        $args->confirm = $_SESSION['confirm'];
        session_write_close();

        $args->email = urlencode($email);
        if ($id == 1) {
            $name = $this->getApp()->site->author;
        } else {
            $item = Users::i()->getitem($id);
            $args->add($item);
            $name = $item['name'];
        }

        $args->password = $password;
        Lang::usefile('mail');
        $lang = Lang::i('mailpassword');
        $theme = $this->theme;
        $subject = $theme->parseArg($lang->subject, $args);
        $body = $theme->parseArg($lang->body, $args);

        Mailer::sendmail($this->getApp()->site->name, $this->getApp()->options->fromemail, $name, $email, $subject, $body);
        return true;
    }
}
