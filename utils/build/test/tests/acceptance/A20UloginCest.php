<?php

namespace litepubl\tests\acceptance;

class A20UloginCest extends \Page\Ulogin
{
    private $comment;
    private $regUser;

    public function _inject(A11CommentCest  $comment, A04RegUserCest $regUser)
    {
        $this->comment = $comment;
        $this->regUser = $regUser;
    }

    protected function test(\AcceptanceTester $i)
    {
        $i->wantTo('Test ulogin plugin');
        $this->regUser->_enableUsers($i);
        $this->install();
        $this->screenshot('install');
        $this->logout();

        $i->wantTo('Send comment as authorized user');
        $i->openPage('/');
        $i->wantTo('Open first post');
        $comment = $this->comment;
        $data = $comment->load('comment');
        $i->click($comment->postlink);
        $i->waitForJs('return (\'litepubl\' in window) && (\'authdialog\' in window.litepubl);', 7);
        $i->wantTo('Open auth dialog');
        $this->screenshot('post');
        $url = $i->grabFromCurrentUrl();
        $i->click($data->login);
        $this->click();
        if (!static::$logged) {
                $this->screenshot('dialog');
                $this->auth();
        }

        $this->waitForcloseDialog();
        $i->seeCurrentUrlEquals($url);
        $i->reloadPage();
        sleep(4);
        $text = $data->comment . time();
        $i->fillField($comment->comment, $text);
        $this->screenshot('comment');
        $i->click($comment->submit);
        sleep(5);
        $i->checkError();
        $i->wantTo('Check comment sent');
        $i->waitForText($text, 6);
        $this->screenshot('comment');

        $i->wantTo('test ulogin without dialog box');
        $this->logout();
        sleep(2);
        codecept_debug($i->grabFromCurrentUrl());
        $this->screenshot('login');
        $this->click();
        $i->waitForUrlChanged(10);
        codecept_debug($i->grabFromCurrentUrl());
        $this->logout();

        $this->uninstallPlugin('ulogin');
        $this->screenshot('uninstall');
        $this->deleteUser();
    }
}
