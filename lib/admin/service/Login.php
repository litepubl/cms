<?php

namespace litepubl\admin\service;
use litepubl\updater\Backuper;
use litepubl\view\Lang;
use litepubl\view\Args;

class Login extends \litepubl\admin\Menu
{

    public function getloginform() {
        $backuper = Backuper::i();
        //$backuper->filertype = 'ftp';
        if ($backuper->filertype == 'file') {
            return '';
        }

        $admin = $this->admintheme;
$lang = Lang::admin('service');
        $args = new Args();

        $result = $backuper->filertype == 'ssh2' ? $admin->h($lang->ssh2account) : $admin->h($lang->ftpaccount);
        $args->host = Html::getparam('host', '');
        $args->login = Html::getparam('login', '');
        $args->password = Html::getparam('pasword', '');
        return $result .
 $admin->parsearg('
[text=host]
 [text=login]
 [password=password]
', $args);
    }

    public function checkbackuper() {
        $backuper = Backuper::i();
        if ($backuper->filertype == 'file') {
            return true;
        }

        $host = Html::getparam('host', '');
        $login = Html::getparam('login', '');
        $password = Html::getparam('password', '');
        if (!$host  || !$login || !$password) {
            return '';
        }

        return $backuper->connect($host, $login, $password);
    }

}
