<?php
/**
 * Lite Publisher CMS
 *
 * @copyright 2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.06
  */

namespace Page;

use test\config;

class Installed extends Base
{
    public $url = '/';
    public $email = '#email';
    public $password = '#password';
    public $link = '#admin-login';

    public function saveAccount()
    {
        $i = $this->tester;
        $i->wantTo('Save admin account');

        $data = [
        'email' => $i->grabTextFrom($this->email),
        'password' => $i->grabTextFrom($this->password),
        ];

        config::save('admin', $data);
        return $this;
    }
}
