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

class A11CommentCest extends \Page\Base
{
    public $comment = '#comment';
    public $email = 'input[name=email]';
    public $submit = '#submit-button';

    protected function test(\AcceptanceTester $i)
    {
        $this->logout();
        $data = $this->load('comment');

        $i->wantTo('Click post on home page');
        $i->openPage('/');
        $i->wantTo('Open first post');
        $i->click($this->postlink);
        $posturl = $i->grabFromCurrentUrl();
        $i->wantTo('Send anonimouse comment');
        $this->send($data->comment . time());
        $i->wantTo('Confirm comment');
        $i->waitForText($data->human, 7);
        $this->screenShot('confirm');
        $i->click($data->human);
        sleep(3);
        $i->checkError();

        $i->wantTo('Send empty comment');
        $this->send('');
        $i->checkError();
        $i->see($data->error);
        $this->screenShot('emptyerror');

        $i->wantTo('Close error dialog');
        $this->logUrl();
        $i->click('Ok');

        $i->wantTo('Send comment as admin');
        $i->click($data->login);
        sleep(2);
        $i->seeInCurrentUrl(urlencode($posturl));
        $this->login();

        $i->wantTo('Must be returned back to post');
        $i->seeCurrentUrlEquals($posturl);

        $this->send($data->comment2 . time());
        $i->wantTo('Check comment sent');
        $i->see($data->comment2);
    }

    protected function send(string $comment)
    {
        $i = $this->tester;
        //$i->fillField($this->email, $email);
        $i->fillField($this->comment, $comment);
        $this->screenshot('send');
        $i->click($this->submit);
        $i->checkError();
    }
}
