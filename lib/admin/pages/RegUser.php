<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\admin\pages;
use litepubl\core\Session;
use litepubl\core\Users;
use litepubl\core\UserGroups;
use litepubl\view\Lang;
use litepubl\view\Filter;
use litepubl\view\Theme;
use litepubl\utils\Mailer;
use litepubl\core\Str;
use litepubl\view\Args;

class RegUser extends Form
{
    private $regstatus;
    private $backurl;

    protected function create() {
        parent::create();
        $this->basename = 'admin.reguser';
        $this->addevents('oncontent');
        $this->data['widget'] = '';
        $this->section = 'users';
        $this->regstatus = false;
    }

    public function getTitle() {
        return Lang::get('users', 'adduser');
    }

    public function getLogged() {
        return  $this->getApp()->options->authcookie();
    }

    public function request($arg) {
        if (! $this->getApp()->options->usersenabled || ! $this->getApp()->options->reguser) {
 return 403;
}


        parent::request($arg);

        if (!empty($_GET['confirm'])) {
            $confirm = $_GET['confirm'];
            $email = $_GET['email'];
            Session::start('reguser-' . md5( $this->getApp()->options->hash($email)));
            if (!isset($_SESSION['email']) || ($email != $_SESSION['email']) || ($confirm != $_SESSION['confirm'])) {
                if (!isset($_SESSION['email'])) session_destroy();
                $this->regstatus = 'error';
                return;
            }

            $this->backurl = $_SESSION['backurl'];

            $users = Users::i();
            $id = $users->add(array(
                'password' => $_SESSION['password'],
                'name' => $_SESSION['name'],
                'email' => $_SESSION['email']
            ));

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

    public function getContent() {
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
                    if (!$backurl) $backurl = UserGroups::i()->gethome( $this->getApp()->options->group);
                    if (!Str::begin($backurl, 'http')) $backurl =  $this->getApp()->site->url . $backurl;
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
            $args->action =  $this->getApp()->site->url . '/admin/reguser/' . (!empty($_GET['backurl']) ? '?backurl=' : '');
            $result.= $theme->parsearg($this->getform() , $args);

            if (!empty($_GET['backurl'])) {
                //normalize
                $result = str_replace('&amp;backurl=', '&backurl=', $result);
                $result = str_replace('backurl=', 'backurl=' . urlencode($_GET['backurl']) , $result);
                $result = str_replace('backurl%3D', 'backurl%3D' . urlencode(urlencode($_GET['backurl'])) , $result);
            }

            $this->callevent('oncontent', array(&$result
            ));
            return $result;
    }

    public function createform() {
        $lang = Lang::i('users');
        $theme = $this->theme;

        $form = new adminform();
        $form->title = $lang->regform;
        $form->action = '$action';
        $form->body = $theme->getinput('email', 'email', '$email', 'E-Mail');
        $form->body.= $theme->getinput('text', 'name', '$name', $lang->name);
        $form->submit = 'signup';

        $result = $form->gettml();
        $result.= $this->widget;
        return $result;
    }

    public function processForm() {
        $this->regstatus = 'error';
        try {
            if ($this->reguser($_POST['email'], $_POST['name'])) $this->regstatus = 'mail';
        }
        catch(Exception $e) {
            return sprintf('<h4 class="red">%s</h4>', $e->getMessage());
        }
    }

    public function reguser($email, $name) {
        $email = strtolower(trim($email));
        if (!Filter::ValidateEmail($email)) {
 return $this->error(Lang::get('comment', 'invalidemail'));
}



        if (substr_count($email, '.', 0, strpos($email, '@')) > 2) {
 return $this->error(Lang::get('comment', 'invalidemail'));
}



        $users = Users::i();
        if ($id = $users->emailexists($email)) {
            if ('comuser' != $users->getvalue($id, 'status')) {
 return $this->error(Lang::i()->invalidregdata);
}


        }

        Session::start('reguser-' . md5( $this->getApp()->options->hash($email)));
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
        $args->confirmurl =  $this->getApp()->site->url . '/admin/reguser/' .  $this->getApp()->site->q . 'email=' . urlencode($email);

        Lang::usefile('mail');
        $lang = Lang::i('mailusers');
        $theme = Theme::i();

        $subject = $theme->parsearg($lang->subject, $args);
        $body = $theme->parsearg($lang->body, $args);

        Mailer::sendmail( $this->getApp()->site->name,  $this->getApp()->options->fromemail, $name, $email, $subject, $body);

        return true;
    }

} 