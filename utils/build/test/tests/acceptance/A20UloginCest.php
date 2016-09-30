<?php

namespace litepubl\tests\acceptance;

class A20UloginCest extends \Page\Ulogin
{
private $comment;

public function _inject(A11CommentCest  $comment)
{
$this->comment = $comment;
}

    protected function test(\AcceptanceTester $i)
    {
$i->wantTo('Test ulogin plugin');
$this->installPlugin('ulogin');
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
$i->click($data->login);
$this->click();
$this->screenshot('dialog');
$this->auth();
$this->waitForcloseDialog();
$i->reloadPage();
$text = $data->comment . time();
$i->fillField($comment->comment, $text);
$this->screenshot('comment');
$i->click($comment->submit);
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