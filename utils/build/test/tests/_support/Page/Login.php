<?php
namespace Page;

class Login
{
    // include url of current page
    public static $url = '/admin/login/';
      public static $email = '#form-login [name=email]';
public static $password = '#password-password';
      public static $submit = '#submitbutton-log_in';

    protected $tester;

    public function __construct(\AcceptanceTester $I)
    {
        $this->tester = $I;
    }

public function login($email, $password)
{
$i = $this->tester;
$i->wantTo('log in');
$i->openPage(static::$url);
$i->fillField(static::$email, $email);
$i->fillField(static::$password, $password);
$i->click(static::$submit);
$i->checkError();

return $this;
}

    /**
     * Basic route example for your current URL
     * You can append any additional parameter to URL
     * and use it in tests like: Page\Edit::route('/123-post');
     */
    public static function route($param)
    {
        return static::$URL.$param;
    }


}
