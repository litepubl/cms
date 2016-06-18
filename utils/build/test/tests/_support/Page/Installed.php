<?php
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
