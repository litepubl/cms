<?php
/**
 * LitePubl CMS
 *
 * @copyright 2010 - 2017 Vladimir Yushko http://litepublisher.com/ http://litepublisher.ru/
 * @license   https://github.com/litepubl/cms/blob/master/LICENSE.txt MIT
 * @link      https://github.com/litepubl\cms
 * @version   7.08
  */

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
