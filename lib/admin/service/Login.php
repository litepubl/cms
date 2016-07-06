<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.00
  */

namespace litepubl\admin\service;

use litepubl\updater\Backuper;
use litepubl\view\Args;
use litepubl\view\Lang;

class Login extends \litepubl\admin\Menu
{

    public function getLoginform()
    {
        $backuper = Backuper::i();
        if ($backuper->filertype == 'file') {
            return '';
        }

        $admin = $this->admintheme;
        $lang = Lang::admin('service');
        $args = new Args();

        $result = $backuper->filertype == 'ssh2' ? $admin->h($lang->ssh2account) : $admin->h($lang->ftpaccount);
        $args->host = $this->getparam('host', '');
        $args->login = $this->getparam('login', '');
        $args->password = $this->getparam('pasword', '');
        return $result . $admin->parseArg(
            '
[text=host]
 [text=login]
 [password=password]
', $args
        );
    }

    public function checkbackuper()
    {
        $backuper = Backuper::i();
        if ($backuper->filertype == 'file') {
            return true;
        }

        $host = $this->getparam('host', '');
        $login = $this->getparam('login', '');
        $password = $this->getparam('password', '');
        if (!$host || !$login || !$password) {
            return '';
        }

        return $backuper->connect($host, $login, $password);
    }
}
