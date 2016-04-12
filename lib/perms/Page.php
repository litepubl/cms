<?php
/**
 * Lite Publisher
 * Copyright (C) 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * Licensed under the MIT (LICENSE.txt) license.
 *
 */

namespace litepubl\perms;
use litepubl\view\Lang;

class Page extends \litepubl\core\Events implements \litepubl\view\ViewInterface
{
use \litepubl\view\EmptyViewTrait;

    public $perm;
public $url;
    private $formresult;

    protected function create() {
        parent::create();
        $this->basename = 'passwordpage';
        $this->formresult = '';
$this->url = '/check-password.php';
        $this->data['title'] = '';
    }

    private function checkspam($s) {
        if (!($s = @base64_decode($s))) {
return false;
}

        $sign = 'megaspamer';
        if (!strbegin($s, $sign)) {
return false;
}

        $timekey = (int)substr($s, strlen($sign));
        return time() < $timekey;
    }

    public function request($arg) {
        $this->cache = false;
        if (!isset($_POST) || !count($_POST)) {
return;
}

        $antispam = isset($_POST['antispam']) ? $_POST['antispam'] : '';
        if (!$this->checkspam($antispam)) {
return 403;
}

        $password = isset($_POST['password']) ? trim($_POST['password']) : '';
        if (!$password) {
return;
}

        if (!isset($this->perm)) {
            $idperm = isset($_GET['idperm']) ? (int)$_GET['idperm'] : 0;
            $perms = Perms::i();
            if (!$perms->itemexists($idperm)) {
return 403;
}

            $this->perm = tperm::i($idperm);
        }

        $backurl = isset($_GET['backurl']) ? $_GET['backurl'] : '';
        if ($this->perm->checkpassword($password)) {
            if ($backurl) {
litepubl::$urlmap->redir($backurl);
}
        } else {
            $this->formresult = Lang::i()->errpassword;
        }
    }

    public function gettitle() {
        return Lang::i('perms')->reqpassword;
    }

    public function getcont() {
        $result = $this->formresult == '' ? '' : sprintf('<h4>%s</h4>', $this->formresult);

        $args->antispam = base64_encode('megaspamer' . strtotime("+1 hour"));
        $args->remember = isset($_POST['remember']);
        $result.= $this->getSchema()->theme->parsearg($this->form, $args);
        return $result;
    }

public function getform($antispam, $remember) {
$form = litepubl::$cache->getString('perms-form');
if (!$form) {
$form = $this->createForm();
litepubl::$cache->setString('perms-form', $form);
}

return strtr($form, [
'$antispam' => $antispam,
'$remember' => $remember ? 'checked="checked"' : '',
]);
}

public function createForm() {
$theme = $this->getSchema()->theme;
$lang = Lang::i('perms');

return strtr($theme->templates['content.admin.form'], [
'$formtitle' => $lang->pwdaccess,
'$items' => $theme->getinput('password', 'password', '', $lang->password) .
$theme->getinput('checkbox', 'remember', '$remember', $lang->remember) .
$theme->getinput('hidden', 'antispam','$antispam' ),

'[submit=update]' => $theme->getsubmit($lang->send),
]);
}

}