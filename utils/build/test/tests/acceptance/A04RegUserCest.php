<?php
namespace litepubl\tests\acceptance;

class A04RegUserCest extends A03PasswordCest
{
    protected $regUrl = '/admin/reguser/';
    protected $optionsUrl = '/admin/options/secure/';
    protected $groupsUrl= '/admin/users/options/';
    protected $boardUrl = '/admin/';
    protected $enabled = 'input[name=usersenabled]';
    protected $reguser = 'input[name=reguser]';
    protected $cmtCheckbox= 'input[name=idgroup-5]';
    protected $regEmail = '[name=email]';
    protected $name = '[name=name]';
    protected $regButton = '#submitbutton-signup';

    protected function test(\AcceptanceTester $i)
    {
        $i->wantTo('Enable user registration');
        $this->open($this->optionsUrl);
        $i->checkOption($this->enabled);
        $i->checkOption($this->reguser);
        $this->screenshot('options');
        $i->click($this->updateButton);
        $i->checkError();

        $i->wantTo('Add commentator to default group');
        $i->openPage($this->groupsUrl);
        $i->checkOption($this->cmtCheckbox);
        $this->screenshot('groups');
        $i->click($this->updateButton);
        $i->checkError();


        $i->wantTo('Register new user');$this->logout();
        $this->open($this->regUrl);
        $user = $this->load('reguser');
        $user->email = time() . $user->email;
        $i->fillField($this->regEmail, $user->email);
        $i->fillField($this->name, $user->name);
        $this->screenshot('regform');
        $this->removeLogs();
        $i->click($this->regButton);
        $i->checkError();
        $this->screenshot('confirm');
        $this->confirmEmail();
        $this->screenshot('confirmed');
        $i->wantTo('Logon as new user');
        $i->openPage($this->boardUrl);
        $i->checkError();
        $this->screenshot('logged');

        $i->wantTo('Check restore password');
        $this->logout();
        $this->removeLogs();
        $i->wantTo('Open restore password page');
        $i->openPage($this->url);
        $user->password = $this->restore($user->email);
        $i->wantTo('Login with new password');
        $i->openPage($this->loginUrl);
        $this->authAccount($user->email, $user->password);
        $this->logout();

    }
}
