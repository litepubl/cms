<?php
/**
* Lite Publisher CMS
* @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
* @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
* @link https://github.com/litepubl\cms
* @version 6.15
**/

namespace litepubl\admin\pages;
use litepubl\core\Context;
use litepubl\core\Request;
use litepubl\core\Users;
use litepubl\core\UserGroups;
use litepubl\core\Session;
use litepubl\core\Str;
use litepubl\view\Args;
use litepubl\view\Lang;
use litepubl\admin\Form as AdminForm;

class Login extends Form
{

    protected function create() {
        parent::create();
        $this->basename = 'admin.loginform';
        $this->addevents('oncontent');
        $this->data['widget'] = '';
    }

    public function auth(Context $context) {
        if ($context->checkAttack()) {
 return;
}

        if (! $this->getApp()->options->authcookie()) {
$context->response->redir('/admin/login/');
}
    }

    private function logout(Context $context) {
$app = $this->getApp();
         $app->options->logout();
        setcookie('backurl', '', 0,  $app->site->subdir, false);
$context->response->cache = false;
$context->response->redir('/admin/login/');
    }

    //return error string message if not logged
    public static function autherror($email, $password) {
        Lang::admin();
        if (empty($email) || empty($password)) {
return Lang::get('login', 'empty');
}

        $iduser =  $this->getApp()->options->emailexists($email);
        if (!$iduser) {
            if (static ::confirm_reg($email, $password)) {
return;
}

            return Lang::get('login', 'unknownemail');
        }

        if ( $this->getApp()->options->authpassword($iduser, $password)) {
return;
}

        if (static ::confirm_restore($email, $password)) {
return;
}

        //check if password is empty and neet to restore password
        if ($iduser == 1) {
            if (! $this->getApp()->options->password) {
return Lang::get('login', 'torestorepass');
}
        } else {
            if (!Users::i()->getpassword($iduser)) {
return Lang::get('login', 'torestorepass');
}
        }

        return Lang::get('login', 'error');
    }

    public function request(Context $context) {
        if ($context->itemRoute['arg'] == 'out') {
return $this->logout($context);
}

        parent::request($context);
        $this->section = 'login';

        if (!isset($_POST['email']) || !isset($_POST['password'])) {
return;
}

        $email = trim($_POST['email']);
        $password = trim($_POST['password']);

        if ($mesg = static ::autherror($email, $password)) {
            $this->formresult = $this->admintheme->geterr($mesg);
            return;
        }

        $expired = isset($_POST['remember']) ? time() + 31536000 : time() + 8 * 3600;
        $cookie = Str::md5Uniq();
$app = $this->getApp();
$app->options->setcookies($cookie, $expired);
         $app->options->setcookie('litepubl_regservice', 'email', $expired);

        $url = !empty($_GET['backurl']) ? $_GET['backurl'] : (!empty($_GET['amp;backurl']) ? $_GET['amp;backurl'] : (isset($_COOKIE['backurl']) ? $_COOKIE['backurl'] : ''));

        if ($url && Str::begin($url,  $app->site->url)) {
$url = substr($url, strlen( $app->site->url));
}

        if ($url && (Str::begin($url, '/admin/login/') || Str::begin($url, '/admin/password/'))) {
$url = false;
}

        if (!$url) {
            $url = '/admin/';
            if ( $app->options->group != 'admin') {
                $groups = UserGroups::i();
                $url = $groups->gethome( $this->getApp()->options->group);
            }
        }

         $app->options->setcookie('backurl', '', 0);
$context->response->redir($url);
    }

    public function createform() {
        $result = $this->widget;
        $theme = $this->theme;
        $args = new Args();

        if ( $this->getApp()->options->usersenabled &&  $this->getApp()->options->reguser) {
            $lang = Lang::admin('users');
            $form = new adminform($args);
            $form->title = $lang->regform;
            $form->action = '$site.url/admin/reguser/{$site.q}backurl=';
            $form->body = $theme->getinput('email', 'email', '', 'E-Mail');
            $form->body.= $theme->getinput('text', 'name', '', $lang->name);
            $form->submit = 'signup';
            $result.= $form->get();
        }

        $lang = Lang::admin('login');
        $form = new adminform($args);
        $form->title = $lang->emailpass;
        $form->body = $theme->getinput('email', 'email', '$email', 'E-Mail');
        $form->body.= $theme->getinput('password', 'password', '', $lang->password);
        $form->body.= $theme->getinput('checkbox', 'remember', '$remember', $lang->remember);
        $form->submit = 'log_in';
        $result.= $form->gettml();

        $form = new adminform($args);
        $form->title = $lang->lostpass;
        $form->action = '$site.url/admin/password/';
        $form->target = '_blank';
        $form->inline = true;
        $form->body = $theme->getinput('email', 'email', '', 'E-Mail');
        $form->submit = 'sendpass';
        $result.= $form->get();

        return $result;
    }

    public function getContent() {
        $result = $this->getform();

        $args = new Args();
        $args->email = isset($_POST['email']) ? trim(strip_tags($_POST['email'])) : '';
        $args->remember = isset($_POST['remember']);
        $result = $this->theme->parsearg($result, $args);

        $result = str_replace('&amp;backurl=', '&backurl=', $result);
        if (!empty($_GET['backurl'])) {
            $result = str_replace('backurl=', 'backurl=' . urlencode($_GET['backurl']) , $result);
            //support ulogin
            $result = str_replace('backurl%3D', 'backurl%3D' . urlencode(urlencode($_GET['backurl'])) , $result);
        } else {
            $result = str_replace('&backurl=', '', $result);
            $result = str_replace('backurl=', '', $result);
            //support ulogin
            $result = str_replace('%3Fbackurl%3D', '', $result);
        }

        $this->callevent('oncontent', array(&$result
        ));
        return $result;
    }

    public static function confirm_reg($email, $password) {
        if (! $this->getApp()->options->usersenabled || ! $this->getApp()->options->reguser) {
 return false;
}



        Ssession::start('reguser-' . md5( $this->getApp()->options->hash($email)));
        if (!isset($_SESSION['email']) || ($email != $_SESSION['email']) || ($password != $_SESSION['password'])) {
            if (isset($_SESSION['email'])) {
                session_write_close();
            } else {
                session_destroy();
            }
            return false;
        }

        $users = Users::i();
        $id = $users->add(array(
            'password' => $password,
            'name' => $_SESSION['name'],
            'email' => $email
        ));

        session_destroy();

        if ($id) {
             $this->getApp()->options->user = $id;
             $this->getApp()->options->updategroup();
        }

        return $id;
    }

    public static function confirm_restore($email, $password) {
        Session::start('password-restore-' . md5( $this->getApp()->options->hash($email)));
        if (!isset($_SESSION['email']) || ($email != $_SESSION['email']) || ($password != $_SESSION['password'])) {
            if (isset($_SESSION['email'])) {
                session_write_close();
            } else {
                session_destroy();
            }
            return false;
        }

        session_destroy();
        if ($email == strtolower(trim( $this->getApp()->options->email))) {
             $this->getApp()->options->changepassword($password);
            return 1;
        } else {
            $users = Users::i();
            if ($id = $users->emailexists($email)) $users->changepassword($id, $password);
            return $id;
        }
    }

}