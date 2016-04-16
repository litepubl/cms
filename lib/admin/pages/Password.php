<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\admin\pages;
use litepubl\admin\Form as AdminForm;
use litepubl\admin\UList;
use litepubl\view\Lang;
use litepubl\view\Theme;
use litepubl\core\Session;
use litepubl\core\Users;
use litepubl\utils\Mailer;

class Password extends Form
{

    protected function create() {
        parent::create();
        $this->section = 'password';
    }

    public function createForm() {
        $form = new AdminForm();
        $form->title = tlocal::admin('password')->enteremail;
        $form->body = $this->theme->getinput('email', 'email', '', 'E-Mail');
        $form->submit = 'send';
        return $form->gettml();
    }

    public function getcontent() {
        $theme = $this->theme;
        $lang = tlocal::admin('password');

        if (empty($_GET['confirm'])) {
            return $this->getform();
        }

        $email = $_GET['email'];
        $confirm = $_GET['confirm'];
        Session::start('password-restore-' . md5(litepubl::$options->hash($email)));

        if (!isset($_SESSION['email']) || ($email != $_SESSION['email']) || ($confirm != $_SESSION['confirm'])) {
            if (!isset($_SESSION['email'])) {
                session_destroy();
            }

            return $theme->h($lang->notfound);
        }

        $password = $_SESSION['password'];
        session_destroy();

        if ($id = $this->getiduser($email)) {
            if ($id == 1) {
                litepubl::$options->changepassword($password);
            } else {
                Users::i()->changepassword($id, $password);
            }

            $admin = $this->admintheme;
            $ulist = new UList($admin);
            return $admin->getsection($lang->uselogin, $ulist->get(array(
                $theme->link('/admin/login/', $lang->controlpanel) ,
                'E-Mail' => $email,
                $lang->password => $password
            )));
        } else {
            return $theme->h($lang->notfound);
        }
    }

    public function getiduser($email) {
        if (empty($email)) return false;
        if ($email == strtolower(trim(litepubl::$options->email))) return 1;
        return Users::i()->emailexists($email);
    }

    public function processform() {
        try {
            $this->restore($_POST['email']);
        }
        catch(Exception $e) {
            return sprintf('<h4 class="red">%s</h4>', $e->getMessage());
        }

        return $this->admintheme->success(Lang::admin()->success);
    }

    public function restore($email) {
        $lang = tlocal::admin('password');
        $email = strtolower(trim($email));
        if (empty($email)) return $this->error($lang->error);
        $id = $this->getiduser($email);
        if (!$id) return $this->error($lang->error);

        $args = new targs();

        Session::start('password-restore-' . md5(litepubl::$options->hash($email)));
        if (!isset($_SESSION['count'])) {
            $_SESSION['count'] = 1;
        } else {
            if ($_SESSION['count']++ > 3) return $this->error($lang->outofcount);
        }

        $_SESSION['email'] = $email;
        $password = md5uniq();
        $_SESSION['password'] = $password;
        $_SESSION['confirm'] = md5rand();
        $args->confirm = $_SESSION['confirm'];
        session_write_close();

        $args->email = urlencode($email);
        if ($id == 1) {
            $name = litepubl::$site->author;
        } else {
            $item = Users::i()->getitem($id);
            $args->add($item);
            $name = $item['name'];
        }

        $args->password = $password;
        tlocal::usefile('mail');
        $lang = tlocal::i('mailpassword');
        $theme = Theme::i();

        $subject = $theme->parsearg($lang->subject, $args);
        $body = $theme->parsearg($lang->body, $args);

        Mailer::sendmail(litepubl::$site->name, litepubl::$options->fromemail, $name, $email, $subject, $body);
        return true;
    }

} //class