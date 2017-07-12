<?php

namespace litepubl\tests\acceptance;

class A10ContactsCest extends \Page\Base
{
    protected $email = 'input[name=email]';
    protected $message = '#editor-content';

    protected function test(\AcceptanceTester $i)
    {
        $data = $this->load('contacts');
        $i->wantTo('Click contacts on home page');
        $i->openPage('/');
        $i->click($data->title);
        $this->screenShot('contacts');
        $i->wantTo('Send form');
        $this->sendForm($data->email, $data->message);
        $i->wantTo('Send form with empty email');
        $this->sendForm('', $data->message);
        $i->wantTo('Send form without message');
        $this->sendForm($data->email, '');
    }

    protected function sendForm($email, $message)
    {
        $i = $this->tester;
        $i->fillField($this->email, $email);
        $i->fillField($this->message, $message);
        $i->click($this->updateButton);
        $i->checkError();
    }
}
