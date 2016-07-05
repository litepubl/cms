<?php
/**
 * Lite Publisher CMS
 * @copyright  2010 - 2016 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link https://github.com/litepubl\cms
 * @version 7.00
 *
 */

namespace Page;

class Login extends Base
{
    use Singleton;

    public $url = '/admin/login/';
    public $email = '#form-login [name=email]';
    public $password = '#password-password';
    public $submit = '#submitbutton-log_in';
    private $admin;

    public function login()
    {
        $admin = $this->getAdmin();
        return $this->auth($admin->email, $admin->password);
    }

    public function auth($email, $password)
    {
        $i = $this->tester;
        $i->wantTo('log in');
        $i->fillField($this->email, $email);
        $i->fillField($this->password, $password);
        $i->click($this->submit);
        $i->checkError();
        return $this;
    }

    public function getAdmin()
    {
        if (!$this->admin) {
            $this->admin = $this->load('admin');
        }

        return $this->admin;
    }
}
